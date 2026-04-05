<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickPesaService
{
    private string $baseUrl;

    private string $clientId;

    private string $apiKey;

    private ?string $checksumKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.clickpesa.base_url', 'https://api.clickpesa.com/third-parties'), '/');
        $this->clientId = config('services.clickpesa.client_id', '');
        $this->apiKey = config('services.clickpesa.api_key', '');
        $this->checksumKey = config('services.clickpesa.checksum_key') ?: null;
    }

    // ─── TOKEN ───────────────────────────────────────────────────────────

    /**
     * Generate or retrieve a cached Bearer token (valid ~1 hour).
     */
    public function getToken(): string
    {
        return Cache::remember('clickpesa_token', 3500, function () {
            $resp = Http::withHeaders([
                'client-id' => $this->clientId,
                'api-key' => $this->apiKey,
            ])->post($this->baseUrl.'/generate-token');

            if ($resp->successful() && $resp->json('token')) {
                // ClickPesa returns "Bearer eyJ..." — strip the prefix so
                // Http::withToken() can add it cleanly.
                $raw = $resp->json('token');

                return str_starts_with($raw, 'Bearer ')
                    ? substr($raw, 7)
                    : $raw;
            }

            Log::error('ClickPesa token generation failed', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);

            throw new \RuntimeException('ClickPesa: imeshindikana kupata token — '.$resp->body());
        });
    }

    /**
     * Force a fresh token (e.g. after a 401).
     */
    public function refreshToken(): string
    {
        Cache::forget('clickpesa_token');

        return $this->getToken();
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
