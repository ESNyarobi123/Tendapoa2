<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Automatic localization: detect language of user input, translate to the other,
 * and return both title_sw, title_en, description_sw, description_en.
 * - User writes Swahili → store in _sw, translate to _en.
 * - User writes English → store in _en, translate to _sw.
 */
class TranslationService
{
    public const LANG_SW = 'sw';
    public const LANG_EN = 'en';

    /**
     * Detect if text is primarily Swahili or English (simple heuristic).
     */
    public static function detectLanguage(string $text): string
    {
        if (trim($text) === '') {
            return self::LANG_SW;
        }

        $text = mb_strtolower($text);
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            return self::LANG_SW;
        }

        // Common Swahili words / patterns (ngeli, viambishi, kazi, etc.)
        $swahiliIndicators = [
            'na', 'ya', 'wa', 'ka', 'ki', 'cha', 'ni', 'kwa', 'hii', 'hiyo', 'ile',
            'kama', 'lakini', 'pia', 'sana', 'tu', 'bado', 'tayari', 'hapana', 'ndiyo',
            'kufua', 'kusafisha', 'kazi', 'huduma', 'nyumbani', 'ofisi', 'usafi',
            'tafadhali', 'asante', 'karibu', 'habari', 'sasa', 'baadaye', 'leo',
            // Job/title vocabulary so "Fundi bomba anahitajika" etc. detected as Swahili
            'fundi', 'bomba', 'anahitajika', 'unahitajika', 'hitajika', 'tafuta', 'nahitaji',
            'seremala', 'umeme', 'maji', 'jikoni', 'nyumba', 'kuta', 'rangi', 'bustani',
            'gari', 'simu', 'kompyuta', 'ufundi', 'mwenyeji',
        ];
        $swCount = 0;
        foreach ($words as $w) {
            // PCRE2: use \x{...} with /u modifier (not \u)
            $w = preg_replace('/[^a-z\x{0080}-\x{FFFF}]/u', '', $w);
            if (in_array($w, $swahiliIndicators, true)) {
                $swCount++;
            }
        }

        // If we have a few Swahili indicators, treat as Swahili
        if ($swCount >= 1) {
            return self::LANG_SW;
        }

        // Default: assume English for generic/short text
        return self::LANG_EN;
    }

    /**
     * Translate text from one language to another.
     * Returns translated string or original on failure.
     */
    public static function translate(string $text, string $fromLang, string $toLang): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        if ($fromLang === $toLang) {
            return $text;
        }

        $driver = config('services.translation.driver', 'null');
        $result = $text;

        try {
            if ($driver === 'groq') {
                $result = self::translateViaGroq($text, $fromLang, $toLang);
            } elseif ($driver === 'openai') {
                $result = self::translateViaOpenAi($text, $fromLang, $toLang);
            } elseif ($driver === 'google') {
                $result = self::translateViaGoogle($text, $fromLang, $toLang);
            }
            if ($result !== $text) {
                Log::info('TranslationService: translated', [
                    'driver' => $driver,
                    'from' => $fromLang,
                    'to' => $toLang,
                    'original_preview' => mb_substr($text, 0, 60) . (mb_strlen($text) > 60 ? '...' : ''),
                    'translated_preview' => mb_substr($result, 0, 60) . (mb_strlen($result) > 60 ? '...' : ''),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Translation failed, using original text', [
                'driver' => $driver,
                'from' => $fromLang,
                'to' => $toLang,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Given user-submitted title and description, return all four localized fields.
     * User can write in either Swahili or English; we detect and fill both.
     */
    public static function ensureBothLanguages(?string $title, ?string $description = null): array
    {
        $title = $title ?? '';
        $description = $description ?? '';

        $detectFrom = $title !== '' ? $title : $description;
        $lang = self::detectLanguage($detectFrom);

        Log::info('TranslationService: ensureBothLanguages', [
            'input_title' => $title,
            'input_description_length' => mb_strlen($description),
            'detected_language' => $lang,
        ]);

        if ($lang === self::LANG_SW) {
            $title_sw = $title;
            $title_en = self::translate($title, self::LANG_SW, self::LANG_EN);
            $description_sw = $description;
            $description_en = $description !== '' ? self::translate($description, self::LANG_SW, self::LANG_EN) : '';
        } else {
            $title_en = $title;
            $title_sw = self::translate($title, self::LANG_EN, self::LANG_SW);
            $description_en = $description;
            $description_sw = $description !== '' ? self::translate($description, self::LANG_EN, self::LANG_SW) : '';
        }

        $result = [
            'title_sw' => $title_sw,
            'title_en' => $title_en,
            'description_sw' => $description_sw ?: null,
            'description_en' => $description_en ?: null,
        ];

        Log::info('TranslationService: localized result', [
            'title_sw' => $result['title_sw'],
            'title_en' => $result['title_en'],
            'description_sw_length' => $result['description_sw'] ? mb_strlen($result['description_sw']) : 0,
            'description_en_length' => $result['description_en'] ? mb_strlen($result['description_en']) : 0,
        ]);

        return $result;
    }

    /**
     * Groq (LPU) – OpenAI-compatible API, very fast for Swahili <-> English.
     * Flow: Save user text → send to Groq → save translation to other column.
     */
    protected static function translateViaGroq(string $text, string $from, string $to): string
    {
        $key = config('services.groq.api_key');
        if (!$key) {
            return $text;
        }

        $fromName = $from === self::LANG_SW ? 'Swahili' : 'English';
        $toName = $to === self::LANG_EN ? 'English' : 'Swahili';

        $baseUrl = rtrim(config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        $model = config('services.groq.model', 'llama-3.1-8b-instant');

        $prompt = "Translate the following text from {$fromName} to {$toName}. "
            . "Output ONLY the {$toName} translation, nothing else (no explanation, no quotes).\n\nText:\n{$text}";

        $response = Http::withToken($key)
            ->timeout(10)
            ->baseUrl($baseUrl)
            ->post('/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 500,
                'temperature' => 0.2,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Groq API error: ' . $response->body());
        }

        $result = $response->json('choices.0.message.content');
        return trim((string) ($result ?? $text));
    }

    protected static function translateViaOpenAi(string $text, string $from, string $to): string
    {
        $key = config('services.openai.api_key');
        if (!$key) {
            return $text;
        }

        $fromName = $from === self::LANG_SW ? 'Swahili' : 'English';
        $toName = $to === self::LANG_EN ? 'English' : 'Swahili';
        $prompt = "Translate the following text from {$fromName} to {$toName}. Output ONLY the {$toName} translation, nothing else.\n\nText:\n{$text}";

        $response = Http::withToken($key)
            ->timeout(15)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.translation_model', 'gpt-3.5-turbo'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 500,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        $result = $response->json('choices.0.message.content');
        return trim($result ?? $text);
    }

    protected static function translateViaGoogle(string $text, string $from, string $to): string
    {
        $key = config('services.google.translate_api_key') ?? config('services.google.translate_api_key');
        if (!$key) {
            return $text;
        }

        $response = Http::timeout(10)->get('https://translation.googleapis.com/language/translate/v2', [
            'key' => $key,
            'q' => $text,
            'source' => $from,
            'target' => $to,
            'format' => 'text',
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Google Translate API error: ' . $response->body());
        }

        $translated = $response->json('data.translations.0.translatedText');
        return $translated ?? $text;
    }
}
