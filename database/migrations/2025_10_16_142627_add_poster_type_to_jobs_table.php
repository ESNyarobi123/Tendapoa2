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
        // Add to legacy jobs table
        try {
            Schema::table('jobs', function (Blueprint $table) {
                $table->enum('poster_type', ['muhitaji', 'mfanyakazi'])->default('muhitaji')->after('user_id');
                $table->decimal('posting_fee', 10, 2)->nullable()->after('poster_type');
            });
        } catch (Throwable $e) {
        }

        // Also add to work_orders (the actual table used by Job model)
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('work_orders', 'poster_type')) {
                    $table->string('poster_type', 20)->default('muhitaji')->after('user_id');
                }
                if (! Schema::hasColumn('work_orders', 'posting_fee')) {
                    $table->decimal('posting_fee', 10, 2)->nullable()->after('poster_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropColumn(['poster_type', 'posting_fee']);
            });
        } catch (Throwable $e) {
        }

        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                foreach (['poster_type', 'posting_fee'] as $col) {
                    if (Schema::hasColumn('work_orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
