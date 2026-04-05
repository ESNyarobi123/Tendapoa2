<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pending', 'selected', 'rejected', 'expired', 'withdrawn', 'confirmed_price') DEFAULT 'pending'");
        } catch (Throwable $e) {
            // SQLite does not support MODIFY COLUMN; skip gracefully
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pending', 'selected', 'rejected', 'expired', 'withdrawn') DEFAULT 'pending'");
        } catch (Throwable $e) {
        }
    }
};
