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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            $table->integer('quoted_price'); // Worker's bid/quote
            $table->integer('eta_minutes')->nullable(); // Estimated time to arrive (in minutes)
            $table->text('notes')->nullable(); // Worker's notes/message
            $table->enum('status', ['pending', 'selected', 'rejected', 'expired', 'withdrawn'])->default('pending');
            $table->timestamp('expires_at')->nullable(); // When this quote expires
            $table->timestamp('selected_at')->nullable(); // When muhitaji selected this quote
            $table->timestamps();
            
            // Index for performance
            $table->index(['job_id', 'status']);
            $table->index(['worker_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
