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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'telegram' => [
        'bot_token' => env('BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME', 'jzubot'),
    ],

    'paytabs' => [
        'server_key' => env('PAYTABS_SERVER_KEY'),
        'client_key' => env('PAYTABS_CLIENT_KEY'),
        'profile_id' => env('PAYTABS_PROFILE_ID'),
        'base_url' => env('PAYTABS_BASE_URL', 'https://secure-iraq.paytabs.com'),
        'currency' => env('PAYTABS_CURRENCY', 'IQD'),
        'country' => env('PAYTABS_COUNTRY', 'IQ'),
    ],

];
