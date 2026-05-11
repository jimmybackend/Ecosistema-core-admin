<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'Ecosistema Core Admin'),
    'env' => Env::get('APP_ENV', 'local'),
    'debug' => filter_var(Env::get('APP_DEBUG', true), FILTER_VALIDATE_BOOL),
    'url' => Env::get('APP_URL', 'http://127.0.0.1:8000'),
    'layer' => 'Capa 3 — Configuración de entorno y conexión PDO segura',
    'session' => [
        'name' => Env::get('SESSION_NAME', 'ecosistema_core_admin'),
        'secure' => filter_var(Env::get('SESSION_SECURE', false), FILTER_VALIDATE_BOOL),
    ],
];
