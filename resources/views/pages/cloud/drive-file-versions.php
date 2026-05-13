<?php
$versions = isset($contentData['versions']) && is_array($contentData['versions']) ? $contentData['versions'] : [];
$fileId = isset($contentData['fileId']) ? (int)$contentData['fileId'] : 0;
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Versiones del archivo Drive</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$fileId) ?>">Detalle del archivo</a>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$fileId) ?>/s3-key-validation">Validación s3_key</a>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$fileId) ?>/signed-url-dry-run">Signed URL dry-run</a>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$fileId) ?>/download">Descarga controlada</a>
    <a class="eco-button btn" href="/cloud/drive/upload-dry-run">Subida dry-run</a>
    <a class="eco-button btn" href="/cloud/drive/summary">Resumen Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">Vista read-only; no hay descarga ni restauración en este PR.</div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <?php if ($versions === []): ?>
    <div class="eco-alert">No hay versiones disponibles para este archivo.</div>
  <?php else: ?>
    <table class="eco-table">
      <thead>
        <tr>
          <th>Versión</th><th>Tamaño</th><th>Checksum</th><th>s3_key</th><th>Estado forma s3_key</th><th>s3_version_id</th><th>s3_version_id_exposed</th><th>Creado por</th><th>Creado en</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($versions as $version): ?>
        <tr>
          <td><?= e((string)($version['version_no'] ?? '')) ?></td>
          <td><?= e((string)($version['size_human'] ?? '')) ?></td>
          <td>
            <?= !empty($version['checksum_sha256_present']) ? 'Sí' : 'No' ?>
            <?php if (!empty($version['checksum_sha256_prefix'])): ?>
              <span class="eco-badge"><?= e((string)$version['checksum_sha256_prefix']) ?></span>
            <?php endif; ?>
          </td>
          <td><span class="eco-badge"><?= !empty($version['has_s3_key']) ? 'Sí' : 'No' ?></span></td>
          <td><span class="eco-badge"><?= e((string)($version['s3_key_shape_status'] ?? 'missing')) ?></span></td>
          <td><span class="eco-badge"><?= !empty($version['has_s3_version_id']) ? 'Sí' : 'No' ?></span></td>
          <td><span class="eco-badge"><?= !empty($version['s3_version_id_exposed']) ? 'true' : 'false' ?></span></td>
          <td><?= e((string)($version['created_by_user_id'] ?? '')) ?></td>
          <td><?= e((string)($version['created_at'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
