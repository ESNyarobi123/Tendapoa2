<?php

use App\Models\Job;
use App\Models\User;

test('admin can hide job from public feed', function () {
    $admin = User::factory()->admin()->create();
    $poster = User::factory()->create(['role' => 'muhitaji']);
    $worker = User::factory()->mfanyakazi()->create();
    $job = Job::factory()->open()->create(['user_id' => $poster->id]);

    $this->actingAs($admin)
        ->post(route('admin.job.hide', $job), ['reason' => 'Maelezo si sahihi'])
        ->assertRedirect()
        ->assertSessionHas('success');

    $job->refresh();
    expect($job->isHidden())->toBeTrue();
    expect($job->hidden_reason)->toBe('Maelezo si sahihi');

    $this->actingAs($worker)
        ->getJson('/api/feed')
        ->assertOk()
        ->assertJsonMissing(['id' => $job->id]);
});

test('poster can still see hidden job in my jobs', function () {
    $poster = User::factory()->create(['role' => 'muhitaji']);
    $job = Job::factory()->open()->create(['user_id' => $poster->id, 'hidden_at' => now()]);

    $this->actingAs($poster)
        ->get(route('my.jobs'))
        ->assertOk()
        ->assertSee('Imefichwa', false)
        ->assertSee($job->title, false);
});

test('poster can view hidden job details but other users cannot', function () {
    $poster = User::factory()->create(['role' => 'muhitaji']);
    $stranger = User::factory()->mfanyakazi()->create();
    $job = Job::factory()->open()->create(['user_id' => $poster->id, 'hidden_at' => now()]);

    $this->actingAs($poster)
        ->get(route('jobs.show', $job))
        ->assertOk()
        ->assertSee('imefichwa', false);

    $this->actingAs($stranger)
        ->get(route('jobs.show', $job))
        ->assertNotFound();
});

test('admin can unhide job', function () {
    $admin = User::factory()->admin()->create();
    $job = Job::factory()->open()->create(['hidden_at' => now(), 'hidden_by' => $admin->id]);

    $this->actingAs($admin)
        ->post(route('admin.job.unhide', $job))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($job->fresh()->isHidden())->toBeFalse();
});

test('hidden job returns to feed after unhide', function () {
    $admin = User::factory()->admin()->create();
    $worker = User::factory()->mfanyakazi()->create();
    $job = Job::factory()->open()->create(['hidden_at' => now()]);

    $this->actingAs($admin)->post(route('admin.job.unhide', $job));

    $this->actingAs($worker)
        ->getJson('/api/feed')
        ->assertOk();

    $ids = collect(json_decode($this->getJson('/api/feed')->getContent(), true)['jobs'])->pluck('id');
    expect($ids)->toContain($job->id);
});
