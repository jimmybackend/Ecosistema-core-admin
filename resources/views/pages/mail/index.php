<?php
$messages = is_array($messages ?? null) ? $messages : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
$summary = static function (mixed $json): string { $arr = json_decode((string)$json, true); return is_array($arr) ? implode(', ', array_slice($arr, 0, 2)) : (string)$json; };
$smtpAccounts = is_array($smtpAccounts ?? null) ? $smtpAccounts : [];
$selectedSmtpId = (int)($smtpAccounts[0]['id'] ?? 0);
$maskUser = static function (mixed $username): string { $value = trim((string)$username); if ($value === '') { return '***'; } $len = strlen($value); if ($len <= 2) { return str_repeat('*', $len); } return substr($value, 0, 1) . str_repeat('*', $len - 2) . substr($value, -1); };

$imported = (int)($imported ?? 0);
$skipped = (int)($skipped ?? 0);
$attachmentsPending = (int)($attachmentsPending ?? 0);
$syncErrors = (int)($syncErrors ?? 0);
?>
<section>
  <h1>Mail</h1>
  <p>Listado mínimo de mensajes por tenant/usuario autenticado. La autorización fina queda para PR posterior.</p>
  <?php if ($statusMessage): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?> imported=<?= e((string)$imported) ?>, skipped=<?= e((string)$skipped) ?>, attachments_pending=<?= e((string)$attachmentsPending) ?>, errors=<?= e((string)$syncErrors) ?></div><?php endif; ?>
  <?php if ($errorMessage): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <article class="eco-card"><div style="margin-bottom:.75rem;"><a class="eco-button btn" href="/mail/compose">Nuevo borrador</a> <a class="eco-button btn" href="/mail/settings">Configuración SMTP</a></div>
  <form method="post" action="/mail/imap-sync" style="margin-bottom:.75rem; display:flex; gap:.5rem; align-items:center;">
    <input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>">
    <label for="smtp_account_id">Cuenta:</label>
    <select id="smtp_account_id" name="smtp_account_id" required>
      <?php foreach($smtpAccounts as $acc): ?>
        <option value="<?= e((string)($acc['id'] ?? 0)) ?>" <?= ((int)($acc['id'] ?? 0) === $selectedSmtpId ? 'selected' : '') ?>>#<?= e((string)($acc['id'] ?? 0)) ?> · <?= e((string)($acc['mailbox_full_address'] ?? 'sin-mailbox')) ?> · <?= e((string)($acc['host_in'] ?? '')) ?>:<?= e((string)($acc['port_in'] ?? '')) ?> · <?= e($maskUser($acc['username'] ?? '')) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="limit">Límite:</label>
    <input id="limit" name="limit" type="number" min="1" max="250" value="25">
    <button class="eco-button btn" type="submit" <?= $smtpAccounts === [] ? 'disabled' : '' ?>>Sincronizar IMAP</button>
    <?php if ($smtpAccounts === []): ?><small style="color:#555">No hay cuentas IMAP activas disponibles. Configura una cuenta SMTP/IMAP activa primero.</small><?php endif; ?>
  </form>
  <table class="eco-table" style="width:100%"><thead><tr><th>ID</th><th>Dir</th><th>From</th><th>To</th><th>Subject</th><th>Flags</th><th>Fechas</th><th>Acciones</th></tr></thead><tbody>
  <?php if ($messages===[]): ?><tr><td colspan="8">No hay mensajes para mostrar.</td></tr><?php else: foreach($messages as $m): ?><tr>
    <td><?= e((string)$m['id']) ?></td><td><span class="eco-badge"><?= e((string)$m['direction']) ?></span></td><td><?= e((string)$m['from_address']) ?></td><td><?= e($summary($m['to_addresses'] ?? '')) ?></td><td><?= e((string)($m['subject'] ?? '')) ?></td>
    <td><span class="eco-badge">R:<?= e((string)$m['is_read']) ?></span> <span class="eco-badge">S:<?= e((string)$m['is_starred']) ?></span> <span class="eco-badge">D:<?= e((string)$m['is_draft']) ?></span> <span class="eco-badge">A:<?= e((string)$m['has_attachments']) ?></span></td>
    <td><?= e((string)($m['received_at'] ?? '')) ?><br><?= e((string)($m['sent_at'] ?? '')) ?><br><?= e((string)($m['created_at'] ?? '')) ?></td>
    <td><a class="eco-button btn" href="/mail/messages/<?= e((string)$m['id']) ?>">Ver</a>
    <form method="post" action="/mail/messages/<?= e((string)$m['id']) ?>/read" style="display:inline-block;"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>"><button class="eco-button btn" type="submit">Read</button></form>
    <form method="post" action="/mail/messages/<?= e((string)$m['id']) ?>/star" style="display:inline-block;"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>"><button class="eco-button btn" type="submit">Star</button></form>
    <form method="post" action="/mail/messages/<?= e((string)$m['id']) ?>/trash" style="display:inline-block;"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>"><button class="eco-button btn" type="submit">Trash</button></form></td>
  </tr><?php endforeach; endif; ?></tbody></table></article>
</section>
