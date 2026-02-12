<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation (Localization on Write)
    |--------------------------------------------------------------------------
    | driver: null | groq | openai | google
    | Groq (LPU) = very fast, recommended for Swahili <-> English.
    */
    'translation' => [
        'driver' => env('TRANSLATION_DRIVER', 'groq'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_TRANSLATION_MODEL', 'llama-3.1-8b-instant'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'translation_model' => env('OPENAI_TRANSLATION_MODEL', 'gpt-3.5-turbo'),
    ],

    'google' => [
        'translate_api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
    ],

];
