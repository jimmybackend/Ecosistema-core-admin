<?php
$accounts = is_array($accounts ?? null) ? $accounts : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
?>
<section><h1>SMTP Accounts</h1><p>Listado seguro (sin password SMTP).</p>
<p>Login del panel y mailbox operativa pueden ser distintos.</p>
<?php if ($statusMessage): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
<?php if ($errorMessage): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<div><a class="eco-button btn" href="/mail/smtp-accounts/create">Crear SMTP propio</a></div>
<table class="eco-table" style="width:100%"><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Host</th><th>Port</th><th>SSL</th><th>Username</th><th>Status</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($accounts as $a): ?><tr><td><?= e((string)($a['id']??'')) ?></td><td><?= e((string)($a['name']??'')) ?></td><td><?= e((string)($a['email_address']??'')) ?></td><td><?= e((string)($a['host_out']??'')) ?></td><td><?= e((string)($a['port_out']??'')) ?></td><td><?= e((string)($a['ssl_out']??'')) ?></td><td><?= e(substr((string)($a['username']??''),0,1).'***') ?></td><td><?= e((string)($a['status']??'')) ?></td><td><a href="/mail/smtp-accounts/<?= e((string)$a['id']) ?>/edit">Editar</a> | <a href="/mail/smtp-accounts/<?= e((string)$a['id']) ?>/test-dry-run">Test dry-run</a><form method="post" action="/mail/smtp-accounts/<?= e((string)$a['id']) ?>/disable" style="display:inline"><input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>"><button type="submit">Desactivar</button></form></td></tr><?php endforeach; ?>
</tbody></table></section>
