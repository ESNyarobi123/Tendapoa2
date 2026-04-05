<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WORKFLOW UPGRADE: Add new columns to work_orders for the modern marketplace flow.
 * - Free posting (no pending_payment required)
 * - Escrow/funding after worker selection
 * - Two-sided completion (submit + confirm)
 * - Dispute support
 * - Auto-release timeout
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $t) {
            // Agreed amount (may differ from original price after negotiation)
            if (! Schema::hasColumn('work_orders', 'agreed_amount')) {
                $t->unsignedInteger('agreed_amount')->nullable()->after('price');
            }

            // Escrow amount currently held for this job
            if (! Schema::hasColumn('work_orders', 'escrow_amount')) {
                $t->unsignedInteger('escrow_amount')->default(0)->after('agreed_amount');
            }

            // Platform fee amount (calculated on release)
            if (! Schema::hasColumn('work_orders', 'platform_fee_amount')) {
                $t->unsignedInteger('platform_fee_amount')->default(0)->after('escrow_amount');
            }

            // Release amount (worker receives)
            if (! Schema::hasColumn('work_orders', 'release_amount')) {
                $t->unsignedInteger('release_amount')->default(0)->after('platform_fee_amount');
            }

            // Selected worker (before funding — different from accepted_worker_id which is post-accept)
            if (! Schema::hasColumn('work_orders', 'selected_worker_id')) {
                $t->foreignId('selected_worker_id')->nullable()->after('accepted_worker_id');
            }

            // Funded payment reference
            if (! Schema::hasColumn('work_orders', 'funded_payment_id')) {
                $t->unsignedBigInteger('funded_payment_id')->nullable()->after('selected_worker_id');
            }

            // Key timestamps for new lifecycle
            if (! Schema::hasColumn('work_orders', 'funded_at')) {
                $t->timestamp('funded_at')->nullable()->after('published_at');
            }
            if (! Schema::hasColumn('work_orders', 'accepted_by_worker_at')) {
                $t->timestamp('accepted_by_worker_at')->nullable()->after('funded_at');
            }
            if (! Schema::hasColumn('work_orders', 'submitted_at')) {
                $t->timestamp('submitted_at')->nullable()->after('accepted_by_worker_at');
            }
            if (! Schema::hasColumn('work_orders', 'confirmed_at')) {
                $t->timestamp('confirmed_at')->nullable()->after('submitted_at');
            }
            if (! Schema::hasColumn('work_orders', 'cancelled_at')) {
                $t->timestamp('cancelled_at')->nullable()->after('confirmed_at');
            }
            if (! Schema::hasColumn('work_orders', 'disputed_at')) {
                $t->timestamp('disputed_at')->nullable()->after('cancelled_at');
            }
            if (! Schema::hasColumn('work_orders', 'auto_release_at')) {
                $t->timestamp('auto_release_at')->nullable()->after('disputed_at');
            }

            // Urgency level
            if (! Schema::hasColumn('work_orders', 'urgency')) {
                $t->string('urgency', 16)->default('normal')->after('status'); // normal, urgent, flexible
            }

            // Cancellation reason
            if (! Schema::hasColumn('work_orders', 'cancel_reason')) {
                $t->text('cancel_reason')->nullable()->after('auto_release_at');
            }

            // Application count cache
            if (! Schema::hasColumn('work_orders', 'application_count')) {
                $t->unsignedInteger('application_count')->default(0)->after('cancel_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $t) {
            $cols = [
                'agreed_amount', 'escrow_amount', 'platform_fee_amount', 'release_amount',
                'selected_worker_id', 'funded_payment_id',
                'funded_at', 'accepted_by_worker_at', 'submitted_at', 'confirmed_at',
                'cancelled_at', 'disputed_at', 'auto_release_at',
                'urgency', 'cancel_reason', 'application_count',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('work_orders', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
