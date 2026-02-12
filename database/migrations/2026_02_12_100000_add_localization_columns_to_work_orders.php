<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Database localization: title and description in Swahili + English.
     * API returns single "title"/"description" based on Accept-Language.
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('work_orders', 'title_sw')) {
                $table->string('title_sw', 255)->nullable()->after('title');
            }
            if (!Schema::hasColumn('work_orders', 'title_en')) {
                $table->string('title_en', 255)->nullable()->after('title_sw');
            }
            if (!Schema::hasColumn('work_orders', 'description_sw')) {
                $table->text('description_sw')->nullable()->after('description');
            }
            if (!Schema::hasColumn('work_orders', 'description_en')) {
                $table->text('description_en')->nullable()->after('description_sw');
            }
        });

        // Backfill: copy existing title/description to both _sw and _en only where still null
        \Illuminate\Support\Facades\DB::table('work_orders')
            ->whereNotNull('title')
            ->where(function ($q) {
                $q->whereNull('title_sw')->orWhereNull('title_en');
            })
            ->update([
                'title_sw' => \Illuminate\Support\Facades\DB::raw('COALESCE(title_sw, title)'),
                'title_en' => \Illuminate\Support\Facades\DB::raw('COALESCE(title_en, title)'),
            ]);
        \Illuminate\Support\Facades\DB::table('work_orders')
            ->where(function ($q) {
                $q->whereNull('description_sw')->orWhereNull('description_en');
            })
            ->update([
                'description_sw' => \Illuminate\Support\Facades\DB::raw('COALESCE(description_sw, description)'),
                'description_en' => \Illuminate\Support\Facades\DB::raw('COALESCE(description_en, description)'),
            ]);
    }

    public function down(): void
    {
        $columns = ['title_sw', 'title_en', 'description_sw', 'description_en'];
        $existing = array_filter($columns, fn ($c) => Schema::hasColumn('work_orders', $c));
        if (!empty($existing)) {
            Schema::table('work_orders', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
