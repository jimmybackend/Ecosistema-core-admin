<?php
$result = (array)($contentData['result'] ?? []);
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<div class="eco-card">
  <h1>Browser Analytics collector dry-run</h1>
  <div class="eco-alert eco-alert--warning">Simulación segura: no se escribe en DB, no crea sesión/pageview/event y sólo devuelve payload saneado.</div>
  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><div class="eco-alert"><?= e($errorMessage) ?></div><?php endif; ?>

  <form method="post" action="/browser/analytics/collector-dry-run" class="eco-form">
    <input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
    <label>event_type <input type="text" name="event_type" required></label>
    <label>event_name <input type="text" name="event_name" required></label>
    <label>page_url <input type="url" name="page_url" required></label>
    <label>path <input type="text" name="path" placeholder="/home" required></label>
    <label>referrer_url <input type="url" name="referrer_url"></label>
    <label>campaign_id <input type="number" name="campaign_id" min="1"></label>
    <label>landing_page_id <input type="number" name="landing_page_id" min="1"></label>
    <label>short_link_id <input type="number" name="short_link_id" min="1"></label>
    <button class="eco-button btn" type="submit">Simular collector</button>
  </form>

  <?php if ($result !== []): ?>
    <h2>Resultado DTO</h2>
    <ul>
      <li>mode: <?= e((string)($result['mode'] ?? '')) ?></li>
      <li>collector_write: <?= !empty($result['collector_write']) ? 'true' : 'false' ?></li>
      <li>would_create_session: <?= !empty($result['would_create_session']) ? 'true' : 'false' ?></li>
      <li>would_create_pageview: <?= !empty($result['would_create_pageview']) ? 'true' : 'false' ?></li>
      <li>would_create_event: <?= !empty($result['would_create_event']) ? 'true' : 'false' ?></li>
      <li>validation_status: <?= e((string)($result['validation_status'] ?? '')) ?></li>
    </ul>
    <h3>warnings</h3>
    <pre><?= e(json_encode((array)($result['warnings'] ?? []), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    <h3>sanitized_payload</h3>
    <pre><?= e(json_encode((array)($result['sanitized_payload'] ?? []), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
  <?php endif; ?>
</div>
