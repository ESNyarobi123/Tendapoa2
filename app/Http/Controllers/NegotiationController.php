<?php

namespace App\Http\Controllers;

use App\Models\{Job, Quote, JobComment, Negotiation};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NegotiationController extends Controller
{
    /**
     * Show negotiation messages for a quote
     */
    public function showQuoteNegotiation(Quote $quote, Request $request)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->id !== $quote->worker_id && $user->id !== $quote->job->user_id) {
            abort(403, 'Huna ruhusa kuona mazungumzo haya.');
        }

        $job = $quote->job;
        $job->load('muhitaji', 'category');

        // Check if this is a real-time check for new messages
        if ($request->has('check_messages')) {
            $lastId = $request->get('last_id', 0);
            
            $newMessages = Negotiation::where('quote_id', $quote->id)
                ->where('id', '>', $lastId)
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($message) {
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'sender_name' => $message->sender->name,
                        'message' => $message->message,
                        'proposed_price' => $message->proposed_price,
                        'time_ago' => $message->created_at->diffForHumans(),
                    ];
                });

            return response()->json([
                'new_messages' => $newMessages,
                'sender_name' => $newMessages->count() > 0 ? $newMessages->last()['sender_name'] : null,
            ]);
        }

        $messages = Negotiation::where('quote_id', $quote->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Negotiation::where('quote_id', $quote->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('negotiations.quote', compact('quote', 'job', 'messages'));
    }

    /**
     * Send message in quote negotiation
     */
    public function sendQuoteMessage(Request $request, Quote $quote)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->id !== $quote->worker_id && $user->id !== $quote->job->user_id) {
            abort(403, 'Huna ruhusa kutuma ujumbe hapa.');
        }

        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'proposed_price' => ['nullable', 'integer', 'min:500'],
        ]);

        // Determine receiver
        $receiverId = ($user->id === $quote->worker_id) 
            ? $quote->job->user_id 
            : $quote->worker_id;

        Negotiation::create([
            'job_id' => $quote->job_id,
            'quote_id' => $quote->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'proposed_price' => $request->proposed_price,
        ]);

        // Update quote price if proposed
        if ($request->proposed_price && $user->role === 'muhitaji') {
            $quote->update(['quoted_price' => $request->proposed_price]);
        }

        return back()->with('success', 'Ujumbe umetumwa!');
    }

    /**
     * Confirm negotiated price
     */
    public function confirmPrice(Request $request, Quote $quote)
    {
        $user = Auth::user();
        
        // Check authorization - both muhitaji and mfanyakazi can confirm price
        if ($user->id !== $quote->job->user_id && $user->id !== $quote->worker_id) {
            abort(403, 'Huna ruhusa kuthibitisha bei hii.');
        }

        $request->validate([
            'confirmed_price' => ['required', 'integer', 'min:500'],
        ]);

        $confirmedPrice = $request->confirmed_price;

        // Update quote with confirmed price
        $quote->update([
            'quoted_price' => $confirmedPrice,
            'status' => 'confirmed_price',
            'confirmed_at' => now(),
        ]);

        // Update job with confirmed price
        $quote->job->update([
            'price' => $confirmedPrice,
            'status' => 'quote_confirmed',
        ]);

        // Determine receiver and sender names
        $receiverId = ($user->id === $quote->worker_id) ? $quote->job->user_id : $quote->worker_id;
        $senderName = $user->name;
        $receiverName = ($user->id === $quote->worker_id) ? $quote->job->muhitaji->name : $quote->worker->name;

        // Send notification message
        Negotiation::create([
            'job_id' => $quote->job_id,
            'quote_id' => $quote->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => "Bei imethibitishwa na {$senderName}: TZS " . number_format($confirmedPrice) . ". Kazi itaendelea na bei hii.",
            'proposed_price' => $confirmedPrice,
        ]);

        return redirect()->back()->with('success', "Bei imethibitishwa! {$receiverName} atapata taarifa.");
    }

    /**
     * Show negotiation messages for a comment
     */
    public function showCommentNegotiation(JobComment $comment)
    {
        $user = Auth::user();
        $job = $comment->workOrder;

        // Check authorization
        if ($user->id !== $comment->user_id && $user->id !== $job->user_id) {
            abort(403, 'Huna ruhusa kuona mazungumzo haya.');
        }

        $job->load('muhitaji', 'category');

        $messages = Negotiation::where('comment_id', $comment->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Negotiation::where('comment_id', $comment->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('negotiations.comment', compact('comment', 'job', 'messages'));
    }

    /**
     * Send message in comment negotiation
     */
    public function sendCommentMessage(Request $request, JobComment $comment)
    {
        $user = Auth::user();
        $job = $comment->workOrder;

        // Check authorization
        if ($user->id !== $comment->user_id && $user->id !== $job->user_id) {
            abort(403, 'Huna ruhusa kutuma ujumbe hapa.');
        }

        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'proposed_price' => ['nullable', 'integer', 'min:500'],
        ]);

        // Determine receiver
        $receiverId = ($user->id === $comment->user_id) 
            ? $job->user_id 
            : $comment->user_id;

        Negotiation::create([
            'job_id' => $job->id,
            'comment_id' => $comment->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'proposed_price' => $request->proposed_price,
        ]);

        // Update comment bid amount if proposed by worker
        if ($request->proposed_price && $user->role === 'mfanyakazi') {
            $comment->update(['bid_amount' => $request->proposed_price]);
        }

        return back()->with('success', 'Ujumbe umetumwa!');
    }

    /**
     * Get unread negotiation count for user
     */
    public function getUnreadCount()
    {
        $user = Auth::user();

        $count = Negotiation::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
