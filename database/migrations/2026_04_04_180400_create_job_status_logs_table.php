<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WORKFLOW UPGRADE: Audit trail for all job status changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_status_logs')) {
            return;
        }

        Schema::create('job_status_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('from_status', 32)->nullable();
            $t->string('to_status', 32);
            $t->text('note')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['work_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_status_logs');
    }
};
