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
        Schema::table('jobs', function (Blueprint $table) {
            // Add new status values for matching flow
            // Status flow: pending_quotes → receiving_quotes → quote_selected → payment_pending → escrow_paid → assigned → in_progress
            $table->foreignId('selected_quote_id')->nullable()->constrained('quotes')->onDelete('set null')->after('accepted_worker_id');
            $table->timestamp('quote_window_closes_at')->nullable()->after('published_at'); // When quote submission window closes
            $table->timestamp('payment_deadline_at')->nullable()->after('quote_window_closes_at'); // Payment deadline after quote selection
            $table->integer('quote_count')->default(0)->after('payment_deadline_at'); // Cache for number of quotes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['selected_quote_id']);
            $table->dropColumn(['selected_quote_id', 'quote_window_closes_at', 'payment_deadline_at', 'quote_count']);
        });
    }
};
