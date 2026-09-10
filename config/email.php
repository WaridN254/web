<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sender Identity
    |--------------------------------------------------------------------------
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'no-reply@halis.com'),
        'name' => env('MAIL_FROM_NAME', 'HALIS'),
    ],

    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS', 'support@halis.com'),
        'name' => env('MAIL_REPLY_TO_NAME', 'HALIS Support'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Expiration (minutes)
    |--------------------------------------------------------------------------
    */
    'activation_expiration' => (int) env('ACCOUNT_ACTIVATION_EXPIRATION', 1440),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (seconds)
    |--------------------------------------------------------------------------
    */
    'registration_rate_limit' => (int) env('MAIL_REGISTRATION_RATE_LIMIT', 60),
    'resend_rate_limit' => (int) env('MAIL_RESEND_RATE_LIMIT', 120),

];
