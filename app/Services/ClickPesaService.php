<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClickPesaService
{
    private string $baseUrl;

    private string $clientId;

    private string $apiKey;

    private ?string $checksumKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.clickpesa.base_url', 'https://api.clickpesa.com/third-parties'), '/');
        $this->clientId = trim((string) config('services.clickpesa.client_id', ''));
        $this->apiKey = trim((string) config('services.clickpesa.api_key', ''));
        $ck = config('services.clickpesa.checksum_key');
        $this->checksumKey = $ck !== null && $ck !== '' ? trim((string) $ck) : null;
    }

    // ─── TOKEN ───────────────────────────────────────────────────────────

    /**
     * Generate or retrieve a cached Bearer token (valid ~1 hour).
     * Cache read/write failures are ignored so HTTP still works when CACHE_DRIVER=database but DB is down (e.g. local artisan).
     */
    public function getToken(): string
    {
        $cacheKey = 'clickpesa_token.'.hash('sha256', $this->clientId.'|'.$this->apiKey.'|'.$this->baseUrl);

        try {
            $cached = Cache::get($cacheKey);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        } catch (Throwable $e) {
            Log::warning('ClickPesa: cache read skipped', ['error' => $e->getMessage()]);
        }

        $token = $this->requestNewAccessToken();

        try {
            Cache::put($cacheKey, $token, now()->addSeconds(3500));
        } catch (Throwable $e) {
            Log::warning('ClickPesa: could not store token in cache', ['error' => $e->getMessage()]);
        }

        return $token;
    }

    /**
     * POST /generate-token (no cache).
     */
    private function requestNewAccessToken(): string
    {
        if ($this->clientId === '' || $this->apiKey === '') {
            Log::error('ClickPesa: CLICKPESA_CLIENT_ID / CLICKPESA_API_KEY are empty');

            throw new \RuntimeException(
                'Malipo ya ClickPesa hayajapangwa kwenye seva: weka CLICKPESA_CLIENT_ID na CLICKPESA_API_KEY kwenye .env, kisha endesha `php artisan config:clear` (na uwe usiache nafasi za ziada mbele/yuma ya thamani).'
            );
        }

        $url = $this->baseUrl.'/generate-token';
        $resp = Http::withHeaders([
            'client-id' => $this->clientId,
            'api-key' => $this->apiKey,
            'Accept' => 'application/json',
        ])->acceptJson()->post($url);

        $body = $resp->json();
        $body = is_array($body) ? $body : [];

        $token = $body['token'] ?? null;
        $successFlag = $body['success'] ?? null;

        if ($resp->successful() && $successFlag !== false && is_string($token) && $token !== '') {
            $raw = $token;

            return str_starts_with($raw, 'Bearer ')
                ? substr($raw, 7)
                : $raw;
        }

        $apiMessage = isset($body['message']) && is_string($body['message']) ? $body['message'] : null;
        $rawBody = $resp->body();
        $looksUnauthorized = $apiMessage !== null && strcasecmp(trim($apiMessage), 'Unauthorized') === 0;
        $bodyUnauthorized = $rawBody !== '' && stripos($rawBody, 'Unauthorized') !== false;

        Log::error('ClickPesa token generation failed', [
            'url' => $url,
            'status' => $resp->status(),
            'body' => strlen($rawBody) > 500 ? substr($rawBody, 0, 500).'…' : $rawBody,
        ]);

        $sandboxHint = str_contains($this->baseUrl, 'sandbox')
            ? ''
            : ' Ikiwa funguo zako ni za majaribio (sandbox), weka CLICKPESA_USE_SANDBOX=true au CLICKPESA_BASE_URL=https://api-sandbox.clickpesa.com/third-parties kwenye .env.';

        if ($resp->status() === 401 || $looksUnauthorized || ($resp->failed() && $bodyUnauthorized)) {
            throw new \RuntimeException(
                'ClickPesa: haikuweza kuingia (Unauthorized). Hakikisha CLIENT_ID na API_KEY zinafanana na mazingira (sandbox vs production), `php artisan config:clear`, kisha jaribu `php artisan clickpesa:test-token` kwenye seva.'.$sandboxHint
            );
        }

        if ($resp->status() === 403) {
            throw new \RuntimeException(
                'ClickPesa: '.($apiMessage ?? 'API key si sahihi au imekwisha muda. Tengeneza API key mpya kwenye dashboard ya ClickPesa na usasishe .env.')
            );
        }

        throw new \RuntimeException(
            'ClickPesa: imeshindikana kupata token — '.($apiMessage ?: (strlen($rawBody) > 200 ? 'HTTP '.$resp->status() : $rawBody))
        );
    }

    /**
     * Force a fresh token (e.g. after a 401).
     */
    public function refreshToken(): string
    {
        $this->forgetTokenCache();

        return $this->getToken();
    }

    /**
     * Clear cached JWT for current configured credentials (e.g. after key rotation).
     */
    public function forgetTokenCache(): void
    {
        $cacheKey = 'clickpesa_token.'.hash('sha256', $this->clientId.'|'.$this->apiKey.'|'.$this->baseUrl);
        try {
            Cache::forget($cacheKey);
            Cache::forget('clickpesa_token');
        } catch (Throwable $e) {
            Log::warning('ClickPesa: could not clear token cache', ['error' => $e->getMessage()]);
        }
    }

    // ─── CHECKSUM ────────────────────────────────────────────────────────

    /**
     * HMAC-SHA256 checksum over a recursively key-sorted JSON payload.
     */
    public function createChecksum(array $payload): string
    {
        $clean = $payload;
        unset($clean['checksum'], $clean['checksumMethod']);
        $sorted = $this->sortKeysRecursive($clean);
        $json = json_encode($sorted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash_hmac('sha256', $json, $this->checksumKey ?? '');
    }

    private function sortKeysRecursive(array $data): array
    {
        ksort($data);
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->sortKeysRecursive($v);
            }
        }

        return $data;
    }

    /**
     * Validate a webhook checksum.
     */
    public function verifyChecksum(array $payload): bool
    {
        if (! $this->checksumKey) {
            return true; // checksum not enabled
        }

        $provided = $payload['checksum'] ?? '';
        $expected = $this->createChecksum($payload);

        return hash_equals($expected, $provided);
    }

    // ─── COLLECTION (USSD Push) ──────────────────────────────────────────

    /**
     * Preview a USSD Push collection request.
     */
    public function previewPayment(array $data): array
    {
        $payload = [
            'amount' => (string) $data['amount'],
            'currency' => 'TZS',
            'orderReference' => $data['orderReference'],
            'phoneNumber' => $this->formatPhone($data['phoneNumber']),
        ];

        if ($this->checksumKey) {
            $payload['checksum'] = $this->createChecksum($payload);
        }

        return $this->post('/payments/preview-ussd-push-request', $payload);
    }

    /**
     * Initiate a USSD Push collection request (sends prompt to phone).
     */
    public function initiatePayment(array $data): array
    {
        $payload = [
            'amount' => (string) $data['amount'],
            'currency' => 'TZS',
            'orderReference' => $data['orderReference'],
            'phoneNumber' => $this->formatPhone($data['phoneNumber']),
        ];

        if ($this->checksumKey) {
            $payload['checksum'] = $this->createChecksum($payload);
        }

        return $this->post('/payments/initiate-ussd-push-request', $payload);
    }

    /**
     * Start a full collection flow: preview → initiate.
     * Returns the initiate response if preview passes.
     */
    public function startPayment(array $data): array
    {
        // Step 1: Preview
        $preview = $this->previewPayment($data);
        if (! $preview['ok']) {
            return $preview;
        }

        // Step 2: Check at least one method is AVAILABLE
        $methods = $preview['json']['activeMethods'] ?? [];
        $available = collect($methods)->where('status', 'AVAILABLE');
        if ($available->isEmpty()) {
            $msg = collect($methods)->pluck('message')->filter()->first()
                   ?? 'Hakuna njia ya malipo inayopatikana kwa kiasi hiki.';
            Log::warning('ClickPesa: no available payment method', ['methods' => $methods]);

            return ['ok' => false, 'json' => ['message' => $msg], 'status' => 400];
        }

        // Step 3: Initiate
        return $this->initiatePayment($data);
    }

    /**
     * Query a single payment by orderReference.
     */
    public function queryPayment(string $orderReference): array
    {
        return $this->get('/payments/'.urlencode($orderReference));
    }

    /**
     * Query all payments with optional filters.
     */
    public function queryAllPayments(array $filters = []): array
    {
        return $this->get('/payments/all', $filters);
    }

    // ─── PAYOUT (Mobile Money) ───────────────────────────────────────────

    /**
     * Preview a mobile-money payout.
     */
    public function previewPayout(array $data): array
    {
        // API expects phoneNumber as string with country code (e.g. 255712345678).
        $payload = [
            'amount' => (int) $data['amount'],
            'phoneNumber' => (string) $this->formatPhone($data['phoneNumber']),
            'currency' => $data['currency'] ?? 'TZS',
            'orderReference' => (string) $data['orderReference'],
        ];

        if ($this->checksumKey) {
            $payload['checksum'] = $this->createChecksum($payload);
        }

        return $this->post('/payouts/preview-mobile-money-payout', $payload);
    }

    /**
     * Create (execute) a mobile-money payout.
     */
    public function createPayout(array $data): array
    {
        $payload = [
            'amount' => (int) $data['amount'],
            'phoneNumber' => (string) $this->formatPhone($data['phoneNumber']),
            'currency' => $data['currency'] ?? 'TZS',
            'orderReference' => (string) $data['orderReference'],
        ];

        if ($this->checksumKey) {
            $payload['checksum'] = $this->createChecksum($payload);
        }

        return $this->post('/payouts/create-mobile-money-payout', $payload);
    }

    /**
     * Full payout flow: preview → create.
     */
    public function startPayout(array $data): array
    {
        $preview = $this->previewPayout($data);
        if (! $preview['ok']) {
            return $preview;
        }

        return $this->createPayout($data);
    }

    /**
     * Query a single payout by orderReference.
     */
    public function queryPayout(string $orderReference): array
    {
        return $this->get('/payouts/'.urlencode($orderReference));
    }

    /**
     * Query all payouts with optional filters.
     */
    public function queryAllPayouts(array $filters = []): array
    {
        return $this->get('/payouts/all', $filters);
    }

    // ─── WEBHOOK VERIFICATION ────────────────────────────────────────────

    /**
     * Verify an incoming webhook payload.
     */
    public function verifyWebhook(array $payload): bool
    {
        return $this->verifyChecksum($payload);
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────

    /**
     * Determine the final status of a payment.
     *   SUCCESS / SETTLED  → paid
     *   FAILED             → failed
     *   other              → pending
     */
    public static function resolvePaymentStatus(?string $status): string
    {
        $status = strtoupper($status ?? '');
        if (in_array($status, ['SUCCESS', 'SETTLED'])) {
            return 'COMPLETED';
        }
        if ($status === 'FAILED') {
            return 'FAILED';
        }

        return 'PENDING';
    }

    /**
     * Determine the final status of a payout.
     */
    public static function resolvePayoutStatus(?string $status): string
    {
        $status = strtoupper($status ?? '');
        if ($status === 'SUCCESS') {
            return 'COMPLETED';
        }
        if (in_array($status, ['FAILED', 'REFUNDED', 'REVERSED'])) {
            return 'FAILED';
        }

        return 'PENDING';
    }

    /**
     * Human-readable error from a failed post()/get() result (validation, 4xx, etc.).
     */
    public static function apiErrorMessage(array $result, string $fallback = 'Ombi imeshindikana.'): string
    {
        $json = $result['json'] ?? null;
        if (! is_array($json)) {
            return $fallback;
        }
        $msg = $json['message'] ?? null;
        if (is_string($msg) && $msg !== '') {
            return $msg;
        }
        if (isset($json['error']) && is_string($json['error']) && $json['error'] !== '') {
            return $json['error'];
        }
        if (isset($json['errors']) && is_array($json['errors'])) {
            $flat = [];
            foreach ($json['errors'] as $v) {
                if (is_string($v)) {
                    $flat[] = $v;
                } elseif (is_array($v)) {
                    foreach ($v as $vv) {
                        if (is_string($vv)) {
                            $flat[] = $vv;
                        }
                    }
                }
            }
            if ($flat !== []) {
                return implode(' ', $flat);
            }
        }

        return $fallback;
    }

    /**
     * Ensure phone is in 255xxxxxxxxx format.
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '255'.substr($phone, 1);
        }

        return $phone;
    }

    // ─── HTTP helpers ────────────────────────────────────────────────────

    private function post(string $path, array $payload): array
    {
        try {
            $token = $this->getToken();
            $resp = Http::withToken($token)
                ->timeout(30)
                ->post($this->baseUrl.$path, $payload);

            // Auto-refresh on 401
            if ($resp->status() === 401) {
                $token = $this->refreshToken();
                $resp = Http::withToken($token)
                    ->timeout(30)
                    ->post($this->baseUrl.$path, $payload);
            }

            Log::info('ClickPesa POST '.$path, [
                'status' => $resp->status(),
                'payload' => $payload,
                'body' => $resp->json(),
            ]);

            return [
                'ok' => $resp->successful(),
                'json' => $resp->json() ?? [],
                'status' => $resp->status(),
            ];
        } catch (\Exception $e) {
            Log::error('ClickPesa POST '.$path.' exception', ['error' => $e->getMessage()]);

            return ['ok' => false, 'json' => ['error' => $e->getMessage()], 'status' => 500];
        }
    }

    private function get(string $path, array $query = []): array
    {
        try {
            $token = $this->getToken();
            $resp = Http::withToken($token)
                ->timeout(30)
                ->get($this->baseUrl.$path, $query);

            if ($resp->status() === 401) {
                $token = $this->refreshToken();
                $resp = Http::withToken($token)
                    ->timeout(30)
                    ->get($this->baseUrl.$path, $query);
            }

            Log::info('ClickPesa GET '.$path, [
                'status' => $resp->status(),
                'query' => $query,
                'body' => $resp->json(),
            ]);

            return [
                'ok' => $resp->successful(),
                'json' => $resp->json() ?? [],
                'status' => $resp->status(),
            ];
        } catch (\Exception $e) {
            Log::error('ClickPesa GET '.$path.' exception', ['error' => $e->getMessage()]);

            return ['ok' => false, 'json' => ['error' => $e->getMessage()], 'status' => 500];
        }
    }
}
