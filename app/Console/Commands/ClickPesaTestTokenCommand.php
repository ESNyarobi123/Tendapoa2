<?php

namespace App\Console\Commands;

use App\Services\ClickPesaService;
use Illuminate\Console\Command;

/**
 * Verify ClickPesa generate-token from the server (no secrets printed).
 */
class ClickPesaTestTokenCommand extends Command
{
    protected $signature = 'clickpesa:test-token {--fresh : Clear cached token first}';

    protected $description = 'Test ClickPesa authentication (works without MySQL if cache read fails; use CACHE_STORE=file locally to cache tokens)';

    public function handle(ClickPesaService $clickpesa): int
    {
        $base = rtrim((string) config('services.clickpesa.base_url'), '/');
        $id = trim((string) config('services.clickpesa.client_id', ''));
        $key = trim((string) config('services.clickpesa.api_key', ''));

        $this->line('base_url: '.$base);
        $this->line('client_id length: '.strlen($id));
        $this->line('api_key length: '.strlen($key));

        if ($id === '' || $key === '') {
            $this->error('CLICKPESA_CLIENT_ID au CLICKPESA_API_KEY ni tupu kwenye config. Tumia .env na php artisan config:clear.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $clickpesa->forgetTokenCache();
            $this->comment('Token cache clear requested (no error if cache store e.g. database is unavailable).');
        }

        try {
            $token = $clickpesa->getToken();
            $this->info('OK: token ipatikana (urefu wa JWT: '.strlen($token).').');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
