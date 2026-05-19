<?php
$mailboxes = is_array($mailboxes ?? null) ? $mailboxes : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
$authData = is_array($auth ?? null) ? $auth : [];
$authEmail = (string) ($authData['email'] ?? $authData['auth_email'] ?? 'no-disponible');
$authName = (string) ($authData['display_name'] ?? $authData['auth_display_name'] ?? '');
?>
<section>
<h1>Crear SMTP propio</h1>
<p>Usuario autenticado: <strong><?= e($authName !== '' ? ($authName . ' (' . $authEmail . ')') : $authEmail) ?></strong>.</p>
<p>Tu correo de acceso al panel puede ser distinto del correo operativo asignado para seguimiento y soporte.</p>
<p>SMTP global de <code>.env</code> funciona como fallback solo si la política/flags lo permiten. La contraseña SMTP es independiente de la contraseña del panel.</p>
<?php if ($statusMessage): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
<?php if ($errorMessage): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>

<?php if ($mailboxes === []): ?>
<div class="eco-alert" role="alert">No tienes mailboxes operativas activas asignadas.</div>
<p>No es posible crear una cuenta SMTP hasta que tengas una mailbox activa asignada en tu tenant.</p>
<?php else: ?>
<form method="post" action="/mail/smtp-accounts">
<input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
<label>Mailbox operativa asignada</label>
<select name="mailbox_id" required>
<?php foreach($mailboxes as $m): ?>
<option value="<?= e((string)$m['id']) ?>"><?= e((string)($m['full_address'] ?? ('Mailbox #'.$m['id']))) ?></option>
<?php endforeach; ?>
</select>
<label>Nombre</label><input name="name" required>
<label>Email SMTP (from)</label><input type="email" name="email_address" required>
<label>Host out</label><input name="host_out" required>
<label>Port out</label><input type="number" name="port_out" value="587" min="1" max="65535" required>
<label>Encryption</label><select name="ssl_out"><option value="none">none</option><option value="tls" selected>tls</option><option value="ssl">ssl</option></select>
<label>Username SMTP</label><input name="username" required>
<label>Password SMTP</label><input type="password" name="smtp_password" required autocomplete="new-password">
<label>Max daily email</label><input type="number" name="max_daily_email" value="0" min="0">
<label><input type="checkbox" name="enable_limit" value="1"> Habilitar límite diario</label>
<label><input type="checkbox" name="available_to_everyone" value="1"> Disponible para todo el tenant (si política aplica)</label>
<label>Status</label><select name="status"><option value="active">active</option><option value="disabled">disabled</option></select>
<div style="margin-top:1rem;"><button class="eco-button btn" type="submit">Guardar SMTP</button> <a class="eco-button btn" href="/mail/smtp-accounts">Cancelar</a></div>
</form>
<?php endif; ?>
</section>
