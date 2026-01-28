<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobComment;
use App\Models\PrivateMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Store a new comment (Mfanyakazi)
     * Types: comment, application, offer
     */
    public function store(Job $job, Request $request)
    {
        $user = Auth::user();

        // Only mfanyakazi or admin can comment
        if (!in_array($user->role, ['mfanyakazi', 'admin'])) {
            return back()->with('error', 'Huna ruhusa ya kutuma maoni (mfanyakazi tu).');
        }

        // Can't comment on your own job
        if ($job->user_id === $user->id) {
            return back()->with('error', 'Huwezi kujisajili kwenye kazi yako mwenyewe.');
        }

        // Can't comment if job is not posted
        if ($job->status !== 'posted') {
            return back()->with('error', 'Kazi hii haipo wazi tena.');
        }

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'in:comment,application,offer'],
            'bid_amount' => ['nullable', 'integer', 'min:1000'],
        ]);

        $type = $request->input('type', 'comment');
        $isApplication = $type === 'application' || $request->boolean('is_application');
        $isNegotiation = $type === 'offer' || ($request->filled('bid_amount') && $request->bid_amount != $job->price);

        // Create the comment
        $comment = JobComment::create([
            'work_order_id' => $job->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'type' => $isNegotiation ? JobComment::TYPE_OFFER : ($isApplication ? JobComment::TYPE_APPLICATION : JobComment::TYPE_COMMENT),
            'status' => JobComment::STATUS_PENDING,
            'is_application' => $isApplication,
            'is_negotiation' => $isNegotiation,
            'bid_amount' => $request->bid_amount,
            'original_price' => $job->price,
        ]);

        // Notify muhitaji via private message
        if ($isApplication || $isNegotiation) {
            $notificationMessage = $isNegotiation
                ? "💰 {$user->name} amependekeza bei ya TZS " . number_format($request->bid_amount) . " kwa kazi yako: \"{$job->title}\""
                : "✋ {$user->name} ameomba kufanya kazi yako: \"{$job->title}\"";

            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $job->user_id,
                'message' => $notificationMessage,
            ]);
        }

        $successMessage = match ($type) {
            'application' => 'Ombi lako limetumwa! Subiri muhitaji akujibu.',
            'offer' => 'Pendekezo la bei limetumwa! Subiri muhitaji akujibu.',
            default => 'Maoni yako yametumwa!'
        };

        return back()->with('success', $successMessage);
    }

    /**
     * Muhitaji replies to a comment
     */
    public function reply(Job $job, JobComment $comment, Request $request)
    {
        $user = Auth::user();

        // Only job owner can reply
        if ($job->user_id !== $user->id) {
            return back()->with('error', 'Wewe si mmiliki wa kazi hii.');
        }

        $request->validate([
            'reply_message' => ['required', 'string', 'max:1000'],
        ]);

        $comment->update([
            'reply_message' => $request->reply_message,
            'replied_at' => now(),
        ]);

        // Notify mfanyakazi
        PrivateMessage::create([
            'work_order_id' => $job->id,
            'sender_id' => $user->id,
            'receiver_id' => $comment->user_id,
            'message' => "💬 {$user->name} amejibu maoni yako kuhusu: \"{$job->title}\" - \"{$request->reply_message}\"",
        ]);

        return back()->with('success', 'Jibu limemetumwa!');
    }

    /**
     * Muhitaji accepts an application/offer
     */
    public function accept(Job $job, JobComment $comment, Request $request)
    {
        $user = Auth::user();

        // Only job owner can accept
        if ($job->user_id !== $user->id) {
            return back()->with('error', 'Wewe si mmiliki wa kazi hii.');
        }

        // Job must be in posted status
        if ($job->status !== 'posted') {
            return back()->with('error', 'Kazi hii haipo wazi tena.');
        }

        // Comment user must be mfanyakazi
        if ($comment->user->role !== 'mfanyakazi') {
            return back()->with('error', 'Mtumiaji huyu si mfanyakazi.');
        }

        DB::transaction(function () use ($job, $comment, $user) {
            // Update comment status
            $comment->update([
                'status' => JobComment::STATUS_ACCEPTED,
                'replied_at' => now(),
            ]);

            // If there was a price negotiation, update job price
            $agreedPrice = $comment->bid_amount ?? $job->price;

            // Generate completion code if not exists
            if (!$job->completion_code) {
                $job->completion_code = (string) random_int(100000, 999999);
            }

            // Update job
            $job->update([
                'accepted_worker_id' => $comment->user_id,
                'status' => 'assigned',
                'price' => $agreedPrice,
                'completion_code' => $job->completion_code,
            ]);

            // Mark other applications as rejected
            JobComment::where('work_order_id', $job->id)
                ->where('id', '!=', $comment->id)
                ->where(function ($q) {
                    $q->where('is_application', true)
                        ->orWhere('is_negotiation', true);
                })
                ->update(['status' => JobComment::STATUS_REJECTED]);

            // Notify accepted worker if exists or newly assigned
            if ($job->accepted_worker_id) {
                // Send Private Message
                PrivateMessage::create([
                    'work_order_id' => $job->id,
                    'sender_id' => $user->id,
                    'receiver_id' => $job->accepted_worker_id,
                    'message' => "🎉 Hongera! Umechaguliwa kufanya kazi: \"{$job->title}\"! Bei iliyokubaliwa: TZS " . number_format($agreedPrice) . ". Code ya ukamilishaji: {$job->completion_code}",
                ]);

                // Send Notification
                try {
                    $worker = \App\Models\User::find($job->accepted_worker_id);
                    if ($worker) {
                        $worker->notify(new \App\Notifications\JobAssignedNotification($job));
                    }
                } catch (\Exception $e) {
                }
            }
        });

        return back()->with('success', 'Umemchagua mfanyakazi! Code: ' . $job->completion_code);
    }

    /**
     * Muhitaji rejects an application/offer
     */
    public function reject(Job $job, JobComment $comment, Request $request)
    {
        $user = Auth::user();

        if ($job->user_id !== $user->id) {
            return back()->with('error', 'Wewe si mmiliki wa kazi hii.');
        }

        $request->validate([
            'reject_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $comment->update([
            'status' => JobComment::STATUS_REJECTED,
            'reply_message' => $request->reject_reason ?? 'Ombi limekataliwa.',
            'replied_at' => now(),
        ]);

        // Notify mfanyakazi
        PrivateMessage::create([
            'work_order_id' => $job->id,
            'sender_id' => $user->id,
            'receiver_id' => $comment->user_id,
            'message' => "❌ Pole, ombi lako la kazi \"{$job->title}\" limekataliwa. " . ($request->reject_reason ?? ''),
        ]);

        return back()->with('success', 'Ombi limekataliwa.');
    }

    /**
     * Muhitaji sends a counter offer
     */
    public function counterOffer(Job $job, JobComment $comment, Request $request)
    {
        $user = Auth::user();

        if ($job->user_id !== $user->id) {
            return back()->with('error', 'Wewe si mmiliki wa kazi hii.');
        }

        $request->validate([
            'counter_amount' => ['required', 'integer', 'min:1000'],
            'counter_message' => ['nullable', 'string', 'max:500'],
        ]);

        $comment->update([
            'status' => JobComment::STATUS_COUNTERED,
            'counter_amount' => $request->counter_amount,
            'reply_message' => $request->counter_message ?? 'Nimependekeza bei nyingine.',
            'replied_at' => now(),
        ]);

        // Notify mfanyakazi
        PrivateMessage::create([
            'work_order_id' => $job->id,
            'sender_id' => $user->id,
            'receiver_id' => $comment->user_id,
            'message' => "💰 {$user->name} amekupa counter offer ya TZS " . number_format($request->counter_amount) . " kwa kazi: \"{$job->title}\". " . ($request->counter_message ?? ''),
        ]);

        return back()->with('success', 'Counter offer imetumwa!');
    }

    /**
     * Mfanyakazi accepts a counter offer
     */
    public function acceptCounter(Job $job, JobComment $comment, Request $request)
    {
        $user = Auth::user();

        if ($comment->user_id !== $user->id) {
            return back()->with('error', 'Hii si counter offer yako.');
        }

        if ($comment->status !== JobComment::STATUS_COUNTERED) {
            return back()->with('error', 'Counter offer hii haipo tena.');
        }

        // Create a new acceptance comment
        $acceptanceComment = JobComment::create([
            'work_order_id' => $job->id,
            'user_id' => $user->id,
            'parent_id' => $comment->id,
            'message' => 'Nimekubali counter offer ya TZS ' . number_format($comment->counter_amount),
            'type' => JobComment::TYPE_OFFER,
            'status' => JobComment::STATUS_PENDING,
            'is_application' => true,
            'is_negotiation' => true,
            'bid_amount' => $comment->counter_amount,
            'original_price' => $job->price,
        ]);

        // Notify muhitaji
        PrivateMessage::create([
            'work_order_id' => $job->id,
            'sender_id' => $user->id,
            'receiver_id' => $job->user_id,
            'message' => "✅ {$user->name} amekubali counter offer yako ya TZS " . number_format($comment->counter_amount) . " kwa kazi: \"{$job->title}\". Bonyeza kumchagua!",
        ]);

        return back()->with('success', 'Umekubali counter offer! Subiri muhitaji akuchague.');
    }

    /**
     * Muhitaji increases job budget
     */
    public function increaseBudget(Job $job, Request $request)
    {
        $user = Auth::user();

        if ($job->user_id !== $user->id) {
            return back()->with('error', 'Wewe si mmiliki wa kazi hii.');
        }

        if (!in_array($job->status, ['posted', 'assigned'])) {
            return back()->with('error', 'Huwezi kuongeza bei kwa kazi hii.');
        }

        $request->validate([
            'additional_amount' => ['required', 'integer', 'min:1000'],
        ]);

        // Check if user has enough balance
        $wallet = $user->wallet;
        if (!$wallet || $wallet->balance < $request->additional_amount) {
            return back()->with('error', 'Huna pesa za kutosha kwenye wallet.');
        }

        DB::transaction(function () use ($job, $user, $request) {
            $oldPrice = $job->price;
            $newPrice = $job->price + $request->additional_amount;

            // Deduct from wallet
            $user->wallet->decrement('balance', $request->additional_amount);

            // Create transaction
            $user->wallet->transactions()->create([
                'type' => 'escrow_topup',
                'amount' => -$request->additional_amount,
                'balance_after' => $user->wallet->balance,
                'description' => "Kuongeza bajeti ya kazi: {$job->title}",
                'reference_id' => $job->id,
                'reference_type' => Job::class,
            ]);

            // Update job price
            $job->update(['price' => $newPrice]);

            // Add escrow amount
            if ($job->payment) {
                $job->payment->increment('amount', $request->additional_amount);
            }

            // Create system comment
            JobComment::create([
                'work_order_id' => $job->id,
                'user_id' => $user->id,
                'message' => "💰 Bei ya kazi imeongezwa kutoka TZS " . number_format($oldPrice) . " hadi TZS " . number_format($newPrice),
                'type' => JobComment::TYPE_SYSTEM,
                'status' => JobComment::STATUS_ACCEPTED,
            ]);

            // Notify accepted worker if exists
            if ($job->accepted_worker_id) {
                PrivateMessage::create([
                    'work_order_id' => $job->id,
                    'sender_id' => $user->id,
                    'receiver_id' => $job->accepted_worker_id,
                    'message' => "💰 Habari njema! Bei ya kazi imeongezwa hadi TZS " . number_format($newPrice),
                ]);
            }
        });

        return back()->with('success', 'Bei ya kazi imeongezwa!');
    }
}
