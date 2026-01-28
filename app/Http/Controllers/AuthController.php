<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /* REGISTER */
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name'     => ['required','string','min:2','max:120'],
            'email'    => ['required','email', Rule::unique('users','email')],
            'password' => ['required','confirmed', Password::min(6)],
            'role'     => ['required', Rule::in(['muhitaji','mfanyakazi'])],
            'phone'    => ['nullable','regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
            // ✅ Enforce valid geo ranges
            'lat'      => ['nullable','numeric','between:-90,90'],
            'lng'      => ['nullable','numeric','between:-180,180'],
        ],[
            'phone.regex' => 'Weka 06/07xxxxxxxx au 2556/2557xxxxxxxx.',
            'lat.between' => 'Lat lazima iwe kati ya -90 na 90.',
            'lng.between' => 'Lng lazima iwe kati ya -180 na 180.',
        ]);

        // Sanitize & round (ikizidi range, kuwa null)
        $lat = $r->filled('lat') ? (float) $r->input('lat') : null;
        $lng = $r->filled('lng') ? (float) $r->input('lng') : null;

        if ($lat !== null && ($lat < -90 || $lat > 90))   { $lat = null; }
        if ($lng !== null && ($lng < -180 || $lng > 180)) { $lng = null; }

        if ($lat !== null) { $lat = round($lat, 6); }
        if ($lng !== null) { $lng = round($lng, 6); }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'phone'    => $data['phone'] ?? null,
            'lat'      => $lat,
            'lng'      => $lng,
        ]);

        Auth::login($user);
        if ($r->hasSession()) {
            $r->session()->regenerate();
        }

        return redirect()->route('dashboard');
    }

    /* LOGIN */
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $cred = $r->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
            'remember' => ['nullable','boolean'],
        ]);

        if (Auth::attempt(['email'=>$cred['email'], 'password'=>$cred['password']], $r->boolean('remember'))) {
            $r->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email'=>'Taarifa si sahihi au akaunti haipo.'])->onlyInput('email');
    }

    /* LOGOUT */
    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('home');
    }

    /* API METHODS */
    public function apiRegister(Request $r)
    {
        $data = $r->validate([
            'name'     => ['required','string','min:2','max:120'],
            'email'    => ['required','email', Rule::unique('users','email')],
            'password' => ['required','confirmed', Password::min(6)],
            'role'     => ['required', Rule::in(['muhitaji','mfanyakazi'])],
            'phone'    => ['nullable','regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
            'lat'      => ['nullable','numeric','between:-90,90'],
            'lng'      => ['nullable','numeric','between:-180,180'],
        ],[
            'phone.regex' => 'Weka 06/07xxxxxxxx au 2556/2557xxxxxxxx.',
            'lat.between' => 'Lat lazima iwe kati ya -90 na 90.',
            'lng.between' => 'Lng lazima iwe kati ya -180 na 180.',
        ]);

        // Sanitize & round (ikizidi range, kuwa null)
        $lat = $r->filled('lat') ? (float) $r->input('lat') : null;
        $lng = $r->filled('lng') ? (float) $r->input('lng') : null;

        if ($lat !== null && ($lat < -90 || $lat > 90))   { $lat = null; }
        if ($lng !== null && ($lng < -180 || $lng > 180)) { $lng = null; }

        if ($lat !== null) { $lat = round($lat, 6); }
        if ($lng !== null) { $lng = round($lng, 6); }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'phone'    => $data['phone'] ?? null,
            'lat'      => $lat,
            'lng'      => $lng,
        ]);

        Auth::login($user);
        if ($r->hasSession()) {
            $r->session()->regenerate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Akaunti imeundwa kwa mafanikio!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'lat' => $user->lat,
                'lng' => $user->lng,
                'profile_photo_path' => $user->profile_photo_path,
                'profile_photo_url' => $user->profile_photo_url,
            ]
        ]);
    }

    public function apiLogin(Request $r)
    {
        $cred = $r->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
            'remember' => ['nullable','boolean'],
        ]);

        if (Auth::attempt(['email'=>$cred['email'], 'password'=>$cred['password']], $r->boolean('remember'))) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // HAPA NDIPO TUNAPOTENGENEZA TOKEN
            // Futa token za zamani (optional, kwa usalama)
            $user->tokens()->delete();
            
            // Tengeneza token mpya
            $token = $user->createToken('mobile-app')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'message' => 'Umeingia kwa mafanikio!',
                'token' => $token, // <--- HII NDIO MUHIMU SANA
                'user' => $user,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Taarifa si sahihi au akaunti haipo.',
            'errors' => ['email' => 'Taarifa si sahihi au akaunti haipo.']
        ], 401);
    }

    public function apiLogout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        
        return response()->json([
            'success' => true,
            'message' => 'Umetoka kwa mafanikio!'
        ]);
    }

    public function getuser(Request $r)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Huna ruhusa'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'lat' => $user->lat,
                'lng' => $user->lng,
                'profile_photo_path' => $user->profile_photo_path,
                'profile_photo_url' => $user->profile_photo_url,
            ]
        ]);
    }

    // Njia ya kusajili FCM Token
    public function updateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->fcm_token = $request->token; // Hakikisha una column 'fcm_token' kwenye users table
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully'
        ]);
    }

    // Update Profile (Photo & Details)
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:5120'], // 5MB Max
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }
            
            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        
        if ($request->filled('phone')) {
            $user->phone = $request->phone;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Wasifu umesasishwa kikamilifu.',
            'user' => $user->fresh(),
            'photo_url' => $user->profile_photo_url
        ]);
    }
}
