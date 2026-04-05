<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Mfanyakazi anaweza gumzo ikiwa amecomment, amechaguliwa, ameomba (ombi halisi), n.k.
     */
    private function workerHasChatAccess(Job $job, int $userId): bool
    {
        if ((int) $job->accepted_worker_id === $userId) {
            return true;
        }
        if ((int) $job->selected_worker_id === $userId) {
            return true;
        }
        if ($job->comments()->where('user_id', $userId)->exists()) {
            return true;
        }

        return $job->applications()
            ->where('worker_id', $userId)
            ->whereNotIn('status', ['withdrawn', 'rejected'])
            ->exists();
    }

    /**
     * API / muunganiko: muhitaji mmiliki au mfanyakazi aliye na haki ya gumzo.
     */
    public function userCanAccessJobChat(Job $job, User $user): bool
    {
        if ((int) $job->user_id === (int) $user->id) {
            return true;
        }

        return $this->workerHasChatAccess($job, (int) $user->id);
    }

    /**
     * Display chat interface for a specific job
     */
    public function show(Job $job, Request $request)
    {
        $user = Auth::user();

        // Check if user is muhitaji (job owner)
        $isMuhitaji = $job->user_id === $user->id;

        // Mfanyakazi: maoni ya zamani, ombi jipya, au amekabidhiwa kazi
        if (! $isMuhitaji && ! $this->workerHasChatAccess($job, (int) $user->id)) {
            abort(403, 'Huna ruhusa ya kuona mazungumzo haya. Omba kazi au tumia mfumo wa maoni kwanza.');
        }

        // Determine the other user for the conversation
        if ($isMuhitaji) {
            $workerId = $request->query('worker_id');
            if ($workerId) {
                $otherUser = User::where('id', $workerId)->where('role', 'mfanyakazi')->first();
                if (! $otherUser) {
                    abort(404, 'Mfanyakazi hajapatikana.');
                }
                $workerAllowed = $this->workerHasChatAccess($job, (int) $otherUser->id);
                if (! $workerAllowed) {
                    abort(403, 'Huna mazungumzo na mfanyakazi huyu kwenye kazi hii.');
                }
            } else {
                $otherUser = $job->acceptedWorker;
            }
        } else {
            $otherUser = $job->muhitaji;
        }

        if (! $otherUser) {
            abort(404, 'Mtumiaji mwingine hajapatikana.');
        }

        // Get messages for this job between these two users
        $messages = PrivateMessage::forJob($job->id)
            ->betweenUsers($user->id, $otherUser->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark only this thread as read (muhitaji + worker_id = separate threads per worker)
        PrivateMessage::forJob($job->id)
            ->betweenUsers($user->id, $otherUser->id)
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

        if (! $isMuhitaji && ! $this->workerHasChatAccess($job, (int) $user->id)) {
            abort(403, 'Huna ruhusa ya kutuma ujumbe. Omba kazi au tumia mfumo wa maoni kwanza.');
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

        if (! $receiverId) {
            return back()->with('error', 'Mpokeaji hajapatikana.');
        }

        if ($isMuhitaji) {
            $recv = User::find($receiverId);
            if (! $recv || $recv->role !== 'mfanyakazi') {
                return back()->with('error', 'Mpokeaji si sahihi.');
            }
            $workerOk = $this->workerHasChatAccess($job, (int) $receiverId);
            if (! $workerOk) {
                return back()->with('error', 'Huwezi kutuma ujumbe kwa mfanyakazi huyu kwenye kazi hii.');
            }
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

        $redirectTo = ['job' => $job];
        if ($isMuhitaji && $receiverId) {
            $redirectTo['worker_id'] = $receiverId;
        }

        return redirect()->route('chat.show', $redirectTo)->with('success', 'Ujumbe umetumwa.');
    }

    /**
     * Send a message (API)
     */
    public function apiSend(Request $request, Job $job)
    {
        $user = Auth::user();

        // Check if user is muhitaji (job owner)
        $isMuhitaji = $job->user_id === $user->id;

        if (! $isMuhitaji && ! $this->workerHasChatAccess($job, (int) $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Huna ruhusa ya kutuma ujumbe. Omba kazi au tumia mfumo wa maoni kwanza.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
            'receiver_id' => 'nullable|exists:users,id', // Allow specifying receiver
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Taarifa hazujakamilika',
                'errors' => $validator->errors(),
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

        if (! $receiverId) {
            return response()->json([
                'success' => false,
                'message' => 'Mpokeaji hajapatikana in this context.',
            ], 404);
        }

        if ($isMuhitaji) {
            $recv = User::find($receiverId);
            if (! $recv || $recv->role !== 'mfanyakazi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Mpokeaji si sahihi.',
                ], 422);
            }
            if (! $this->workerHasChatAccess($job, (int) $receiverId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huwezi kutuma ujumbe kwa mfanyakazi huyu kwenye kazi hii.',
                ], 403);
            }
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

            if (! $otherUser) {
                continue;
            }

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

        $isMuhitaji = $job->user_id === $user->id;

        if (! $isMuhitaji && ! $this->workerHasChatAccess($job, (int) $user->id)) {
            abort(403);
        }

        $lastId = $request->get('last_id', 0);
        $otherUserId = $request->get('other_user_id');

        if (! $otherUserId) {
            $otherUserId = $user->id === $job->user_id
                ? $job->accepted_worker_id
                : $job->user_id;
        }
        $otherUserId = (int) $otherUserId;

        if ($isMuhitaji) {
            $ou = User::find($otherUserId);
            if (! $ou || $ou->role !== 'mfanyakazi') {
                abort(403);
            }
            $workerOk = $this->workerHasChatAccess($job, $otherUserId);
            if (! $workerOk) {
                abort(403);
            }
        } elseif ((int) $otherUserId !== (int) $job->user_id) {
            abort(403);
        }

        $newMessages = PrivateMessage::forJob($job->id)
            ->betweenUsers($user->id, $otherUserId)
            ->where('id', '>', $lastId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        PrivateMessage::forJob($job->id)
            ->betweenUsers($user->id, $otherUserId)
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
