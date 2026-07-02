<?php

use App\Models\Job;
use App\Models\User;

test('muhitaji can browse services web page', function () {
    $client = User::factory()->muhitaji()->create();
    $listing = Job::factory()->serviceListing()->create([
        'title' => 'Usafi wa ofisi',
        'title_sw' => 'Usafi wa ofisi',
        'title_en' => 'Office cleaning',
    ]);

    $this->actingAs($client)
        ->get(route('services.index'))
        ->assertOk()
        ->assertSee('Huduma za Watoa Huduma', false)
        ->assertSee(route('services.show', $listing), false);
});

test('mfanyakazi cannot access services web page', function () {
    $worker = User::factory()->mfanyakazi()->create();

    $this->actingAs($worker)
        ->get(route('services.index'))
        ->assertForbidden();
});

test('muhitaji can view service detail and book redirects to fund page', function () {
    $client = User::factory()->muhitaji()->create();
    $listing = Job::factory()->serviceListing()->create(['price' => 35000]);

    $this->actingAs($client)
        ->get(route('services.show', $listing))
        ->assertOk()
        ->assertSee($listing->title, false)
        ->assertSee('Chagua Mtoa Huduma', false);

    $response = $this->actingAs($client)
        ->post(route('services.book', $listing));

    $booking = Job::query()->serviceBookings()->where('user_id', $client->id)->first();
    expect($booking)->not->toBeNull()
        ->and($booking->status)->toBe(Job::S_AWAITING_PAYMENT);

    $response->assertRedirect(route('jobs.fund', $booking));
});
