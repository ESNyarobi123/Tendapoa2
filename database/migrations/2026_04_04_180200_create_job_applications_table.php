<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WORKFLOW UPGRADE: Proper job_applications table.
 * Replaces the comment-based application system with a dedicated entity.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_applications')) {
            return;
        }

        Schema::create('job_applications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $t->foreignId('worker_id')->constrained('users')->cascadeOnDelete();

            // Application content
            $t->unsignedInteger('proposed_amount');
            $t->text('message')->nullable();
            $t->string('eta_text', 100)->nullable(); // e.g. "Saa 2", "Leo jioni", "Kesho asubuhi"
            $t->unsignedInteger('eta_minutes')->nullable(); // machine-readable ETA

            // Status: applied, shortlisted, rejected, selected, withdrawn, countered, accepted_counter
            $t->string('status', 24)->default('applied');

            // Counter offer from client
            $t->unsignedInteger('counter_amount')->nullable();
            $t->text('client_response_note')->nullable();

            // Timestamps for status transitions
            $t->timestamp('shortlisted_at')->nullable();
            $t->timestamp('selected_at')->nullable();
            $t->timestamp('rejected_at')->nullable();
            $t->timestamp('withdrawn_at')->nullable();
            $t->timestamp('countered_at')->nullable();

            $t->timestamps();

            // Prevent duplicate active applications from same worker
            $t->unique(['work_order_id', 'worker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
