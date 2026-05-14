<?php $result = $contentData['result'] ?? null; $errorMessage = $contentData['errorMessage'] ?? null; ?>
<section class="eco-card">
  <h1>Send Notification Dry-Run</h1>
  <p><strong>Modo:</strong> simulación segura, sin INSERT en notifications_queue/mail_messages y sin SMTP.</p>
  <p><a href="/mail-notifications">← Volver a Mail Notifications</a></p>

  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
    <p><?= htmlspecialchars($errorMessage) ?></p>
  <?php endif; ?>

  <?php if (is_array($result)): ?>
    <h2>Resultado</h2>
    <ul>
      <li>mode: <?= htmlspecialchars((string) ($result['mode'] ?? '')) ?></li>
      <li>would_queue: <?= !empty($result['would_queue']) ? 'true' : 'false' ?></li>
      <li>would_send: <?= !empty($result['would_send']) ? 'true' : 'false' ?></li>
      <li>send_executed: false</li>
      <li>queue_created: false</li>
      <li>smtp_connection: false</li>
    </ul>
    <p><strong>Subject preview:</strong> <?= htmlspecialchars((string) ($result['subject_preview'] ?? '')) ?></p>
    <p><strong>Body preview:</strong></p>
    <pre><?= htmlspecialchars((string) ($result['body_preview'] ?? '')) ?></pre>
  <?php endif; ?>

  <h2>Simular envío</h2>
  <form method="post" action="/mail-notifications/send-dry-run">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrfToken ?? '')) ?>">

    <label>template_id</label><br>
    <input type="number" min="1" name="template_id" required><br><br>

    <label>recipient_user_id (opcional)</label><br>
    <input type="number" min="1" name="recipient_user_id"><br><br>

    <label>recipient_email_preview (opcional)</label><br>
    <input type="email" name="recipient_email_preview" placeholder="preview@example.com"><br><br>

    <label>payload_json (opcional, objeto JSON)</label><br>
    <textarea name="payload_json" rows="8" cols="80" placeholder='{"nombre":"Ana","empresa":"Ecosistema"}'></textarea><br><br>

    <button type="submit">Simular envío</button>
  </form>


  <?php $canRealSend = filter_var((string) getenv('ECOSISTEMA_MAIL_NOTIFICATIONS_ENABLED'), FILTER_VALIDATE_BOOL) && filter_var((string) getenv('ECOSISTEMA_MAIL_SEND_ENABLED'), FILTER_VALIDATE_BOOL); ?>
  <?php if ($canRealSend): ?>
    <h2>Enviar controlado</h2>
    <form method="post" action="/mail-notifications/send">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrfToken ?? '')) ?>">
      <label>template_id</label><br><input type="number" min="1" name="template_id" required><br><br>
      <label>recipient_user_id</label><br><input type="number" min="1" name="recipient_user_id" required><br><br>
      <label>mailbox_id</label><br><input type="number" min="1" name="mailbox_id" required><br><br>
      <label>payload_json (opcional)</label><br><textarea name="payload_json" rows="4" cols="80">{}</textarea><br><br>
      <button type="submit">Enviar controlado</button>
    </form>
  <?php endif; ?>

</section>
