<?php
$result = (array)($contentData['result'] ?? []);
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<div class="eco-card">
  <h1>Redirect dry-run</h1>
  <div class="eco-alert eco-alert--warning">Simulación segura: no se redirigió, no se escribió DB y no se registró click.</div>
  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><div class="eco-alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <p><a class="eco-button btn" href="/url/locator/links/<?= e((string)($result['link_id'] ?? '0')) ?>">Volver al detalle</a></p>
  <ul>
    <li>eligible: <?= !empty($result['eligible']) ? 'true' : 'false' ?></li>
    <li>blocked_reason: <?= e((string)($result['blocked_reason'] ?? 'none')) ?></li>
    <li>smart_type: <?= e((string)($result['smart_type_label'] ?? 'unknown')) ?></li>
    <li>detected_language: <?= e((string)($result['detected_language'] ?? '')) ?></li>
    <li>selected_language: <?= e((string)($result['selected_language'] ?? '')) ?></li>
    <li>target_url_present: <?= !empty($result['target_url_present']) ? 'true' : 'false' ?></li>
    <li>target_url_preview: <?= e((string)($result['target_url_preview'] ?? '')) ?></li>
    <li>target_url_exposed: false</li>
    <li>would_redirect: <?= !empty($result['would_redirect']) ? 'true' : 'false' ?></li>
    <li>would_log_click: false</li>
    <li>click_count_incremented: false</li>
  </ul>
</div>
