<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;

return [
    '/' => static function (array $config): void {
        header('Content-Type: text/html; charset=UTF-8');
        $appName = htmlspecialchars((string) ($config['app']['name'] ?? 'Ecosistema Core Admin'), ENT_QUOTES, 'UTF-8');

        echo <<<HTML
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$appName}</title>
  <link rel="stylesheet" href="/assets/css/ecosistema-ui.css">
</head>
<body class="ec-main">
  <main class="ec-container" style="padding: 2rem;">
    <h1>{$appName}</h1>
    <p>Estructura base inicial lista (Capa 2).</p>
  </main>
</body>
</html>
HTML;
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
