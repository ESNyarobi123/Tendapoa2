<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * This migration enhances the comments system for:
     * - Mfanyakazi: comment, apply, propose offer/negotiate price
     * - Muhitaji: reply to comments, approve worker, increase budget
     */
    public function up(): void
    {
        Schema::table('work_order_comments', function (Blueprint $table) {
            // Reply functionality - muhitaji can reply to mfanyakazi comments
            if (!Schema::hasColumn('work_order_comments', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('user_id')
                    ->constrained('work_order_comments')->nullOnDelete();
            }

            // Comment type: comment, application, offer, counter_offer, reply
            if (!Schema::hasColumn('work_order_comments', 'type')) {
                $table->enum('type', ['comment', 'application', 'offer', 'counter_offer', 'reply', 'system'])
                    ->default('comment')->after('message');
            }

            // Status of application/offer: pending, accepted, rejected, countered
            if (!Schema::hasColumn('work_order_comments', 'status')) {
                $table->enum('status', ['pending', 'accepted', 'rejected', 'countered'])
                    ->default('pending')->after('type');
            }

            // Original price at the time of comment (for tracking)
            if (!Schema::hasColumn('work_order_comments', 'original_price')) {
                $table->unsignedInteger('original_price')->nullable()->after('bid_amount');
            }

            // Counter offer amount (muhitaji can counter)
            if (!Schema::hasColumn('work_order_comments', 'counter_amount')) {
                $table->unsignedInteger('counter_amount')->nullable()->after('original_price');
            }

            // Muhitaji's reply message
            if (!Schema::hasColumn('work_order_comments', 'reply_message')) {
                $table->text('reply_message')->nullable()->after('counter_amount');
            }

            // Timestamp when muhitaji replied
            if (!Schema::hasColumn('work_order_comments', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('reply_message');
            }

            // Is this an offer/negotiation that needs price agreement?
            if (!Schema::hasColumn('work_order_comments', 'is_negotiation')) {
                $table->boolean('is_negotiation')->default(false)->after('is_application');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'type',
                'status',
                'original_price',
                'counter_amount',
                'reply_message',
                'replied_at',
                'is_negotiation'
            ]);
        });
    }
};
