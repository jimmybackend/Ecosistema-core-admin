<?php
$messages = is_array($messages ?? null) ? $messages : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
$mailDebug = is_array($mailDebug ?? null) ? $mailDebug : null;
$smtpAccounts = is_array($smtpAccounts ?? null) ? $smtpAccounts : [];
$selectedSmtpId = (int)($selectedAccountId ?? ($smtpAccounts[0]['id'] ?? 0));
$decodeAddresses = static function (mixed $raw): array { $arr = json_decode((string)$raw, true); return is_array($arr) ? array_values(array_filter(array_map('strval', $arr))) : []; };
$summary = static function (mixed $json) use ($decodeAddresses): string { $arr = $decodeAddresses($json); return $arr === [] ? '[]' : implode(', ', array_slice($arr, 0, 2)); };
$folderName = static function (array $m): string { if (!empty($m['folder_name'])) { return (string)$m['folder_name']; } return (($m['direction'] ?? '') === 'inbound') ? 'INBOX' : 'Sin carpeta'; };
$imported = (int)($imported ?? 0); $skipped = (int)($skipped ?? 0); $attachmentsPending = (int)($attachmentsPending ?? 0); $syncErrors = (int)($syncErrors ?? 0);
?>
<section><h1>Mail</h1>
<?php if ($statusMessage): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?> imported=<?= e((string)$imported) ?>, skipped=<?= e((string)$skipped) ?>, attachments_pending=<?= e((string)$attachmentsPending) ?>, errors=<?= e((string)$syncErrors) ?></div><?php endif; ?>
<?php if ($errorMessage): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<?php if ($mailDebug): ?><pre class="eco-alert" role="alert"><?= e((string)json_encode($mailDebug, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre><?php endif; ?>
<form method="post" action="/mail/imap-sync"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>"><select name="smtp_account_id" required><?php foreach($smtpAccounts as $acc): ?><option value="<?= e((string)($acc['id'] ?? 0)) ?>" <?= ((int)($acc['id'] ?? 0) === $selectedSmtpId ? 'selected' : '') ?>>#<?= e((string)($acc['id'] ?? 0)) ?> · <?= e((string)($acc['mailbox_full_address'] ?? 'sin-mailbox')) ?></option><?php endforeach; ?></select><input name="limit" type="number" min="1" max="250" value="25"><button class="eco-button btn" type="submit" <?= $smtpAccounts===[]?'disabled':'' ?>>Sincronizar IMAP</button></form>
<table class="eco-table" style="width:100%"><thead><tr><th>ID</th><th>Dir</th><th>From</th><th>To</th><th>Subject</th><th>Attachments</th><th>Flags</th><th>Fechas</th><th>Acciones</th></tr></thead><tbody>
<?php if ($messages===[]): ?><tr><td colspan="9">No hay mensajes para mostrar.</td></tr><?php else: foreach($messages as $m): ?><tr>
<td><?= e((string)$m['id']) ?></td><td><?= e((string)$m['direction']) ?><br><small><?= e($folderName($m)) ?></small></td><td><?= e((string)($m['from_address'] ?? '')) ?></td><td><?= e($summary($m['to_addresses'] ?? '')) ?></td><td><?= e((string)($m['subject'] ?? '')) ?></td><td><?= ((int)($m['has_attachments'] ?? 0)===1)?'Sí':'No' ?></td>
<td>R:<?= e((string)$m['is_read']) ?> S:<?= e((string)$m['is_starred']) ?> D:<?= e((string)$m['is_draft']) ?> SP:<?= e((string)($m['is_spam'] ?? 0)) ?></td>
<td><?= e((string)($m['received_at'] ?? '')) ?><br><?= e((string)($m['sent_at'] ?? '')) ?><br><?= e((string)($m['created_at'] ?? '')) ?></td>
<td><a class="eco-button btn" href="/mail/messages/<?= e((string)$m['id']) ?>">Ver</a></td>
</tr><?php endforeach; endif; ?></tbody></table></section>
