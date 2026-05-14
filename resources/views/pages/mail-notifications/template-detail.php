<?php
/** @var array<string,mixed> $contentData */
$template = $contentData['template'] ?? null;
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<section class="eco-card">
  <h1>Notification Template Detail</h1>
  <p><strong>Modo:</strong> read-only.</p>
  <?php if ($errorMessage): ?><p><?= htmlspecialchars((string) $errorMessage) ?></p><?php endif; ?>
  <?php if (!is_array($template)): ?>
    <p>No se encontró la plantilla solicitada.</p>
  <?php else: ?>
    <ul>
      <li>ID: <?= (int) $template['id'] ?></li>
      <li>Canal: <?= htmlspecialchars((string) ($template['channel_name'] ?? '')) ?> (<?= htmlspecialchars((string) ($template['channel_code'] ?? '')) ?>)</li>
      <li>Código: <?= htmlspecialchars((string) ($template['code'] ?? '')) ?></li>
      <li>Nombre: <?= htmlspecialchars((string) ($template['name'] ?? '')) ?></li>
      <li>Subject: <?= htmlspecialchars((string) ($template['subject'] ?? '')) ?></li>
      <li>Body present: <?= !empty($template['body_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>Body preview: <?= htmlspecialchars((string) ($template['body_preview'] ?? '')) ?></li>
      <li>Variables JSON present: <?= !empty($template['variables_json_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>Activo: <?= !empty($template['is_active']) ? 'true' : 'false' ?></li>
      <li>Created at: <?= htmlspecialchars((string) ($template['created_at'] ?? '')) ?></li>
      <li>Updated at: <?= htmlspecialchars((string) ($template['updated_at'] ?? '')) ?></li>
    </ul>
  <?php endif; ?>
</section>
