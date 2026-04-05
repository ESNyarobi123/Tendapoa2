<?php

/**
 * WORKFLOW LIFECYCLE TESTS
 * End-to-end feature tests for the upgraded marketplace workflow:
 * free-post → apply → select → fund → accept → in_progress → submitted → completed
 */

use App\Models\Category;
use App\Models\Dispute;
use App\Models\EscrowLedger;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobStatusLog;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\DisputeService;
use App\Services\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── HELPERS ───────────────────────────────────────────────────────────

function createClient(array $a = []): User
{
    return User::factory()->muhitaji()->create($a);
}
function createWorker(array $a = []): User
{
    return User::factory()->mfanyakazi()->create($a);
}
function createAdmin(array $a = []): User
{
    return User::factory()->admin()->create($a);
}

function createOpenJob(?User $client = null, array $a = []): Job
{
    $client ??= createClient();

    return Job::factory()->open()->create(array_merge(['user_id' => $client->id, 'price' => 50000], $a));
}

function fundClientWallet(User $client, int $amount): Wallet
{
    $wallet = $client->ensureWallet();
    $wallet->balance = $amount;
    $wallet->save();

    return $wallet;
}

function setupFundedJob(?User $client = null, ?User $worker = null): array
{
    $client ??= createClient();
    $worker ??= createWorker();
    $job = createOpenJob($client, ['price' => 50000]);

    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => $worker->id,
        'proposed_amount' => 50000, 'message' => 'I can do this',
        'status' => JobApplication::STATUS_SELECTED, 'selected_at' => now(),
    ]);

    $job->update([
        'status' => Job::S_AWAITING_PAYMENT,
        'selected_worker_id' => $worker->id,
        'agreed_amount' => 50000,
    ]);

    fundClientWallet($client, 100000);
    app(EscrowService::class)->holdFromWallet($job->fresh(), $client, 50000);
    $job->update(['status' => Job::S_FUNDED, 'funded_at' => now(), 'accepted_worker_id' => $worker->id]);

    return ['client' => $client, 'worker' => $worker, 'job' => $job->fresh(), 'application' => $app];
}

// ═══════════════════════════════════════════════════════════════════════
// A. JOB CREATION
// ═══════════════════════════════════════════════════════════════════════

test('client can create a job for free', function () {
    $client = createClient();
    $cat = Category::factory()->create();

    $resp = $this->actingAs($client)->post(route('jobs.store'), [
        'title' => 'Fix my sink', 'description' => 'Kitchen sink leaking',
        'price' => 30000, 'category_id' => $cat->id,
        'lat' => -6.8, 'lng' => 39.28, 'address_text' => 'Dar es Salaam',
    ]);
    $resp->assertRedirect();

    $job = Job::where('user_id', $client->id)->latest()->first();
    expect($job)->not->toBeNull()
        ->and($job->status)->toBe(Job::S_OPEN)
        ->and($job->published_at)->not->toBeNull();
});

test('new job has no payment at posting time', function () {
    $client = createClient();
    $cat = Category::factory()->create();
    $this->actingAs($client)->post(route('jobs.store'), [
        'title' => 'Paint house', 'description' => 'Need painting',
        'price' => 80000, 'category_id' => $cat->id, 'lat' => -6.8, 'lng' => 39.28,
    ]);
    $job = Job::where('user_id', $client->id)->latest()->first();
    expect(Payment::where('work_order_id', $job->id)->count())->toBe(0);
});

test('unauthenticated user cannot create a job', function () {
    $cat = Category::factory()->create();
    $this->post(route('jobs.store'), [
        'title' => 'Fix', 'description' => 'desc', 'price' => 10000,
        'category_id' => $cat->id, 'lat' => -6.8, 'lng' => 39.28,
    ])->assertRedirect(route('login'));
});

// ═══════════════════════════════════════════════════════════════════════
// B. APPLICATION FLOW
// ═══════════════════════════════════════════════════════════════════════

test('worker can apply to an open job', function () {
    $job = createOpenJob();
    $worker = createWorker();

    $this->actingAs($worker)->post(route('jobs.apply', $job), [
        'proposed_amount' => 45000, 'message' => 'Experienced',
    ])->assertRedirect();

    $app = JobApplication::where('work_order_id', $job->id)->where('worker_id', $worker->id)->first();
    expect($app)->not->toBeNull()
        ->and($app->status)->toBe(JobApplication::STATUS_APPLIED)
        ->and($app->proposed_amount)->toBe(45000);
});

