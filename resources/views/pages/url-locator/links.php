<?php
$summary = (array)($contentData['summary'] ?? []);
$links = (array)($contentData['links'] ?? []);
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<div class="eco-card">
  <h1>URL Locator</h1>
  <div class="eco-alert eco-alert--warning">Listado administrativo read-only de short links.</div>
  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><div class="eco-alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <p><a class="eco-button btn" href="/url/locator">Resumen URL Locator</a> <a class="eco-button btn" href="/url/locator/links/new">Nuevo link</a></p>
  <h2>Resumen por status</h2>
  <ul><?php foreach ((array)($summary['by_status'] ?? []) as $item): ?><li><?= e((string)($item['status'] ?? 'sin-status')) ?>: <?= e((string)($item['total'] ?? 0)) ?></li><?php endforeach; ?></ul>
  <h2>Resumen por smart type</h2>
  <ul><?php foreach ((array)($summary['by_smart_type'] ?? []) as $item): ?><li><?= e((string)($item['smart_type'] ?? 'sin-smart-type')) ?>: <?= e((string)($item['total'] ?? 0)) ?></li><?php endforeach; ?></ul>
  <table class="eco-table"><thead><tr><th>id</th><th>slug</th><th>title</th><th>status</th><th>acciones</th></tr></thead><tbody>
    <?php foreach ($links as $link): ?>
      <tr><td><?= e((string)($link['id'] ?? '')) ?></td><td><?= e((string)($link['slug'] ?? '')) ?></td><td><?= e((string)($link['title'] ?? '')) ?></td><td><?= e((string)($link['status'] ?? '')) ?></td><td><a href="/url/locator/links/<?= e((string)($link['id'] ?? '0')) ?>">Ver detalle</a> | <a href="/url/locator/links/<?= e((string)($link['id'] ?? '0')) ?>/edit">Editar</a></td></tr>
    <?php endforeach; ?>
  </tbody></table>
</div>
