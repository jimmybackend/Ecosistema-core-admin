<?php $tenants=$contentData['tenants']??[]; $errorMessage=$contentData['errorMessage']??null; ?>
<section class="eco-card"><h1>Crear rol</h1><?php if($errorMessage): ?><div class="eco-alert"><?= e($errorMessage) ?></div><?php endif; ?>
<form method="post" action="/roles"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>">
<label>Tenant</label><select class="eco-form-control" name="tenant_id"><option value="">Global / sin tenant</option><?php foreach($tenants as $tenant): ?><option value="<?= e((string)$tenant['id']) ?>"><?= e($tenant['name'].' ('.$tenant['slug'].')') ?></option><?php endforeach; ?></select>
<label>Code</label><input class="eco-form-control" name="code" required>
<label>Name</label><input class="eco-form-control" name="name" required>
<label>Description</label><textarea class="eco-form-control" name="description"></textarea>
<label>Scope</label><select class="eco-form-control" name="scope" required><option value="global">global</option><option value="tenant">tenant</option><option value="module">module</option></select>
<label>is_system</label><select class="eco-form-control" name="is_system" required><option value="0">0</option><option value="1">1</option></select>
<label>Status</label><select class="eco-form-control" name="status" required><option value="active">active</option><option value="inactive">inactive</option><option value="deleted">deleted</option></select>
<button class="eco-button btn" type="submit">Guardar</button></form></section>
