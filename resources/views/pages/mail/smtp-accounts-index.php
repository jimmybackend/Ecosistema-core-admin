<?php
$accounts = is_array($accounts ?? null) ? $accounts : [];
$emptyAccountsMessage = trim((string)($emptyAccountsMessage ?? 'No hay cuentas SMTP disponibles para tu usuario/tenant.'));
$clean = static fn ($v): string => trim(preg_replace('/\s+/', ' ', strip_tags((string) $v)) ?? '');
$yn = static fn ($v): string => ((string)$v === '1' || $v === 1 || $v === true || $v === 'yes') ? 'yes' : 'no';
$mask = static function ($value): string { $value=(string)$value; if ($value==='') return 'no-configurado'; $len=strlen($value); if ($len<=2) return str_repeat('*',$len); return substr($value,0,1).str_repeat('*',$len-2).substr($value,-1); };
?>
<section>
<h1>SMTP Accounts</h1>
<div style="margin-bottom:.75rem;"><a class="eco-button btn" href="/mail/smtp-accounts/create">Crear SMTP propio</a></div>

<?php if ($accounts === []): ?>
  <article class="eco-card"><p><?= e($emptyAccountsMessage) ?></p></article>
<?php else: ?>
<table class="eco-table" style="width:100%;font-size:.92rem;">
<thead><tr><th>ID</th><th>Mailbox operativa</th><th>Nombre</th><th>Email/from</th><th>Entrada</th><th>Salida</th><th>Username enmascarado</th><th>Límite diario</th><th>Compartida</th><th>Status</th><th>Last error</th><th>Password guardado</th><th>Acciones</th></tr></thead>
<tbody>
<?php foreach($accounts as $a): ?>
<tr>
<td><?= e((string)($a['id']??'')) ?></td>
<td><?= e((string)($a['mailbox_full_address'] ?? '')) ?></td>
<td><?= e((string)($a['name']??'')) ?></td>
<td><?= e((string)($a['email_address']??'')) ?></td>
<td><?= e((string)($a['host_in']??'')) ?>:<?= e((string)($a['port_in']??'')) ?> <?= e((string)($a['ssl_in']??'')) ?></td>
<td><?= e((string)($a['host_out']??'')) ?>:<?= e((string)($a['port_out']??'')) ?> <?= e((string)($a['ssl_out']??'')) ?></td>
<td><?= e($mask($a['username'] ?? '')) ?></td>
<td><?= e($yn($a['enable_limit'] ?? 0)) ?> / <?= e((string)($a['max_daily_email'] ?? 0)) ?></td>
<td><?= e($yn($a['available_to_everyone'] ?? 0)) ?></td>
<td><?= e((string)($a['status']??'')) ?></td>
<td><?= e($clean($a['last_error'] ?? '')) ?></td>
<td><?= e((string)($a['password_encrypted_present'] ?? 'no')) ?></td>
<td><a href="/mail/smtp-accounts/<?= e((string)$a['id']) ?>/edit">Editar</a> | <a href="/mail/smtp-accounts/<?= e((string)$a['id']) ?>/test-dry-run">Test dry-run</a> |
<form method="post" action="/mail/smtp-accounts/<?= e((string)$a['id']) ?>/disable" style="display:inline;"><input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>"><button type="submit" style="border:none;background:none;padding:0;color:#b00;cursor:pointer;">Desactivar</button></form> |
<?php if ((string)($a['status'] ?? '') === 'disabled'): ?>
<form method="post" action="/mail/smtp-accounts/<?= e((string)$a['id']) ?>/delete" style="display:inline;" onsubmit="return confirm('¿Eliminar esta cuenta SMTP? Esta acción no mostrará ni tocará contraseñas.');"><input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>"><button type="submit" style="border:none;background:none;padding:0;color:#b00;cursor:pointer;">Eliminar</button></form>
<?php else: ?>
<span style="color:#666;">Desactiva primero</span>
<?php endif; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</section>
