<?php

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\PrivateMessage;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ServiceBookedNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

test('service booking e2e: book fund notify chat accept submit complete', function () {
    Notification::fake();
    Setting::set('commission_rate', 10);

    $client = User::factory()->muhitaji()->create(['name' => 'Asha Mteja']);
    $worker = User::factory()->mfanyakazi()->create();
    $listing = Job::factory()->serviceListing()->create([
        'user_id' => $worker->id,
        'title' => 'Usafi wa Nyumba',
        'price' => 50000,
    ]);

    Sanctum::actingAs($client);
    $bookResponse = $this->postJson("/api/services/{$listing->id}/book");
    $bookResponse->assertCreated();
    $bookingId = $bookResponse->json('data.booking.id');

    $client->ensureWallet()->update(['balance' => 200000]);
    $this->actingAs($client)
        ->post(route('jobs.fund.wallet', $bookingId))
        ->assertRedirect();

    $booking = Job::find($bookingId)->fresh();
    expect($booking->status)->toBe(Job::S_FUNDED)
        ->and($booking->engagement_type)->toBe(Job::ENGAGEMENT_SERVICE_BOOKING)
        ->and($booking->accepted_worker_id)->toBe($worker->id);

    Notification::assertSentTo(
        $worker,
        ServiceBookedNotification::class,
        function (ServiceBookedNotification $notification) use ($client) {
            $data = $notification->toArray($client);

            return ($data['type'] ?? '') === 'service_booked'
                && str_contains($data['message'], 'Umeajiriwa na '.$client->name)
                && str_contains($data['message'], 'Usafi wa Nyumba');
        }
    );

    Sanctum::actingAs($worker);
    $assigned = $this->getJson('/api/worker/assigned')->assertOk();
    $pendingIds = collect($assigned->json('pending_jobs'))->pluck('id')->map(fn ($id) => (int) $id);
    expect($pendingIds)->toContain($bookingId);

    $pendingBooking = collect($assigned->json('pending_jobs'))
        ->first(fn ($j) => (int) $j['id'] === $bookingId);
    expect($pendingBooking['engagement_type'] ?? null)->toBe(Job::ENGAGEMENT_SERVICE_BOOKING);

    $this->actingAs($client)->get(route('chat.show', $booking))->assertOk();
    $this->actingAs($worker)->get(route('chat.show', $booking))->assertOk();
    expect(PrivateMessage::where('work_order_id', $booking->id)->exists())->toBeTrue();

    $this->actingAs($worker)->post(route('jobs.worker.accept', $booking))->assertRedirect();
    expect($booking->fresh()->status)->toBe(Job::S_IN_PROGRESS);

    $this->actingAs($worker)->post(route('jobs.worker.submit', $booking))->assertRedirect();
    expect($booking->fresh()->status)->toBe(Job::S_SUBMITTED);

    $this->actingAs($client)->post(route('jobs.client.confirm', $booking))->assertRedirect();
    $completed = $booking->fresh();
    expect($completed->status)->toBe(Job::S_COMPLETED)
        ->and($completed->confirmed_at)->not->toBeNull()
        ->and($completed->release_amount)->toBe(45000);

    expect($worker->ensureWallet()->fresh()->balance)->toBe(45000);
});

test('classic muhitaji job flow regression is unaffected by service bookings', function () {
    Notification::fake();

    $client = User::factory()->muhitaji()->create();
    $worker = User::factory()->mfanyakazi()->create();
    $job = Job::factory()->jobRequest()->create([
        'user_id' => $client->id,
        'price' => 50000,
        'status' => Job::S_OPEN,
    ]);

    $this->actingAs($worker)->post(route('jobs.apply', $job), [
        'proposed_amount' => 48000,
        'message' => 'Naweza kufanya',
    ])->assertRedirect();

    $application = JobApplication::where('work_order_id', $job->id)
        ->where('worker_id', $worker->id)
        ->first();
    expect($application)->not->toBeNull();

    $this->actingAs($client)->post(route('applications.select', [$job, $application]))
        ->assertRedirect();

    $job->refresh();
    expect($job->status)->toBe(Job::S_AWAITING_PAYMENT)
        ->and($job->engagement_type)->toBe(Job::ENGAGEMENT_JOB_REQUEST);

    $client->ensureWallet()->update(['balance' => 100000]);
    $this->actingAs($client)->post(route('jobs.fund.wallet', $job))->assertRedirect();

    $job->refresh();
    expect($job->status)->toBe(Job::S_FUNDED)
        ->and($job->accepted_worker_id)->toBe($worker->id);

    Notification::assertNotSentTo($worker, ServiceBookedNotification::class);

    Sanctum::actingAs($worker);
    $assigned = $this->getJson('/api/worker/assigned')->assertOk();
    $pendingIds = collect($assigned->json('pending_jobs'))->pluck('id')->map(fn ($id) => (int) $id);
    expect($pendingIds)->toContain($job->id);

    $this->actingAs($worker)->post(route('jobs.worker.accept', $job))->assertRedirect();
    expect($job->fresh()->status)->toBe(Job::S_IN_PROGRESS);
});

test('admin jobs panel shows engagement type for listing and booking', function () {
    $admin = User::factory()->admin()->create();
    $worker = User::factory()->mfanyakazi()->create();
    $client = User::factory()->muhitaji()->create();

    $listing = Job::factory()->serviceListing()->create(['user_id' => $worker->id]);
    $booking = Job::factory()->serviceBooking()->forListing($listing)->create([
        'user_id' => $client->id,
        'selected_worker_id' => $worker->id,
        'accepted_worker_id' => $worker->id,
        'status' => Job::S_FUNDED,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.jobs', ['engagement' => Job::ENGAGEMENT_SERVICE_BOOKING]))
        ->assertOk()
        ->assertSee('Agizo la Huduma')
        ->assertSee($booking->title);

    $this->actingAs($admin)
        ->get(route('admin.jobs', ['engagement' => Job::ENGAGEMENT_SERVICE_LISTING]))
        ->assertOk()
        ->assertSee('Tangazo la Huduma')
        ->assertSee($listing->title);

    $this->actingAs($admin)
        ->get(route('admin.job.details', $booking))
        ->assertOk()
        ->assertSee('Agizo la Huduma')
        ->assertSee((string) $listing->id);
});
