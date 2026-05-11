<?php

declare(strict_types=1);

$title = $title ?? 'Ecosistema Core Admin';
$contentView = $contentView ?? 'pages/auth/login';
$contentData = $contentData ?? [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="/assets/css/ecosistema-ui.css">
</head>
<body class="eco-auth-body">
  <main class="eco-auth-main" style="min-height: 100vh; display: grid; place-items: center; padding: 1rem;">
    <section class="eco-card" style="width: 100%; max-width: 440px;">
      <header style="text-align: center; margin-bottom: 1.25rem;">
        <h1 style="margin-bottom: .4rem;">Ecosistema Core Admin</h1>
        <p style="margin: 0; opacity: .8;">Acceso administrativo</p>
      </header>

      <?php extract($contentData, EXTR_SKIP); ?>
      <?php include __DIR__ . '/../' . $contentView . '.php'; ?>
    </section>
  </main>
</body>
</html>
