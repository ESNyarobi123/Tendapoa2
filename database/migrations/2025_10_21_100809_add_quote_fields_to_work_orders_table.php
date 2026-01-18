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
        Schema::table('work_orders', function (Blueprint $table) {
            // Check if columns don't already exist
            if (!Schema::hasColumn('work_orders', 'selected_quote_id')) {
                $table->foreignId('selected_quote_id')->nullable()->constrained('quotes')->onDelete('set null')->after('accepted_worker_id');
            }
            if (!Schema::hasColumn('work_orders', 'quote_window_closes_at')) {
                $table->timestamp('quote_window_closes_at')->nullable()->after('published_at');
            }
            if (!Schema::hasColumn('work_orders', 'payment_deadline_at')) {
                $table->timestamp('payment_deadline_at')->nullable()->after('quote_window_closes_at');
            }
            if (!Schema::hasColumn('work_orders', 'quote_count')) {
                $table->integer('quote_count')->default(0)->after('payment_deadline_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            if (Schema::hasColumn('work_orders', 'selected_quote_id')) {
                $table->dropForeign(['selected_quote_id']);
                $table->dropColumn('selected_quote_id');
            }
            if (Schema::hasColumn('work_orders', 'quote_window_closes_at')) {
                $table->dropColumn('quote_window_closes_at');
            }
            if (Schema::hasColumn('work_orders', 'payment_deadline_at')) {
                $table->dropColumn('payment_deadline_at');
            }
            if (Schema::hasColumn('work_orders', 'quote_count')) {
                $table->dropColumn('quote_count');
            }
        });
    }
};
