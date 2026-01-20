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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique(); // e.g., "1.0.0", "1.1.0"
            $table->string('file_path'); // path to APK file in storage
            $table->string('file_name'); // original filename
            $table->bigInteger('file_size'); // file size in bytes
            $table->boolean('is_active')->default(false); // whether this is the current active version
            $table->text('description')->nullable(); // version description/release notes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
