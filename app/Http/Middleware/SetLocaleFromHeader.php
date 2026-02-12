<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set app locale from Accept-Language header so API returns
 * title/description (and static labels) in the requested language.
 * Supported: en, sw. Default: en.
 */
class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', 'en');

        // Accept "en", "en-US", "sw", "sw-TZ" etc.
        if (preg_match('/^sw\b/i', $locale)) {
            $locale = 'sw';
        } elseif (preg_match('/^en\b/i', $locale)) {
            $locale = 'en';
        } else {
            $locale = config('app.fallback_locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
