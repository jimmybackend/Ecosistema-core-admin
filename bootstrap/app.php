<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/app.php';
$routes = require __DIR__ . '/../routes/web.php';

return [
    'config' => $config,
    'router' => static function (string $uri) use ($routes): void {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if (isset($routes[$path]) && is_callable($routes[$path])) {
            $routes[$path]();
            return;
        }

        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<h1>404</h1><p>Ruta no encontrada.</p>';
    },
];
