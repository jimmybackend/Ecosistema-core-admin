<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Http\View\View;

return [
    '/' => static function (array $config): void {
        header('Content-Type: text/html; charset=UTF-8');

        View::render('layouts.admin', [
            'title' => (string) ($config['app']['name'] ?? 'Ecosistema Core Admin'),
            'contentView' => 'pages/home',
            'contentData' => [],
        ]);
    },

    '/health/db' => static function (array $config): void {
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
