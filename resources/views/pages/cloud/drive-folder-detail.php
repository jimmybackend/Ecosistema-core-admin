<?php
$folder = isset($contentData['folder']) && is_array($contentData['folder']) ? $contentData['folder'] : null;
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Detalle de carpeta Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive/folders">Volver a carpetas Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">
    Esta vista solo expone metadata segura de <code>cloud_folders</code>. No usa AWS/S3 real, no permite navegación y no permite crear/editar/borrar.
  </div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <?php if ($folder !== null): ?>
    <table class="eco-table">
      <thead>
        <tr><th>Campo</th><th>Valor</th></tr>
      </thead>
      <tbody>
        <tr><td>ID</td><td><?= e((string)($folder['id'] ?? '')) ?></td></tr>
        <tr><td>Nombre</td><td><?= e((string)($folder['name'] ?? '')) ?></td></tr>
        <tr><td>Tipo</td><td><span class="eco-badge"><?= e((string)($folder['folder_type'] ?? '')) ?></span></td></tr>
        <tr><td>Acceso</td><td><?= e((string)($folder['access_type'] ?? '')) ?></td></tr>
        <tr><td>Encontrado en S3</td><td><span class="eco-badge"><?= !empty($folder['found_in_s3']) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><td>Es sistema</td><td><span class="eco-badge"><?= !empty($folder['is_system']) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><td>Parent folder ID</td><td><?= e((string)($folder['parent_folder_id'] ?? '')) ?></td></tr>
        <tr><td>Root ID</td><td><?= e((string)($folder['root_id'] ?? '')) ?></td></tr>
        <tr><td>Bucket ID</td><td><?= e((string)($folder['bucket_id'] ?? '')) ?></td></tr>
        <tr><td>Creado</td><td><?= e((string)($folder['created_at'] ?? '')) ?></td></tr>
        <tr><td>Actualizado</td><td><?= e((string)($folder['updated_at'] ?? '')) ?></td></tr>
        <?php if (!empty($folder['deleted_at'])): ?>
          <tr><td>Eliminado</td><td><?= e((string)$folder['deleted_at']) ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
