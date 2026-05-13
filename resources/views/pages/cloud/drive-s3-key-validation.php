<?php
$validation = isset($contentData['validation']) && is_array($contentData['validation']) ? $contentData['validation'] : null;
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Validación segura de s3_key (dry-run)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive/files">Volver al listado</a>
  </p>

  <div class="eco-alert eco-alert--warning">Validación dry-run: no hay descarga real.</div>
  <div class="eco-alert eco-alert--info">s3_key no se muestra por seguridad.</div>

  <?php if ($errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <?php if ($validation !== null): ?>
    <table class="eco-table">
      <tbody>
        <tr><th>File ID</th><td><?= e((string)($validation['file_id'] ?? '')) ?></td></tr>
        <tr><th>Bucket ID</th><td><?= e((string)($validation['bucket_id'] ?? '')) ?></td></tr>
        <tr><th>Status</th><td><span class="eco-badge"><?= e((string)($validation['status'] ?? '')) ?></span></td></tr>
        <tr><th>Found in S3 (DB)</th><td><span class="eco-badge"><?= !empty($validation['found_in_s3']) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><th>Has s3_key</th><td><span class="eco-badge"><?= !empty($validation['has_s3_key']) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><th>Key shape</th><td><span class="eco-badge"><?= e((string)($validation['key_shape_status'] ?? '')) ?></span></td></tr>
        <tr><th>Validation</th><td><span class="eco-badge"><?= e((string)($validation['validation_status'] ?? '')) ?></span></td></tr>
        <tr><th>Mode</th><td><?= e((string)($validation['mode'] ?? '')) ?></td></tr>
        <tr><th>AWS connection</th><td><?= !empty($validation['aws_connection']) ? 'true' : 'false' ?></td></tr>
        <tr><th>Signed URL generated</th><td><?= !empty($validation['signed_url_generated']) ? 'true' : 'false' ?></td></tr>
        <tr><th>Download enabled</th><td><?= !empty($validation['download_enabled']) ? 'true' : 'false' ?></td></tr>
      </tbody>
    </table>

    <h3>Warnings seguros</h3>
    <ul>
      <?php foreach ((array)($validation['warnings'] ?? []) as $warning): ?>
        <li><?= e((string)$warning) ?></li>
      <?php endforeach; ?>
    </ul>

    <h3>Operaciones bloqueadas / razones</h3>
    <ul>
      <?php foreach ((array)($validation['blocked_reasons'] ?? []) as $reason): ?>
        <li><?= e((string)$reason) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
