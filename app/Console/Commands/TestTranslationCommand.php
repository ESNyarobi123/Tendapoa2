<?php

namespace App\Console\Commands;

use App\Services\TranslationService;
use Illuminate\Console\Command;

/**
 * Test backend translations without posting a real job.
 * Usage:
 *   php artisan translation:test "Fundi bomba anahitajika"
 *   php artisan translation:test "Plumber needed" "Water leak in kitchen"
 */
class TestTranslationCommand extends Command
{
    protected $signature = 'translation:test
                            {title : Job title (Swahili or English)}
                            {description? : Optional job description}';

    protected $description = 'Test TranslationService: detect language and get title_sw, title_en, description_sw, description_en.';

    public function handle(): int
    {
        $title = $this->argument('title');
        $description = (string) $this->argument('description');

        $this->info('Input:');
        $this->line('  title: ' . $title);
        $this->line('  description: ' . ($description ?: '(empty)'));

        $detected = TranslationService::detectLanguage($title ?: $description);
        $this->newLine();
        $this->info('Detected language: ' . $detected);

        $localized = TranslationService::ensureBothLanguages($title, $description ?: null);

        $this->newLine();
        $this->info('Localized result:');
        $this->table(
            ['Field', 'Value'],
            [
                ['title_sw', $localized['title_sw'] ?? ''],
                ['title_en', $localized['title_en'] ?? ''],
                ['description_sw', $localized['description_sw'] ? mb_substr($localized['description_sw'], 0, 80) . (mb_strlen($localized['description_sw']) > 80 ? '...' : '') : '(null)'],
                ['description_en', $localized['description_en'] ? mb_substr($localized['description_en'], 0, 80) . (mb_strlen($localized['description_en']) > 80 ? '...' : '') : '(null)'],
            ]
        );

        $this->newLine();
        $this->comment('Check storage/logs/laravel.log for TranslationService log entries (ensureBothLanguages, translated).');

        return self::SUCCESS;
    }
}
