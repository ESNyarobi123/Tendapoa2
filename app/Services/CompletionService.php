<?php

namespace App\Services;

use App\Models\Job;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * CompletionService handles the two-sided completion flow:
 * 1. Worker submits completion (job → submitted)
 * 2. Client confirms completion (job → completed, escrow released)
 * 3. Auto-release after timeout if client doesn't respond
 */
class CompletionService
{
    public function __construct(
        protected EscrowService $escrowService
    ) {}

    /**
     * Worker submits job as completed.
     * Optionally with photos/notes as proof.
     */
    public function workerSubmit(Job $job, User $worker, ?string $notes = null, ?string $completionCode = null): Job
    {
        if ($job->status !== Job::S_IN_PROGRESS) {
            throw new RuntimeException('Kazi lazima iwe katika hali ya "in_progress" ili kuwasilisha.');
        }

        if ($job->accepted_worker_id !== $worker->id) {
            throw new RuntimeException('Wewe si mfanyakazi aliyechaguliwa kwa kazi hii.');
        }

        // Optional: validate completion code if it exists
        if ($job->completion_code && $completionCode) {
            if ($completionCode !== $job->completion_code) {
                throw new RuntimeException('Code ya kukamilisha si sahihi.');
            }
        }

        return DB::transaction(function () use ($job, $worker, $notes) {
            $job->transitionStatus(Job::S_SUBMITTED, $worker->id, $notes ?? 'Worker submitted completion');

            $job->submitted_at = now();

            // Set auto-release deadline
            $autoReleaseHours = (int) Setting::get('auto_release_hours', 72);
            if ($autoReleaseHours > 0) {
                $job->auto_release_at = now()->addHours($autoReleaseHours);
            }

            $job->save();

            return $job;
        });
    }

    /**
     * Client confirms job completion.
     * Triggers escrow release to worker.
     */
    public function clientConfirm(Job $job, User $client): array
    {
        if ($job->status !== Job::S_SUBMITTED) {
            throw new RuntimeException('Kazi lazima iwe "submitted" ili kuthibitisha.');
        }

        if ($job->user_id !== $client->id) {
            throw new RuntimeException('Wewe si mmiliki wa kazi hii.');
        }

        return DB::transaction(function () use ($job, $client) {
            $job->transitionStatus(Job::S_COMPLETED, $client->id, 'Client confirmed completion');

            $job->confirmed_at = now();
            $job->completed_at = now();
            $job->save();

            // Release escrow to worker
            $releaseResult = $this->escrowService->releaseToWorker($job);

            return [
                'job' => $job->fresh(),
                'release' => $releaseResult,
            ];
        });
    }

    /**
     * Client requests revision (job goes back to in_progress).
     */
    public function clientRequestRevision(Job $job, User $client, string $reason): Job
    {
        if ($job->status !== Job::S_SUBMITTED) {
            throw new RuntimeException('Kazi lazima iwe "submitted" ili kuomba marekebisho.');
        }

        if ($job->user_id !== $client->id) {
            throw new RuntimeException('Wewe si mmiliki wa kazi hii.');
        }

        return DB::transaction(function () use ($job, $client, $reason) {
            $job->transitionStatus(Job::S_IN_PROGRESS, $client->id, "Revision requested: {$reason}");

            $job->submitted_at = null;
            $job->auto_release_at = null;
            $job->save();

            return $job;
        });
    }

    /**
     * Auto-release: system releases funds if client hasn't responded within timeout.
     * Called by a scheduled command.
     */
    public function processAutoReleases(): int
    {
        $jobs = Job::where('status', Job::S_SUBMITTED)
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($jobs as $job) {
            try {
                DB::transaction(function () use ($job) {
                    $job->transitionStatus(Job::S_COMPLETED, null, 'Auto-released: client did not respond within deadline');
                    $job->confirmed_at = now();
                    $job->completed_at = now();
                    $job->save();

                    $this->escrowService->releaseToWorker($job);
                });
                $count++;
            } catch (\Throwable $e) {
                \Log::error("Auto-release failed for job #{$job->id}: ".$e->getMessage());
            }
        }

        return $count;
    }
}
