<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Payment;
use App\Notifications\JobStatusNotification;
use App\Services\ClickPesaService;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private function handleJobPaymentSuccess(Job $job)
    {
        if ($job->status === 'pending_payment') {
            // Transaction is already active from caller (poll/webhook)

            // 1. Update job status first
            $job->update([
                'status' => 'posted',
                'published_at' => now(),
            ]);

            // 2. Safely attempt to notify the user
            try {
                // Ensure user relation is loaded
                if (! $job->relationLoaded('muhitaji')) {
                    $job->load('muhitaji');
                }

                $user = $job->muhitaji;
                // Fallback to 'user' relationship if 'muhitaji' is undefined or null but 'user' exists
                if (! $user) {
                    $job->load('user');
                    $user = $job->user;
                }

                if ($user) {
                    $user->notify(new JobStatusNotification($job, 'posted'));
                } else {
                    \Log::warning("Payment success: Job #{$job->id} has no linked user to notify.");
                }

                // 3. Notify Nearby Workers (Logic from JobController)
                // We wrap this in try-catch to ensure we don't break the payment flow
                try {
                    $jobController = app(JobController::class);
                    $jobController->notifyNearbyWorkers($job);
                } catch (\Exception $e) {
                    // Log worker notification failure but don't stop
                    \Log::error('Failed to notify nearby workers: '.$e->getMessage());
                }

            } catch (\Exception $e) {
                // Catch any other notification errors (e.g. mailer issues)
                \Log::error('Payment Success Notification Failed: '.$e->getMessage());
                // Crucial: We do NOT re-throw, so the transaction commits successfully.
            }
        }
    }

    public function poll(Job $job, ClickPesaService $clickpesa)
    {
        $payment = $job->payment;
        if (! $payment) {
            abort(404);
        }

        if ($payment->status === 'COMPLETED') {
            return response()->json(['done' => true]);
        }

        $resp = $clickpesa->queryPayment($payment->order_id);

        // LOG RESPONSE FOR DEBUGGING
        \Log::info('Polling Job #'.$job->id.' Order: '.$payment->order_id, ['clickpesa_response' => $resp]);

        // Determine final status from ClickPesa response
        $finalStatus = 'PENDING';
        if ($resp['ok']) {
            $records = $resp['json'];
            // ClickPesa returns a list of payment records for the orderReference
            if (is_array($records)) {
                // Could be a list or a single record
                $record = isset($records[0]) ? $records[0] : $records;
                $cpStatus = $record['status'] ?? '';
                $finalStatus = ClickPesaService::resolvePaymentStatus($cpStatus);
            }
        }

        if ($finalStatus === 'COMPLETED') {
            try {
                DB::transaction(function () use ($payment, $resp, $job) {
                    $record = is_array($resp['json']) && isset($resp['json'][0]) ? $resp['json'][0] : $resp['json'];
                    $payment->update([
                        'status' => 'COMPLETED',
                        'reference' => $record['paymentReference'] ?? null,
                        'channel' => $record['channel'] ?? null,
                        'msisdn' => $record['paymentPhoneNumber'] ?? null,
                        'transid' => $record['id'] ?? null,
                        'meta' => $resp['json'],
                    ]);
                    $this->handleJobPaymentSuccess($job);
                });
            } catch (\Exception $e) {
                \Log::error('Poll DB Update Failed: '.$e->getMessage());
            }

            return response()->json([
                'done' => true,
                'status' => 'COMPLETED',
            ]);
        }

        if ($finalStatus === 'FAILED') {
            $payment->update(['status' => 'FAILED', 'meta' => $resp['json']]);
        }

        return response()->json([
            'done' => $payment->status === 'COMPLETED',
            'status' => $payment->status,
        ]);
    }

    public function webhook(ClickPesaService $clickpesa)
    {
        $payload = request()->all();

        if (! $clickpesa->verifyWebhook($payload)) {
            abort(401);
        }

        $orderRef = $payload['orderReference'] ?? '';
        $payment = Payment::where('order_id', $orderRef)->first();

        if (! $payment) {
            return response()->json(['ok' => false, 'message' => 'Payment not found'], 404);
        }

        $cpStatus = $payload['status'] ?? '';
        $finalStatus = ClickPesaService::resolvePaymentStatus($cpStatus);

        if ($finalStatus === 'COMPLETED' && $payment->status !== 'COMPLETED') {
            DB::transaction(function () use ($payment, $payload) {
                $payment->update([
                    'status' => 'COMPLETED',
                    'reference' => $payload['paymentReference'] ?? null,
                    'channel' => $payload['channel'] ?? null,
                    'msisdn' => $payload['paymentPhoneNumber'] ?? null,
                    'transid' => $payload['id'] ?? null,
                    'meta' => $payload,
                ]);

                // Handle job posting payment completion
                $job = $payment->job;
                if ($job) {
                    $this->handleJobPaymentSuccess($job);
                }
            });
        } elseif ($finalStatus === 'FAILED' && $payment->status !== 'COMPLETED') {
            $payment->update(['status' => 'FAILED', 'meta' => $payload]);
        }

        return response()->json(['ok' => true]);
    }

    public function apiPoll(Job $job, ClickPesaService $clickpesa)
    {
        $payment = $job->payment;
        if (! $payment) {
            return response()->json([
                'error' => 'Payment not found',
                'status' => 'not_found',
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
                    'completed_at' => $payment->updated_at,
                ],
            ]);
        }

        $resp = $clickpesa->queryPayment($payment->order_id);

        $finalStatus = 'PENDING';
        if ($resp['ok']) {
            $records = $resp['json'];
            $record = is_array($records) && isset($records[0]) ? $records[0] : $records;
            $cpStatus = $record['status'] ?? '';
            $finalStatus = ClickPesaService::resolvePaymentStatus($cpStatus);
        }

        if ($finalStatus === 'COMPLETED') {
            $record = is_array($resp['json']) && isset($resp['json'][0]) ? $resp['json'][0] : $resp['json'];
            $payment->update([
                'status' => 'COMPLETED',
                'reference' => $record['paymentReference'] ?? null,
                'channel' => $record['channel'] ?? null,
                'msisdn' => $record['paymentPhoneNumber'] ?? null,
                'transid' => $record['id'] ?? null,
                'meta' => $resp['json'],
            ]);

            // Activate the job
            $this->handleJobPaymentSuccess($job);
        } elseif ($finalStatus === 'FAILED') {
            $payment->update(['status' => 'FAILED', 'meta' => $resp['json']]);
        }

        return response()->json([
            'done' => $payment->status === 'COMPLETED',
            'status' => $payment->status,
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'order_id' => $payment->order_id,
            ],
        ]);
    }
}
