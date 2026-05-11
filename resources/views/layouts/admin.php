<?php

declare(strict_types=1);

$title = $title ?? 'Ecosistema Core Admin';
$contentView = $contentView ?? 'pages/home';
$contentData = $contentData ?? [];
$auth = $auth ?? [];
$csrfToken = $csrfToken ?? '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="/assets/css/ecosistema-ui.css">
</head>
<body>
  <div class="eco-layout">
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="eco-main">
      <?php extract($contentData, EXTR_SKIP); ?>
      <?php include __DIR__ . '/../' . $contentView . '.php'; ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
  </div>
</body>
</html>
