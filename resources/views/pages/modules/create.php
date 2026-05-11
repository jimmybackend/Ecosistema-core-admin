<?php declare(strict_types=1); $errorMessage=is_string($errorMessage??null)?$errorMessage:null; ?>
<section><h1>Crear módulo</h1><?php if($errorMessage!==null):?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<article class="eco-card"><form method="post" action="/modules"><input type="hidden" name="_csrf" value="<?= e((string)($csrfToken??'')) ?>">
<label>Code <input class="eco-form-control" type="text" name="code" required></label>
<label>Name <input class="eco-form-control" type="text" name="name" required></label>
<label>Description <textarea class="eco-form-control" name="description"></textarea></label>
<label>Table prefix <input class="eco-form-control" type="text" name="table_prefix"></label>
<label>Billable <select class="eco-form-control" name="is_billable" required><option value="1">1</option><option value="0">0</option></select></label>
<label>Core <select class="eco-form-control" name="is_core" required><option value="0">0</option><option value="1">1</option></select></label>
<label>Status <select class="eco-form-control" name="status" required><option value="active">active</option><option value="inactive">inactive</option><option value="deprecated">deprecated</option></select></label>
<button class="eco-button btn" type="submit">Guardar</button></form></article></section>
