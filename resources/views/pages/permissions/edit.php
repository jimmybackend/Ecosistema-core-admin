<?php $modules=$contentData['modules']??[]; $permission=$contentData['permission']??null; ?>
<section class="eco-card"><h1>Editar permiso</h1><form method="post" action="/permissions/<?= e((string)$permission['id']) ?>"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>">
<label>Módulo</label><select class="eco-form-control" name="module_id" required><?php foreach($modules as $m): ?><option value="<?= e((string)$m['id']) ?>" <?= ((int)$permission['module_id']===(int)$m['id'])?'selected':'' ?>><?= e((string)$m['name']) ?> (<?= e((string)$m['code']) ?>)</option><?php endforeach; ?></select>
<label>Code</label><input class="eco-form-control" type="text" name="code" value="<?= e((string)$permission['code']) ?>" required>
<label>Name</label><input class="eco-form-control" type="text" name="name" value="<?= e((string)$permission['name']) ?>" required>
<label>Description</label><textarea class="eco-form-control" name="description"><?= e((string)($permission['description']??'')) ?></textarea>
<label>Action</label><input class="eco-form-control" type="text" name="action" value="<?= e((string)$permission['action']) ?>" required>
<label>Resource</label><input class="eco-form-control" type="text" name="resource" value="<?= e((string)$permission['resource']) ?>" required>
<label>Status</label><select class="eco-form-control" name="status" required><?php foreach(['active','inactive','deleted'] as $s): ?><option value="<?= e($s) ?>" <?= ((string)$permission['status']===$s)?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select>
<p><button class="eco-button btn" type="submit">Actualizar</button></p></form>
<form method="post" action="/permissions/<?= e((string)$permission['id']) ?>/status"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>"><label>Cambiar status</label><select class="eco-form-control" name="status"><option>active</option><option>inactive</option><option>deleted</option></select><button class="btn" type="submit">Cambiar</button></form></section>
