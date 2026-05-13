<?php $summary=(array)($contentData['summary']??[]); $forms=(array)($contentData['forms']??[]); ?>
<section class="eco-card"><h1>Landing Forms</h1><p><strong>Aviso:</strong> modo read-only, sin envíos públicos.</p>
<p>Total: <?= (int)($summary['total']??0) ?> | Activos: <?= (int)($summary['active']??0) ?> | Inactivos: <?= (int)($summary['inactive']??0) ?></p>
<table class="eco-table"><thead><tr><th>ID</th><th>Nombre</th><th>Landing</th><th>Campaña</th><th>Campos</th><th>Redirect</th><th>Activo</th><th>Detalle</th></tr></thead><tbody>
<?php foreach($forms as $form): ?><tr><td><?= (int)$form['id'] ?></td><td><?= htmlspecialchars((string)$form['name']) ?></td><td><?= htmlspecialchars((string)($form['landing_page_title']??'')) ?></td><td><?= htmlspecialchars((string)($form['campaign_name']??'')) ?></td><td><?= (int)($form['fields_count']??0) ?></td><td><?= !empty($form['redirect_url_present'])?'present (hidden)':'-' ?></td><td><?= !empty($form['is_active'])?'true':'false' ?></td><td><a href="/landing/forms/<?= (int)$form['id'] ?>">Ver</a></td></tr><?php endforeach; ?>
</tbody></table></section>
