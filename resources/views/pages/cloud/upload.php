<?php $options=$contentData['options']??[]; $statusMessage=$contentData['statusMessage']??null; $errorMessage=$contentData['errorMessage']??null; ?>
<section class="eco-card"><h1>Subir archivo Cloud</h1>
<p><span class="eco-badge">Uploads <?= !empty($options['allow_uploads'])?'habilitados':'deshabilitados' ?></span> <span class="eco-badge">Modo <?= !empty($options['s3_enabled'])?'S3':'local' ?></span></p>
<p>Tamaño máximo: <strong><?= e((string)($options['max_upload_mb']??10)) ?> MB</strong></p>
<p>Extensiones permitidas: <strong><?= e(implode(', ', $options['allowed_extensions']??[])) ?></strong></p>
<?php if($statusMessage):?><div class="eco-alert eco-alert--success"><?= e($statusMessage) ?></div><?php endif; ?>
<?php if($errorMessage):?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>
<form method="post" action="/cloud/files/upload" enctype="multipart/form-data"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>">
<input class="eco-form-control" type="file" name="file" required>
<button class="eco-button btn" type="submit">Subir archivo</button> <a class="eco-button btn" href="/cloud">Volver</a>
</form></section>
