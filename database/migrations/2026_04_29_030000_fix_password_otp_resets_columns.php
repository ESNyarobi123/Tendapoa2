<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Fixes critical bugs in the password OTP reset flow:
     * - otp column was varchar(6) but controller stored 74-char reset tokens in it (broken!)
     * - Now: otp stays small (10), reset_token is its own column (64 chars), and we
     *   track verified_at separately so we can enforce a short reset-window expiry.
     */
    public function up(): void
    {
        if (! Schema::hasTable('password_otp_resets')) {
            return;
        }

        Schema::table('password_otp_resets', function (Blueprint $table) {
            // Expand otp to be safe (still small)
            $table->string('otp', 10)->change();

            if (! Schema::hasColumn('password_otp_resets', 'reset_token')) {
                $table->string('reset_token', 64)->nullable()->after('otp')->index();
            }
            if (! Schema::hasColumn('password_otp_resets', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('reset_token');
            }
            if (! Schema::hasColumn('password_otp_resets', 'attempts')) {
                $table->unsignedTinyInteger('attempts')->default(0)->after('verified_at');
            }
        });

        // Best-effort cleanup: delete any rows whose otp got truncated/corrupted in old flow
        // (these can never be reset successfully anyway).
        \DB::table('password_otp_resets')
            ->where('otp', 'like', 'VERIFI%')
            ->delete();
    }

    public function down(): void
    {
        if (! Schema::hasTable('password_otp_resets')) {
            return;
        }
        Schema::table('password_otp_resets', function (Blueprint $table) {
            if (Schema::hasColumn('password_otp_resets', 'attempts')) {
                $table->dropColumn('attempts');
            }
            if (Schema::hasColumn('password_otp_resets', 'verified_at')) {
                $table->dropColumn('verified_at');
            }
            if (Schema::hasColumn('password_otp_resets', 'reset_token')) {
                $table->dropColumn('reset_token');
            }
            $table->string('otp', 6)->change();
        });
    }
};