test('worker cannot apply twice to same job', function () {
    $job = createOpenJob();
    $worker = createWorker();

    $this->actingAs($worker)->post(route('jobs.apply', $job), ['proposed_amount' => 45000, 'message' => 'First']);
    $this->actingAs($worker)->post(route('jobs.apply', $job), ['proposed_amount' => 40000, 'message' => 'Second']);

    expect(JobApplication::where('work_order_id', $job->id)->where('worker_id', $worker->id)->count())->toBe(1);
});

test('client can shortlist an application', function () {
    $client = createClient();
    $job = createOpenJob($client);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => createWorker()->id,
        'proposed_amount' => 40000, 'message' => 'test', 'status' => JobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($client)->post(route('applications.shortlist', [$job, $app]))->assertRedirect();
    expect($app->fresh()->status)->toBe(JobApplication::STATUS_SHORTLISTED);
});

test('client can reject an application', function () {
    $client = createClient();
    $job = createOpenJob($client);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => createWorker()->id,
        'proposed_amount' => 40000, 'message' => 'test', 'status' => JobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($client)->post(route('applications.reject', [$job, $app]))->assertRedirect();
    expect($app->fresh()->status)->toBe(JobApplication::STATUS_REJECTED);
});

test('worker can withdraw their application', function () {
    $client = createClient();
    $worker = createWorker();
    $job = createOpenJob($client);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => $worker->id,
        'proposed_amount' => 40000, 'message' => 'test', 'status' => JobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($worker)->post(route('applications.withdraw', [$job, $app]))->assertRedirect();
    expect($app->fresh()->status)->toBe(JobApplication::STATUS_WITHDRAWN);
});

test('client can counter an application', function () {
    $client = createClient();
    $job = createOpenJob($client);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => createWorker()->id,
        'proposed_amount' => 40000, 'message' => 'test', 'status' => JobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($client)->post(route('applications.counter', [$job, $app]), [
        'counter_amount' => 35000, 'client_response_note' => 'Can you do it for less?',
    ])->assertRedirect();

    $fresh = $app->fresh();
    expect($fresh->status)->toBe(JobApplication::STATUS_COUNTERED)
        ->and($fresh->counter_amount)->toBe(35000);
});

test('worker can accept counter offer', function () {
    $client = createClient();
    $worker = createWorker();
    $job = createOpenJob($client);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => $worker->id,
        'proposed_amount' => 40000, 'message' => 'test',
        'status' => JobApplication::STATUS_COUNTERED, 'counter_amount' => 35000, 'countered_at' => now(),
    ]);

    $this->actingAs($worker)->post(route('applications.accept-counter', [$job, $app]))->assertRedirect();
    expect($app->fresh()->status)->toBe(JobApplication::STATUS_ACCEPTED_COUNTER);
});

// ═══════════════════════════════════════════════════════════════════════
// C. SELECTION FLOW
// ═══════════════════════════════════════════════════════════════════════

test('client can select a worker and job moves to awaiting_payment', function () {
    $client = createClient();
    $worker = createWorker();
    $job = createOpenJob($client);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => $worker->id,
        'proposed_amount' => 40000, 'message' => 'test', 'status' => JobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($client)->post(route('applications.select', [$job, $app]))->assertRedirect();

    $job->refresh();
    expect($job->status)->toBe(Job::S_AWAITING_PAYMENT)
        ->and($job->selected_worker_id)->toBe($worker->id)
        ->and($job->agreed_amount)->toBe(40000)
        ->and($app->fresh()->status)->toBe(JobApplication::STATUS_SELECTED);
});

test('unauthorized user cannot select a worker', function () {
    $client = createClient();
    $other = createClient();
    $worker = createWorker();
    $job = createOpenJob($client);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => $worker->id,
        'proposed_amount' => 40000, 'message' => 'test', 'status' => JobApplication::STATUS_APPLIED,
    ]);

    $this->actingAs($other)->post(route('applications.select', [$job, $app]));
    expect($job->fresh()->status)->toBe(Job::S_OPEN);
});

