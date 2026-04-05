<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkerApplicationsController extends Controller
{
    /**
     * Maombi yote aliyowasilisha mfanyakazi (fuatilia hali, nenda kwenye kazi).
     */
    public function index()
    {
        $user = Auth::user();
        abort_unless($user && in_array($user->role, ['mfanyakazi', 'admin'], true), 403);

        $applications = JobApplication::query()
            ->where('worker_id', $user->id)
            ->with(['job.category', 'job.muhitaji'])
            ->latest('updated_at')
            ->paginate(20);

        return view('mfanyakazi.my_applications', compact('applications'));
    }

    /**
     * API: Maombi yote aliyowasilisha mfanyakazi (kama /mfanyakazi/my-applications).
     */
    public function apiIndex(Request $request)
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['mfanyakazi', 'admin'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Huna ruhusa.',
            ], 403);
        }

        $applications = JobApplication::query()
            ->where('worker_id', $user->id)
            ->with(['job.category', 'job.muhitaji'])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'applications' => $applications->items(),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                    'has_more' => $applications->hasMorePages(),
                ],
            ],
        ]);
    }
}
