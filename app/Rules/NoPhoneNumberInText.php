<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Blocks Tanzania mobile numbers embedded in free-text fields
 * (07…, 06…, 7…/6… without leading 0, 255…, +255…, with optional spaces or hyphens).
 */
class NoPhoneNumberInText implements ValidationRule
{
    public const MESSAGE = 'Usiweke nambari ya simu kwenye kichwa au maelezo. Tumia mazungumzo ya ndani ya mfumo.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        if (self::containsPhoneNumber($value)) {
            $fail(self::MESSAGE);
        }
    }

    public static function containsPhoneNumber(string $text): bool
    {
        if (trim($text) === '') {
            return false;
        }

        $digitsOnly = preg_replace('/\D/u', '', $text) ?? '';

        if ($digitsOnly === '') {
            return false;
        }

        if (preg_match('/0[67]\d{8}/', $digitsOnly)) {
            return true;
        }

        if (preg_match('/255[67]\d{8}/', $digitsOnly)) {
            return true;
        }

        // Local mobile without leading 0 (e.g. 750599412, 650599412)
        if (preg_match('/(?<!\d)[67]\d{8}(?!\d)/', $digitsOnly)) {
            return true;
        }

        // Partial / shortened numbers (e.g. 0786432) — at least 7 digits starting 06/07 or 2556/2557
        if (preg_match('/(?<!\d)0[67]\d{5,9}(?!\d)/', $digitsOnly)) {
            return true;
        }

        if (preg_match('/(?<!\d)255[67]\d{5,9}(?!\d)/', $digitsOnly)) {
            return true;
        }

        // Partial without leading 0 (e.g. 7505994)
        if (preg_match('/(?<!\d)[67]\d{5,7}(?!\d)/', $digitsOnly)) {
            return true;
        }

        // Digits with spaces or hyphens in the original text
        if (preg_match('/(?<!\d)0[\s\-\.]?[67](?:[\s\-\.]?\d){5,11}/u', $text)) {
            return true;
        }

        return (bool) preg_match('/(?:\+|00)?[\s]*255[\s\-\.]?[67](?:[\s\-\.]?\d){5,11}/u', $text)
            || (bool) preg_match('/(?<!\d)[67](?:[\s\-\.]?\d){5,11}(?!\d)/u', $text);
    }
}
