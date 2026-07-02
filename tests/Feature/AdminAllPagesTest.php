<?php

use App\Models\Job;
use App\Models\SystemNotification;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->muhitaji = User::factory()->create(['role' => 'muhitaji']);
    $this->worker = User::factory()->mfanyakazi()->create(['is_active' => true]);
    $this->job = Job::factory()->open()->create(['user_id' => $this->muhitaji->id]);
});

test('guest is redirected from admin pages', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect();
});

test('non-admin receives forbidden on admin pages', function () {
    $this->actingAs($this->worker)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin can load all admin html pages', function () {
    $notification = SystemNotification::create([
        'title' => 'Test broadcast',
        'message' => 'Hello team',
        'target' => 'all',
        'sent_by' => $this->admin->id,
        'total_count' => 1,
        'sent_count' => 1,
        'failed_count' => 0,
        'fcm_sent_count' => 0,
    ]);

    $pages = [
        ['admin.dashboard', []],
        ['admin.users', []],
        ['admin.user.details', ['user' => $this->worker]],
        ['admin.user.dashboard', ['user' => $this->worker]],
        ['admin.user.monitor', ['user' => $this->worker]],
        ['admin.user.chats', ['user' => $this->worker]],
        ['admin.user.edit', ['user' => $this->worker]],
        ['admin.jobs', []],
        ['admin.job.details', ['job' => $this->job]],
        ['admin.chats', []],
        ['admin.chat.view', ['job' => $this->job]],
        ['admin.commissions', []],
        ['admin.analytics', []],
        ['admin.completed-jobs', []],
        ['admin.system-logs', []],
        ['admin.system-settings', []],
        ['admin.categories', []],
        ['admin.broadcast', []],
        ['admin.broadcast.edit', ['id' => $notification->id]],
        ['admin.withdrawals', []],
    ];

    foreach ($pages as [$name, $params]) {
        $this->actingAs($this->admin)
            ->get(route($name, $params))
            ->assertOk("Failed loading route: {$name}");
    }
});

test('admin jobs page supports category filter', function () {
    $category = $this->job->category;

    $this->actingAs($this->admin)
        ->get(route('admin.jobs', ['category' => $category->slug]))
        ->assertOk()
        ->assertSee($this->job->title, false);
});

test('admin jobs page shows global stats not only current page', function () {
    Job::factory()->count(3)->open()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.jobs'))
        ->assertOk()
        ->assertSee(number_format(Job::count()), false);
});
