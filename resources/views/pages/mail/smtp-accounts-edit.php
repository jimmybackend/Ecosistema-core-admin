<?php $account = is_array($account ?? null) ? $account : []; $mailboxes=is_array($mailboxes??null)?$mailboxes:[]; $authData = is_array($auth ?? null) ? $auth : []; $authEmail = (string) ($authData['email'] ?? $authData['auth_email'] ?? 'no-disponible'); $authName = (string) ($authData['display_name'] ?? $authData['auth_display_name'] ?? ''); ?>
<section><h1>Editar SMTP</h1><p>Usuario autenticado: <strong><?= e($authName !== "" ? ($authName . " (" . $authEmail . ")") : $authEmail) ?></strong>.</p><p>Tu correo de acceso al panel puede ser distinto del correo operativo asignado para seguimiento y soporte.</p><p>La contraseña SMTP no es la contraseña de acceso al panel.</p>
<form method="post" action="/mail/smtp-accounts/<?= e((string)($account['id'] ?? '0')) ?>"><input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
<label>Mailbox</label><select name="mailbox_id" required><?php foreach($mailboxes as $m): ?><option value="<?= e((string)$m['id']) ?>" <?= ((int)($account['mailbox_id']??0)===(int)$m['id'])?'selected':'' ?>><?= e((string)($m['full_address'] ?? ('Mailbox #'.$m['id']))) ?></option><?php endforeach; ?></select>
<label>Nombre</label><input name="name" value="<?= e((string)($account['name']??'')) ?>" required>
<label>Email</label><input type="email" name="email_address" value="<?= e((string)($account['email_address']??'')) ?>" required>
<label>Host out</label><input name="host_out" value="<?= e((string)($account['host_out']??'')) ?>" required>
<label>Port out</label><input type="number" name="port_out" value="<?= e((string)($account['port_out']??587)) ?>" required>
<label>Encryption</label><select name="ssl_out"><?php foreach(['none','tls','ssl'] as $enc): ?><option value="<?= $enc ?>" <?= (($account['ssl_out']??'tls')===$enc)?'selected':'' ?>><?= $enc ?></option><?php endforeach; ?></select>
<label>Username</label><input name="username" value="<?= e((string)($account['username']??'')) ?>" required>
<label>Password SMTP (opcional)</label><input type="password" name="smtp_password" autocomplete="new-password">
<label>Max daily email</label><input type="number" name="max_daily_email" value="<?= e((string)($account['max_daily_email']??0)) ?>">
<label><input type="checkbox" name="enable_limit" value="1" <?= !empty($account['enable_limit'])?'checked':'' ?>> Habilitar límite diario</label>
<label><input type="checkbox" name="available_to_everyone" value="1" <?= !empty($account['available_to_everyone'])?'checked':'' ?>> Disponible para todo el tenant</label>
<label>Status</label><select name="status"><option value="active" <?= (($account['status']??'')==='active')?'selected':'' ?>>active</option><option value="disabled" <?= (($account['status']??'')==='disabled')?'selected':'' ?>>disabled</option></select>
<button class="eco-button btn" type="submit">Guardar cambios</button></form></section>
