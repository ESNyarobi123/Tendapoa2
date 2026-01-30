<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Display chat interface for a specific job
     */
    public function show(Job $job, Request $request)
    {
        $user = Auth::user();

        // Check if user is muhitaji (job owner)
        $isMuhitaji = $job->user_id === $user->id;

        // Check if user is mfanyakazi who has commented on this job
        $hasCommented = $job->comments()->where('user_id', $user->id)->exists();

        // Check if user is accepted worker
        $isAcceptedWorker = $job->accepted_worker_id === $user->id;

        // Allow access if: muhitaji, commented mfanyakazi, or accepted worker
        if (!$isMuhitaji && !$hasCommented && !$isAcceptedWorker) {
            abort(403, 'Huna ruhusa ya kuona mazungumzo haya. Tuma comment kwanza.');
        }

        // Determine the other user for the conversation
        if ($isMuhitaji) {
            // If muhitaji, check if there's a specific worker_id in request
            $workerId = $request->get('worker_id');
            if ($workerId) {
                $otherUser = User::find($workerId);
            } else {
                // Default to accepted worker if exists
                $otherUser = $job->acceptedWorker;
            }
        } else {
            // If mfanyakazi, other user is muhitaji
            $otherUser = $job->muhitaji;
        }

        if (!$otherUser) {
            abort(404, 'Mtumiaji mwingine hajapatikana.');
        }

        // Get messages for this job between these two users
        $messages = PrivateMessage::forJob($job->id)
            ->betweenUsers($user->id, $otherUser->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        PrivateMessage::forJob($job->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Get unread count for other conversations
        $unreadCount = PrivateMessage::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('chat.show', compact('job', 'messages', 'otherUser', 'unreadCount'));
    }

    /**
     * Send a message
     */
    public function send(Request $request, Job $job)
    {
        $user = Auth::user();

        // Check if user is muhitaji (job owner)
        $isMuhitaji = $job->user_id === $user->id;

        // Check if user is mfanyakazi who has commented on this job
        $hasCommented = $job->comments()->where('user_id', $user->id)->exists();

        // Check if user is accepted worker
        $isAcceptedWorker = $job->accepted_worker_id === $user->id;

        // Allow sending if: muhitaji, commented mfanyakazi, or accepted worker
        if (!$isMuhitaji && !$hasCommented && !$isAcceptedWorker) {
            abort(403, 'Huna ruhusa ya kutuma ujumbe. Tuma comment kwanza.');
        }

        $request->validate([
            'message' => 'required|string|max:5000',
            'receiver_id' => 'nullable|exists:users,id', // Allow specifying receiver
        ]);

        // Determine receiver
        if ($isMuhitaji) {
            // Muhitaji can specify which mfanyakazi to send to
            $receiverId = $request->input('receiver_id') ?? $job->accepted_worker_id;
        } else {
            // Mfanyakazi always sends to muhitaji
            $receiverId = $job->user_id;
        }

        if (!$receiverId) {
            return back()->with('error', 'Mpokeaji hajapatikana.');
        }

        $message = PrivateMessage::create([
            'work_order_id' => $job->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ]);
        }

        return redirect()->route('chat.show', $job)->with('success', 'Ujumbe umetumwa.');
    }

    /**
     * Send a message (API)
     */
    public function apiSend(Request $request, Job $job)
    {
        $user = Auth::user();

        // Check if user is muhitaji (job owner)
        $isMuhitaji = $job->user_id === $user->id;

        // Check if user is mfanyakazi who has commented on this job
        $hasCommented = $job->comments()->where('user_id', $user->id)->exists();

        // Check if user is accepted worker
        $isAcceptedWorker = $job->accepted_worker_id === $user->id;

        // Allow sending if: muhitaji, commented mfanyakazi, or accepted worker
        if (!$isMuhitaji && !$hasCommented && !$isAcceptedWorker) {
            return response()->json([
                'success' => false,
                'message' => 'Huna ruhusa ya kutuma ujumbe. Tuma comment kwanza.'
            ], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
            'receiver_id' => 'nullable|exists:users,id', // Allow specifying receiver
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Taarifa hazujakamilika',
                'errors' => $validator->errors()
            ], 422);
        }

        // Determine receiver
        if ($isMuhitaji) {
            // Muhitaji can specify which mfanyakazi to send to
            $receiverId = $request->input('receiver_id') ?? $job->accepted_worker_id;
        } else {
            // Mfanyakazi always sends to muhitaji
            $receiverId = $job->user_id;
        }

        if (!$receiverId) {
            return response()->json([
                'success' => false,
                'message' => 'Mpokeaji hajapatikana in this context.'
            ], 404);
        }

        $message = PrivateMessage::create([
            'work_order_id' => $job->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ujumbe umetumwa',
            'data' => $message->load('sender'),
        ]);
    }

    /**
     * List all conversations for current user
     */
    public function index()
    {
        $user = Auth::user();

        // Get conversations from messages
        $messageConversations = DB::table('private_messages')
            ->select(
                'work_order_id',
                DB::raw('MAX(created_at) as last_message_at'),
                DB::raw("COUNT(CASE WHEN receiver_id = {$user->id} AND is_read = 0 THEN 1 END) as unread_count")
            )
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->groupBy('work_order_id')
            ->get()
            ->keyBy('work_order_id');

        // Get jobs where user is muhitaji with accepted worker OR user is accepted worker
        // These should appear in chat even if no messages yet
        $activeJobs = Job::with(['muhitaji', 'acceptedWorker', 'category'])
            ->whereNotNull('accepted_worker_id')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id) // User is muhitaji
                    ->orWhere('accepted_worker_id', $user->id); // User is accepted worker
            })
            ->get();

        // Build conversations list
        $conversations = collect();

        foreach ($activeJobs as $job) {
            $messageData = $messageConversations->get($job->id);

            // Determine the other user
            $otherUser = $user->id === $job->user_id
                ? $job->acceptedWorker
                : $job->muhitaji;

            if (!$otherUser)
                continue;

            $conversations->push((object) [
                'job' => $job,
                'other_user' => $otherUser,
                'last_message_at' => $messageData->last_message_at ?? $job->updated_at,
                'unread_count' => $messageData->unread_count ?? 0,
            ]);
        }

        // Sort by last message date
        $conversations = $conversations->sortByDesc('last_message_at');

        return view('chat.index', compact('conversations'));
    }

    /**
     * Get new messages (AJAX polling)
     */
    public function poll(Request $request, Job $job)
    {
        $user = Auth::user();

        // Check if user is muhitaji or has commented
        $isMuhitaji = $job->user_id === $user->id;
        $hasCommented = $job->comments()->where('user_id', $user->id)->exists();
        $isAcceptedWorker = $job->accepted_worker_id === $user->id;

        if (!$isMuhitaji && !$hasCommented && !$isAcceptedWorker) {
            abort(403);
        }

        $lastId = $request->get('last_id', 0);
        $otherUserId = $request->get('other_user_id');

        if (!$otherUserId) {
            $otherUserId = $user->id === $job->user_id
                ? $job->accepted_worker_id
                : $job->user_id;
        }

        $newMessages = PrivateMessage::forJob($job->id)
            ->betweenUsers($user->id, $otherUserId)
            ->where('id', '>', $lastId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        PrivateMessage::forJob($job->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'messages' => $newMessages,
            'count' => $newMessages->count(),
        ]);
    }

    /**
     * Get unread count for current user
     */
    public function unreadCount()
    {
        $count = PrivateMessage::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}

