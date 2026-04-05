<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WORKFLOW UPGRADE: Two-sided reviews after job completion.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews')) {
            return;
        }

        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $t->foreignId('reviewer_id')->constrained('users');
            $t->foreignId('reviewee_id')->constrained('users');

            $t->unsignedTinyInteger('rating'); // 1-5
            $t->text('comment')->nullable();

            $t->timestamps();

            // One review per reviewer per job
            $t->unique(['work_order_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
