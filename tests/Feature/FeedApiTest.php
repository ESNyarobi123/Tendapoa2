<?php

use App\Models\Job;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('feed api returns jobs as a json list not an object', function () {
    $worker = User::factory()->mfanyakazi()->create([
        'lat' => -6.907494,
        'lng' => 39.197082,
    ]);

    Job::factory()->open()->create([
        'title' => 'Usafi wa nyumba',
        'lat' => -6.91,
        'lng' => 39.20,
    ]);

    Sanctum::actingAs($worker);

    $response = $this->getJson('/api/feed');

    $response->assertOk()
        ->assertJsonPath('status', 'success');

    $jobs = $response->json('jobs');
    expect($jobs)->toBeArray();
    expect(array_is_list($jobs))->toBeTrue();
    expect($jobs)->not->toBeEmpty();
});

test('inactive worker cannot login via api', function () {
    $worker = User::factory()->mfanyakazi()->create([
        'is_active' => false,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $worker->email,
        'password' => 'password',
    ]);

    $response->assertStatus(403)
        ->assertJsonPath('success', false);
});

test('mfanyakazi can access web feed with open jobs available', function () {
    $worker = User::factory()->mfanyakazi()->create([
        'lat' => -6.907494,
        'lng' => 39.197082,
    ]);

    Job::factory()->open()->create([
        'title' => 'Kazi ya majaribio',
        'title_sw' => 'Kazi ya majaribio',
        'title_en' => 'Test job',
    ]);

    expect(Job::whereIn('status', [Job::S_OPEN, 'posted'])->count())->toBe(1);

    $this->actingAs($worker)
        ->get(route('feed'))
        ->assertOk()
        ->assertDontSee('Hakuna kazi');
});
