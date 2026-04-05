<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WORKFLOW UPGRADE: Disputes table for handling conflicts between client and worker.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('disputes')) {
            return;
        }

        Schema::create('disputes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $t->foreignId('raised_by')->constrained('users');
            $t->foreignId('against_user')->constrained('users');

            // open, under_review, resolved_full_worker, resolved_full_client, resolved_split, resolved_partial, closed
            $t->string('status', 32)->default('open');

            $t->text('reason');
            $t->text('resolution_note')->nullable();

            // Resolution amounts
            $t->unsignedInteger('worker_amount')->nullable();
            $t->unsignedInteger('client_refund_amount')->nullable();

            $t->foreignId('resolved_by')->nullable()->constrained('users');
            $t->timestamp('resolved_at')->nullable();

            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['work_order_id', 'status']);
        });

        // Dispute messages / evidence
        if (! Schema::hasTable('dispute_messages')) {
            Schema::create('dispute_messages', function (Blueprint $t) {
                $t->id();
                $t->foreignId('dispute_id')->constrained('disputes')->cascadeOnDelete();
                $t->foreignId('user_id')->constrained('users');
                $t->text('message');
                $t->string('attachment')->nullable(); // file path
                $t->boolean('is_admin')->default(false);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_messages');
        Schema::dropIfExists('disputes');
    }
};
