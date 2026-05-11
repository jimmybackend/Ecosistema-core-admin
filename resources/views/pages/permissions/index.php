<?php $permissions=$contentData['permissions']??[]; $statusMessage=$contentData['statusMessage']??null; $errorMessage=$contentData['errorMessage']??null; ?>
<section class="eco-card"><h1>Permisos</h1><p><a class="eco-button btn" href="/permissions/create">Crear permiso</a></p>
<?php if($statusMessage): ?><div class="eco-alert"><?= e($statusMessage) ?></div><?php endif; ?>
<?php if($errorMessage): ?><div class="eco-alert"><?= e($errorMessage) ?></div><?php endif; ?>
<table class="eco-table"><thead><tr><th>ID</th><th>Módulo</th><th>Code</th><th>Name</th><th>Action</th><th>Resource</th><th>Status</th><th>Created</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($permissions as $p): ?><tr><td><?= e((string)$p['id']) ?></td><td><?= e((string)($p['module_name']??$p['module_code']??'')) ?></td><td><?= e((string)$p['code']) ?></td><td><?= e((string)$p['name']) ?></td><td><?= e((string)$p['action']) ?></td><td><?= e((string)$p['resource']) ?></td><td><span class="eco-badge"><?= e((string)$p['status']) ?></span></td><td><?= e((string)$p['created_at']) ?></td><td><a class="btn" href="/permissions/<?= e((string)$p['id']) ?>/edit">Editar</a></td></tr><?php endforeach; ?>
</tbody></table></section>