// ═══════════════════════════════════════════════════════════════════════
// D. FUNDING FLOW
// ═══════════════════════════════════════════════════════════════════════

test('client can fund a job from wallet', function () {
    $client = createClient();
    $worker = createWorker();
    $job = createOpenJob($client, ['price' => 50000]);

    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => $worker->id,
        'proposed_amount' => 50000, 'status' => JobApplication::STATUS_SELECTED, 'selected_at' => now(),
    ]);
    $job->update(['status' => Job::S_AWAITING_PAYMENT, 'selected_worker_id' => $worker->id, 'agreed_amount' => 50000]);
    fundClientWallet($client, 100000);

    $this->actingAs($client)->post(route('jobs.fund.wallet', $job))->assertRedirect();

    $job->refresh();
    expect($job->status)->toBe(Job::S_FUNDED)
        ->and($job->funded_at)->not->toBeNull()
        ->and($job->escrow_amount)->toBe(50000);

    $ledger = EscrowLedger::where('work_order_id', $job->id)->where('type', EscrowLedger::TYPE_HOLD)->first();
    expect($ledger)->not->toBeNull()->and($ledger->amount)->toBe(50000);
    expect($client->ensureWallet()->fresh()->held_balance)->toBe(50000);
});

test('insufficient wallet balance blocks funding', function () {
    $client = createClient();
    $worker = createWorker();
    $job = createOpenJob($client, ['price' => 50000]);
    JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => $worker->id,
        'proposed_amount' => 50000, 'status' => JobApplication::STATUS_SELECTED, 'selected_at' => now(),
    ]);
    $job->update(['status' => Job::S_AWAITING_PAYMENT, 'selected_worker_id' => $worker->id, 'agreed_amount' => 50000]);
    fundClientWallet($client, 10000);

    $this->actingAs($client)->post(route('jobs.fund.wallet', $job));
    expect($job->fresh()->status)->toBe(Job::S_AWAITING_PAYMENT);
});

test('funds move to escrow not directly to worker', function () {
    $data = setupFundedJob();
    expect($data['worker']->ensureWallet()->balance)->toBe(0);
    expect($data['client']->ensureWallet()->fresh()->held_balance)->toBe(50000);
});

// ═══════════════════════════════════════════════════════════════════════
// E. WORKER RESPONSE
// ═══════════════════════════════════════════════════════════════════════

test('selected worker can accept funded job', function () {
    $data = setupFundedJob();
    $this->actingAs($data['worker'])->post(route('jobs.worker.accept', $data['job']))->assertRedirect();
    $data['job']->refresh();
    expect($data['job']->status)->toBe(Job::S_IN_PROGRESS)
        ->and($data['job']->accepted_by_worker_at)->not->toBeNull();
});

test('selected worker can decline funded job with refund', function () {
    $data = setupFundedJob();
    $this->actingAs($data['worker'])->post(route('jobs.worker.decline', $data['job']))->assertRedirect();
    $data['job']->refresh();
    expect($data['job']->status)->toBe(Job::S_OPEN)
        ->and($data['job']->escrow_amount)->toBe(0);
    expect($data['client']->ensureWallet()->fresh()->held_balance)->toBe(0);
});

test('non-selected worker cannot accept funded job', function () {
    $data = setupFundedJob();
    $other = createWorker();
    $this->actingAs($other)->post(route('jobs.worker.accept', $data['job']));
    expect($data['job']->fresh()->status)->toBe(Job::S_FUNDED);
});

// ═══════════════════════════════════════════════════════════════════════
// F. COMPLETION FLOW
// ═══════════════════════════════════════════════════════════════════════

test('worker can submit completion', function () {
    $data = setupFundedJob();
    $data['job']->update([
        'status' => Job::S_IN_PROGRESS, 'accepted_by_worker_at' => now(),
        'accepted_worker_id' => $data['worker']->id,
    ]);

    $this->actingAs($data['worker'])->post(route('jobs.worker.submit', $data['job']))->assertRedirect();
    $data['job']->refresh();
    expect($data['job']->status)->toBe(Job::S_SUBMITTED)
        ->and($data['job']->submitted_at)->not->toBeNull();
});

