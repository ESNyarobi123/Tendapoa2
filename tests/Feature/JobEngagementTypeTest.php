<?php

use App\Models\Job;
use App\Models\User;

test('default job factory creates job request for muhitaji', function () {
    $job = Job::factory()->create();

    expect($job->engagement_type)->toBe(Job::ENGAGEMENT_JOB_REQUEST)
        ->and($job->poster_type)->toBe('muhitaji')
        ->and($job->source_listing_id)->toBeNull()
        ->and($job->isJobRequest())->toBeTrue()
        ->and($job->isServiceListing())->toBeFalse()
        ->and($job->isServiceBooking())->toBeFalse();

    expect($job->user->role)->toBe('muhitaji');
});

test('service listing factory creates worker catalog entry', function () {
    $listing = Job::factory()->serviceListing()->create();

    expect($listing->engagement_type)->toBe(Job::ENGAGEMENT_SERVICE_LISTING)
        ->and($listing->poster_type)->toBe('mfanyakazi')
        ->and($listing->source_listing_id)->toBeNull()
        ->and($listing->status)->toBe('posted')
        ->and($listing->isServiceListing())->toBeTrue();

    expect($listing->user->role)->toBe('mfanyakazi');
});

test('service booking factory state can be linked to listing via forListing', function () {
    $listing = Job::factory()->serviceListing()->create();
    $client = User::factory()->muhitaji()->create();

    $booking = Job::factory()
        ->forListing($listing)
        ->create(['user_id' => $client->id]);

    expect($booking->engagement_type)->toBe(Job::ENGAGEMENT_SERVICE_BOOKING)
        ->and($booking->user_id)->toBe($client->id)
        ->and($booking->source_listing_id)->toBe($listing->id)
        ->and($booking->selected_worker_id)->toBe($listing->user_id)
        ->and($booking->status)->toBe(Job::S_AWAITING_PAYMENT)
        ->and($booking->isServiceBooking())->toBeTrue();
});

test('scopeServiceListings returns only service listings', function () {
    $listing = Job::factory()->serviceListing()->create();
    Job::factory()->jobRequest()->create();
    Job::factory()->serviceListing()->create();
    $listing2 = Job::factory()->serviceListing()->create();
    $booking = Job::factory()->forListing($listing)->create();

    $ids = Job::query()->serviceListings()->pluck('id')->all();

    expect($ids)->toContain($listing->id, $listing2->id)
        ->and($ids)->not->toContain($booking->id);
    expect(Job::query()->serviceListings()->count())->toBe(3);
});

test('scopeServiceBookings returns only service bookings', function () {
    $listing = Job::factory()->serviceListing()->create();
    $booking = Job::factory()->forListing($listing)->create();
    Job::factory()->jobRequest()->create();
    Job::factory()->serviceListing()->create();

    $ids = Job::query()->serviceBookings()->pluck('id')->all();

    expect($ids)->toBe([$booking->id]);
});

test('scopeJobRequests returns only classic client job requests', function () {
    $listing = Job::factory()->serviceListing()->create();
    $jobRequest = Job::factory()->jobRequest()->create();
    Job::factory()->forListing($listing)->create();
    Job::factory()->jobRequest()->create();

    $ids = Job::query()->jobRequests()->pluck('id')->all();

    expect($ids)->toHaveCount(2)
        ->and($ids)->toContain($jobRequest->id);
});

test('source listing and service bookings relationships', function () {
    $listing = Job::factory()->serviceListing()->create();
    $bookingA = Job::factory()->forListing($listing)->create();
    $bookingB = Job::factory()->forListing($listing)->create();

    $listing->load('serviceBookings');
    $bookingA->load('sourceListing');

    expect($bookingA->sourceListing->is($listing))->toBeTrue()
        ->and($bookingB->sourceListing->id)->toBe($listing->id)
        ->and($listing->serviceBookings)->toHaveCount(2)
        ->and($listing->serviceBookings->pluck('id')->all())->toContain($bookingA->id, $bookingB->id);
});

test('legacy mfanyakazi poster type is backfilled as service listing', function () {
    $legacy = Job::factory()->create([
        'poster_type' => 'mfanyakazi',
        'engagement_type' => Job::ENGAGEMENT_JOB_REQUEST,
        'status' => 'posted',
        'source_listing_id' => null,
    ]);

    Job::query()
        ->where('poster_type', 'mfanyakazi')
        ->whereNull('source_listing_id')
        ->update(['engagement_type' => Job::ENGAGEMENT_SERVICE_LISTING]);

    expect($legacy->fresh()->engagement_type)->toBe(Job::ENGAGEMENT_SERVICE_LISTING);
});
