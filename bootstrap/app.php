<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Support\Env;

require_once __DIR__ . '/../app/Support/Env.php';
require_once __DIR__ . '/../app/Support/helpers.php';

Env::load(__DIR__ . '/../.env');

$config = [
    'app' => require __DIR__ . '/../config/app.php',
    'database' => require __DIR__ . '/../config/database.php',
];

$routes = require __DIR__ . '/../routes/web.php';

return [
    'config' => $config,
    'db' => static fn () => PdoFactory::make($config['database']),
    'router' => static function (string $uri) use ($routes, $config): void {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if (isset($routes[$path]) && is_callable($routes[$path])) {
            $routes[$path]($config);
            return;
        }

        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<h1>404</h1><p>Ruta no encontrada.</p>';
    },
];