test('client confirm releases funds correctly', function () {
    Setting::set('commission_rate', 10);
    $data = setupFundedJob();
    $data['job']->update([
        'status' => Job::S_SUBMITTED, 'accepted_by_worker_at' => now(),
        'accepted_worker_id' => $data['worker']->id, 'submitted_at' => now(),
    ]);

    $this->actingAs($data['client'])->post(route('jobs.client.confirm', $data['job']))->assertRedirect();
    $data['job']->refresh();

    expect($data['job']->status)->toBe(Job::S_COMPLETED)
        ->and($data['job']->confirmed_at)->not->toBeNull()
        ->and($data['job']->platform_fee_amount)->toBe(5000)
        ->and($data['job']->release_amount)->toBe(45000)
        ->and($data['job']->escrow_amount)->toBe(0);

    expect($data['worker']->ensureWallet()->fresh()->balance)->toBe(45000);
    expect($data['client']->ensureWallet()->fresh()->held_balance)->toBe(0);

    $release = EscrowLedger::where('work_order_id', $data['job']->id)->where('type', EscrowLedger::TYPE_RELEASE)->first();
    expect($release)->not->toBeNull()->and($release->amount)->toBe(45000);
    $fee = EscrowLedger::where('work_order_id', $data['job']->id)->where('type', EscrowLedger::TYPE_PLATFORM_FEE)->first();
    expect($fee)->not->toBeNull()->and($fee->amount)->toBe(5000);
});

test('client can request revision', function () {
    $data = setupFundedJob();
    $data['job']->update([
        'status' => Job::S_SUBMITTED, 'accepted_by_worker_at' => now(),
        'accepted_worker_id' => $data['worker']->id, 'submitted_at' => now(),
    ]);

    $this->actingAs($data['client'])->post(route('jobs.client.revision', $data['job']), ['reason' => 'Fix edges']);
    $data['job']->refresh();
    expect($data['job']->status)->toBe(Job::S_IN_PROGRESS)
        ->and($data['job']->escrow_amount)->toBe(50000);
});

// ═══════════════════════════════════════════════════════════════════════
// G. DISPUTE FLOW
// ═══════════════════════════════════════════════════════════════════════

test('client can open dispute on submitted job', function () {
    $data = setupFundedJob();
    $data['job']->update([
        'status' => Job::S_SUBMITTED, 'accepted_by_worker_at' => now(),
        'accepted_worker_id' => $data['worker']->id, 'submitted_at' => now(),
    ]);

    $this->actingAs($data['client'])->post(route('jobs.client.dispute', $data['job']), [
        'reason' => 'Work not completed properly',
    ])->assertRedirect();

    $data['job']->refresh();
    expect($data['job']->status)->toBe(Job::S_DISPUTED)
        ->and($data['job']->disputed_at)->not->toBeNull();

    $dispute = Dispute::where('work_order_id', $data['job']->id)->first();
    expect($dispute)->not->toBeNull()
        ->and($dispute->status)->toBe(Dispute::STATUS_OPEN)
        ->and($dispute->raised_by)->toBe($data['client']->id);
});

test('disputed job freezes escrow', function () {
    $data = setupFundedJob();
    $data['job']->update([
        'status' => Job::S_DISPUTED, 'accepted_worker_id' => $data['worker']->id, 'disputed_at' => now(),
    ]);

    $this->actingAs($data['client'])->post(route('jobs.client.confirm', $data['job']));
    expect($data['job']->fresh()->status)->toBe(Job::S_DISPUTED)
        ->and($data['job']->fresh()->escrow_amount)->toBe(50000);
});

test('admin resolve dispute full worker', function () {
    Setting::set('commission_rate', 10);
    $data = setupFundedJob();
    $admin = createAdmin();
    $data['job']->update(['status' => Job::S_DISPUTED, 'accepted_worker_id' => $data['worker']->id, 'disputed_at' => now()]);

    $dispute = Dispute::create([
        'work_order_id' => $data['job']->id, 'raised_by' => $data['client']->id,
        'against_user' => $data['worker']->id, 'status' => Dispute::STATUS_OPEN, 'reason' => 'Test',
    ]);

    app(DisputeService::class)->resolveFullWorker($dispute, $admin, 'Worker did the job');
    expect($dispute->fresh()->isResolved())->toBeTrue();
    expect($data['worker']->ensureWallet()->fresh()->balance)->toBe(45000);
});

