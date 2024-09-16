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

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'infobip' => [
        'api_key' => env('INFOBIP_API_KEY'),
        'base_url' => env('INFOBIP_BASE_URL'),
        'from' => env('INFOBIP_FROM'),
    ],
    'firebase' => [
        // 'credentials' => env('FIREBASE_CREDENTIALS'),
        'credentials' => base_path('config/gestion-dette-laravel-2-firebase-adminsdk-6mz1n-7ba959275a.json'),
        'database_url' => env('FIREBASE_DATABASE_URL'), // Ajoute cette ligne pour spécifier l'URL correcte de la base de données
    ],



];
