<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ApplicationController — handles worker applications and client actions on them.
 *
 * NEW WORKFLOW:
 * - Worker applies with proposed_amount + message + ETA
 * - Client can shortlist, reject, counter, or select
 * - Selecting a worker moves job → awaiting_payment
 */
class ApplicationController extends Controller
{
    /** Kazi inapokea maombi mapya (open au posted, bila mfanyakazi aliyechaguliwa). */
    private static function jobAcceptsApplications(Job $job): bool
    {
        if ($job->selected_worker_id || $job->accepted_worker_id) {
            return false;
        }

        return in_array($job->status, [Job::S_OPEN, 'posted'], true);
    }

    /** Mteja anaweza kuchagua mfanyakazi kwenye kazi hii. */
    private static function jobAllowsWorkerSelection(Job $job): bool
    {
        return in_array($job->status, [Job::S_OPEN, 'posted'], true);
    }

    /**
     * Worker submits an application on an open job.
     */
    public function store(Job $job, Request $request)
    {
        $user = Auth::user();

        if (! in_array($user->role, ['mfanyakazi', 'admin'])) {
            abort(403, 'Wafanyakazi tu wanaweza kuomba kazi.');
        }

        if ($job->user_id === $user->id) {
            abort(403, 'Huwezi kuomba kazi yako mwenyewe.');
        }

        if (! self::jobAcceptsApplications($job)) {
            return back()->withErrors(['error' => 'Kazi hii haipokei maombi kwa sasa.']);
        }

        // Check for existing application
        $existing = JobApplication::where('work_order_id', $job->id)
            ->where('worker_id', $user->id)
            ->whereNotIn('status', [JobApplication::STATUS_WITHDRAWN, JobApplication::STATUS_REJECTED])
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'Tayari umeomba kazi hii.']);
        }

        $request->validate([
            'proposed_amount' => ['required', 'integer', 'min:1000'],
            'message' => ['required', 'string', 'max:1000'],
            'eta_text' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($job, $user, $request) {
            $application = JobApplication::create([
                'work_order_id' => $job->id,
                'worker_id' => $user->id,
                'proposed_amount' => $request->input('proposed_amount'),
                'message' => $request->input('message'),
                'eta_text' => $request->input('eta_text'),
                'status' => JobApplication::STATUS_APPLIED,
            ]);

            // Increment application count
            $job->increment('application_count');

            // Notify client via private message
            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => "✋ {$user->name} ameomba kufanya kazi yako \"{$job->title}\" kwa TZS ".number_format($request->input('proposed_amount')).($request->input('eta_text') ? " | ETA: {$request->input('eta_text')}" : ''),
            ]);
        });

        return redirect()
            ->route('mfanyakazi.applications')
            ->with('success', 'Ombi lako limewasilishwa! Fuata hali yako hapa au rudi kwenye kazi ukiwa tayari.');
    }

    /**
     * Client shortlists an application.
     */
    public function shortlist(Job $job, JobApplication $application)
    {
        $this->authorizeClient($job);

        if (! $application->isActive() || $application->status === JobApplication::STATUS_SHORTLISTED) {
            return back()->withErrors(['error' => 'Ombi hili haliwezi kufanyiwa shortlist.']);
        }

        $application->update([
            'status' => JobApplication::STATUS_SHORTLISTED,
            'shortlisted_at' => now(),
        ]);

        return back()->with('success', 'Mfanyakazi amewekwa kwenye orodha fupi.');
    }

    /**
     * Client rejects an application.
     */
    public function reject(Job $job, JobApplication $application)
    {
        $this->authorizeClient($job);

        if (! $application->isActive()) {
            return back()->withErrors(['error' => 'Ombi hili tayari limeshughulikiwa.']);
        }

        DB::transaction(function () use ($job, $application) {
            $application->update([
                'status' => JobApplication::STATUS_REJECTED,
                'rejected_at' => now(),
            ]);

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => Auth::id(),
                'receiver_id' => $application->worker_id,
                'message' => "❌ Pole, ombi lako la kazi \"{$job->title}\" limekataliwa.",
            ]);
        });

        return back()->with('success', 'Ombi limekataliwa.');
    }

    /**
     * Client sends counter offer on an application.
     */
    public function counter(Job $job, JobApplication $application, Request $request)
    {
        $this->authorizeClient($job);

        $request->validate([
            'counter_amount' => ['required', 'integer', 'min:1000'],
            'client_response_note' => ['nullable', 'string', 'max:500'],
        ]);

        if (! $application->isActive()) {
            return back()->withErrors(['error' => 'Ombi hili halipo tena.']);
        }

        DB::transaction(function () use ($job, $application, $request) {
            $application->update([
                'status' => JobApplication::STATUS_COUNTERED,
                'counter_amount' => $request->input('counter_amount'),
                'client_response_note' => $request->input('client_response_note'),
                'countered_at' => now(),
            ]);

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => Auth::id(),
                'receiver_id' => $application->worker_id,
                'message' => '💰 Counter offer ya TZS '.number_format($request->input('counter_amount'))." kwa kazi \"{$job->title}\".".($request->input('client_response_note') ? "\n📝 ".$request->input('client_response_note') : ''),
            ]);
        });

        return back()->with('success', 'Counter offer imetumwa.');
    }

    /**
     * Worker accepts a counter offer.
     */
    public function acceptCounter(Job $job, JobApplication $application)
    {
        $user = Auth::user();

        if ($application->worker_id !== $user->id) {
            abort(403, 'Hii sio counter offer yako.');
        }

        if ($application->status !== JobApplication::STATUS_COUNTERED) {
            return back()->withErrors(['error' => 'Hakuna counter offer ya kukubali.']);
        }

        DB::transaction(function () use ($job, $application) {
            $application->update([
                'status' => JobApplication::STATUS_ACCEPTED_COUNTER,
            ]);

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $application->worker_id,
                'receiver_id' => $job->user_id,
                'message' => "✅ {$application->worker->name} amekubali counter offer ya TZS ".number_format($application->counter_amount).'.',
            ]);
        });

        return redirect()
            ->route('mfanyakazi.applications')
            ->with('success', 'Umekubali counter offer. Subiri mteja akuchague kisha alipe escrow.');
    }

    /**
     * Worker withdraws their application.
     */
    public function withdraw(Job $job, JobApplication $application)
    {
        $user = Auth::user();

        if ($application->worker_id !== $user->id) {
            abort(403);
        }

        if (! $application->isActive()) {
            return back()->withErrors(['error' => 'Ombi hili halipo tena.']);
        }

        $application->update([
            'status' => JobApplication::STATUS_WITHDRAWN,
            'withdrawn_at' => now(),
        ]);

        $job->decrement('application_count');

        return redirect()
            ->route('mfanyakazi.applications')
            ->with('success', 'Ombi limeondolewa. Unaweza kuomba tena ikiwa kazi bado wazi.');
    }

    /**
     * CLIENT SELECTS A WORKER — the key action that triggers the funding flow.
     * Job moves from open → awaiting_payment.
     */
    public function select(Job $job, JobApplication $application)
    {
        $this->authorizeClient($job);

        if (! self::jobAllowsWorkerSelection($job)) {
            return back()->withErrors(['error' => 'Kazi hii haiko katika hali ya kuchagua mfanyakazi.']);
        }

        if (! in_array($application->status, [
            JobApplication::STATUS_APPLIED,
            JobApplication::STATUS_SHORTLISTED,
            JobApplication::STATUS_ACCEPTED_COUNTER,
        ])) {
            return back()->withErrors(['error' => 'Ombi hili haliwezi kuchaguliwa.']);
        }

        DB::transaction(function () use ($job, $application) {
            $agreedAmount = $application->getAgreedAmount();

            // Mark application as selected
            $application->update([
                'status' => JobApplication::STATUS_SELECTED,
                'selected_at' => now(),
            ]);

            // Reject all other active applications
            JobApplication::where('work_order_id', $job->id)
                ->where('id', '!=', $application->id)
                ->whereNotIn('status', [JobApplication::STATUS_WITHDRAWN, JobApplication::STATUS_REJECTED])
                ->update([
                    'status' => JobApplication::STATUS_REJECTED,
                    'rejected_at' => now(),
                ]);

            // Update job
            $job->selected_worker_id = $application->worker_id;
            $job->agreed_amount = $agreedAmount;

            // Generate completion code
            if (! $job->completion_code) {
                $job->completion_code = (string) random_int(100000, 999999);
            }

            $job->transitionStatus(Job::S_AWAITING_PAYMENT, Auth::id(), "Selected worker: {$application->worker->name}, agreed amount: TZS ".number_format($agreedAmount));

            // Notify selected worker
            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => Auth::id(),
                'receiver_id' => $application->worker_id,
                'message' => "🎉 Umechaguliwa kufanya kazi \"{$job->title}\"! Tunasubiri muhitaji afanye malipo, kisha utaarifu.",
            ]);
        });

        // Redirect to funding page
        return redirect()->route('jobs.fund', $job)->with('success', 'Umemchagua mfanyakazi! Sasa fanya malipo.');
    }

    /**
     * API: Worker submits application.
     */
    public function apiStore(Job $job, Request $request)
    {
        $user = $request->user();

        if (! in_array($user->role, ['mfanyakazi', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Wafanyakazi tu.'], 403);
        }

        if ($job->user_id === $user->id) {
            return response()->json(['success' => false, 'message' => 'Huwezi kuomba kazi yako.'], 403);
        }

        if (! self::jobAcceptsApplications($job)) {
            return response()->json(['success' => false, 'message' => 'Kazi haipokei maombi.'], 422);
        }

        $existing = JobApplication::where('work_order_id', $job->id)
            ->where('worker_id', $user->id)
            ->whereNotIn('status', [JobApplication::STATUS_WITHDRAWN, JobApplication::STATUS_REJECTED])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Tayari umeomba.'], 422);
        }

        $validated = $request->validate([
            'proposed_amount' => ['required', 'integer', 'min:1000'],
            'message' => ['required', 'string', 'max:1000'],
            'eta_text' => ['nullable', 'string', 'max:100'],
        ]);

        $application = DB::transaction(function () use ($job, $user, $validated) {
            $app = JobApplication::create([
                'work_order_id' => $job->id,
                'worker_id' => $user->id,
                'proposed_amount' => $validated['proposed_amount'],
                'message' => $validated['message'],
                'eta_text' => $validated['eta_text'] ?? null,
                'status' => JobApplication::STATUS_APPLIED,
            ]);

            $job->increment('application_count');

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => "✋ {$user->name} ameomba kufanya kazi yako kwa TZS ".number_format($validated['proposed_amount']),
            ]);

            return $app;
        });

        return response()->json([
            'success' => true,
            'data' => $application->load('worker'),
            'message' => 'Ombi limewasilishwa!',
        ], 201);
    }

    /**
     * API: Client selects a worker.
     */
    public function apiSelect(Job $job, JobApplication $application, Request $request)
    {
        $user = $request->user();

        if ($job->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hii sio kazi yako.'], 403);
        }

        if (! self::jobAllowsWorkerSelection($job)) {
            return response()->json(['success' => false, 'message' => 'Kazi haiko katika hali ya kuchagua.'], 422);
        }

        DB::transaction(function () use ($job, $application, $user) {
            $agreedAmount = $application->getAgreedAmount();

            $application->update(['status' => JobApplication::STATUS_SELECTED, 'selected_at' => now()]);

            JobApplication::where('work_order_id', $job->id)
                ->where('id', '!=', $application->id)
                ->whereNotIn('status', [JobApplication::STATUS_WITHDRAWN, JobApplication::STATUS_REJECTED])
                ->update(['status' => JobApplication::STATUS_REJECTED, 'rejected_at' => now()]);

            $job->selected_worker_id = $application->worker_id;
            $job->agreed_amount = $agreedAmount;
            if (! $job->completion_code) {
                $job->completion_code = (string) random_int(100000, 999999);
            }
            $job->transitionStatus(Job::S_AWAITING_PAYMENT, $user->id, "Selected worker #{$application->worker_id}");

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $application->worker_id,
                'message' => '🎉 Umechaguliwa! Tunasubiri malipo.',
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => $job->fresh()->load('selectedWorker', 'applications'),
            'message' => 'Mfanyakazi amechaguliwa. Fanya malipo.',
        ]);
    }

    /**
     * Ensure current user owns the job.
     */
    protected function authorizeClient(Job $job): void
    {
        $user = Auth::user();
        if ($job->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Hii sio kazi yako.');
        }
    }
}
