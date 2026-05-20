<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\PdoFactory;
use App\Support\Env;
use App\Http\Response\ErrorResponder;

require_once __DIR__ . '/../app/Support/Env.php';
require_once __DIR__ . '/../app/Support/helpers.php';

Env::load(__DIR__ . '/../.env');

$config = [
    'app' => require __DIR__ . '/../config/app.php',
    'database' => require __DIR__ . '/../config/database.php',
    'mail' => require __DIR__ . '/../config/mail.php',
    'cloud' => require __DIR__ . '/../config/cloud.php',
];

$routes = require __DIR__ . '/../routes/web.php';

return [
    'config' => $config,
    'db' => static fn () => PdoFactory::make($config['database']),
    'router' => static function (string $uri, string $method) use ($routes, $config): void {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $routeKey = strtoupper($method) . ' ' . $path;

        if (isset($routes[$routeKey]) && is_callable($routes[$routeKey])) {
            $routes[$routeKey]($config, []);
            return;
        }

        foreach ($routes as $key => $handler) {
            if (!is_callable($handler)) {
                continue;
            }

            [$routeMethod, $routePath] = explode(' ', $key, 2);
            if ($routeMethod !== strtoupper($method)) {
                continue;
            }

            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
            if (!is_string($pattern)) {
                continue;
            }

            if (preg_match('#^' . $pattern . '$#', $path, $matches) === 1) {
                $params = array_filter($matches, static fn ($k): bool => is_string($k), ARRAY_FILTER_USE_KEY);
                $handler($config, $params);
                return;
            }
        }

        ErrorResponder::render($config, 404);
    },
];
