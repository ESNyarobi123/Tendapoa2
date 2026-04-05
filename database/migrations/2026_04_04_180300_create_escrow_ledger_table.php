<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WORKFLOW UPGRADE: Escrow ledger tracks all held fund movements.
 * Every hold, release, refund is an immutable record.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('escrow_ledger')) {
            return;
        }

        Schema::create('escrow_ledger', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $t->foreignId('client_id')->constrained('users');
            $t->foreignId('worker_id')->nullable()->constrained('users');

            // Type: hold, release, refund, partial_refund, platform_fee
            $t->string('type', 24);

            $t->unsignedInteger('amount');
            $t->text('description')->nullable();

            // Reference to payment or wallet transaction
            $t->unsignedBigInteger('payment_id')->nullable();
            $t->unsignedBigInteger('wallet_transaction_id')->nullable();

            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['work_order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrow_ledger');
    }
};