test('admin resolve dispute full client refund', function () {
    $data = setupFundedJob();
    $admin = createAdmin();
    $data['job']->update(['status' => Job::S_DISPUTED, 'accepted_worker_id' => $data['worker']->id, 'disputed_at' => now()]);

    $dispute = Dispute::create([
        'work_order_id' => $data['job']->id, 'raised_by' => $data['client']->id,
        'against_user' => $data['worker']->id, 'status' => Dispute::STATUS_OPEN, 'reason' => 'Not done',
    ]);

    app(DisputeService::class)->resolveFullClient($dispute, $admin, 'Worker failed');
    expect($dispute->fresh()->isResolved())->toBeTrue();
    expect($data['client']->ensureWallet()->fresh()->held_balance)->toBe(0);
    expect($data['worker']->ensureWallet()->fresh()->balance)->toBe(0);
});

test('admin resolve dispute split', function () {
    $data = setupFundedJob();
    $admin = createAdmin();
    $data['job']->update(['status' => Job::S_DISPUTED, 'accepted_worker_id' => $data['worker']->id, 'disputed_at' => now()]);

    $dispute = Dispute::create([
        'work_order_id' => $data['job']->id, 'raised_by' => $data['client']->id,
        'against_user' => $data['worker']->id, 'status' => Dispute::STATUS_OPEN, 'reason' => 'Partial',
    ]);

    app(DisputeService::class)->resolveSplit($dispute, $admin, 25000, 25000, 'Partial completion');
    expect($data['worker']->ensureWallet()->fresh()->balance)->toBe(25000);
    expect($data['client']->ensureWallet()->fresh()->held_balance)->toBe(0);
});

// ═══════════════════════════════════════════════════════════════════════
// H. WALLET / LEDGER
// ═══════════════════════════════════════════════════════════════════════

test('wallet available_balance reflects held funds', function () {
    $client = createClient();
    $w = fundClientWallet($client, 100000);
    expect($w->available_balance)->toBe(100000);
    $w->update(['held_balance' => 30000]);
    expect($w->fresh()->available_balance)->toBe(70000);
});

test('escrow hold creates wallet transaction', function () {
    $data = setupFundedJob();
    $txn = WalletTransaction::where('user_id', $data['client']->id)->where('type', 'JOB_FUNDING')->first();
    expect($txn)->not->toBeNull()->and($txn->amount)->toBe(-50000);
});

test('escrow release creates worker wallet transaction', function () {
    Setting::set('commission_rate', 10);
    $data = setupFundedJob();
    $data['job']->update(['accepted_worker_id' => $data['worker']->id]);
    $result = app(EscrowService::class)->releaseToWorker($data['job']);
    $txn = WalletTransaction::where('user_id', $data['worker']->id)->where('type', 'EARN')->latest()->first();
    expect($txn)->not->toBeNull()->and($txn->amount)->toBe(45000);
    expect($result['platform_fee'])->toBe(5000)->and($result['release_amount'])->toBe(45000);
});

test('refund restores client available balance', function () {
    $data = setupFundedJob();
    app(EscrowService::class)->refundToClient($data['job'], 'Worker declined');
    $w = $data['client']->ensureWallet()->fresh();
    expect($w->held_balance)->toBe(0)->and($w->available_balance)->toBe($w->balance);
});

// ═══════════════════════════════════════════════════════════════════════
// I. AUTHORIZATION / SECURITY
// ═══════════════════════════════════════════════════════════════════════

test('client cannot manage others job', function () {
    $c1 = createClient();
    $c2 = createClient();
    $job = createOpenJob($c1);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => createWorker()->id,
        'proposed_amount' => 40000, 'message' => 'test', 'status' => JobApplication::STATUS_APPLIED,
    ]);
    $this->actingAs($c2)->post(route('applications.select', [$job, $app]));
    expect($job->fresh()->status)->toBe(Job::S_OPEN);
});

