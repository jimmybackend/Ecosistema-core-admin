<?php
/** @var array<string,mixed> $contentData */
$item = $contentData['item'] ?? null;
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<section class="eco-card">
  <h1>Notification Queue Detail</h1>
  <p><a href="/mail-notifications/queue">← Volver a la cola</a></p>
  <p><strong>Modo:</strong> read-only (processing_enabled=false).</p>
  <?php if ($errorMessage): ?><p><?= htmlspecialchars((string) $errorMessage) ?></p><?php endif; ?>
  <?php if (!is_array($item)): ?>
    <p>No se encontró el elemento solicitado.</p>
  <?php else: ?>
    <ul>
      <li>ID: <?= (int) ($item['id'] ?? 0) ?></li>
      <li>User ID: <?= (int) ($item['user_id'] ?? 0) ?></li>
      <li>Channel ID: <?= (int) ($item['channel_id'] ?? 0) ?></li>
      <li>Template ID: <?= (int) ($item['template_id'] ?? 0) ?></li>
      <li>Module: <?= htmlspecialchars((string) ($item['module_code'] ?? '')) ?></li>
      <li>Entity: <?= htmlspecialchars((string) ($item['entity_table'] ?? '')) ?> #<?= (int) ($item['entity_id'] ?? 0) ?></li>
      <li>Title preview: <?= htmlspecialchars((string) ($item['title_preview'] ?? '')) ?></li>
      <li>Body present: <?= !empty($item['body_present']) ? 'true' : 'false' ?></li>
      <li>Body preview: <?= htmlspecialchars((string) ($item['body_preview'] ?? '')) ?></li>
      <li>Payload JSON present: <?= !empty($item['payload_json_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>Status: <?= htmlspecialchars((string) ($item['status'] ?? '')) ?></li>
      <li>Scheduled at: <?= htmlspecialchars((string) ($item['scheduled_at'] ?? '')) ?></li>
      <li>Sent at: <?= htmlspecialchars((string) ($item['sent_at'] ?? '')) ?></li>
      <li>Failed at: <?= htmlspecialchars((string) ($item['failed_at'] ?? '')) ?></li>
      <li>Fail reason present: <?= !empty($item['fail_reason_present']) ? 'true' : 'false' ?></li>
      <li>Fail reason preview: <?= htmlspecialchars((string) ($item['fail_reason_preview'] ?? '')) ?></li>
      <li>Created at: <?= htmlspecialchars((string) ($item['created_at'] ?? '')) ?></li>
    </ul>
  <?php endif; ?>
</section>
