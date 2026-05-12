<?php $file=$contentData['file']??null; $downloadsEnabled=(bool)($config['cloud']['allow_downloads']??false); ?>
<div class="eco-card"><h1>Detalle archivo</h1><a class="eco-button btn" href="/cloud">Volver</a><a class="eco-button btn" href="/cloud/folders">Carpetas</a>
<?php if($file===null): ?><div class="eco-alert eco-alert--danger">Archivo no encontrado.</div><?php else: ?>
<?php if(!$downloadsEnabled): ?><div class="eco-alert">Descargas deshabilitadas por configuración.</div><?php endif; ?>
<?php if($downloadsEnabled && (string)($file['status']??'')==='active' && (int)($file['found_in_s3']??0)===0): ?><a class="eco-button btn" href="/cloud/files/<?= e((string)$file['id']) ?>/download">Descargar</a><?php endif; ?>
<ul>
<?php foreach(['original_name','mime_type','extension','size_bytes','checksum_sha256','etag','storage_class','origin_module','origin_table','origin_id','access_type','secure_hint','encrypted','found_in_s3','virus_scan_status','status','uploaded_at','updated_at'] as $k): ?><li><strong><?= e($k) ?>:</strong> <?= e((string)($file[$k]??'')) ?></li><?php endforeach; ?>
</ul><?php endif; ?></div>
