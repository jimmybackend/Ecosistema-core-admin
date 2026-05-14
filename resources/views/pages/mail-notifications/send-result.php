<?php $result = $contentData['result'] ?? null; $errorMessage = $contentData['errorMessage'] ?? null; ?>
<section class="eco-card">
  <h1>Send Notification Result</h1>
  <p><a href="/mail-notifications/send-dry-run">← Volver a send dry-run</a></p>

  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
    <p><?= htmlspecialchars($errorMessage) ?></p>
  <?php endif; ?>

  <?php if (is_array($result)): ?>
    <ul>
      <li>ok: <?= !empty($result['ok']) ? 'true' : 'false' ?></li>
      <li>queue_created: <?= !empty($result['queue_created']) ? 'true' : 'false' ?></li>
      <li>send_executed: false</li>
      <li>smtp_connection: <?= !empty($result['smtp_connection']) ? 'true' : 'false' ?></li>
      <li>status: <?= htmlspecialchars((string) ($result['status'] ?? '')) ?></li>
      <li>queue_id: <?= htmlspecialchars((string) ($result['queue_id'] ?? '')) ?></li>
      <li>mail_message_id: <?= htmlspecialchars((string) ($result['mail_message_id'] ?? '')) ?></li>
      <li>mail_delivery_log_id: <?= htmlspecialchars((string) ($result['mail_delivery_log_id'] ?? '')) ?></li>
      <li>recipient: <?= htmlspecialchars((string) ($result['recipient_masked'] ?? '')) ?></li>
      <li>subject_preview: <?= htmlspecialchars((string) ($result['subject_preview'] ?? '')) ?></li>
    </ul>
  <?php endif; ?>
</section>
