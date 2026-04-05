<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function updates()
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $since = now()->subSeconds(45);

        // Kazi zilizosasishwa hivi karibuni na mfanyakazi huyu ndiye aliyeidhinishwa
        $newJobs = Job::where('accepted_worker_id', $user->id)
            ->where('updated_at', '>=', $since)
            ->get();

        $completedJobs = Job::where('accepted_worker_id', $user->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $since)
            ->get();

        $newJobsPayload = $newJobs->map(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'amount' => $job->amount,
                'status' => $job->status,
                'updated_at' => $job->updated_at?->toIso8601String(),
            ];
        });

        $completedPayload = $completedJobs->map(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'amount' => $job->amount,
                'completed_at' => $job->completed_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'new_jobs' => $newJobsPayload,
                'completed_jobs' => $completedPayload,
                'timestamp' => now()->toIso8601String(),
            ],
            // Legacy keys (Flutter / clients za zamani)
            'newJobs' => $newJobsPayload,
            'completedJobs' => $completedPayload,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
