<?php $account = is_array($account ?? null) ? $account : []; $mailboxes=is_array($mailboxes??null)?$mailboxes:[]; ?>
<section><h1>Editar SMTP</h1>
<form method="post" action="/mail/smtp-accounts/<?= e((string)($account['id'] ?? '0')) ?>"><input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
<fieldset><legend>Identidad operativa</legend>
<label>Mailbox</label><select name="mailbox_id" required><?php foreach($mailboxes as $m): ?><option value="<?= e((string)$m['id']) ?>" <?= ((int)($account['mailbox_id']??0)===(int)$m['id'])?'selected':'' ?>><?= e((string)($m['full_address'] ?? ('Mailbox #'.$m['id']))) ?></option><?php endforeach; ?></select>
<label>Nombre</label><input name="name" value="<?= e((string)($account['name']??'')) ?>" required>
<label>Email</label><input type="email" name="email_address" value="<?= e((string)($account['email_address']??'')) ?>" required>
</fieldset>
<fieldset><legend>Servidor de entrada</legend>
<label>Host entrada</label><input name="host_in" value="<?= e((string)($account['host_in']??'')) ?>" required>
<label>Puerto entrada</label><input type="number" name="port_in" value="<?= e((string)($account['port_in']??993)) ?>" min="1" max="65535" required>
<label>SSL entrada</label><select name="ssl_in"><?php foreach(['none','tls','ssl'] as $enc): ?><option value="<?= $enc ?>" <?= (($account['ssl_in']??'ssl')===$enc)?'selected':'' ?>><?= $enc ?></option><?php endforeach; ?></select>
</fieldset>
<fieldset><legend>Servidor de salida</legend>
<label>Host salida</label><input name="host_out" value="<?= e((string)($account['host_out']??'')) ?>" required>
<label>Puerto salida</label><input type="number" name="port_out" value="<?= e((string)($account['port_out']??587)) ?>" required>
<label>SSL salida</label><select name="ssl_out"><?php foreach(['none','tls','ssl'] as $enc): ?><option value="<?= $enc ?>" <?= (($account['ssl_out']??'tls')===$enc)?'selected':'' ?>><?= $enc ?></option><?php endforeach; ?></select>
</fieldset>
<fieldset><legend>Credenciales</legend>
<label>Username</label><input name="username" value="<?= e((string)($account['username']??'')) ?>" required>
<label>Password SMTP (opcional)</label><input type="password" name="smtp_password" autocomplete="new-password">
</fieldset>
<fieldset><legend>Límites y estado</legend>
<label>Max daily email</label><input type="number" name="max_daily_email" value="<?= e((string)($account['max_daily_email']??0)) ?>">
<label><input type="checkbox" name="enable_limit" value="1" <?= !empty($account['enable_limit'])?'checked':'' ?>> Habilitar límite diario</label>
<label><input type="checkbox" name="available_to_everyone" value="1" <?= !empty($account['available_to_everyone'])?'checked':'' ?>> Disponible para todo el tenant</label>
<label>Status</label><select name="status"><option value="active" <?= (($account['status']??'')==='active')?'selected':'' ?>>active</option><option value="disabled" <?= (($account['status']??'')==='disabled')?'selected':'' ?>>disabled</option></select>
</fieldset>
<button class="eco-button btn" type="submit">Guardar cambios</button></form></section>
