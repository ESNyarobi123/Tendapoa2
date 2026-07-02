<?php

use App\Models\Job;
use App\Models\PrivateMessage;
use App\Models\User;
use App\Notifications\ServiceBookedNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

test('funding service booking moves job to funded and notifies worker', function () {
    Notification::fake();

    $client = User::factory()->muhitaji()->create();
    $worker = User::factory()->mfanyakazi()->create();
    $listing = Job::factory()->serviceListing()->create([
        'user_id' => $worker->id,
        'price' => 55000,
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
        ->and($booking->funded_at)->not->toBeNull()
        ->and($booking->accepted_worker_id)->toBe($worker->id)
        ->and($booking->escrow_amount)->toBe(55000);

    Notification::assertSentTo($worker, ServiceBookedNotification::class, function (ServiceBookedNotification $n) use ($client, $worker) {
        $data = $n->toArray($worker);

        return str_contains($data['message'], 'Umeajiriwa na '.$client->name)
            && ($data['type'] ?? '') === 'service_booked';
    });

    expect(PrivateMessage::where('work_order_id', $booking->id)->count())->toBeGreaterThan(0);
});

test('client and worker can access chat after service booking is funded', function () {
    Notification::fake();

    $client = User::factory()->muhitaji()->create();
    $worker = User::factory()->mfanyakazi()->create();
    $listing = Job::factory()->serviceListing()->create(['user_id' => $worker->id, 'price' => 40000]);

    Sanctum::actingAs($client);
    $bookingId = $this->postJson("/api/services/{$listing->id}/book")
        ->assertCreated()
        ->json('data.booking.id');

    $client->ensureWallet()->update(['balance' => 150000]);
    $this->actingAs($client)->post(route('jobs.fund.wallet', $bookingId));

    $booking = Job::find($bookingId);

    $this->actingAs($client)
        ->get(route('chat.show', $booking))
        ->assertOk();

    $this->actingAs($worker)
        ->get(route('chat.show', $booking))
        ->assertOk();
});

test('classic job funding still sends escrow dm without service booked notification', function () {
    Notification::fake();

    $client = User::factory()->muhitaji()->create();
    $worker = User::factory()->mfanyakazi()->create();
    $job = Job::factory()->jobRequest()->create([
        'user_id' => $client->id,
        'price' => 50000,
        'status' => Job::S_AWAITING_PAYMENT,
        'selected_worker_id' => $worker->id,
        'agreed_amount' => 50000,
    ]);

    $client->ensureWallet()->update(['balance' => 100000]);

    $this->actingAs($client)->post(route('jobs.fund.wallet', $job));

    Notification::assertNotSentTo($worker, ServiceBookedNotification::class);
    expect(PrivateMessage::where('work_order_id', $job->id)->exists())->toBeTrue();
});
