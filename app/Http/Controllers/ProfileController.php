<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Update basic fields
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            try {
                // Ensure directory exists
                $directory = 'profile-photos';
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($directory)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($directory);
                }
                
                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    $oldPath = $user->profile_photo_path;
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                    }
                }
                
                // Store new photo with unique name
                $file = $request->file('photo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($directory, $filename, 'public');
                $user->profile_photo_path = $path;
            } catch (\Exception $e) {
                \Log::error('Profile photo upload error: ' . $e->getMessage());
                return Redirect::route('profile.edit')
                    ->withErrors(['photo' => 'Kuna tatizo la kupakia picha: ' . $e->getMessage()]);
            }
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
