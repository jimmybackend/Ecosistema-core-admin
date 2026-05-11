<?php

declare(strict_types=1);

/**
 * Front Controller mínimo para Core Admin.
 * Capa 2: Estructura base del proyecto.
 */

$app = require __DIR__ . '/../bootstrap/app.php';

/** @var callable $router */
$router = $app['router'];

$router($_SERVER['REQUEST_URI'] ?? '/');
