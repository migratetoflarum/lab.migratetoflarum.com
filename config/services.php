<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'github' => [
        'api_access_token' => env('GITHUB_API_ACCESS_TOKEN'),
    ],

    'matomo-lab' => [
        'url' => env('MATOMO_LAB_URL', env('MATOMO_URL')),
        'site_id' => env('MATOMO_LAB_SITE_ID'),
    ],

    'matomo-showcase' => [
        'url' => env('MATOMO_SHOWCASE_URL', env('MATOMO_URL')),
        'site_id' => env('MATOMO_SHOWCASE_SITE_ID'),
    ],

];
