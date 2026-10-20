<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'ap-south-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | NestJS Backend API
    |--------------------------------------------------------------------------
    | Configuration for the NestJS backend service that handles
    | core business logic, student/teacher data, etc.
    |--------------------------------------------------------------------------
    */
    'nestjs' => [
        'base_url' => env('NESTJS_API_BASE_URL', 'http://localhost:3000/api'),
        'api_key' => env('NESTJS_API_KEY', ''),
        'timeout' => (int) env('NESTJS_API_TIMEOUT', 30),
    ],

    /*
    | Encryption key for global credentials saved on Admin → Integrations.
    | Must be the SAME base64 32-byte value in the NestJS API .env.
    | Generate: php -r "echo base64_encode(random_bytes(32));"
    */
    'platform_settings' => [
        'key' => env('SETTINGS_ENCRYPTION_KEY'),
    ],

];
