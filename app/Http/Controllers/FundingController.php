<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Payment;
use App\Models\PrivateMessage;
use App\Services\ClickPesaService;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * FundingController — handles job funding AFTER worker selection.
 *
 * NEW WORKFLOW:
 * - Client selected a worker -> job is awaiting_payment
 * - Client can fund from wallet OR external payment (ClickPesa)
 * - On success -> job becomes funded, worker is notified
 */
class FundingController extends Controller
{
    /**
     * Show the funding page (choose wallet or mobile payment).
     */
    public function show(Job $job)
    {
        $this->authorizeClient($job);

        if ($job->status !== Job::S_AWAITING_PAYMENT) {
            return redirect()->route('jobs.show', $job)
                ->withErrors(['error' => 'Kazi hii haihitaji malipo kwa sasa.']);
        }

        $wallet = Auth::user()->ensureWallet();
        $agreedAmount = (int) $job->agreed_amount;

        return view('jobs.fund', [
            'job' => $job->load('selectedWorker', 'category'),
            'wallet' => $wallet,
            'agreedAmount' => $agreedAmount,
            'canPayFromWallet' => $wallet->available_balance >= $agreedAmount,
        ]);
    }

    /**
     * Fund from wallet balance.
     */
    public function fundFromWallet(Job $job, EscrowService $escrow)
    {
        $this->authorizeClient($job);

        if ($job->status !== Job::S_AWAITING_PAYMENT) {
            return back()->withErrors(['error' => 'Kazi hii haihitaji malipo.']);
        }

        $client = Auth::user();
        $amount = (int) $job->agreed_amount;

        try {
            DB::transaction(function () use ($job, $client, $amount, $escrow) {
                $escrow->holdFromWallet($job, $client, $amount);
                $this->activateFundedJob($job, $client);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Malipo yamefanikiwa! Mfanyakazi amearifu.');
    }

    /**
     * Fund via external payment (ClickPesa USSD push).
     */
    public function fundExternal(Job $job, Request $request, ClickPesaService $clickpesa)
    {
        $this->authorizeClient($job);

        if ($job->status !== Job::S_AWAITING_PAYMENT) {
            return back()->withErrors(['error' => 'Kazi hii haihitaji malipo.']);
        }

        $request->validate([
            'phone' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
        ]);

        $amount = (int) $job->agreed_amount;
        $orderId = strtoupper(Str::random(16));

        $payment = $job->payments()->create([
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => 'PENDING',
        ]);

        $res = $clickpesa->startPayment([
            'orderReference' => $orderId,
            'phoneNumber' => $request->input('phone'),
            'amount' => $amount,
        ]);

        if (! $res['ok']) {
            return back()->withErrors(['error' => 'Imeshindikana kuanzisha malipo. Jaribu tena.']);
        }

        return redirect()->route('jobs.fund.wait', $job)
            ->with('payment_id', $payment->id);
    }

    /**
     * Show payment waiting screen (polling).
     */
    public function waitPage(Job $job)
    {
        $this->authorizeClient($job);

        $payment = $job->payments()->where('status', 'PENDING')->latest()->first();
        if (! $payment) {
            return redirect()->route('jobs.show', $job);
        }

        return view('jobs.fund-wait', [
            'job' => $job->load('selectedWorker', 'category'),
            'payment' => $payment,
        ]);
    }

    /**
     * Poll payment status for funding.
     */
    public function poll(Job $job, ClickPesaService $clickpesa, EscrowService $escrow)
    {
        $payment = $job->payments()->where('status', 'PENDING')->latest()->first();
        if (! $payment) {
            return response()->json(['done' => false, 'status' => 'NO_PAYMENT']);
        }

        if ($payment->status === 'COMPLETED') {
            return response()->json(['done' => true, 'status' => 'COMPLETED']);
        }

        $resp = $clickpesa->queryPayment($payment->order_id);
        if (! $resp['ok']) {
            return response()->json(['done' => false, 'status' => $payment->status]);
        }

        $records = $resp['json'];
        $record = is_array($records) && isset($records[0]) ? $records[0] : $records;
        $cpStatus = $record['status'] ?? '';
        $finalStatus = ClickPesaService::resolvePaymentStatus($cpStatus);

        if ($finalStatus === 'COMPLETED') {
            DB::transaction(function () use ($payment, $record, $resp, $job, $escrow) {
                $payment->update([
                    'status' => 'COMPLETED',
                    'reference' => $record['paymentReference'] ?? null,
                    'channel' => $record['channel'] ?? null,
                    'msisdn' => $record['paymentPhoneNumber'] ?? null,
                    'transid' => $record['id'] ?? null,
                    'meta' => $resp['json'],
                ]);

                $client = $job->muhitaji;
                $escrow->holdFromPayment($job, $client, $payment->amount, $payment->id);
                $this->activateFundedJob($job, $client);
            });

            return response()->json(['done' => true, 'status' => 'COMPLETED']);
        }

        if ($finalStatus === 'FAILED') {
            $payment->update(['status' => 'FAILED', 'meta' => $resp['json']]);
        }

        return response()->json(['done' => false, 'status' => $payment->status]);
    }

    /**
     * API: Fund job from wallet.
     */
    public function apiFundFromWallet(Job $job, Request $request, EscrowService $escrow)
    {
        $user = $request->user();
        if ($job->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hii sio kazi yako.'], 403);
        }

        if ($job->status !== Job::S_AWAITING_PAYMENT) {
            return response()->json(['success' => false, 'message' => 'Kazi haihitaji malipo.'], 422);
        }

        $amount = (int) $job->agreed_amount;

        try {
            DB::transaction(function () use ($job, $user, $amount, $escrow) {
                $escrow->holdFromWallet($job, $user, $amount);
                $this->activateFundedJob($job, $user);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $job->fresh()->load('selectedWorker', 'category'),
            'message' => 'Malipo yamefanikiwa! Mfanyakazi amearifu.',
        ]);
    }

    /**
     * API: Fund job via ClickPesa.
     */
    public function apiFundExternal(Job $job, Request $request, ClickPesaService $clickpesa)
    {
        $user = $request->user();
        if ($job->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hii sio kazi yako.'], 403);
        }

        if ($job->status !== Job::S_AWAITING_PAYMENT) {
            return response()->json(['success' => false, 'message' => 'Kazi haihitaji malipo.'], 422);
        }

        $validated = $request->validate([
            'phone' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
        ]);

        $amount = (int) $job->agreed_amount;
        $orderId = strtoupper(Str::random(16));

        $payment = $job->payments()->create([
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => 'PENDING',
        ]);

        $res = $clickpesa->startPayment([
            'orderReference' => $orderId,
            'phoneNumber' => $validated['phone'],
            'amount' => $amount,
        ]);

        return response()->json([
            'success' => $res['ok'],
            'data' => [
                'job' => $job->fresh()->load('selectedWorker'),
                'payment' => $payment,
            ],
            'message' => $res['ok']
                ? 'Malipo yameanzishwa. Subiri USSD kwenye simu yako.'
                : 'Imeshindikana kuanzisha malipo.',
        ], $res['ok'] ? 200 : 400);
    }

    /**
     * API: Poll funding payment status.
     */
    public function apiPoll(Job $job, Request $request, ClickPesaService $clickpesa, EscrowService $escrow)
    {
        $payment = $job->payments()->where('status', 'PENDING')->latest()->first();
        if (! $payment) {
            return response()->json(['success' => false, 'done' => false, 'status' => 'NO_PAYMENT']);
        }

        if ($payment->status === 'COMPLETED') {
            return response()->json(['success' => true, 'done' => true, 'status' => 'COMPLETED']);
        }

        $resp = $clickpesa->queryPayment($payment->order_id);
        if ($resp['ok']) {
            $records = $resp['json'];
            $record = is_array($records) && isset($records[0]) ? $records[0] : $records;
            $finalStatus = ClickPesaService::resolvePaymentStatus($record['status'] ?? '');

            if ($finalStatus === 'COMPLETED') {
                DB::transaction(function () use ($payment, $record, $resp, $job, $escrow) {
                    $payment->update([
                        'status' => 'COMPLETED',
                        'reference' => $record['paymentReference'] ?? null,
                        'channel' => $record['channel'] ?? null,
                        'msisdn' => $record['paymentPhoneNumber'] ?? null,
                        'transid' => $record['id'] ?? null,
                        'meta' => $resp['json'],
                    ]);
                    $client = $job->muhitaji;
                    $escrow->holdFromPayment($job, $client, $payment->amount, $payment->id);
                    $this->activateFundedJob($job, $client);
                });

                return response()->json(['success' => true, 'done' => true, 'status' => 'COMPLETED']);
            }

            if ($finalStatus === 'FAILED') {
                $payment->update(['status' => 'FAILED', 'meta' => $resp['json']]);
            }
        }

        return response()->json([
            'success' => true,
            'done' => false,
            'status' => $payment->fresh()->status,
        ]);
    }

    /**
     * Activate a funded job: set status to funded, notify worker.
     */
    protected function activateFundedJob(Job $job, $client): void
    {
        $job->funded_at = now();
        $job->accepted_worker_id = $job->selected_worker_id;
        $job->transitionStatus(Job::S_FUNDED, $client->id, 'Job funded, waiting for worker acceptance');

        $worker = $job->selectedWorker;
        if ($worker) {
            PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $client->id,
                'receiver_id' => $worker->id,
                'message' => '💰 Malipo ya TZS '.number_format($job->agreed_amount)." yamefanywa kwa kazi \"{$job->title}\". Tafadhali kubali au kataa kazi.",
            ]);
        }
    }

    protected function authorizeClient(Job $job): void
    {
        $user = Auth::user();
        if ($job->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Hii sio kazi yako.');
        }
    }
}
