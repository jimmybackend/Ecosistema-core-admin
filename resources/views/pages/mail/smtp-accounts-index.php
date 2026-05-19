<?php $accounts = is_array($accounts ?? null) ? $accounts : []; ?>
<section><h1>SMTP Accounts</h1><p>Listado seguro (sin password SMTP).</p>
<table class="eco-table" style="width:100%"><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Host</th><th>Port</th><th>SSL</th><th>Username</th><th>Status</th></tr></thead><tbody>
<?php foreach($accounts as $a): ?><tr><td><?= e((string)($a['id']??'')) ?></td><td><?= e((string)($a['name']??'')) ?></td><td><?= e((string)($a['email_address']??'')) ?></td><td><?= e((string)($a['host_out']??'')) ?></td><td><?= e((string)($a['port_out']??'')) ?></td><td><?= e((string)($a['ssl_out']??'')) ?></td><td><?= e((string)($a['username']??'')) ?></td><td><?= e((string)($a['status']??'')) ?></td></tr><?php endforeach; ?>
</tbody></table></section>
