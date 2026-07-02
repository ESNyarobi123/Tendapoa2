<?php

use App\Models\Job;
use App\Models\Setting;
use App\Models\User;

test('admin can assign worker to open job', function () {
    $admin = User::factory()->admin()->create();
    $worker = User::factory()->mfanyakazi()->create(['is_active' => true]);
    $job = Job::factory()->open()->create(['price' => 25000]);

    $this->actingAs($admin)
        ->post(route('admin.job.assign-worker', $job), [
            'worker_id' => $worker->id,
            'agreed_amount' => 25000,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $job->refresh();
    expect($job->selected_worker_id)->toBe($worker->id);
    expect($job->status)->toBe(Job::S_AWAITING_PAYMENT);
});

test('admin can change job status', function () {
    $admin = User::factory()->admin()->create();
    $job = Job::factory()->open()->create();

    $this->actingAs($admin)
        ->post(route('admin.job.change-status', $job), [
            'status' => Job::S_CANCELLED,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($job->fresh()->status)->toBe(Job::S_CANCELLED);
});

test('admin can force cancel job from jobs list flow', function () {
    $admin = User::factory()->admin()->create();
    $job = Job::factory()->open()->create();

    $this->actingAs($admin)
        ->post(route('admin.job.force-cancel', $job))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($job->fresh()->status)->toBe(Job::S_CANCELLED);
});

test('admin can delete non-active job', function () {
    $admin = User::factory()->admin()->create();
    $job = Job::factory()->open()->create();

    $this->actingAs($admin)
        ->delete(route('admin.job.delete', $job))
        ->assertRedirect(route('admin.jobs'))
        ->assertSessionHas('success');

    expect(Job::find($job->id))->toBeNull();
});

test('admin can suspend and activate user', function () {
    $admin = User::factory()->admin()->create();
    $worker = User::factory()->mfanyakazi()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->post(route('admin.user.toggle-status', $worker))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($worker->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->post(route('admin.user.toggle-status', $worker->fresh()))
        ->assertRedirect();

    expect($worker->fresh()->is_active)->toBeTrue();
});

test('admin can save system settings', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.system-settings.update'), [
            'platform_name' => 'TendaPoa Test',
            'commission_rate' => '12',
            'min_withdrawal' => '5000',
            'email_notifications' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Setting::get('platform_name'))->toBe('TendaPoa Test');
    expect(Setting::get('commission_rate'))->toBe('12');
});

test('impersonated user can stop impersonation', function () {
    $admin = User::factory()->admin()->create();
    $worker = User::factory()->mfanyakazi()->create();

    $this->actingAs($admin)
        ->get(route('admin.impersonate', $worker))
        ->assertRedirect(route('dashboard'));

    expect(session('admin_id'))->toBe($admin->id);
    expect(auth()->id())->toBe($worker->id);

    $this->get(route('admin.stop-impersonate'))
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('success');

    expect(auth()->id())->toBe($admin->id);
});
