<?php
$summary = (array)($contentData['summary'] ?? []);
$links = (array)($contentData['links'] ?? []);
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<div class="eco-card">
  <h1>URL Locator</h1>
  <div class="eco-alert eco-alert--warning">Listado administrativo read-only de short links.</div>
  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><div class="eco-alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <p><a class="eco-button btn" href="/url/locator">Resumen URL Locator</a></p>
  <h2>Resumen por status</h2>
  <ul><?php foreach ((array)($summary['by_status'] ?? []) as $item): ?><li><?= e((string)($item['status'] ?? 'sin-status')) ?>: <?= e((string)($item['total'] ?? 0)) ?></li><?php endforeach; ?></ul>
  <h2>Resumen por smart type</h2>
  <ul><?php foreach ((array)($summary['by_smart_type'] ?? []) as $item): ?><li><?= e((string)($item['smart_type'] ?? 'sin-smart-type')) ?>: <?= e((string)($item['total'] ?? 0)) ?></li><?php endforeach; ?></ul>
  <table class="eco-table"><thead><tr><th>id</th><th>slug</th><th>title</th><th>status</th><th>smart_type_label</th><th>campaign</th><th>landing page</th><th>target_url_present</th><th>target_url_exposed</th><th>requires_access_token</th><th>access_token_hash_present</th><th>access_token_hash_exposed</th><th>language_detection_enabled</th><th>expires_at</th><th>max_clicks</th><th>click_count</th><th>created_at</th><th>updated_at</th></tr></thead><tbody>
    <?php foreach ($links as $link): ?>
      <tr><td><?= e((string)($link['id'] ?? '')) ?></td><td><?= e((string)($link['slug'] ?? '')) ?></td><td><?= e((string)($link['title'] ?? '')) ?></td><td><?= e((string)($link['status'] ?? '')) ?></td><td><?= e((string)($link['smart_type_label'] ?? '')) ?></td><td><?= e((string)($link['campaign_name'] ?? '')) ?></td><td><?= e((string)($link['landing_page_title'] ?? '')) ?></td><td><?= !empty($link['target_url_present']) ? 'true' : 'false' ?></td><td>false</td><td><?= !empty($link['requires_access_token']) ? 'true' : 'false' ?></td><td><?= !empty($link['access_token_hash_present']) ? 'true' : 'false' ?></td><td>false</td><td><?= !empty($link['language_detection_enabled']) ? 'true' : 'false' ?></td><td><?= e((string)($link['expires_at'] ?? '')) ?></td><td><?= e((string)($link['max_clicks'] ?? '')) ?></td><td><?= e((string)($link['click_count'] ?? '0')) ?></td><td><?= e((string)($link['created_at'] ?? '')) ?></td><td><?= e((string)($link['updated_at'] ?? '')) ?></td></tr>
    <?php endforeach; ?>
  </tbody></table>
</div>
