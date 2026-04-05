<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MyJobsController extends Controller
{
    public function index()
    {
        $u = Auth::user();
        if (! $u || ! in_array($u->role, ['muhitaji', 'admin'])) {
            abort(403);
        }

        $query = Job::withCount(['comments', 'applications'])
            ->with(['acceptedWorker', 'selectedWorker', 'category'])
            ->where('user_id', $u->id);

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $jobs = $query->latest()->paginate(12);

        return view('muhitaji.my_jobs', compact('jobs'));
    }

    /**
     * Maombi yote ya wafanyakazi kwenye kazi za mteja (inbox ya kuunganisha na jobs.show).
     */
    public function applications()
    {
        $u = Auth::user();
        abort_unless($u && in_array($u->role, ['muhitaji', 'admin'], true), 403);

        $filter = request('filter');
        $query = JobApplication::query()
            ->whereHas('job', fn ($q) => $q->where('user_id', $u->id))
            ->active()
            ->with(['job.category', 'worker']);

        if ($filter === 'hatua') {
            $query->whereIn('status', [
                JobApplication::STATUS_APPLIED,
                JobApplication::STATUS_SHORTLISTED,
                JobApplication::STATUS_ACCEPTED_COUNTER,
            ]);
        }

        $applications = $query->latest('updated_at')->paginate(15)->withQueryString();

        return view('muhitaji.applications', compact('applications', 'filter'));
    }

    public function apiIndex()
    {
        $u = Auth::user();
        if (! $u || ! in_array($u->role, ['muhitaji', 'admin'])) {
            return response()->json([
                'error' => 'Huna ruhusa ya kupata kazi hizi.',
                'status' => 'forbidden',
            ], 403);
        }

        $query = Job::withCount(['comments', 'applications'])
            ->with(['acceptedWorker', 'selectedWorker', 'category', 'payment'])
            ->where('user_id', $u->id);

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $jobs = $query->latest()->paginate(12);

        // Add image URLs to jobs
        $jobsData = $jobs->items();
        foreach ($jobsData as $job) {
            if ($job->image) {
                $job->image_url = asset('storage/'.$job->image);
                // Add cache busting
                if (Storage::disk('public')->exists($job->image)) {
                    $timestamp = filemtime(storage_path('app/public/'.$job->image));
                    $job->image_url = asset('storage/'.$job->image).'?v='.$timestamp;
                }
            }
        }

        return response()->json([
            'jobs' => $jobsData,
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'has_more' => $jobs->hasMorePages(),
            ],
            'status' => 'success',
        ]);
    }

    /**
     * API: Maombi yote ya wafanyakazi kwenye kazi za muhitaji (kama /my/applications).
     */
    public function apiApplications(Request $request)
    {
        $u = $request->user();
        if (! $u || ! in_array($u->role, ['muhitaji', 'admin'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Huna ruhusa.',
            ], 403);
        }

        $filter = $request->get('filter');
        $query = JobApplication::query()
            ->whereHas('job', fn ($q) => $q->where('user_id', $u->id))
            ->active()
            ->with(['job.category', 'worker']);

        if ($filter === 'hatua') {
            $query->whereIn('status', [
                JobApplication::STATUS_APPLIED,
                JobApplication::STATUS_SHORTLISTED,
                JobApplication::STATUS_ACCEPTED_COUNTER,
            ]);
        }

        $applications = $query->latest('updated_at')->paginate(20)->withQueryString();

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
