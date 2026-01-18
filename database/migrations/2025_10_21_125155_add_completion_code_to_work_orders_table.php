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
        Schema::table('work_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('work_orders', 'completion_code')) {
                $table->string('completion_code', 6)->nullable()->after('payment_deadline_at');
            }
            if (!Schema::hasColumn('work_orders', 'completion_code_generated_at')) {
                $table->timestamp('completion_code_generated_at')->nullable()->after('completion_code');
            }
            if (!Schema::hasColumn('work_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('completion_code_generated_at');
            }
            if (!Schema::hasColumn('work_orders', 'completion_notes')) {
                $table->text('completion_notes')->nullable()->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['completion_code', 'completion_code_generated_at', 'completed_at', 'completion_notes']);
        });
    }
};
