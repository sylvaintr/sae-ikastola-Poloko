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

    'recaptcha' => [
        // Clés de test Google reCAPTCHA pour le développement local
        // Ces clés fonctionnent toujours et acceptent n'importe quelle réponse
        // Note: Ces clés de test sont publiques et documentées par Google, leur présence dans le code est acceptable
        'test_site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
        'test_secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
        'site_key' => env('RECAPTCHA_SITE_KEY') ?: ((env('APP_ENV') === 'local') ? '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' : null),
        'secret_key' => env('RECAPTCHA_SECRET_KEY') ?: ((env('APP_ENV') === 'local') ? '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe' : null),
        'enabled' => env('RECAPTCHA_ENABLED', true),
    ],

];
