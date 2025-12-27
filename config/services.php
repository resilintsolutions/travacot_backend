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

    'hotelbeds' => [
        'api_key'       => env('HOTELBEDS_API_KEY'),
        'secret'        => env('HOTELBEDS_SECRET'),
        'base_url'      => env('HOTELBEDS_BASE', 'https://api.test.hotelbeds.com'),
        'list_endpoint' => env('HOTELBEDS_LIST_ENDPOINT', '/hotel-api/1.0/hotels'),
        'content_base_uri' => env('HOTELBEDS_CONTENT_BASE_URI', 'https://api.test.hotelbeds.com/hotel-content-api/1.0'),
        'cache'        => env('HOTELBEDS_CACHE_ENDPOINT', '/hotel-cache-api/1.0'),
            'default_destination' => env('HOTELBEDS_DEFAULT_DESTINATION', null),
    'tailored_hotels' => array_filter(explode(',', env('HOTELBEDS_TAILORED_HOTELS', ''))),
    // GEO config (all REQUIRED to use geolocation)
    'geo_lat' => env('HOTELBEDS_GEO_LAT', null),
    'geo_lon' => env('HOTELBEDS_GEO_LON', null),
    'geo_radius' => env('HOTELBEDS_GEO_RADIUS', null),
    'geo_unit' => env('HOTELBEDS_GEO_UNIT', 'KM'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URL'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
    ],

    'stripe' => [
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'), // will be used later for webhooks
    ],

];
