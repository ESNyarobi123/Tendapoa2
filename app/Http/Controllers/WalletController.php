<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\ClickPesaService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    // ─── WEB: Deposit Form ───────────────────────────────────────────────

    public function depositForm()
    {
        $wallet = Auth::user()->ensureWallet();

        return view('wallet.deposit', compact('wallet'));
    }

    // ─── WEB: Initiate Deposit ───────────────────────────────────────────

    public function depositSubmit(Request $r, ClickPesaService $clickpesa)
    {
        $r->validate([
            'amount' => ['required', 'integer', 'min:1000'],
            'phone_number' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
        ], [
            'amount.min' => 'Kiwango cha chini cha kuweka ni TZS 1,000.',
            'phone_number.regex' => 'Namba ya simu si sahihi. Tumia 06/07xxxxxxxx au 2556/2557xxxxxxxx.',
        ]);

        $user = Auth::user();
        $orderId = 'D'.strtoupper(Str::random(16));

        // Save pending deposit
        $txn = WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => 0, // will be set on completion
            'type' => 'DEPOSIT',
            'description' => 'Deposit via ClickPesa – pending',
            'meta' => [
                'order_id' => $orderId,
                'phone' => $r->input('phone_number'),
                'requested' => (int) $r->input('amount'),
                'status' => 'PENDING',
            ],
        ]);

        $res = $clickpesa->startPayment([
            'orderReference' => $orderId,
            'phoneNumber' => $r->input('phone_number'),
            'amount' => (int) $r->input('amount'),
        ]);

        if (! $res['ok']) {
            $txn->update(['description' => 'Deposit failed – ClickPesa error', 'meta' => array_merge($txn->meta ?? [], ['error' => $res])]);

            return back()->withErrors(['deposit' => 'Imeshindikana kuanzisha malipo. Jaribu tena.']);
        }

        $txn->update(['meta' => array_merge($txn->meta ?? [], ['clickpesa_response' => $res['json']])]);

        return redirect()->route('wallet.deposit.wait', ['transaction' => $txn->id])
            ->with('success', 'Ombi la malipo limetumwa! Angalia simu yako.');
    }

    // ─── WEB: Wait / Poll page for deposit ───────────────────────────────

    public function depositWait(WalletTransaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        return view('wallet.deposit-wait', compact('transaction'));
    }

    // ─── POLL: Check deposit status ──────────────────────────────────────

    public function depositPoll(WalletTransaction $transaction, ClickPesaService $clickpesa, WalletService $walletService)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $meta = $transaction->meta ?? [];
        $orderId = $meta['order_id'] ?? '';

        // Already completed
        if (($meta['status'] ?? '') === 'COMPLETED') {
            return response()->json(['done' => true, 'status' => 'COMPLETED']);
        }

        $resp = $clickpesa->queryPayment($orderId);
        Log::info('Deposit poll '.$orderId, ['resp' => $resp]);

        $finalStatus = 'PENDING';
        if ($resp['ok']) {
            $records = $resp['json'];
            $record = is_array($records) && isset($records[0]) ? $records[0] : $records;
            $finalStatus = ClickPesaService::resolvePaymentStatus($record['status'] ?? '');
        }

        if ($finalStatus === 'COMPLETED') {
            DB::transaction(function () use ($transaction, $meta, $walletService, $resp) {
                $amount = (int) ($meta['requested'] ?? 0);
                $record = is_array($resp['json']) && isset($resp['json'][0]) ? $resp['json'][0] : $resp['json'];

                // Credit wallet
                $walletService->credit(
                    $transaction->user,
                    $amount,
                    'DEPOSIT',
                    'Deposit via ClickPesa – '.($record['paymentReference'] ?? $meta['order_id'])
                );

                // Update the tracking transaction
                $transaction->update([
                    'amount' => $amount,
                    'description' => 'Deposit via ClickPesa – completed',
                    'meta' => array_merge($meta, [
                        'status' => 'COMPLETED',
                        'paymentReference' => $record['paymentReference'] ?? null,
                        'channel' => $record['channel'] ?? null,
                        'clickpesa_query' => $resp['json'],
                    ]),
                ]);
            });

            return response()->json(['done' => true, 'status' => 'COMPLETED']);
        }

        if ($finalStatus === 'FAILED') {
            $transaction->update([
                'description' => 'Deposit via ClickPesa – failed',
                'meta' => array_merge($meta, ['status' => 'FAILED', 'clickpesa_query' => $resp['json']]),
            ]);

            return response()->json(['done' => true, 'status' => 'FAILED']);
        }

        return response()->json(['done' => false, 'status' => $meta['status'] ?? 'PENDING']);
    }

    // ─── WEB: Withdraw via ClickPesa Payout ──────────────────────────────

    public function withdrawSubmit(Request $r, ClickPesaService $clickpesa, WalletService $walletService)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, ['mfanyakazi', 'admin', 'muhitaji'])) {
            abort(403);
        }

        $minWithdrawal = (int) Setting::get('min_withdrawal', 5000);

        $data = $r->validate([
            'amount' => ['required', 'integer', 'min:'.$minWithdrawal],
            'phone_number' => ['required', 'string', 'min:10'],
            'registered_name' => ['required', 'string', 'min:2'],
            'network_type' => ['required', 'string', 'in:vodacom,tigo,airtel,halotel,ttcl'],
            'method' => ['required', 'string'],
        ], [
            'amount.min' => 'Kiwango cha chini cha kutoa ni TZS '.number_format($minWithdrawal),
        ]);

        $wallet = $user->ensureWallet();
        $withdrawalFee = (int) Setting::get('withdrawal_fee', 0);
        $totalToDebit = (int) $data['amount'] + $withdrawalFee;

        if ($wallet->available_balance < $totalToDebit) {
            return back()->withErrors(['amount' => 'Salio linalopatikana halitoshi (hesabu ya escrow imeondolewa). Unahitaji TZS '.number_format($totalToDebit).' lakini una TZS '.number_format($wallet->available_balance).' linalopatikana.']);
        }

        $orderId = 'W'.strtoupper(Str::random(16));

        // Preview payout first
        $preview = $clickpesa->previewPayout([
            'orderReference' => $orderId,
            'phoneNumber' => $data['phone_number'],
            'amount' => (int) $data['amount'],
            'currency' => 'TZS',
        ]);

        if (! $preview['ok']) {
            Log::error('ClickPesa payout preview failed', ['resp' => $preview]);

            return back()->withErrors(['withdraw' => 'Imeshindikana kuthibitisha payout. Jaribu tena.']);
        }

        // Debit wallet immediately
        $walletService->debit($user, $totalToDebit, 'WITHDRAW', 'Withdrawal to '.$data['phone_number'].' (fee: TZS '.$withdrawalFee.')');

        // Create payout
        $res = $clickpesa->createPayout([
            'orderReference' => $orderId,
            'phoneNumber' => $data['phone_number'],
            'amount' => (int) $data['amount'],
            'currency' => 'TZS',
        ]);

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => (int) $data['amount'],
            'status' => $res['ok'] ? 'PROCESSING' : 'FAILED',
            'method' => $data['method'],
            'account' => $data['phone_number'],
            'registered_name' => $data['registered_name'],
            'network_type' => $data['network_type'],
        ]);

        if (! $res['ok']) {
            // Refund wallet since payout failed
            $walletService->credit($user, $totalToDebit, 'ADJUST', 'Refund – payout failed for WDR '.$orderId);
            $withdrawal->update(['status' => 'FAILED']);

            return back()->withErrors(['withdraw' => 'Imeshindikana kutuma payout. Pesa imerudishwa kwenye wallet yako.']);
        }

        return redirect()->route('dashboard')->with('status', 'Withdrawal imewasilishwa kupitia ClickPesa. Subiri uthibitisho.');
    }

    // =====================================================================
    // API METHODS
    // =====================================================================

    // ─── API: Initiate Deposit ───────────────────────────────────────────

    public function apiDeposit(Request $r, ClickPesaService $clickpesa)
    {
        $r->validate([
            'amount' => ['required', 'integer', 'min:1000'],
            'phone_number' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
        ]);

        $user = $r->user();
        $orderId = 'D'.strtoupper(Str::random(16));

        $txn = WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => 0,
            'type' => 'DEPOSIT',
            'description' => 'Deposit via ClickPesa – pending',
            'meta' => [
                'order_id' => $orderId,
                'phone' => $r->input('phone_number'),
                'requested' => (int) $r->input('amount'),
                'status' => 'PENDING',
            ],
        ]);

        $res = $clickpesa->startPayment([
            'orderReference' => $orderId,
            'phoneNumber' => $r->input('phone_number'),
            'amount' => (int) $r->input('amount'),
        ]);

        if (! $res['ok']) {
            $txn->update(['description' => 'Deposit failed', 'meta' => array_merge($txn->meta ?? [], ['error' => $res])]);

            return response()->json(['success' => false, 'message' => 'Imeshindikana kuanzisha malipo.'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ombi la malipo limetumwa! Angalia simu yako.',
            'transaction_id' => $txn->id,
            'order_id' => $orderId,
        ], 201);
    }

    // ─── API: Poll Deposit Status ────────────────────────────────────────

    public function apiDepositPoll(WalletTransaction $transaction, ClickPesaService $clickpesa, WalletService $walletService)
    {
        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Huna ruhusa.'], 403);
        }

        $meta = $transaction->meta ?? [];
        $orderId = $meta['order_id'] ?? '';

        if (($meta['status'] ?? '') === 'COMPLETED') {
            return response()->json(['success' => true, 'done' => true, 'status' => 'COMPLETED']);
        }

        $resp = $clickpesa->queryPayment($orderId);

        $finalStatus = 'PENDING';
        if ($resp['ok']) {
            $records = $resp['json'];
            $record = is_array($records) && isset($records[0]) ? $records[0] : $records;
            $finalStatus = ClickPesaService::resolvePaymentStatus($record['status'] ?? '');
        }

        if ($finalStatus === 'COMPLETED') {
            DB::transaction(function () use ($transaction, $meta, $walletService, $resp) {
                $amount = (int) ($meta['requested'] ?? 0);
                $record = is_array($resp['json']) && isset($resp['json'][0]) ? $resp['json'][0] : $resp['json'];

                $walletService->credit(
                    $transaction->user,
                    $amount,
                    'DEPOSIT',
                    'Deposit via ClickPesa – '.($record['paymentReference'] ?? $meta['order_id'])
                );

                $transaction->update([
                    'amount' => $amount,
                    'description' => 'Deposit via ClickPesa – completed',
                    'meta' => array_merge($meta, [
                        'status' => 'COMPLETED',
                        'paymentReference' => $record['paymentReference'] ?? null,
                        'clickpesa_query' => $resp['json'],
                    ]),
                ]);
            });

            $wallet = $transaction->user->ensureWallet();

            return response()->json([
                'success' => true,
                'done' => true,
                'status' => 'COMPLETED',
                'new_balance' => $wallet->balance,
            ]);
        }

        if ($finalStatus === 'FAILED') {
            $transaction->update([
                'description' => 'Deposit via ClickPesa – failed',
                'meta' => array_merge($meta, ['status' => 'FAILED']),
            ]);

            return response()->json(['success' => true, 'done' => true, 'status' => 'FAILED']);
        }

        return response()->json(['success' => true, 'done' => false, 'status' => 'PENDING']);
    }

    // ─── API: Withdraw via ClickPesa Payout ──────────────────────────────

    public function apiWithdraw(Request $r, ClickPesaService $clickpesa, WalletService $walletService)
    {
        $user = $r->user();

        $minWithdrawal = (int) Setting::get('min_withdrawal', 5000);

        $data = $r->validate([
            'amount' => ['required', 'integer', 'min:'.$minWithdrawal],
            'phone_number' => ['required', 'string', 'min:10'],
            'registered_name' => ['required', 'string', 'min:2'],
            'network_type' => ['required', 'string', 'in:vodacom,tigo,airtel,halotel,ttcl'],
            'method' => ['required', 'string'],
        ]);

        $wallet = $user->ensureWallet();
        $withdrawalFee = (int) Setting::get('withdrawal_fee', 0);
        $totalToDebit = (int) $data['amount'] + $withdrawalFee;

        if ($wallet->balance < $totalToDebit) {
            return response()->json([
                'success' => false,
                'message' => 'Salio lako halitoshi kulipia kiasi unachotoa pamoja na makato ya TZS '.number_format($withdrawalFee),
            ], 422);
        }

        $orderId = 'W'.strtoupper(Str::random(16));

        // Preview
        $preview = $clickpesa->previewPayout([
            'orderReference' => $orderId,
            'phoneNumber' => $data['phone_number'],
            'amount' => (int) $data['amount'],
            'currency' => 'TZS',
        ]);

        if (! $preview['ok']) {
            return response()->json(['success' => false, 'message' => 'Payout preview imeshindikana.'], 400);
        }

        // Debit
        $walletService->debit($user, $totalToDebit, 'WITHDRAW', 'Withdrawal to '.$data['phone_number'].' (fee: TZS '.$withdrawalFee.')');

        // Create payout
        $res = $clickpesa->createPayout([
            'orderReference' => $orderId,
            'phoneNumber' => $data['phone_number'],
            'amount' => (int) $data['amount'],
            'currency' => 'TZS',
        ]);

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => (int) $data['amount'],
            'status' => $res['ok'] ? 'PROCESSING' : 'FAILED',
            'method' => $data['method'],
            'account' => $data['phone_number'],
            'registered_name' => $data['registered_name'],
            'network_type' => $data['network_type'],
        ]);

        if (! $res['ok']) {
            $walletService->credit($user, $totalToDebit, 'ADJUST', 'Refund – payout failed '.$orderId);
            $withdrawal->update(['status' => 'FAILED']);

            return response()->json(['success' => false, 'message' => 'Payout imeshindikana. Pesa imerudishwa.'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal imetumwa kupitia ClickPesa.',
            'data' => $withdrawal,
            'new_balance' => $user->ensureWallet()->balance,
        ], 201);
    }

    // ─── API: Get Wallet Info ────────────────────────────────────────────

    public function apiWalletInfo(Request $r)
    {
        $user = $r->user();
        $wallet = $user->ensureWallet();

        $recentTxns = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (int) $wallet->balance,
                'held_balance' => (int) $wallet->held_balance,
                'available_balance' => (int) $wallet->available_balance,
                'total_earned' => (int) ($wallet->total_earned ?? 0),
                'total_spent' => (int) ($wallet->total_spent ?? 0),
                'total_withdrawn' => (int) ($wallet->total_withdrawn ?? 0),
                'transactions' => $recentTxns,
            ],
        ]);
    }
}
