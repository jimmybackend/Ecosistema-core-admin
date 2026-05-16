<?php $modules=$contentData['modules']??[]; $permission=$contentData['permission']??null; ?>
<section class="eco-card"><h1>Editar permiso</h1><form method="post" action="/permissions/<?= e((string)$permission['id']) ?>"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>">
<label>Módulo</label><select class="eco-form-control" name="module_id" required><?php foreach($modules as $m): ?><option value="<?= e((string)$m['id']) ?>" <?= ((int)$permission['module_id']===(int)$m['id'])?'selected':'' ?>><?= e((string)$m['name']) ?> (<?= e((string)$m['code']) ?>)</option><?php endforeach; ?></select>
<label>Code</label><input class="eco-form-control" type="text" name="code" value="<?= e((string)$permission['code']) ?>" required>
<label>Name</label><input class="eco-form-control" type="text" name="name" value="<?= e((string)$permission['name']) ?>" required>
<label>Description</label><textarea class="eco-form-control" name="description"><?= e((string)($permission['description']??'')) ?></textarea>
<p><button class="eco-button btn" type="submit">Actualizar</button></p></form></section>