test('worker cannot alter others applications', function () {
    $client = createClient();
    $w1 = createWorker();
    $w2 = createWorker();
    $job = createOpenJob($client);
    $app = JobApplication::create([
        'work_order_id' => $job->id, 'worker_id' => $w1->id,
        'proposed_amount' => 40000, 'message' => 'test', 'status' => JobApplication::STATUS_APPLIED,
    ]);
    $this->actingAs($w2)->post(route('applications.withdraw', [$job, $app]));
    expect($app->fresh()->status)->toBe(JobApplication::STATUS_APPLIED);
});

test('unauthorized user cannot confirm completion', function () {
    $data = setupFundedJob();
    $other = createClient();
    $data['job']->update([
        'status' => Job::S_SUBMITTED, 'accepted_worker_id' => $data['worker']->id, 'submitted_at' => now(),
    ]);
    $this->actingAs($other)->post(route('jobs.client.confirm', $data['job']));
    expect($data['job']->fresh()->status)->toBe(Job::S_SUBMITTED);
});

// ═══════════════════════════════════════════════════════════════════════
// J. BACKWARD COMPATIBILITY
// ═══════════════════════════════════════════════════════════════════════

test('legacy completed jobs viewable', function () {
    $client = createClient();
    $job = Job::factory()->create([
        'user_id' => $client->id, 'status' => Job::S_COMPLETED,
        'accepted_worker_id' => createWorker()->id,
        'completed_at' => now()->subDays(30), 'confirmed_at' => now()->subDays(30), 'price' => 25000,
    ]);
    $this->actingAs($client)->get(route('jobs.show', $job))->assertOk();
});

test('open jobs appear in feed', function () {
    $client = createClient();
    $worker = createWorker();
    Job::factory()->open()->create(['user_id' => $client->id, 'price' => 20000]);
    $this->actingAs($worker)->get(route('feed'))->assertOk();
});

// ═══════════════════════════════════════════════════════════════════════
// K. STATUS LOG AUDIT
// ═══════════════════════════════════════════════════════════════════════

test('status transitions are logged', function () {
    $client = createClient();
    $job = createOpenJob($client);
    $job->transitionStatus(Job::S_AWAITING_PAYMENT, $client->id, 'Worker selected');
    $log = JobStatusLog::where('work_order_id', $job->id)->latest()->first();
    expect($log)->not->toBeNull()
        ->and($log->from_status)->toBe(Job::S_OPEN)
        ->and($log->to_status)->toBe(Job::S_AWAITING_PAYMENT)
        ->and($log->note)->toBe('Worker selected');
});

// ═══════════════════════════════════════════════════════════════════════
// K2. STATE MACHINE HARDENING (Phase 8)
// ═══════════════════════════════════════════════════════════════════════

test('invalid transition is blocked', function () {
    $client = createClient();
    $job = createOpenJob($client);

    // open → completed is not a valid transition
    expect(fn () => $job->transitionStatus(Job::S_COMPLETED, $client->id, 'Skip everything'))
        ->toThrow(RuntimeException::class);

    expect($job->fresh()->status)->toBe(Job::S_OPEN);
});

test('invalid transition can be forced', function () {
    $client = createClient();
    $job = createOpenJob($client);

    // Force bypass allows any transition
    $job->transitionStatus(Job::S_COMPLETED, $client->id, 'Admin forced', [], true);
    expect($job->fresh()->status)->toBe(Job::S_COMPLETED);
});

test('completed job cannot transition further', function () {
    $client = createClient();
    $job = Job::factory()->create([
        'user_id' => $client->id, 'status' => Job::S_COMPLETED,
        'completed_at' => now(), 'confirmed_at' => now(), 'price' => 10000,
    ]);

    expect(fn () => $job->transitionStatus(Job::S_OPEN, $client->id))
        ->toThrow(RuntimeException::class);
    expect($job->fresh()->status)->toBe(Job::S_COMPLETED);
});

test('cancelled job cannot transition further', function () {
    $client = createClient();
    $job = Job::factory()->create([
        'user_id' => $client->id, 'status' => Job::S_CANCELLED, 'price' => 10000,
    ]);

    expect(fn () => $job->transitionStatus(Job::S_OPEN, $client->id))
        ->toThrow(RuntimeException::class);
});

