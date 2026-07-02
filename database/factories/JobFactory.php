<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->muhitaji(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'title_sw' => fake()->sentence(4),
            'title_en' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'description_sw' => fake()->paragraph(),
            'description_en' => fake()->paragraph(),
            'price' => fake()->numberBetween(5000, 100000),
            'lat' => fake()->latitude(-7.0, -6.5),
            'lng' => fake()->longitude(39.0, 39.5),
            'address_text' => fake()->address(),
            'status' => Job::S_OPEN,
            'published_at' => now(),
            'poster_type' => 'muhitaji',
            'engagement_type' => Job::ENGAGEMENT_JOB_REQUEST,
            'source_listing_id' => null,
        ];
    }

    public function jobRequest(): static
    {
        return $this->state(fn () => [
            'engagement_type' => Job::ENGAGEMENT_JOB_REQUEST,
            'poster_type' => 'muhitaji',
            'source_listing_id' => null,
        ]);
    }

    public function serviceListing(): static
    {
        return $this->state(fn () => [
            'engagement_type' => Job::ENGAGEMENT_SERVICE_LISTING,
            'poster_type' => 'mfanyakazi',
            'user_id' => User::factory()->mfanyakazi(),
            'source_listing_id' => null,
            'status' => 'posted',
        ]);
    }

    public function serviceBooking(): static
    {
        return $this->state(fn () => [
            'engagement_type' => Job::ENGAGEMENT_SERVICE_BOOKING,
            'poster_type' => 'muhitaji',
            'user_id' => User::factory()->muhitaji(),
            'status' => Job::S_AWAITING_PAYMENT,
        ]);
    }

    public function forListing(Job $listing): static
    {
        return $this->state(fn () => [
            'engagement_type' => Job::ENGAGEMENT_SERVICE_BOOKING,
            'poster_type' => 'muhitaji',
            'user_id' => User::factory()->muhitaji(),
            'source_listing_id' => $listing->id,
            'selected_worker_id' => $listing->user_id,
            'category_id' => $listing->category_id,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'lat' => $listing->lat,
            'lng' => $listing->lng,
            'address_text' => $listing->address_text,
            'status' => Job::S_AWAITING_PAYMENT,
        ]);
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => Job::S_OPEN,
            'published_at' => now(),
        ]);
    }

    public function awaitingPayment(): static
    {
        return $this->state(fn () => [
            'status' => Job::S_AWAITING_PAYMENT,
        ]);
    }

    public function funded(): static
    {
        return $this->state(fn () => [
            'status' => Job::S_FUNDED,
            'funded_at' => now(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => Job::S_IN_PROGRESS,
            'funded_at' => now(),
            'accepted_by_worker_at' => now(),
        ]);
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => Job::S_SUBMITTED,
            'funded_at' => now(),
            'accepted_by_worker_at' => now(),
            'submitted_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => Job::S_COMPLETED,
            'funded_at' => now(),
            'accepted_by_worker_at' => now(),
            'submitted_at' => now(),
            'confirmed_at' => now(),
            'completed_at' => now(),
        ]);
    }
}
