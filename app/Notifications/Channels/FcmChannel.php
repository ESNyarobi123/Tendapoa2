<?php

namespace App\Notifications\Channels;

use App\Models\DeviceToken;
use App\Services\FirebaseService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Custom Laravel notification channel for FCM push.
 * Notifications must implement `toFcm($notifiable): array` returning:
 *   ['title' => string, 'body' => string, 'data' => array]
 * Falls back to `toArray()` payload if toFcm is absent.
 */
class FcmChannel
{
    public function __construct(protected FirebaseService $fcm)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $this->fcm->isConfigured()) {
            Log::info('FcmChannel: skipped — Firebase not configured');

            return;
        }

        // Gather tokens: prefer DeviceToken table (multi-device); fallback to users.fcm_token
        $tokens = [];
        if (method_exists($notifiable, 'deviceTokens')) {
            $tokens = $notifiable->deviceTokens()->pluck('token')->all();
        }
        if (empty($tokens) && isset($notifiable->fcm_token) && $notifiable->fcm_token) {
            $tokens[] = $notifiable->fcm_token;
        }

        if (empty($tokens)) {
            Log::info('FcmChannel: no tokens for notifiable', ['notifiable_id' => $notifiable->id ?? null]);

            return;
        }

        // Build payload
        if (method_exists($notification, 'toFcm')) {
            $payload = $notification->toFcm($notifiable);
        } elseif (method_exists($notification, 'toArray')) {
            $arr = $notification->toArray($notifiable);
            $payload = [
                'title' => $arr['title'] ?? config('app.name', 'TendaPoa'),
                'body' => $arr['message'] ?? '',
                'data' => $arr,
            ];
        } else {
            Log::info('FcmChannel: no toFcm/toArray on notification', ['notification' => get_class($notification)]);

            return;
        }

        $title = (string) ($payload['title'] ?? 'TendaPoa');
        $body = (string) ($payload['body'] ?? '');
        $data = (array) ($payload['data'] ?? []);

        // Include standard fields for mobile app routing
        $data['notification_type'] = $payload['type'] ?? ($data['type'] ?? 'general');

        Log::info('FcmChannel: sending', [
            'notifiable_id' => $notifiable->id ?? null,
            'token_count' => count($tokens),
            'title' => $title,
            'body' => mb_substr($body, 0, 80),
            'type' => $data['notification_type'] ?? 'general',
        ]);

        try {
            $result = $this->fcm->sendToTokens($tokens, $title, $body, $data);

            Log::info('FcmChannel: result', [
                'sent' => $result['sent'],
                'failed' => $result['failed'],
                'invalid_tokens' => count($result['invalidTokens'] ?? []),
            ]);

            // Clean up invalid tokens
            if (! empty($result['invalidTokens'])) {
                DeviceToken::whereIn('token', $result['invalidTokens'])->delete();
            }
        } catch (\Throwable $e) {
            Log::error('FcmChannel send failed: '.$e->getMessage());
        }
    }
}
