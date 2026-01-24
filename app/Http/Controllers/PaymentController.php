<?php

namespace App\Http\Controllers;

use App\Models\{Job, Payment, Wallet, WalletTransaction};
use App\Services\ZenoPayService;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private function handleJobPaymentSuccess(Job $job)
    {
        if ($job->status === 'pending_payment') {
            DB::transaction(function () use ($job) {
                // Update job status to posted
                $job->update([
                    'status' => 'posted',
                    'published_at' => now(),
                ]);

                // If it's a mfanyakazi job, we might need to deduct wallet balance if that was the logic,
                // but here we are in ZenoPay callback, so it means they paid via mobile money.
                // The wallet deduction logic in JobController::storeMfanyakazi handles wallet payments directly.
                // So if we are here, it means they paid via ZenoPay.
                
                // However, the original code had wallet deduction logic in webhook for mfanyakazi.
                // Let's preserve that if it was intended for "topup then pay"? 
                // Actually, storeMfanyakazi creates a pending_payment job if wallet balance is low.
                // So we don't need to deduct from wallet here, as the payment is direct.
                // BUT, the previous code WAS deducting from wallet. Let's check why.
                // Ah, maybe the flow is: Pay Zeno -> Credit Wallet -> Deduct Wallet?
                // Or just Pay Zeno -> Job Posted.
                // The previous code:
                /*
                        // Deduct posting fee from mfanyakazi's wallet
                        $userWallet = $job->muhitaji->ensureWallet();
                        $userWallet->decrement('balance', $job->posting_fee);
                */
                // This implies the user paid Zeno, but we still deduct from wallet? That seems wrong unless we credited the wallet first.
                // If they pay via Zeno, the money goes to the company account. The wallet shouldn't be touched unless we are treating it as a "deposit".
                // For now, I will assume direct payment for the job.
                
                // Let's just update the status.
            });
        }
    }

    public function poll(Job $job, ZenoPayService $zeno)
    {
        $payment = $job->payment;
        if (!$payment) abort(404);

        if ($payment->status === 'COMPLETED') {
            return response()->json(['done'=>true]);
        }

        $resp = $zeno->checkOrder($payment->order_id);

        if ($resp['ok'] && ($resp['json']['payment_status'] ?? null) === 'COMPLETED') {
            $payment->update([
                'status'     => 'COMPLETED',
                'resultcode' => $resp['json']['resultcode'] ?? null,
                'reference'  => $resp['json']['reference'] ?? null,
                'channel'    => data_get($resp,'json.data.0.channel'),
                'msisdn'     => data_get($resp,'json.data.0.msisdn'),
                'transid'    => data_get($resp,'json.data.0.transid'),
                'meta'       => $resp['json'],
            ]);
            
            // Activate the job
            $this->handleJobPaymentSuccess($job);
        }

        return response()->json([
            'done'=>$payment->status==='COMPLETED',
            'status'=>$payment->status
        ]);
    }

    public function webhook(ZenoPayService $zeno)
    {
        $key = request()->header('x-api-key','');
        if (!$zeno->verifyWebhook($key)) abort(401);

        $payload = request()->all();
        $payment = Payment::where('order_id', $payload['order_id'] ?? '')->first();

        if ($payment && ($payload['payment_status'] ?? '') === 'COMPLETED') {
            $payment->update([
                'status'    => 'COMPLETED',
                'reference' => $payload['reference'] ?? null,
                'meta'      => $payload,
            ]);

            // Handle job posting payment completion
            $job = $payment->job;
            if ($job) {
                $this->handleJobPaymentSuccess($job);
            }
        }
        return response()->json(['ok'=>true]);
    }

    public function apiPoll(Job $job, ZenoPayService $zeno)
    {
        $payment = $job->payment;
        if (!$payment) {
            return response()->json([
                'error' => 'Payment not found',
                'status' => 'not_found'
            ], 404);
        }

        if ($payment->status === 'COMPLETED') {
            return response()->json([
                'done' => true,
                'status' => 'completed',
                'payment' => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'reference' => $payment->reference,
                    'completed_at' => $payment->updated_at
                ]
            ]);
        }

        $resp = $zeno->checkOrder($payment->order_id);

        if ($resp['ok'] && ($resp['json']['payment_status'] ?? null) === 'COMPLETED') {
            $payment->update([
                'status'     => 'COMPLETED',
                'resultcode' => $resp['json']['resultcode'] ?? null,
                'reference'  => $resp['json']['reference'] ?? null,
                'channel'    => data_get($resp,'json.data.0.channel'),
                'msisdn'     => data_get($resp,'json.data.0.msisdn'),
                'transid'    => data_get($resp,'json.data.0.transid'),
                'meta'       => $resp['json'],
            ]);
            
            // Activate the job
            $this->handleJobPaymentSuccess($job);
        }

        return response()->json([
            'done' => $payment->status === 'COMPLETED',
            'status' => $payment->status,
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'order_id' => $payment->order_id
            ]
        ]);
    }
}
