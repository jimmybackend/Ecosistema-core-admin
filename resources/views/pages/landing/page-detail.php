<?php
/** @var array<string,mixed> $contentData */
$page = $contentData['page'] ?? null;
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<section class="eco-card">
  <h1>Landing Page Detail</h1>
  <p><strong>Modo:</strong> read-only.</p>
  <?php if ($errorMessage): ?><p><?= htmlspecialchars((string)$errorMessage) ?></p><?php endif; ?>
  <?php if (!is_array($page)): ?>
    <p>No se encontró la landing page solicitada.</p>
  <?php else: ?>
    <p><a href="/landing/pages/<?= (int)$page['id'] ?>/visits">Ver visitas</a> | <a href="/landing/pages/<?= (int)$page['id'] ?>/forms">Ver formularios</a></p>
    <h2>Metadata segura</h2>
    <ul>
      <li>ID: <?= (int)$page['id'] ?></li><li>Title: <?= htmlspecialchars((string)$page['title']) ?></li><li>Slug: <?= htmlspecialchars((string)$page['slug']) ?></li><li>Status: <?= htmlspecialchars((string)$page['status']) ?></li><li>Page type: <?= htmlspecialchars((string)$page['page_type']) ?></li>
      <li>Template JSON present: <?= !empty($page['template_json_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>Custom head HTML present: <?= !empty($page['custom_head_html_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>Custom body HTML present: <?= !empty($page['custom_body_html_present']) ? 'true' : 'false' ?> (exposed=false)</li>
    </ul>

    <h2>Versiones (resumen seguro)</h2>
    <ul><?php foreach (($page['versions_summary'] ?? []) as $v): ?><li>#<?= (int)$v['version_no'] ?> <?= htmlspecialchars((string)$v['title']) ?> | layout_json_present=<?= !empty($v['layout_json_present']) ? 'true' : 'false' ?></li><?php endforeach; ?></ul>

    <h2>Bloques (resumen seguro)</h2>
    <ul><?php foreach (($page['blocks_summary'] ?? []) as $b): ?><li>#<?= (int)$b['id'] ?> <?= htmlspecialchars((string)$b['block_type']) ?> / <?= htmlspecialchars((string)$b['name']) ?> | settings_json_present=<?= !empty($b['settings_json_present']) ? 'true' : 'false' ?> | content_json_present=<?= !empty($b['content_json_present']) ? 'true' : 'false' ?></li><?php endforeach; ?></ul>
  <?php endif; ?>
</section>
