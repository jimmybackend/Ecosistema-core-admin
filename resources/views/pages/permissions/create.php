<?php $modules=$contentData['modules']??[]; ?>
<section class="eco-card"><h1>Crear permiso</h1><form method="post" action="/permissions"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>">
<label>Módulo</label><select class="eco-form-control" name="module_id" required><?php foreach($modules as $m): ?><option value="<?= e((string)$m['id']) ?>"><?= e((string)$m['name']) ?> (<?= e((string)$m['code']) ?>)</option><?php endforeach; ?></select>
<label>Code</label><input class="eco-form-control" type="text" name="code" required>
<label>Name</label><input class="eco-form-control" type="text" name="name" required>
<label>Description</label><textarea class="eco-form-control" name="description"></textarea>
<label>Action</label><input class="eco-form-control" type="text" name="action" required>
<label>Resource</label><input class="eco-form-control" type="text" name="resource" required>
<label>Status</label><select class="eco-form-control" name="status" required><option>active</option><option>inactive</option><option>deleted</option></select>
<p><button class="eco-button btn" type="submit">Guardar</button></p></form></section>
