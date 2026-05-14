<?php

declare(strict_types=1);

/** @var array<string,mixed> $result */
$result = $result ?? [];
$page = is_array($result['page'] ?? null) ? $result['page'] : [];
$blocks = is_array($result['blocks'] ?? null) ? $result['blocks'] : [];
$title = trim((string) ($page['seo_title'] ?? '')) !== '' ? (string) $page['seo_title'] : (string) ($page['title'] ?? 'Landing');
$description = (string) ($page['seo_description'] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <?php if ($description !== ''): ?><meta name="description" content="<?= htmlspecialchars($description) ?>"><?php endif; ?>
</head>
<body>
<main>
  <h1><?= htmlspecialchars((string) ($page['title'] ?? 'Landing')) ?></h1>
  <?php if (trim((string) ($page['description'] ?? '')) !== ''): ?><p><?= htmlspecialchars((string) $page['description']) ?></p><?php endif; ?>
  <section>
    <h2>Contenido publicado</h2>
    <?php if ($blocks === []): ?>
      <p>Esta landing no tiene bloques públicos activos.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($blocks as $block): ?>
          <li><?= htmlspecialchars((string) ($block['name'] ?? 'Bloque')) ?> (<?= htmlspecialchars((string) ($block['block_type'] ?? 'block')) ?>)</li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
