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

    // Deploy hook de Vercel: dispara el rebuild del sitio (botón Publicar, modo hook)
    'prismic' => [
        'deploy_hook_url' => env('DEPLOY_HOOK_URL'),
    ],

    // Publicación del sitio (botón "Publicar" del panel)
    'astro' => [
        // 'local' → Laravel ejecuta el build de Astro en esta máquina.
        // 'hook'  → dispara el deploy hook de Vercel (producción).
        'publish_mode'  => env('PUBLISH_MODE', 'hook'),
        // Ruta al proyecto frontend (Astro). Por defecto, la carpeta hermana del monorepo.
        'frontend_path' => env('FRONTEND_PATH', base_path('../blog-frontend')),
        // Comando que compila el sitio.
        'build_command' => env('FRONTEND_BUILD_COMMAND', 'npm run build'),
    ],

];
