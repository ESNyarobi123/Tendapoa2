<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WORKFLOW UPGRADE: Add held_balance to wallets for escrow functionality.
 * available_balance = balance - held_balance
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $t) {
            if (! Schema::hasColumn('wallets', 'held_balance')) {
                $t->unsignedBigInteger('held_balance')->default(0)->after('balance');
            }
            if (! Schema::hasColumn('wallets', 'total_earned')) {
                $t->unsignedBigInteger('total_earned')->default(0)->after('held_balance');
            }
            if (! Schema::hasColumn('wallets', 'total_spent')) {
                $t->unsignedBigInteger('total_spent')->default(0)->after('total_earned');
            }
            if (! Schema::hasColumn('wallets', 'total_withdrawn')) {
                $t->unsignedBigInteger('total_withdrawn')->default(0)->after('total_spent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $t) {
            foreach (['held_balance', 'total_earned', 'total_spent', 'total_withdrawn'] as $col) {
                if (Schema::hasColumn('wallets', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
