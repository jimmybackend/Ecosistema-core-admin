<?php
$preview = is_array($preview ?? null) ? $preview : ['ok' => false, 'reason' => 'Preview no disponible.'];
$id = (int) ($id ?? 0);
$statusMessage = is_string($_GET['ok'] ?? null) ? (string) $_GET['ok'] : null;
$errorMessage = is_string($_GET['error'] ?? null) ? (string) $_GET['error'] : null;
$smtp = is_array($preview['smtp'] ?? null) ? $preview['smtp'] : [];
$attachments = is_array($preview['attachments'] ?? null) ? $preview['attachments'] : [];
$recipients = is_array($preview['recipients'] ?? null) ? $preview['recipients'] : [];
?>
<section>
  <h1>Preview de envío individual</h1>
  <p>Envío individual controlado (sin masivo, campañas ni workers).</p>
  <?php if ($statusMessage): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
  <?php if ($errorMessage): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>

  <?php if (($preview['ok'] ?? false) !== true): ?>
    <article class="eco-card"><div class="eco-alert" role="alert"><?= e((string) ($preview['reason'] ?? 'No se pudo preparar el envío.')) ?></div></article>
  <?php else: ?>
    <article class="eco-card">
      <table class="eco-table" style="width:100%"><tbody>
        <tr><th>Mensaje ID</th><td><?= e((string) $id) ?></td></tr>
        <tr><th>Destinatarios</th><td><?= e(implode(', ', $recipients)) ?></td></tr>
        <tr><th>Asunto</th><td><?= e((string) ($preview['subject'] ?? '')) ?></td></tr>
        <tr><th>MAIL_SEND_ENABLED</th><td><span class="eco-badge"><?= !empty($smtp['send_enabled']) ? 'true' : 'false' ?></span></td></tr>
        <tr><th>MAIL_ALLOW_TEST_SEND</th><td><span class="eco-badge"><?= !empty($smtp['allow_test_send']) ? 'true' : 'false' ?></span></td></tr>
        <tr><th>SMTP host</th><td><?= e((string) ($smtp['host'] ?? '')) ?></td></tr>
        <tr><th>SMTP user (masked)</th><td><?= e((string) ($smtp['username_masked'] ?? '')) ?></td></tr>
        <tr><th>Estado SMTP</th><td><?= !empty($smtp['is_valid']) ? 'válido' : 'inválido' ?></td></tr>
        <tr><th>Estado envío</th><td><?= e((string) ($preview['reason'] ?? '')) ?></td></tr>
      </tbody></table>
      <p><strong>Body text (resumen escapado):</strong><br><?= nl2br(e((string) ($preview['body_text_preview'] ?? ''))) ?></p>

      <?php if ($attachments !== []): ?>
      <?php $summary = is_array($preview['attachments_summary'] ?? null) ? $preview['attachments_summary'] : []; ?>
      <p><strong>Total adjuntos:</strong> <?= e((string) ($summary['count'] ?? 0)) ?> | <strong>Total bytes:</strong> <?= e((string) ($summary['total_bytes'] ?? 0)) ?></p>
      <p><strong>Límites:</strong> max <?= e((string) (($summary['limits']['max_attachments'] ?? 0))) ?> archivos, <?= e((string) (($smtp['max_attachment_mb'] ?? 0))) ?>MB por archivo, <?= e((string) (($smtp['max_total_attachment_mb'] ?? 0))) ?>MB total</p>
      <table class="eco-table" style="width:100%"><thead><tr><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th>Estado</th></tr></thead><tbody>
      <?php foreach ($attachments as $attachment): ?><tr><td><?= e((string) ($attachment['original_name'] ?? '')) ?></td><td><?= e((string) ($attachment['mime_type'] ?? '')) ?></td><td><?= e((string) ($attachment['size_bytes'] ?? '')) ?></td><td><span class="eco-badge"><?= !empty($attachment['ready']) ? 'Listo' : e((string) ($attachment['blocked_reason'] ?? 'Bloqueado')) ?></span></td></tr><?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
      <form method="post" action="/mail/messages/<?= e((string) $id) ?>/prepare-send">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
        <?php if (!empty($preview['can_send_real'])): ?>
          <button type="submit" class="eco-button btn">Enviar borrador</button>
        <?php else: ?>
          <div class="eco-alert" role="alert">Envío real deshabilitado por configuración/validación.</div>
        <?php endif; ?>
      </form>
    </article>
  <?php endif; ?>
</section>
