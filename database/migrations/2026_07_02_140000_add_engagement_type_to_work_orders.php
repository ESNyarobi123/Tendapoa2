<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('engagement_type', 32)
                ->default('job_request')
                ->after('poster_type');

            $table->foreignId('source_listing_id')
                ->nullable()
                ->after('engagement_type')
                ->constrained('work_orders')
                ->nullOnDelete();

            $table->index('engagement_type');
        });

        if (Schema::hasColumn('work_orders', 'poster_type')) {
            DB::table('work_orders')
                ->where('poster_type', 'mfanyakazi')
                ->whereNull('source_listing_id')
                ->update(['engagement_type' => 'service_listing']);
        }
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['source_listing_id']);
            $table->dropIndex(['engagement_type']);
            $table->dropColumn(['engagement_type', 'source_listing_id']);
        });
    }
};
