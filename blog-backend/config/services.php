<?php

return [


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

    // Correo del administrador que recibe los mensajes de contacto
    'contact' => [
        'admin_email' => env('MAIL_ADMIN_TO', 'admin@zooblog.com'),
    ],

    // Token para consultar el panel de mensajes (endpoint protegido)
    'admin' => [
        'token' => env('ADMIN_API_TOKEN'),
    ],

    // Integración con Prismic CMS
    'prismic' => [
        'repo'           => env('PRISMIC_REPO', 'zooblog'),
        'webhook_secret' => env('PRISMIC_WEBHOOK_SECRET'),
        // Deploy hook de Vercel/Netlify: dispara el rebuild del sitio estático
        'deploy_hook_url' => env('DEPLOY_HOOK_URL'),
    ],

];
