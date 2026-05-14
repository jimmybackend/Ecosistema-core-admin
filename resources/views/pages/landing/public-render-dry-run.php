<?php
/** @var array<string,mixed> $contentData */
$result = $contentData['result'] ?? [];
$id = (int) ($contentData['id'] ?? 0);
?>
<section class="eco-card">
  <h1>Landing Public Render (Dry-run)</h1>
  <p><strong>Ruta:</strong> /landing/pages/<?= $id ?>/public-render-dry-run</p>
  <p><strong>Allowed:</strong> <?= !empty($result['allowed']) ? 'true' : 'false' ?></p>
  <p><strong>Reason:</strong> <?= htmlspecialchars((string) ($result['reason'] ?? 'N/A')) ?></p>
  <p><strong>DB write:</strong> false | <strong>Visit write:</strong> false | <strong>Forms write:</strong> false</p>

  <?php $page = $result['page'] ?? null; if (is_array($page)): ?>
    <h2>Landing segura</h2>
    <ul>
      <li>ID: <?= (int) ($page['id'] ?? 0) ?></li>
      <li>Title: <?= htmlspecialchars((string) ($page['title'] ?? '')) ?></li>
      <li>Slug: <?= htmlspecialchars((string) ($page['slug'] ?? '')) ?></li>
      <li>Status: <?= htmlspecialchars((string) ($page['status'] ?? '')) ?></li>
      <li>Public URL present: <?= !empty($page['public_url_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>Public URL preview: <?= htmlspecialchars((string) ($page['public_url_preview'] ?? '')) ?></li>
      <li>Template JSON present: <?= !empty($page['template_json_present']) ? 'true' : 'false' ?> (exposed=false)</li>
    </ul>
  <?php endif; ?>

  <?php $version = $result['published_version'] ?? null; if (is_array($version)): ?>
    <h2>Versión publicada segura</h2>
    <ul>
      <li>ID: <?= (int) ($version['id'] ?? 0) ?></li>
      <li>Version: <?= (int) ($version['version_no'] ?? 0) ?></li>
      <li>Title: <?= htmlspecialchars((string) ($version['title'] ?? '')) ?></li>
      <li>layout_json_present: <?= !empty($version['layout_json_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>layout_json_preview: <?= htmlspecialchars((string) ($version['layout_json_preview'] ?? '')) ?></li>
    </ul>
  <?php endif; ?>

  <h2>Bloques (preview seguro)</h2>
  <?php $blocks = is_array($result['blocks'] ?? null) ? $result['blocks'] : []; ?>
  <?php if ($blocks === []): ?>
    <p>Sin bloques activos/publicados para render simulado.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($blocks as $block): ?>
        <li>#<?= (int) ($block['id'] ?? 0) ?> · <?= htmlspecialchars((string) ($block['block_type'] ?? '')) ?> · <?= htmlspecialchars((string) ($block['name'] ?? '')) ?> · settings_json_present=<?= !empty($block['settings_json_present']) ? 'true' : 'false' ?> · content_json_present=<?= !empty($block['content_json_present']) ? 'true' : 'false' ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
