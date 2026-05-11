<?php $storage = $contentData['storage'] ?? []; ?>
<div class="eco-card">
    <h1>Configuración S3</h1>
    <p>Estado informativo de Cloud/S3 para control administrativo.</p>
    <div class="eco-alert eco-alert--warning">
        Las descargas/subidas reales están deshabilitadas en este PR.
    </div>
    <?php if (!(bool)($storage['is_valid'] ?? false)): ?>
        <div class="eco-alert eco-alert--danger">
            Configuración incompleta. Variables faltantes: <?= e(implode(', ', (array)($storage['missing_fields'] ?? []))) ?>.
        </div>
    <?php endif; ?>
    <table class="eco-table">
        <tbody>
        <tr><th>Disco</th><td><?= e((string)($storage['disk'] ?? 's3')) ?></td></tr>
        <tr><th>S3 habilitado</th><td><span class="eco-badge"><?= (bool)($storage['s3_enabled'] ?? false) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><th>Descargas habilitadas</th><td><span class="eco-badge"><?= (bool)($storage['allow_downloads'] ?? false) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><th>Subidas habilitadas</th><td><span class="eco-badge"><?= (bool)($storage['allow_uploads'] ?? false) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><th>AWS Access Key ID</th><td><?= e((string)($storage['access_key_id_masked'] ?? '(vacío)')) ?></td></tr>
        <tr><th>Región</th><td><?= e((string)($storage['region'] ?? '')) ?></td></tr>
        <tr><th>Bucket</th><td><?= e((string)($storage['bucket'] ?? '')) ?></td></tr>
        <tr><th>Endpoint</th><td><?= e((string)($storage['endpoint'] ?? '')) ?></td></tr>
        <tr><th>Path style endpoint</th><td><span class="eco-badge"><?= (bool)($storage['use_path_style_endpoint'] ?? false) ? 'Sí' : 'No' ?></span></td></tr>
        </tbody>
    </table>
    <p><a class="eco-button btn" href="/cloud">Volver a Cloud</a></p>
</div>
