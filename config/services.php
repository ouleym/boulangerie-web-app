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

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION', null),
        'timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),
    ],

    // Configuration CinetPay
    'cinetpay' => [
        'api_key' => env('CINETPAY_API_KEY'),
        'secret_key' => env('CINETPAY_SECRET_KEY'),
        'site_id' => env('CINETPAY_SITE_ID'),
        'base_url' => env('CINETPAY_BASE_URL', 'https://api-checkout.cinetpay.com'),
        'mode' => env('CINETPAY_MODE', 'test'),
        'notify_url' => env('CINETPAY_NOTIFY_URL'),
        'return_url' => env('CINETPAY_RETURN_URL'),
        'cancel_url' => env('CINETPAY_CANCEL_URL'),
    ],

];
