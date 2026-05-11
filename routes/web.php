<?php

declare(strict_types=1);

return [
    '/' => static function (): void {
        header('Content-Type: text/html; charset=UTF-8');
        echo <<<'HTML'
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ecosistema Core Admin</title>
  <link rel="stylesheet" href="/assets/css/ecosistema-ui.css">
</head>
<body class="ec-main">
  <main class="ec-container" style="padding: 2rem;">
    <h1>Ecosistema Core Admin</h1>
    <p>Estructura base inicial lista (Capa 2).</p>
  </main>
</body>
</html>
HTML;
    },
];