test('all valid forward transitions pass', function () {
    $client = createClient();

    // open → awaiting_payment
    $job = createOpenJob($client);
    $job->transitionStatus(Job::S_AWAITING_PAYMENT, $client->id);
    expect($job->status)->toBe(Job::S_AWAITING_PAYMENT);

    // awaiting_payment → funded
    $job->transitionStatus(Job::S_FUNDED, $client->id);
    expect($job->status)->toBe(Job::S_FUNDED);

    // funded → in_progress
    $job->transitionStatus(Job::S_IN_PROGRESS, $client->id);
    expect($job->status)->toBe(Job::S_IN_PROGRESS);

    // in_progress → submitted
    $job->transitionStatus(Job::S_SUBMITTED, $client->id);
    expect($job->status)->toBe(Job::S_SUBMITTED);

    // submitted → completed
    $job->transitionStatus(Job::S_COMPLETED, $client->id);
    expect($job->status)->toBe(Job::S_COMPLETED);
});

test('isValidTransition helper works correctly', function () {
    expect(Job::isValidTransition(Job::S_OPEN, Job::S_AWAITING_PAYMENT))->toBeTrue();
    expect(Job::isValidTransition(Job::S_OPEN, Job::S_COMPLETED))->toBeFalse();
    expect(Job::isValidTransition(Job::S_FUNDED, Job::S_OPEN))->toBeTrue(); // decline path
    expect(Job::isValidTransition(Job::S_COMPLETED, Job::S_OPEN))->toBeFalse();
    expect(Job::isValidTransition(Job::S_SUBMITTED, Job::S_IN_PROGRESS))->toBeTrue(); // revision path
    expect(Job::isValidTransition(Job::S_SUBMITTED, Job::S_DISPUTED))->toBeTrue();
    expect(Job::isValidTransition('pending_payment', 'posted'))->toBeTrue(); // legacy
    expect(Job::isValidTransition('unknown_status', Job::S_OPEN))->toBeTrue(); // unknown = allow
});

// ═══════════════════════════════════════════════════════════════════════
// L. END-TO-END LIFECYCLE
// ═══════════════════════════════════════════════════════════════════════

test('full lifecycle: post → apply → select → fund → accept → submit → confirm', function () {
    Setting::set('commission_rate', 10);
    $client = createClient();
    $worker = createWorker();
    $cat = Category::factory()->create();

    // 1. Client creates job
    $this->actingAs($client)->post(route('jobs.store'), [
        'title' => 'Lifecycle test', 'description' => 'Full workflow',
        'price' => 60000, 'category_id' => $cat->id, 'lat' => -6.8, 'lng' => 39.28,
    ]);
    $job = Job::where('user_id', $client->id)->latest()->first();
    expect($job->status)->toBe(Job::S_OPEN);

    // 2. Worker applies
    $this->actingAs($worker)->post(route('jobs.apply', $job), [
        'proposed_amount' => 55000, 'message' => 'I can do this',
    ]);
    $app = JobApplication::where('work_order_id', $job->id)->first();
    expect($app->status)->toBe(JobApplication::STATUS_APPLIED);

    // 3. Client selects worker
    $this->actingAs($client)->post(route('applications.select', [$job, $app]));
    $job->refresh();
    expect($job->status)->toBe(Job::S_AWAITING_PAYMENT);

    // 4. Client funds from wallet
    fundClientWallet($client, 200000);
    $this->actingAs($client)->post(route('jobs.fund.wallet', $job));
    $job->refresh();
    expect($job->status)->toBe(Job::S_FUNDED);

    // 5. Worker accepts
    $this->actingAs($worker)->post(route('jobs.worker.accept', $job));
    $job->refresh();
    expect($job->status)->toBe(Job::S_IN_PROGRESS);

    // 6. Worker submits completion
    $this->actingAs($worker)->post(route('jobs.worker.submit', $job));
    $job->refresh();
    expect($job->status)->toBe(Job::S_SUBMITTED);

    // 7. Client confirms
    $this->actingAs($client)->post(route('jobs.client.confirm', $job));
    $job->refresh();
    expect($job->status)->toBe(Job::S_COMPLETED)
        ->and($job->escrow_amount)->toBe(0);

    // Worker received payment (55000 - 10% = 49500)
    $workerWallet = $worker->ensureWallet()->fresh();
    expect($workerWallet->balance)->toBe(49500);
    expect($workerWallet->total_earned)->toBe(49500);
});
