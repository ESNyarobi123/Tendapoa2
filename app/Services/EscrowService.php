<?php

namespace App\Services;

use App\Models\EscrowLedger;
use App\Models\Job;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * EscrowService handles all escrow/held-balance fund movements.
 *
 * - holdFromWallet()        → debit client wallet, move to held_balance, create escrow ledger entry
 * - holdFromPayment()       → after ClickPesa payment success, move to held_balance
 * - releaseToWorker()       → release held funds to worker wallet (minus commission)
 * - refundToClient()        → return held funds to client wallet
 * - partialRelease()        → split release (dispute resolution)
 */
class EscrowService
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Hold funds from client wallet for a job.
     * Moves money from available balance → held_balance.
     */
    public function holdFromWallet(Job $job, User $client, int $amount): EscrowLedger
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be > 0');
        }

        return DB::transaction(function () use ($job, $client, $amount) {
            $wallet = $client->ensureWallet();

            if ($wallet->available_balance < $amount) {
                throw new RuntimeException('Salio haitoshi. Unahitaji TZS '.number_format($amount).' lakini una TZS '.number_format($wallet->available_balance));
            }

            // Move from available → held
            $wallet->held_balance += $amount;
            $wallet->total_spent += $amount;
            $wallet->save();

            // Record wallet transaction
            $txn = WalletTransaction::create([
                'user_id' => $client->id,
                'amount' => -1 * $amount,
                'type' => 'JOB_FUNDING',
                'description' => "Escrow hold for job #{$job->id}: {$job->title}",
                'meta' => ['job_id' => $job->id, 'escrow_type' => 'hold'],
            ]);

            // Record escrow ledger
            $entry = EscrowLedger::create([
                'work_order_id' => $job->id,
                'client_id' => $client->id,
                'worker_id' => $job->selected_worker_id ?? $job->accepted_worker_id,
                'type' => EscrowLedger::TYPE_HOLD,
                'amount' => $amount,
                'description' => 'Escrow hold from wallet',
                'wallet_transaction_id' => $txn->id,
            ]);

            // Update job escrow tracking
            $job->update([
                'escrow_amount' => $amount,
                'agreed_amount' => $amount,
            ]);

            return $entry;
        });
    }

    /**
     * Hold funds from an external payment (ClickPesa) for a job.
     * Client wallet balance is NOT affected — money is external.
     * We just record the hold in escrow ledger.
     */
    public function holdFromPayment(Job $job, User $client, int $amount, int $paymentId): EscrowLedger
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be > 0');
        }

        return DB::transaction(function () use ($job, $client, $amount, $paymentId) {
            $wallet = $client->ensureWallet();

            // Credit wallet then immediately hold
            $wallet->balance += $amount;
            $wallet->held_balance += $amount;
            $wallet->total_spent += $amount;
            $wallet->save();

            $txn = WalletTransaction::create([
                'user_id' => $client->id,
                'amount' => $amount,
                'type' => 'DEPOSIT_AND_HOLD',
                'description' => "Payment received & held for job #{$job->id}",
                'meta' => ['job_id' => $job->id, 'payment_id' => $paymentId],
            ]);

            $entry = EscrowLedger::create([
                'work_order_id' => $job->id,
                'client_id' => $client->id,
                'worker_id' => $job->selected_worker_id ?? $job->accepted_worker_id,
                'type' => EscrowLedger::TYPE_HOLD,
                'amount' => $amount,
                'description' => 'Escrow hold from ClickPesa payment',
                'payment_id' => $paymentId,
                'wallet_transaction_id' => $txn->id,
            ]);

            $job->update([
                'escrow_amount' => $amount,
                'agreed_amount' => $amount,
                'funded_payment_id' => $paymentId,
            ]);

            return $entry;
        });
    }

    /**
     * Release escrowed funds to worker after completion confirmation.
     * Deducts platform commission, credits worker wallet.
     */
    public function releaseToWorker(Job $job): array
    {
        $worker = $job->acceptedWorker ?? $job->selectedWorker;
        if (! $worker) {
            throw new RuntimeException('No worker assigned to this job');
        }

        $escrowAmount = (int) $job->escrow_amount;
        if ($escrowAmount <= 0) {
            throw new RuntimeException('No escrow funds to release');
        }

        $commissionRate = (float) Setting::get('commission_rate', 10);
        $platformFee = (int) round($escrowAmount * ($commissionRate / 100));
        $releaseAmount = $escrowAmount - $platformFee;

        return DB::transaction(function () use ($job, $worker, $escrowAmount, $platformFee, $releaseAmount) {
            $client = $job->muhitaji;
            $clientWallet = $client->ensureWallet();
            $workerWallet = $worker->ensureWallet();

            // Remove from client held_balance
            $clientWallet->held_balance = max(0, $clientWallet->held_balance - $escrowAmount);
            $clientWallet->balance = max(0, $clientWallet->balance - $escrowAmount);
            $clientWallet->save();

            // Credit worker wallet
            $workerWallet->balance += $releaseAmount;
            $workerWallet->total_earned += $releaseAmount;
            $workerWallet->save();

            // Worker earning transaction
            $workerTxn = WalletTransaction::create([
                'user_id' => $worker->id,
                'amount' => $releaseAmount,
                'type' => 'EARN',
                'description' => "Payment for job #{$job->id}: {$job->title}",
                'meta' => [
                    'job_id' => $job->id,
                    'gross_amount' => $escrowAmount,
                    'commission' => $platformFee,
                    'net_amount' => $releaseAmount,
                ],
            ]);

            // Escrow release entry
            EscrowLedger::create([
                'work_order_id' => $job->id,
                'client_id' => $client->id,
                'worker_id' => $worker->id,
                'type' => EscrowLedger::TYPE_RELEASE,
                'amount' => $releaseAmount,
                'description' => 'Released to worker after completion',
                'wallet_transaction_id' => $workerTxn->id,
            ]);

            // Platform fee entry
            if ($platformFee > 0) {
                EscrowLedger::create([
                    'work_order_id' => $job->id,
                    'client_id' => $client->id,
                    'worker_id' => $worker->id,
                    'type' => EscrowLedger::TYPE_PLATFORM_FEE,
                    'amount' => $platformFee,
                    'description' => "Platform commission ({$platformFee} TZS)",
                ]);
            }

            // Update job
            $job->update([
                'escrow_amount' => 0,
                'platform_fee_amount' => $platformFee,
                'release_amount' => $releaseAmount,
            ]);

            return [
                'gross_amount' => $escrowAmount,
                'platform_fee' => $platformFee,
                'release_amount' => $releaseAmount,
                'worker_id' => $worker->id,
            ];
        });
    }

    /**
     * Full refund of escrowed funds back to client wallet.
     */
    public function refundToClient(Job $job, ?string $reason = null): EscrowLedger
    {
        $escrowAmount = (int) $job->escrow_amount;
        if ($escrowAmount <= 0) {
            throw new RuntimeException('No escrow funds to refund');
        }

        return DB::transaction(function () use ($job, $escrowAmount, $reason) {
            $client = $job->muhitaji;
            $wallet = $client->ensureWallet();

            // Return from held → available
            $wallet->held_balance = max(0, $wallet->held_balance - $escrowAmount);
            $wallet->total_spent = max(0, $wallet->total_spent - $escrowAmount);
            $wallet->save();

            $txn = WalletTransaction::create([
                'user_id' => $client->id,
                'amount' => $escrowAmount,
                'type' => 'REFUND',
                'description' => $reason ?? "Refund for job #{$job->id}",
                'meta' => ['job_id' => $job->id],
            ]);

            $entry = EscrowLedger::create([
                'work_order_id' => $job->id,
                'client_id' => $client->id,
                'worker_id' => $job->selected_worker_id ?? $job->accepted_worker_id,
                'type' => EscrowLedger::TYPE_REFUND,
                'amount' => $escrowAmount,
                'description' => $reason ?? 'Full refund to client',
                'wallet_transaction_id' => $txn->id,
            ]);

            $job->update(['escrow_amount' => 0]);

            return $entry;
        });
    }

    /**
     * Partial/split release for dispute resolution.
     */
    public function splitRelease(Job $job, int $workerAmount, int $clientRefundAmount): array
    {
        $escrowAmount = (int) $job->escrow_amount;
        if ($workerAmount + $clientRefundAmount > $escrowAmount) {
            throw new InvalidArgumentException('Split amounts exceed escrow balance');
        }

        return DB::transaction(function () use ($job, $workerAmount, $clientRefundAmount, $escrowAmount) {
            $client = $job->muhitaji;
            $worker = $job->acceptedWorker ?? $job->selectedWorker;
            $results = [];

            $clientWallet = $client->ensureWallet();
            $clientWallet->held_balance = max(0, $clientWallet->held_balance - $escrowAmount);
            $clientWallet->balance = max(0, $clientWallet->balance - $escrowAmount);

            // Refund portion to client
            if ($clientRefundAmount > 0) {
                $clientWallet->balance += $clientRefundAmount;
                $clientWallet->total_spent = max(0, $clientWallet->total_spent - $clientRefundAmount);

                WalletTransaction::create([
                    'user_id' => $client->id,
                    'amount' => $clientRefundAmount,
                    'type' => 'REFUND',
                    'description' => "Partial refund from dispute on job #{$job->id}",
                    'meta' => ['job_id' => $job->id, 'dispute_split' => true],
                ]);

                EscrowLedger::create([
                    'work_order_id' => $job->id,
                    'client_id' => $client->id,
                    'worker_id' => $worker?->id,
                    'type' => EscrowLedger::TYPE_PARTIAL_REFUND,
                    'amount' => $clientRefundAmount,
                    'description' => 'Dispute partial refund to client',
                ]);

                $results['client_refund'] = $clientRefundAmount;
            }

            $clientWallet->save();

            // Pay worker portion
            if ($workerAmount > 0 && $worker) {
                $workerWallet = $worker->ensureWallet();
                $workerWallet->balance += $workerAmount;
                $workerWallet->total_earned += $workerAmount;
                $workerWallet->save();

                WalletTransaction::create([
                    'user_id' => $worker->id,
                    'amount' => $workerAmount,
                    'type' => 'EARN',
                    'description' => "Dispute resolution payment for job #{$job->id}",
                    'meta' => ['job_id' => $job->id, 'dispute_split' => true],
                ]);

                EscrowLedger::create([
                    'work_order_id' => $job->id,
                    'client_id' => $client->id,
                    'worker_id' => $worker->id,
                    'type' => EscrowLedger::TYPE_RELEASE,
                    'amount' => $workerAmount,
                    'description' => 'Dispute resolution release to worker',
                ]);

                $results['worker_payment'] = $workerAmount;
            }

            // Platform fee = remainder
            $platformFee = $escrowAmount - $workerAmount - $clientRefundAmount;
            if ($platformFee > 0) {
                EscrowLedger::create([
                    'work_order_id' => $job->id,
                    'client_id' => $client->id,
                    'worker_id' => $worker?->id,
                    'type' => EscrowLedger::TYPE_PLATFORM_FEE,
                    'amount' => $platformFee,
                    'description' => 'Platform fee from dispute resolution',
                ]);
                $results['platform_fee'] = $platformFee;
            }

            $job->update([
                'escrow_amount' => 0,
                'platform_fee_amount' => $platformFee,
                'release_amount' => $workerAmount,
            ]);

            return $results;
        });
    }
}
