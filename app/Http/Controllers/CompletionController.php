<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PrivateMessage;
use App\Models\Review;
use App\Services\CompletionService;
use App\Services\DisputeService;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * CompletionController — handles the two-sided completion flow.
 *
 * - Worker accepts funded job -> in_progress
 * - Worker submits completion -> submitted
 * - Client confirms -> completed + escrow released
 * - Client requests revision -> back to in_progress
 * - Client opens dispute -> disputed
 * - Auto-release after timeout
 */
class CompletionController extends Controller
{
    public function __construct(
        protected CompletionService $completionService,
        protected DisputeService $disputeService
    ) {}

    /**
     * Worker accepts a funded job (funded -> in_progress).
     */
    public function workerAccept(Job $job)
    {
        $user = Auth::user();

        if ($job->accepted_worker_id !== $user->id) {
            abort(403, 'Wewe si mfanyakazi aliyechaguliwa.');
        }

        if ($job->status !== Job::S_FUNDED) {
            return back()->withErrors(['error' => 'Kazi hii haiko katika hali ya kukubaliwa.']);
        }

        DB::transaction(function () use ($job, $user) {
            $job->accepted_by_worker_at = now();
            $job->transitionStatus(Job::S_IN_PROGRESS, $user->id, 'Worker accepted the funded job');

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => "✅ {$user->name} amekubali kazi na ameanza kufanya kazi!",
            ]);
        });

        return back()->with('success', 'Umekubali kazi. Anza kufanya kazi!');
    }

    /**
     * Worker declines a funded job (funded -> awaiting_payment, refund escrow).
     */
    public function workerDecline(Job $job, EscrowService $escrow)
    {
        $user = Auth::user();

        if ($job->accepted_worker_id !== $user->id) {
            abort(403);
        }

        if ($job->status !== Job::S_FUNDED) {
            return back()->withErrors(['error' => 'Kazi hii haiwezi kukataliwa sasa.']);
        }

        DB::transaction(function () use ($job, $user, $escrow) {
            // Refund escrow to client
            $escrow->refundToClient($job, "Worker {$user->name} declined the job");

            // Reset job for re-selection
            $job->accepted_worker_id = null;
            $job->selected_worker_id = null;
            $job->funded_at = null;
            $job->transitionStatus(Job::S_OPEN, $user->id, 'Worker declined, job re-opened for applications');

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => "❌ {$user->name} amekataa kazi hii. Malipo yamerudishwa kwenye akaunti yako.",
            ]);
        });

        return redirect()->route('mfanyakazi.assigned')->with('status', 'Umekataa kazi. Muhitaji ataarifu.');
    }

    /**
     * Worker submits completion.
     */
    public function workerSubmit(Job $job, Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'code' => ['nullable', 'string', 'size:6'],
        ]);

        try {
            $this->completionService->workerSubmit(
                $job,
                $user,
                $request->input('notes'),
                $request->input('code')
            );

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => "📋 {$user->name} amewasilisha kazi kama imekamilika. Tafadhali thibitisha.",
            ]);

            return back()->with('success', 'Kazi imewasilishwa! Tunasubiri muhitaji athibitishe.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Client confirms completion.
     */
    public function clientConfirm(Job $job)
    {
        $user = Auth::user();

        try {
            $result = $this->completionService->clientConfirm($job, $user);

            $worker = $job->acceptedWorker;
            if ($worker) {
                PrivateMessage::create([
                    'work_order_id' => $job->id,
                    'sender_id' => $user->id,
                    'receiver_id' => $worker->id,
                    'message' => '🎉 Kazi imethibitishwa! TZS '.number_format($result['release']['release_amount']).' zimeingizwa kwenye wallet yako.',
                ]);
            }

            return redirect()->route('jobs.show', $job)
                ->with('success', 'Kazi imethibitishwa na malipo yametolewa!');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Client requests revision.
     */
    public function clientRevision(Job $job, Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->completionService->clientRequestRevision($job, $user, $request->input('reason'));

            $worker = $job->acceptedWorker;
            if ($worker) {
                PrivateMessage::create([
                    'work_order_id' => $job->id,
                    'sender_id' => $user->id,
                    'receiver_id' => $worker->id,
                    'message' => '🔄 Muhitaji ameomba marekebisho: '.$request->input('reason'),
                ]);
            }

            return back()->with('success', 'Ombi la marekebisho limetumwa.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Client opens a dispute.
     */
    public function clientDispute(Job $job, Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $dispute = $this->disputeService->open($job, $user, $request->input('reason'));

            $worker = $job->acceptedWorker;
            if ($worker) {
                PrivateMessage::create([
                    'work_order_id' => $job->id,
                    'sender_id' => $user->id,
                    'receiver_id' => $worker->id,
                    'message' => '⚠️ Mgogoro umefunguliwa kwa kazi hii. Admin wataangalia suala hili.',
                ]);
            }

            return redirect()->route('disputes.show', $dispute)
                ->with('success', 'Mgogoro umefunguliwa. Admin watawasiliana nawe.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Worker opens a dispute.
     */
    public function workerDispute(Job $job, Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $dispute = $this->disputeService->open($job, $user, $request->input('reason'));

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => '⚠️ Mgogoro umefunguliwa kwa kazi hii na mfanyakazi. Admin wataangalia.',
            ]);

            return redirect()->route('disputes.show', $dispute)
                ->with('success', 'Mgogoro umefunguliwa.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Submit a review after completion.
     */
    public function submitReview(Job $job, Request $request)
    {
        $user = Auth::user();

        if ($job->status !== Job::S_COMPLETED) {
            return back()->withErrors(['error' => 'Kazi lazima iwe imekamilika ili kutoa review.']);
        }

        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        // Determine reviewee
        $revieweeId = $user->id === $job->user_id
            ? $job->accepted_worker_id
            : $job->user_id;

        $existing = Review::where('work_order_id', $job->id)
            ->where('reviewer_id', $user->id)
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'Tayari umetoa review.']);
        }

        Review::create([
            'work_order_id' => $job->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $revieweeId,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        return back()->with('success', 'Asante kwa review yako!');
    }

    /* ====================================
     |  API ENDPOINTS
     * ==================================== */

    public function apiWorkerAccept(Job $job, Request $request)
    {
        $user = $request->user();
        if ($job->accepted_worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Si mfanyakazi.'], 403);
        }
        if ($job->status !== Job::S_FUNDED) {
            return response()->json(['success' => false, 'message' => 'Kazi haiko funded.'], 422);
        }

        DB::transaction(function () use ($job, $user) {
            $job->accepted_by_worker_at = now();
            $job->transitionStatus(Job::S_IN_PROGRESS, $user->id, 'Worker accepted');
            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => '✅ Kazi imekubaliwa na mfanyakazi.',
            ]);
        });

        return response()->json([
            'success' => true,
            'data' => $job->fresh()->load('muhitaji', 'category'),
            'message' => 'Umekubali kazi!',
        ]);
    }

    public function apiWorkerDecline(Job $job, Request $request, EscrowService $escrow)
    {
        $user = $request->user();
        if ($job->accepted_worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Si mfanyakazi.'], 403);
        }
        if ($job->status !== Job::S_FUNDED) {
            return response()->json(['success' => false, 'message' => 'Kazi haiko funded.'], 422);
        }

        DB::transaction(function () use ($job, $user, $escrow) {
            $escrow->refundToClient($job, 'Worker declined');
            $job->accepted_worker_id = null;
            $job->selected_worker_id = null;
            $job->funded_at = null;
            $job->transitionStatus(Job::S_OPEN, $user->id, 'Worker declined, re-opened');
        });

        return response()->json(['success' => true, 'message' => 'Umekataa kazi. Malipo yamerudishwa.']);
    }

    public function apiWorkerSubmit(Job $job, Request $request)
    {
        $user = $request->user();
        $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'code' => ['nullable', 'string', 'size:6'],
        ]);

        try {
            $this->completionService->workerSubmit($job, $user, $request->input('notes'), $request->input('code'));
            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => '📋 Kazi imewasilishwa kama imekamilika.',
            ]);

            return response()->json([
                'success' => true,
                'data' => $job->fresh(),
                'message' => 'Kazi imewasilishwa!',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function apiClientConfirm(Job $job, Request $request)
    {
        $user = $request->user();
        try {
            $result = $this->completionService->clientConfirm($job, $user);

            return response()->json([
                'success' => true,
                'data' => ['job' => $result['job'], 'release' => $result['release']],
                'message' => 'Kazi imethibitishwa! Malipo yametolewa.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function apiClientRevision(Job $job, Request $request)
    {
        $user = $request->user();
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        try {
            $this->completionService->clientRequestRevision($job, $user, $request->input('reason'));

            return response()->json(['success' => true, 'message' => 'Ombi la marekebisho limetumwa.']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function apiOpenDispute(Job $job, Request $request)
    {
        $user = $request->user();
        $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        try {
            $dispute = $this->disputeService->open($job, $user, $request->input('reason'));

            return response()->json([
                'success' => true,
                'data' => $dispute,
                'message' => 'Mgogoro umefunguliwa.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function apiSubmitReview(Job $job, Request $request)
    {
        $user = $request->user();
        if ($job->status !== Job::S_COMPLETED) {
            return response()->json(['success' => false, 'message' => 'Kazi haijakamilika.'], 422);
        }
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);
        $revieweeId = $user->id === $job->user_id ? $job->accepted_worker_id : $job->user_id;

        $existing = Review::where('work_order_id', $job->id)->where('reviewer_id', $user->id)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Tayari umetoa review.'], 422);
        }

        $review = Review::create([
            'work_order_id' => $job->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $revieweeId,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        return response()->json(['success' => true, 'data' => $review, 'message' => 'Asante kwa review!']);
    }
}
