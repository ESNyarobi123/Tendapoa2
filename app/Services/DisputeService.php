<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * DisputeService handles dispute lifecycle:
 * - Opening disputes
 * - Admin resolution (full worker, full client, split)
 * - Adding messages/evidence
 */
class DisputeService
{
    public function __construct(
        protected EscrowService $escrowService
    ) {}

    /**
     * Open a dispute on a submitted or in_progress job.
     */
    public function open(Job $job, User $raisedBy, string $reason): Dispute
    {
        if (! in_array($job->status, [Job::S_SUBMITTED, Job::S_IN_PROGRESS, Job::S_FUNDED])) {
            throw new RuntimeException('Kazi lazima iwe katika hali inayoruhusu mgogoro.');
        }

        // Determine against_user
        $againstId = $raisedBy->id === $job->user_id
            ? ($job->accepted_worker_id ?? $job->selected_worker_id)
            : $job->user_id;

        if (! $againstId) {
            throw new RuntimeException('Hakuna mtu wa pili kwenye mgogoro huu.');
        }

        return DB::transaction(function () use ($job, $raisedBy, $againstId, $reason) {
            $dispute = Dispute::create([
                'work_order_id' => $job->id,
                'raised_by' => $raisedBy->id,
                'against_user' => $againstId,
                'status' => Dispute::STATUS_OPEN,
                'reason' => $reason,
            ]);

            $job->transitionStatus(Job::S_DISPUTED, $raisedBy->id, "Dispute opened: {$reason}");
            $job->disputed_at = now();
            $job->save();

            return $dispute;
        });
    }

    /**
     * Add a message/evidence to a dispute.
     */
    public function addMessage(Dispute $dispute, User $user, string $message, ?string $attachment = null): DisputeMessage
    {
        return DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'user_id' => $user->id,
            'message' => $message,
            'attachment' => $attachment,
            'is_admin' => $user->role === 'admin',
        ]);
    }

    /**
     * Admin resolves dispute: full payment to worker.
     */
    public function resolveFullWorker(Dispute $dispute, User $admin, ?string $note = null): Dispute
    {
        return $this->resolve($dispute, $admin, Dispute::STATUS_RESOLVED_FULL_WORKER, $note, function ($job, $escrow) {
            $result = $this->escrowService->releaseToWorker($job);

            return [
                'worker_amount' => $result['release_amount'],
                'client_refund_amount' => 0,
            ];
        });
    }

    /**
     * Admin resolves dispute: full refund to client.
     */
    public function resolveFullClient(Dispute $dispute, User $admin, ?string $note = null): Dispute
    {
        return $this->resolve($dispute, $admin, Dispute::STATUS_RESOLVED_FULL_CLIENT, $note, function ($job, $escrow) {
            $this->escrowService->refundToClient($job, 'Dispute resolved: full refund to client');

            return [
                'worker_amount' => 0,
                'client_refund_amount' => (int) $job->escrow_amount,
            ];
        });
    }

    /**
     * Admin resolves dispute: split between worker and client.
     */
    public function resolveSplit(Dispute $dispute, User $admin, int $workerAmount, int $clientAmount, ?string $note = null): Dispute
    {
        return $this->resolve($dispute, $admin, Dispute::STATUS_RESOLVED_SPLIT, $note, function ($job) use ($workerAmount, $clientAmount) {
            $this->escrowService->splitRelease($job, $workerAmount, $clientAmount);

            return [
                'worker_amount' => $workerAmount,
                'client_refund_amount' => $clientAmount,
            ];
        });
    }

    /**
     * Internal resolution handler.
     */
    protected function resolve(Dispute $dispute, User $admin, string $status, ?string $note, callable $action): Dispute
    {
        if (! $dispute->isOpen()) {
            throw new RuntimeException('Mgogoro huu tayari umeshughulikiwa.');
        }

        return DB::transaction(function () use ($dispute, $admin, $status, $note, $action) {
            $job = $dispute->job;

            // Execute the financial action (release/refund/split)
            $amounts = $action($job, $this->escrowService);

            $dispute->update([
                'status' => $status,
                'resolution_note' => $note,
                'worker_amount' => $amounts['worker_amount'] ?? null,
                'client_refund_amount' => $amounts['client_refund_amount'] ?? null,
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ]);

            // Transition job to completed or refunded based on resolution
            $newJobStatus = ($amounts['worker_amount'] ?? 0) > 0 ? Job::S_COMPLETED : Job::S_REFUNDED;
            $job->transitionStatus($newJobStatus, $admin->id, "Dispute resolved: {$status}");
            $job->completed_at = now();
            $job->save();

            return $dispute->fresh();
        });
    }
}
