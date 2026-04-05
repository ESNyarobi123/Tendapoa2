<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * WORKFLOW UPGRADE: Map old job statuses to new lifecycle.
 * - pending_payment → open (since we no longer require payment before posting)
 * - posted → open
 * - assigned → in_progress (worker was already working)
 * - in_progress → in_progress (no change)
 * - ready_for_confirmation → submitted
 * - completed → completed (no change)
 * - cancelled → cancelled (no change)
 *
 * Historical data is preserved. We just remap statuses.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('work_orders')) {
            return;
        }

        // Map old statuses to new
        // pending_payment jobs that never got paid → cancelled (stale)
        DB::table('work_orders')
            ->where('status', 'pending_payment')
            ->update(['status' => 'cancelled', 'cancel_reason' => 'Auto-cancelled: legacy pending_payment during workflow upgrade']);

        // posted → open
        DB::table('work_orders')
            ->where('status', 'posted')
            ->update(['status' => 'open']);

        // assigned → funded (they were already assigned and implicitly paid in old flow)
        // We set funded_at = published_at as best approximation
        DB::table('work_orders')
            ->where('status', 'assigned')
            ->update([
                'status' => 'funded',
                'funded_at' => DB::raw('COALESCE(published_at, created_at)'),
            ]);

        // ready_for_confirmation → submitted
        DB::table('work_orders')
            ->where('status', 'ready_for_confirmation')
            ->update([
                'status' => 'submitted',
                'submitted_at' => DB::raw('updated_at'),
            ]);

        // For completed jobs, set confirmed_at from completed_at
        DB::table('work_orders')
            ->where('status', 'completed')
            ->whereNull('confirmed_at')
            ->update(['confirmed_at' => DB::raw('COALESCE(completed_at, updated_at)')]);

        // in_progress stays in_progress — set accepted_by_worker_at
        DB::table('work_orders')
            ->where('status', 'in_progress')
            ->whereNull('accepted_by_worker_at')
            ->update(['accepted_by_worker_at' => DB::raw('COALESCE(published_at, created_at)')]);

        // Set agreed_amount = price for all existing jobs where agreed_amount is null
        DB::table('work_orders')
            ->whereNull('agreed_amount')
            ->where('price', '>', 0)
            ->update(['agreed_amount' => DB::raw('price')]);
    }

    public function down(): void
    {
        // Reverse: map new statuses back to old
        DB::table('work_orders')->where('status', 'open')->update(['status' => 'posted']);
        DB::table('work_orders')->where('status', 'funded')->update(['status' => 'assigned']);
        DB::table('work_orders')->where('status', 'submitted')->update(['status' => 'ready_for_confirmation']);
        // pending_payment cancellations can't be perfectly reversed
    }
};
