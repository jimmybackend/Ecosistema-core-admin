<?php declare(strict_types=1);
$modules=is_array($modules??null)?$modules:[]; $statusMessage=is_string($statusMessage??null)?$statusMessage:null; $errorMessage=is_string($errorMessage??null)?$errorMessage:null;
?>
<section><h1>Módulos</h1><p>Gestión básica usando únicamente <code>core_modules</code>. El enforcement fino de permisos se implementará en un PR posterior.</p>
<?php if($statusMessage!==null):?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
<?php if($errorMessage!==null):?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<article class="eco-card" style="margin-top:1rem;"><div style="margin-bottom:.75rem;"><a class="eco-button btn" href="/modules/create">Crear módulo</a></div>
<table class="eco-table" style="width:100%;"><thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Prefix</th><th>Core</th><th>Billable</th><th>Status</th><th>Creado</th><th>Acciones</th></tr></thead><tbody>
<?php if($modules===[]):?><tr><td colspan="9">No hay módulos para mostrar.</td></tr><?php else: foreach($modules as $module): ?><tr>
<td><?= e((string)($module['id']??'')) ?></td><td><?= e((string)($module['code']??'')) ?></td><td><?= e((string)($module['name']??'')) ?></td><td><?= e((string)($module['table_prefix']??'')) ?></td><td><span class="eco-badge"><?= e((string)($module['is_core']??'')) ?></span></td><td><span class="eco-badge"><?= e((string)($module['is_billable']??'')) ?></span></td><td><span class="eco-badge"><?= e((string)($module['status']??'')) ?></span></td><td><?= e((string)($module['created_at']??'')) ?></td>
<td><a class="eco-button btn" href="/modules/<?= e((string)($module['id']??'0')) ?>/edit">Editar</a><form method="post" action="/modules/<?= e((string)($module['id']??'0')) ?>/status" style="display:inline-block;margin-left:.25rem;"><input type="hidden" name="_csrf" value="<?= e((string)($csrfToken??'')) ?>"><select class="eco-form-control" name="status"><?php foreach(['active','inactive','deprecated'] as $status): ?><option value="<?= e($status) ?>" <?= (($module['status']??'')===$status)?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select><button class="eco-button btn" type="submit">Cambiar</button></form></td></tr><?php endforeach; endif; ?>
</tbody></table></article></section>
