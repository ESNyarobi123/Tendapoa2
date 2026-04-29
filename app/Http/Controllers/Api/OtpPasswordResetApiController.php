<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpPasswordResetMail;
use App\Models\PasswordOtpReset;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * OTP-based password reset flow for the mobile app.
 *
 * Three steps:
 *   1) POST /password/send-otp     — generate + email OTP (10-min lifetime)
 *   2) POST /password/verify-otp   — verify OTP, return short-lived reset_token
 *   3) POST /password/reset        — set new password using reset_token (15-min window)
 *
 * Security hardening:
 * - No email enumeration: identical generic response whether email exists or not.
 * - Brute-force protection: max 5 wrong OTP attempts per record before invalidation.
 * - Throttle middleware (configured at route layer) prevents OTP-spamming.
 * - reset_token stored in its own column (not stuffed into the otp column).
 * - All sanctum tokens revoked after a successful password reset.
 */
class OtpPasswordResetApiController extends Controller
{
    private const OTP_LIFETIME_MINUTES = 10;

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($request->input('email')));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        // Always return success to prevent email enumeration.
        // Only do real work if the user exists.
        if ($user) {
            // Invalidate any previous OTP for this email
            PasswordOtpReset::where('email', $user->email)->delete();

            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            PasswordOtpReset::create([
                'email' => $user->email,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(self::OTP_LIFETIME_MINUTES),
            ]);

            try {
                Mail::to($user->email)->send(new OtpPasswordResetMail($otp, $user->name));
                Log::info('Password OTP sent', ['email' => $user->email]);
            } catch (\Throwable $e) {
                Log::error('Password OTP mail failed', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                // Clean up so user can retry; still return generic success to caller.
                PasswordOtpReset::where('email', $user->email)->delete();
            }
        } else {
            Log::info('Password OTP requested for unknown email', ['email' => $email]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ikiwa barua pepe imesajiliwa, msimbo wa OTP umetumwa. Angalia kwenye inbox yako.',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($request->input('email')));

        // Find latest active OTP for this email (case-insensitive)
        $record = PasswordOtpReset::whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Hakuna ombi la OTP linalosubiri. Tafadhali omba msimbo mpya.',
            ], 422);
        }

        if ($record->isExpired()) {
            $record->delete();
            return response()->json([
                'success' => false,
                'message' => 'Msimbo wa OTP umeisha muda. Omba msimbo mpya.',
            ], 422);
        }

        if ($record->hasExceededAttempts()) {
            $record->delete();
            return response()->json([
                'success' => false,
                'message' => 'Umejaribu mara nyingi. Omba msimbo mpya wa OTP.',
            ], 429);
        }

        // Constant-time comparison
        if (! hash_equals($record->otp, (string) $request->input('otp'))) {
            $record->increment('attempts');
            $remaining = max(0, PasswordOtpReset::MAX_ATTEMPTS - $record->attempts);

            return response()->json([
                'success' => false,
                'message' => 'Msimbo wa OTP si sahihi.',
                'attempts_remaining' => $remaining,
            ], 422);
        }

        // Success — issue reset token
        $resetToken = Str::random(64);
        $record->update([
            'reset_token' => $resetToken,
            'verified_at' => now(),
            'attempts' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP imethibitishwa. Weka neno siri jipya ndani ya dakika '.PasswordOtpReset::RESET_WINDOW_MINUTES.'.',
            'reset_token' => $resetToken,
            'expires_in_minutes' => PasswordOtpReset::RESET_WINDOW_MINUTES,
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'reset_token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $email = strtolower(trim($request->input('email')));

        $record = PasswordOtpReset::whereRaw('LOWER(email) = ?', [$email])
            ->where('reset_token', $request->input('reset_token'))
            ->whereNotNull('verified_at')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Ombi si halali. Thibitisha OTP kwanza.',
            ], 422);
        }

        if (! $record->isResetWindowOpen()) {
            $record->delete();
            return response()->json([
                'success' => false,
                'message' => 'Muda wa kubadili neno siri umeisha. Anza upya kwa kuomba OTP.',
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            $record->delete();
            return response()->json([
                'success' => false,
                'message' => 'Ombi si halali.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->input('password'))]);

        // Cleanup: remove reset record + revoke all active sessions/tokens for safety
        $record->delete();
        PasswordOtpReset::where('email', $user->email)->delete();
        try {
            $user->tokens()->delete();
        } catch (\Throwable $e) {
            Log::warning('Token revoke after password reset failed: '.$e->getMessage());
        }

        Log::info('Password reset via OTP succeeded', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Neno siri limebadilishwa kwa mafanikio. Ingia sasa.',
        ]);
    }
}
