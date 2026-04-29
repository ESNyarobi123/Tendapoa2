<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging (FCM) service.
 * Zero-dependency implementation using FCM HTTP v1 API + OAuth2 service account flow.
 * - Reads service account credentials from services.firebase.credentials_path
 * - Generates signed JWT with RS256 (openssl_sign)
 * - Exchanges JWT for access token (cached 55 minutes; tokens expire in 60)
 * - Sends FCM messages via /v1/projects/{project_id}/messages:send
 */
class FirebaseService
{
    protected const TOKEN_URI = 'https://oauth2.googleapis.com/token';
    protected const FCM_ENDPOINT = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';
    protected const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    protected const CACHE_KEY = 'firebase_fcm_access_token';

    protected ?array $credentials = null;

    public function __construct()
    {
        $this->loadCredentials();
    }

    protected function loadCredentials(): void
    {
        $path = config('services.firebase.credentials_path');
        if (! $path || ! file_exists($path)) {
            return;
        }
        $raw = @file_get_contents($path);
        $json = $raw ? json_decode($raw, true) : null;
        if (is_array($json) && isset($json['client_email'], $json['private_key'], $json['project_id'])) {
            $this->credentials = $json;
        }
    }

    public function isConfigured(): bool
    {
        return config('services.firebase.enabled', true) && $this->credentials !== null;
    }

    public function getProjectId(): ?string
    {
        return $this->credentials['project_id'] ?? config('services.firebase.project_id');
    }

    /**
     * Generate / retrieve OAuth2 access token for FCM.
     * Cached ~55 min (tokens expire at 60 min).
     */
    public function getAccessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $cached = Cache::get(self::CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        try {
            $jwt = $this->generateJwt();
            $response = Http::asForm()->timeout(15)->post(self::TOKEN_URI, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (! $response->successful()) {
                Log::error('FCM token exchange failed', ['body' => $response->body()]);
                return null;
            }

            $token = $response->json('access_token');
            $expiresIn = (int) ($response->json('expires_in') ?? 3600);

            if ($token) {
                Cache::put(self::CACHE_KEY, $token, max(60, $expiresIn - 300));
                return $token;
            }
        } catch (\Throwable $e) {
            Log::error('FCM access token error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Send a notification to a single device token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        return $this->sendMessage([
            'token' => $token,
            'notification' => ['title' => $title, 'body' => $body],
            'data' => $this->stringifyData($data),
            'android' => [
                'priority' => 'high',
                'notification' => ['sound' => 'default', 'channel_id' => 'tendapoa_default'],
            ],
            'apns' => [
                'payload' => ['aps' => ['sound' => 'default']],
            ],
        ]);
    }

    /**
     * Send to multiple device tokens. Returns ['sent' => int, 'failed' => int, 'invalid_tokens' => [..]]
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $sent = 0;
        $failed = 0;
        $invalidTokens = [];

        foreach (array_unique(array_filter($tokens)) as $token) {
            $result = $this->sendMessageRaw([
                'token' => $token,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => $this->stringifyData($data),
                'android' => [
                    'priority' => 'high',
                    'notification' => ['sound' => 'default', 'channel_id' => 'tendapoa_default'],
                ],
                'apns' => [
                    'payload' => ['aps' => ['sound' => 'default']],
                ],
            ]);

            if ($result['ok']) {
                $sent++;
            } else {
                $failed++;
                if ($result['invalid_token']) {
                    $invalidTokens[] = $token;
                }
            }
        }

        return compact('sent', 'failed', 'invalidTokens');
    }

    /**
     * Send to a topic (e.g. 'workers', 'clients', 'all').
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        return $this->sendMessage([
            'topic' => $topic,
            'notification' => ['title' => $title, 'body' => $body],
            'data' => $this->stringifyData($data),
            'android' => [
                'priority' => 'high',
                'notification' => ['sound' => 'default', 'channel_id' => 'tendapoa_default'],
            ],
        ]);
    }

    protected function sendMessage(array $message): bool
    {
        return $this->sendMessageRaw($message)['ok'] ?? false;
    }

    /**
     * Low-level send. Returns ['ok' => bool, 'invalid_token' => bool, 'error' => ?string]
     */
    protected function sendMessageRaw(array $message): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'invalid_token' => false, 'error' => 'not_configured'];
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return ['ok' => false, 'invalid_token' => false, 'error' => 'no_access_token'];
        }

        $projectId = $this->getProjectId();
        $url = sprintf(self::FCM_ENDPOINT, $projectId);

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post($url, ['message' => $message]);

            if ($response->successful()) {
                return ['ok' => true, 'invalid_token' => false, 'error' => null];
            }

            // Invalid / unregistered tokens should be cleaned up
            $errorCode = $response->json('error.details.0.errorCode')
                ?? $response->json('error.status');
            $invalidToken = in_array($errorCode, [
                'UNREGISTERED',
                'INVALID_ARGUMENT',
                'NOT_FOUND',
            ], true);

            Log::warning('FCM send failed', [
                'status' => $response->status(),
                'error' => $response->json('error'),
            ]);

            return ['ok' => false, 'invalid_token' => $invalidToken, 'error' => $errorCode];
        } catch (\Throwable $e) {
            Log::error('FCM send exception: '.$e->getMessage());
            return ['ok' => false, 'invalid_token' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generate RS256-signed JWT for Google OAuth2 service account flow.
     */
    protected function generateJwt(): string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $this->credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URI,
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES)),
        ];
        $signingInput = implode('.', $segments);

        $privateKey = openssl_pkey_get_private($this->credentials['private_key']);
        if (! $privateKey) {
            throw new \RuntimeException('Invalid Firebase private key');
        }

        $signature = '';
        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * FCM data payload values must all be strings.
     */
    protected function stringifyData(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($v === null) {
                continue;
            }
            $out[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v);
        }
        return $out;
    }
}
