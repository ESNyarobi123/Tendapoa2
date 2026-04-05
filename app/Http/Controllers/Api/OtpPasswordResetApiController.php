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

class OtpPasswordResetApiController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Barua pepe hii haipo katika mfumo wetu.',
            ], 404);
        }

        PasswordOtpReset::where('email', $request->email)->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordOtpReset::create([
            'email' => $request->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new OtpPasswordResetMail($otp, $user->name));
            Log::info('API OTP mail sent', ['email' => $user->email]);
        } catch (\Exception $e) {
            Log::error('API OTP mail failed', ['email' => $user->email, 'error' => $e->getMessage()]);
            PasswordOtpReset::where('email', $request->email)->delete();

            return response()->json([
                'success' => false,
                'message' => 'Imeshindwa kutuma barua pepe. Jaribu tena baadaye.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Msimbo wa OTP umetumwa kwa barua pepe yako.',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $record = PasswordOtpReset::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Msimbo wa OTP si sahihi.',
            ], 422);
        }

        if ($record->isExpired()) {
            $record->delete();

            return response()->json([
                'success' => false,
                'message' => 'Msimbo wa OTP umeisha muda. Omba msimbo mpya.',
            ], 422);
        }

        $resetToken = Str::random(64);

        $record->update(['otp' => 'VERIFIED__'.$resetToken]);

        return response()->json([
            'success' => true,
            'message' => 'OTP imethibitishwa. Sasa weka neno siri jipya.',
            'reset_token' => $resetToken,
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = PasswordOtpReset::where('email', $request->email)
            ->where('otp', 'VERIFIED__'.$request->reset_token)
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Ombi si halali. Thibitisha OTP kwanza.',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Mtumiaji hapatikani.',
            ], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        $record->delete();

        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Neno siri limebadilishwa kwa mafanikio. Ingia sasa.',
        ]);
    }
}
