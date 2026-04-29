<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Mobile API endpoints for managing FCM device tokens.
 * Each user can have multiple device tokens (multi-device support).
 */
class FcmTokenController extends Controller
{
    /**
     * Register or refresh an FCM device token for the authenticated user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string', 'min:50', 'max:500'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $token = $request->input('token');

        // Upsert: if token already exists, claim it for current user; else create.
        $device = DeviceToken::updateOrCreate(
            ['token' => $token],
            [
                'user_id' => $user->id,
                'platform' => $request->input('platform'),
                'device_name' => $request->input('device_name'),
                'app_version' => $request->input('app_version'),
                'last_used_at' => now(),
            ]
        );

        // Backward compat: also store on users.fcm_token (single most-recent)
        if ($user->fcm_token !== $token) {
            $user->fcm_token = $token;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token registered.',
            'data' => [
                'id' => $device->id,
                'platform' => $device->platform,
                'last_used_at' => $device->last_used_at,
            ],
        ]);
    }

    /**
     * Unregister an FCM device token (e.g. on logout).
     */
    public function unregister(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $deleted = DeviceToken::where('user_id', $user->id)
            ->where('token', $request->input('token'))
            ->delete();

        // Clear single-token field if it matches
        if ($user->fcm_token === $request->input('token')) {
            $user->fcm_token = null;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token removed.',
            'deleted' => $deleted,
        ]);
    }

    /**
     * List the authenticated user's registered devices (for settings UI).
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        $devices = $user->deviceTokens()
            ->select('id', 'platform', 'device_name', 'app_version', 'last_used_at', 'created_at')
            ->orderByDesc('last_used_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }
}
