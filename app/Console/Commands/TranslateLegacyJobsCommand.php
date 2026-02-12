<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Services\TranslationService;
use Illuminate\Console\Command;

/**
 * One-time (or on-demand) backfill: translate legacy jobs that have title_sw/title_en
 * or legacy title but missing the other language. Uses Groq to fill history.
 */
class TranslateLegacyJobsCommand extends Command
{
    protected $signature = 'tendapoa:translate-legacy-jobs
                            {--dry-run : List jobs that would be updated without saving}
                            {--chunk=50 : Number of jobs per batch}
                            {--delay=100 : Milliseconds to wait between Groq calls (rate limit)}';

    protected $description = 'Backfill title_sw/title_en and description_sw/description_en for legacy jobs using Groq.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');
        $delayMs = (int) $this->option('delay');

        if ($dryRun) {
            $this->warn('Dry run — no changes will be saved.');
        }

        // Jobs missing at least one localized column (or have legacy title but empty _sw/_en)
        $query = Job::query()
            ->where(function ($q) {
                $q->whereNull('title_sw')
                    ->orWhere('title_sw', '')
                    ->orWhereNull('title_en')
                    ->orWhere('title_en', '');
            });

        $total = $query->count();
        if ($total === 0) {
            $this->info('No legacy jobs need translation.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} job(s) to process.");

        $updated = 0;
        $failed = 0;

        $query->orderBy('id')->chunk($chunkSize, function ($jobs) use ($dryRun, $delayMs, &$updated, &$failed) {
            foreach ($jobs as $job) {
                $sourceTitle = $this->getSourceTitle($job);
                $sourceDesc = $this->getSourceDescription($job);

                if ($sourceTitle === '' && $sourceDesc === '') {
                    continue;
                }

                try {
                    $localized = TranslationService::ensureBothLanguages($sourceTitle, $sourceDesc ?: null);

                    if ($dryRun) {
                        $this->line("  [DRY] Job #{$job->id}: title_sw=" . ($localized['title_sw'] ?? '') . " | title_en=" . ($localized['title_en'] ?? ''));
                        $updated++;
                        continue;
                    }

                    $legacyTitle = $job->getRawOriginal('title');
                    $legacyDesc = $job->getRawOriginal('description');
                    $job->title_sw = $localized['title_sw'] ?: $job->getRawOriginal('title_sw') ?: $legacyTitle;
                    $job->title_en = $localized['title_en'] ?: $job->getRawOriginal('title_en') ?: $legacyTitle;
                    $job->description_sw = $localized['description_sw'] ?? $job->getRawOriginal('description_sw') ?? $legacyDesc;
                    $job->description_en = $localized['description_en'] ?? $job->getRawOriginal('description_en') ?? $legacyDesc;
                    $job->saveQuietly();

                    $updated++;
                    $this->line("  OK Job #{$job->id}");
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error("  FAIL Job #{$job->id}: " . $e->getMessage());
                }

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }
            }
        });

        $this->newLine();
        $this->info("Done. Updated: {$updated}, Failed: {$failed}.");
        return self::SUCCESS;
    }

    private function getSourceTitle(Job $job): string
    {
        $raw = $job->getRawOriginal('title');
        $sw = $job->getRawOriginal('title_sw');
        $en = $job->getRawOriginal('title_en');
        return trim((string) ($sw ?: $en ?: $raw ?: ''));
    }

    private function getSourceDescription(Job $job): ?string
    {
        $raw = $job->getRawOriginal('description');
        $sw = $job->getRawOriginal('description_sw');
        $en = $job->getRawOriginal('description_en');
        $v = $sw ?: $en ?: $raw;
        return $v !== null && (string) $v !== '' ? (string) $v : null;
    }
}
