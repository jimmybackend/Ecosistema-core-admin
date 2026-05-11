<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Http\View\View;

return [
    'GET /' => static function (array $config): void {
        header('Content-Type: text/html; charset=UTF-8');

        View::render('layouts.admin', [
            'title' => (string) ($config['app']['name'] ?? 'Ecosistema Core Admin'),
            'contentView' => 'pages/home',
            'contentData' => [],
        ]);
    },

    'GET /login' => static function (): void {
        header('Content-Type: text/html; charset=UTF-8');

        View::render('layouts.auth', [
            'title' => 'Login visual | Ecosistema Core Admin',
            'contentView' => 'pages/auth/login',
            'contentData' => [],
        ]);
    },

    'POST /login' => static function (): void {
        header('Content-Type: text/html; charset=UTF-8');
        http_response_code(501);

        View::render('layouts.auth', [
            'title' => 'Login visual | Ecosistema Core Admin',
            'contentView' => 'pages/auth/login',
            'contentData' => [
                'statusMessage' => 'Autenticación no implementada aún. Este formulario es visual y se conectará con core_users en el siguiente PR.',
            ],
        ]);
    },

    'GET /health/db' => static function (array $config): void {
        header('Content-Type: text/html; charset=UTF-8');

        try {
            PdoFactory::make($config['database']);
            http_response_code(200);
            echo '<h1>OK</h1><p>Conexión PDO disponible.</p>';
        } catch (\Throwable) {
            http_response_code(500);
            echo '<h1>ERROR</h1><p>No fue posible conectar a la base de datos.</p>';
        }
    },
];
