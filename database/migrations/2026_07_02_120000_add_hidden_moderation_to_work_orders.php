<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable()->after('cancel_reason');
            $table->foreignId('hidden_by')->nullable()->after('hidden_at')->constrained('users')->nullOnDelete();
            $table->string('hidden_reason', 500)->nullable()->after('hidden_by');
            $table->index('hidden_at');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['hidden_by']);
            $table->dropColumn(['hidden_at', 'hidden_by', 'hidden_reason']);
        });
    }
};
