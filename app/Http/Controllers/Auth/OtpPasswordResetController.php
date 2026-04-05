<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpPasswordResetMail;
use App\Models\PasswordOtpReset;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OtpPasswordResetController extends Controller
{
    public function showRequestForm(): View
    {
        return view('auth.otp-request');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Tafadhali weka barua pepe yako.',
            'email.email' => 'Muundo wa barua pepe si sahihi.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Barua pepe hii haipo katika mfumo wetu.'])->withInput();
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
            Log::info('OTP mail sent successfully', ['email' => $user->email, 'host' => config('mail.mailers.smtp.host')]);
        } catch (\Exception $e) {
            Log::error('OTP mail failed', ['email' => $user->email, 'error' => $e->getMessage()]);

            if (! app()->environment('local')) {
                PasswordOtpReset::where('email', $request->email)->delete();

                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Imeshindwa kutuma barua pepe. Tafadhali jaribu tena baadaye au wasiliana na msaada.']);
            }

            return redirect()->route('password.otp.verify.form')
                ->with('otp_email', $request->email)
                ->with('dev_otp', $otp)
                ->with('status', '[DEV] Barua pepe haikutumwa. OTP yako: '.$otp);
        }

        return redirect()->route('password.otp.verify.form')
            ->with('otp_email', $request->email)
            ->with('status', 'Msimbo wa OTP umetumwa kwa barua pepe yako. Angalia inbox yako.');
    }

    public function showVerifyForm(Request $request): View
    {
        $email = session('otp_email') ?? $request->email;

        return view('auth.otp-verify', compact('email'));
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Tafadhali weka msimbo wa OTP.',
            'otp.digits' => 'Msimbo wa OTP lazima uwe na tarakimu 6.',
        ]);

        $record = PasswordOtpReset::where('email', $request->email)
            ->where('otp', $request->otp)
            ->latest()
            ->first();

        if (! $record) {
            return back()->withErrors(['otp' => 'Msimbo wa OTP si sahihi.'])->withInput();
        }

        if ($record->isExpired()) {
            $record->delete();

            return back()->withErrors(['otp' => 'Msimbo wa OTP umeisha muda. Omba msimbo mpya.'])->withInput();
        }

        $request->session()->put('otp_email', $request->email);
        $request->session()->put('otp_verified', true);

        return redirect()->route('password.otp.reset.form');
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (! session('otp_verified')) {
            return redirect()->route('password.otp.request')
                ->withErrors(['email' => 'Tafadhali thibitisha OTP kwanza.']);
        }

        $email = session('otp_email');

        return view('auth.otp-reset', compact('email'));
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        if (! session('otp_verified') || ! session('otp_email')) {
            return redirect()->route('password.otp.request')
                ->withErrors(['email' => 'Ombi si halali. Anza upya.']);
        }

        $request->validate([
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'password.required' => 'Tafadhali weka neno siri jipya.',
            'password.min' => 'Neno siri lazima liwe na herufi angalau 8.',
            'password.confirmed' => 'Neno siri hazifanani.',
        ]);

        $email = session('otp_email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('password.otp.request')
                ->withErrors(['email' => 'Mtumiaji hakupatikana.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        PasswordOtpReset::where('email', $email)->delete();

        $request->session()->forget(['otp_email', 'otp_verified']);

        return redirect()->route('login')
            ->with('status', '✅ Neno siri limebadilishwa. Ingia sasa kwa neno siri jipya.');
    }
}
