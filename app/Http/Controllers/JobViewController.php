<?php

namespace App\Http\Controllers;

use App\Models\{Job, JobComment, PrivateMessage};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class JobViewController extends Controller
{
    public function show(Job $job)
    {
        $job->load('muhitaji', 'category', 'comments.user');
        return view('jobs.show', compact('job'));
    }

    public function comment(Job $job, Request $r)
    {
        $role = Auth::user()->role ?? null;
        if (!in_array($role, ['mfanyakazi', 'admin'], true)) {
            abort(403, 'Huna ruhusa (mfanyakazi/admin tu).');
        }

        $r->validate([
            'message' => ['required', 'max:1000'],
            'bid_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        JobComment::create([
            'work_order_id' => $job->id,
            'user_id' => Auth::id(),
            'message' => $r->input('message'),
            'is_application' => $r->boolean('is_application'),
            'bid_amount' => $r->input('bid_amount'),
        ]);

        return back();
    }

    public function apiShow(Job $job)
    {
        $job->load('muhitaji', 'category', 'comments.user', 'acceptedWorker', 'payment');
        $jobData = $job->toArray();
        if ($job->image) {
            $jobData['image_url'] = asset('storage/' . $job->image);
            // Add cache busting if file exists
            if (Storage::disk('public')->exists($job->image)) {
                $timestamp = filemtime(storage_path('app/public/' . $job->image));
                $jobData['image_url'] = asset('storage/' . $job->image) . '?v=' . $timestamp;
            }
        } else {
            $jobData['image_url'] = null;
        }
        return response()->json([
            'success' => true,
            'data' => $jobData
        ]);
    }

    public function accept(Job $job, JobComment $comment)
    {
        $role = Auth::user()->role ?? null;
        if (!in_array($role, ['muhitaji', 'admin'], true)) {
            abort(403, 'Huna ruhusa (muhitaji/admin tu).');
        }

        Gate::authorize('update', $job);

        // Generate completion code mara moja muhitaji anapomchagua mfanyakazi
        if (!$job->completion_code) {
            $job->completion_code = (string) random_int(100000, 999999);
        }

        $job->update([
            'accepted_worker_id' => $comment->user_id,
            'status' => 'assigned',
            'completion_code' => $job->completion_code,
        ]);

        // Create automatic welcome message so conversation appears in chat list
        // This notification goes from muhitaji to mfanyakazi
        PrivateMessage::create([
            'work_order_id' => $job->id,
            'sender_id' => Auth::id(), // Muhitaji
            'receiver_id' => $comment->user_id, // Mfanyakazi
            'message' => '🎉 Hongera! Umechaguliwa kufanya kazi hii: "' . $job->title . '". Tafadhali wasiliana nami kuzungumza maelezo zaidi. Code ya ukamilishaji ni: ' . $job->completion_code,
        ]);

        return back()->with('status', 'Umemchagua mfanyakazi. Code ya ukamilishaji: ' . $job->completion_code);
    }
}
