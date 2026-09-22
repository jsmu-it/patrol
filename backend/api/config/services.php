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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'fcm' => [
        // Path absolut atau relatif ke file service account JSON
        'credentials' => env('FIREBASE_CREDENTIALS'),
        // ID project Firebase (bisa dilihat di service account JSON: project_id)
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ],

    'collabora' => [
        // Dipanggil peramban pengguna, jadi memakai alamat publik.
        'url'       => env('COLLABORA_URL', 'https://jsmu.co.id'),
        // Dipanggil dari dalam server, cukup lewat jaringan docker.
        'discovery' => env('COLLABORA_DISCOVERY', 'http://collabora:9980/hosting/discovery'),
    ],

    'livekit' => [
        'api_key' => env('LIVEKIT_API_KEY'),
        'api_secret' => env('LIVEKIT_API_SECRET'),
        'url' => env('LIVEKIT_URL', 'ws://127.0.0.1:7880'),
    ],

];
