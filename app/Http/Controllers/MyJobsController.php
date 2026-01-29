<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Job;

class MyJobsController extends Controller
{
    public function index()
    {
        $u = Auth::user();
        if (!$u || !in_array($u->role, ['muhitaji', 'admin']))
            abort(403);

        $query = Job::withCount('comments')
            ->with(['acceptedWorker', 'category'])
            ->where('user_id', $u->id);

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $jobs = $query->latest()->paginate(12);

        return view('muhitaji.my_jobs', compact('jobs'));
    }

    public function apiIndex()
    {
        $u = Auth::user();
        if (!$u || !in_array($u->role, ['muhitaji', 'admin'])) {
            return response()->json([
                'error' => 'Huna ruhusa ya kupata kazi hizi.',
                'status' => 'forbidden'
            ], 403);
        }

        $query = Job::withCount('comments')
            ->with(['acceptedWorker', 'category', 'payment'])
            ->where('user_id', $u->id);

        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $jobs = $query->latest()->paginate(12);

        // Add image URLs to jobs
        $jobsData = $jobs->items();
        foreach ($jobsData as $job) {
            if ($job->image) {
                $job->image_url = asset('storage/' . $job->image);
                // Add cache busting
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($job->image)) {
                    $timestamp = filemtime(storage_path('app/public/' . $job->image));
                    $job->image_url = asset('storage/' . $job->image) . '?v=' . $timestamp;
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
                'has_more' => $jobs->hasMorePages()
            ],
            'status' => 'success'
        ]);
    }

}
