<?php

use App\Models\Category;
use App\Models\Job;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('muhitaji can browse service listings', function () {
    $client = User::factory()->muhitaji()->create(['lat' => -6.9, 'lng' => 39.2]);
    $listing = Job::factory()->serviceListing()->create([
        'title' => 'Usafi wa nyumba',
        'status' => 'posted',
    ]);
    Job::factory()->jobRequest()->create(['title' => 'Kazi ya muhitaji']);

    Sanctum::actingAs($client);

    $response = $this->getJson('/api/services');

    $response->assertOk()
        ->assertJsonPath('success', true);

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($listing->id)
        ->and($ids)->not->toContain(Job::where('engagement_type', Job::ENGAGEMENT_JOB_REQUEST)->value('id'));
});

test('services list can filter by category slug', function () {
    $client = User::factory()->muhitaji()->create();
    $cat = Category::factory()->create(['slug' => 'cleaning']);
    $match = Job::factory()->serviceListing()->create(['category_id' => $cat->id]);
    Job::factory()->serviceListing()->create();

    Sanctum::actingAs($client);

    $response = $this->getJson('/api/services?category=cleaning');

    $response->assertOk();
    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->all())->toBe([$match->id]);
});

test('muhitaji can view service listing details with provider', function () {
    $client = User::factory()->muhitaji()->create();
    $listing = Job::factory()->serviceListing()->create(['price' => 45000]);

    Sanctum::actingAs($client);

    $this->getJson("/api/services/{$listing->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $listing->id)
        ->assertJsonPath('data.price', 45000)
        ->assertJsonStructure(['data' => ['provider' => ['id', 'name']]]);
});

test('muhitaji can book service and receives payment instructions', function () {
    $client = User::factory()->muhitaji()->create();
    $listing = Job::factory()->serviceListing()->create(['price' => 60000]);

    Sanctum::actingAs($client);

    $response = $this->postJson("/api/services/{$listing->id}/book", [
        'message' => 'Nahitaji huduma kesho asubuhi',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.booking.status', Job::S_AWAITING_PAYMENT)
        ->assertJsonPath('data.booking.engagement_type', Job::ENGAGEMENT_SERVICE_BOOKING)
        ->assertJsonPath('data.booking.source_listing_id', $listing->id)
        ->assertJsonPath('data.booking.selected_worker_id', $listing->user_id)
        ->assertJsonPath('data.booking.user_id', $client->id)
        ->assertJsonPath('payment.amount', 60000)
        ->assertJsonStructure(['payment' => ['fund_wallet_url', 'fund_external_url', 'web_fund_url']]);

    $bookingId = $response->json('data.booking.id');
    expect(Job::find($bookingId)->isServiceBooking())->toBeTrue();
});

test('worker cannot browse services api', function () {
    $worker = User::factory()->mfanyakazi()->create();
    Job::factory()->serviceListing()->create();

    Sanctum::actingAs($worker);

    $this->getJson('/api/services')
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

test('worker cannot book a service', function () {
    $worker = User::factory()->mfanyakazi()->create();
    $listing = Job::factory()->serviceListing()->create();

    Sanctum::actingAs($worker);

    $this->postJson("/api/services/{$listing->id}/book")
        ->assertForbidden()
        ->assertJsonPath('message', 'Mteja tu anaweza kuagiza huduma.');
});

test('booking closed listing returns 422', function () {
    $client = User::factory()->muhitaji()->create();
    $listing = Job::factory()->serviceListing()->create(['status' => Job::S_COMPLETED]);

    Sanctum::actingAs($client);

    $this->postJson("/api/services/{$listing->id}/book")
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('duplicate active booking for same listing is blocked', function () {
    $client = User::factory()->muhitaji()->create();
    $listing = Job::factory()->serviceListing()->create();

    Sanctum::actingAs($client);

    $this->postJson("/api/services/{$listing->id}/book")->assertCreated();

    $this->postJson("/api/services/{$listing->id}/book")
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('hidden service listing cannot be booked', function () {
    $client = User::factory()->muhitaji()->create();
    $listing = Job::factory()->serviceListing()->create(['hidden_at' => now()]);

    Sanctum::actingAs($client);

    $this->postJson("/api/services/{$listing->id}/book")
        ->assertStatus(422);
});

test('client cannot book their own service listing', function () {
    $worker = User::factory()->mfanyakazi()->create();
    $listing = Job::factory()->serviceListing()->create(['user_id' => $worker->id]);

    Sanctum::actingAs($worker);

    $this->postJson("/api/services/{$listing->id}/book")
        ->assertForbidden();
});
