<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(10.00)->after('amount'); // 10% default
            }
            if (!Schema::hasColumn('withdrawals', 'commission_amount')) {
                $table->decimal('commission_amount', 10, 2)->default(0)->after('commission_rate');
            }
            if (!Schema::hasColumn('withdrawals', 'net_amount')) {
                $table->decimal('net_amount', 10, 2)->default(0)->after('commission_amount'); // Amount after commission
            }
            if (!Schema::hasColumn('withdrawals', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('net_amount');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('withdrawals', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('withdrawals', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawals', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }
            $table->dropColumn(['commission_rate', 'commission_amount', 'net_amount', 'approved_by', 'approved_at', 'admin_notes']);
        });
    }
};
