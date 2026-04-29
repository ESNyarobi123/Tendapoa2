<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add fcm_token column to users (single-device fallback, referenced by AuthController@updateToken)
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'fcm_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('fcm_token', 500)->nullable()->after('phone');
            });
        }

        // Multi-device support: one user can have many devices
        if (! Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('token', 500);
                $table->string('platform', 20)->nullable(); // android, ios, web
                $table->string('device_name')->nullable();
                $table->string('app_version', 20)->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->unique('token');
                $table->index(['user_id', 'platform']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'fcm_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('fcm_token');
            });
        }
    }
};
