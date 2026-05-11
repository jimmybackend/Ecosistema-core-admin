<?php declare(strict_types=1); $module=is_array($module??null)?$module:[]; $errorMessage=is_string($errorMessage??null)?$errorMessage:null; ?>
<section><h1>Editar módulo</h1><?php if($errorMessage!==null):?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<article class="eco-card"><form method="post" action="/modules/<?= e((string)($module['id']??'0')) ?>"><input type="hidden" name="_csrf" value="<?= e((string)($csrfToken??'')) ?>">
<label>Code <input class="eco-form-control" type="text" name="code" required value="<?= e((string)($module['code']??'')) ?>"></label>
<label>Name <input class="eco-form-control" type="text" name="name" required value="<?= e((string)($module['name']??'')) ?>"></label>
<label>Description <textarea class="eco-form-control" name="description"><?= e((string)($module['description']??'')) ?></textarea></label>
<label>Table prefix <input class="eco-form-control" type="text" name="table_prefix" value="<?= e((string)($module['table_prefix']??'')) ?>"></label>
<label>Billable <select class="eco-form-control" name="is_billable" required><option value="1" <?= ((string)($module['is_billable']??'0')==='1')?'selected':'' ?>>1</option><option value="0" <?= ((string)($module['is_billable']??'0')==='0')?'selected':'' ?>>0</option></select></label>
<label>Core <select class="eco-form-control" name="is_core" required><option value="0" <?= ((string)($module['is_core']??'0')==='0')?'selected':'' ?>>0</option><option value="1" <?= ((string)($module['is_core']??'0')==='1')?'selected':'' ?>>1</option></select></label>
<label>Status <select class="eco-form-control" name="status" required><?php foreach(['active','inactive','deprecated'] as $status): ?><option value="<?= e($status) ?>" <?= (($module['status']??'')===$status)?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
<button class="eco-button btn" type="submit">Actualizar</button></form></article></section>
