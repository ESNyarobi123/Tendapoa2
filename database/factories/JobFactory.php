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
        ];
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
