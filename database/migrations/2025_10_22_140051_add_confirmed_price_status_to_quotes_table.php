<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'confirmed_price'
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pending', 'selected', 'rejected', 'expired', 'withdrawn', 'confirmed_price') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'confirmed_price' from enum
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pending', 'selected', 'rejected', 'expired', 'withdrawn') DEFAULT 'pending'");
    }
};