<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Categories: name in Swahili + English for API localization.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_sw', 120)->nullable()->after('name');
            $table->string('name_en', 120)->nullable()->after('name_sw');
        });

        // Backfill
        \Illuminate\Support\Facades\DB::table('categories')->whereNotNull('name')->update([
            'name_sw' => \Illuminate\Support\Facades\DB::raw('name'),
            'name_en' => \Illuminate\Support\Facades\DB::raw('name'),
        ]);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_sw', 'name_en']);
        });
    }
};
