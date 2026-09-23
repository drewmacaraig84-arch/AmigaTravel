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

    'sendgrid' => [
        'key' => env('SENDGRID_API_KEY'),
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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'firebase' => [
        'credentials_json' => env('FIREBASE_CREDENTIALS_JSON'),
    ],

    'app_updates' => [
        'force_update' => env('APP_FORCE_UPDATE', false),
        'play_store_url' => env('PLAY_STORE_URL', 'https://play.google.com/store/apps/details?id=com.amiga.travel.flutter_app'),
        'app_store_url' => env('APP_STORE_URL', 'https://apps.apple.com/app/amiga-gracia/id6470000000'),
        'app_gallery_url' => env('APP_GALLERY_URL', 'https://appgallery.huawei.com/app/C118908953'),
    ],

];
