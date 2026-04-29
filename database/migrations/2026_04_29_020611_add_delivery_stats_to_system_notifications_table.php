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
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->unsignedInteger('total_count')->default(0)->after('action_url');
            $table->unsignedInteger('sent_count')->default(0)->after('total_count');
            $table->unsignedInteger('failed_count')->default(0)->after('sent_count');
            $table->unsignedInteger('fcm_sent_count')->default(0)->after('failed_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->dropColumn(['total_count', 'sent_count', 'failed_count', 'fcm_sent_count']);
        });
    }
};
