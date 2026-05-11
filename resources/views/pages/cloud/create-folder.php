<?php $roots=$contentData['roots']??[]; $folders=$contentData['folders']??[]; $errorMessage=$contentData['errorMessage']??null; ?>
<div class="eco-card"><h1>Crear carpeta Cloud</h1><?php if($errorMessage):?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>
<form method="post" action="/cloud/folders"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>">
<label>Root</label><select name="root_id" class="eco-form-control" required><?php foreach($roots as $root):?><option value="<?= e((string)$root['id']) ?>"><?= e((string)$root['display_name']) ?> (<?= e((string)$root['root_prefix']) ?>)</option><?php endforeach;?></select>
<label>Carpeta padre (opcional)</label><select name="parent_folder_id" class="eco-form-control"><option value="">Sin padre</option><?php foreach($folders as $folder):?><option value="<?= e((string)$folder['id']) ?>"><?= e((string)$folder['name']) ?></option><?php endforeach;?></select>
<label>Nombre</label><input class="eco-form-control" type="text" name="name" required>
<label>Tipo</label><select class="eco-form-control" name="folder_type"><option value="custom">custom</option></select>
<label>Acceso</label><select class="eco-form-control" name="access_type"><option value="normal">normal</option><option value="secure">secure</option><option value="unlocked">unlocked</option></select>
<button class="eco-button btn" type="submit">Guardar</button></form></div>
