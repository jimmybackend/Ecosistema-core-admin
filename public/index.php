<?php

declare(strict_types=1);

/**
 * Front Controller mínimo para Core Admin.
 * Capa 3: Configuración de entorno y conexión PDO segura.
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

/** @var callable $router */
$router = $app['router'];

$router($_SERVER['REQUEST_URI'] ?? '/');
