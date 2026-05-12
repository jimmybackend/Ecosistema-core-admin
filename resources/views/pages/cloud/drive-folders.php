<?php
$folders = (array)($contentData['folders'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Carpetas de Ecosistema Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
    <a class="eco-button btn" href="/cloud/drive/files">Ver archivos Drive</a>
    <a class="eco-button btn" href="/cloud/drive/browse">Navegar Drive</a>
    <a class="eco-button btn" href="/cloud/drive/root">Ver raíz Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">
    Este listado usa metadata de <code>cloud_folders</code> (DB). No consulta AWS/S3 real y no permite crear/editar/borrar carpetas.
  </div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <table class="eco-table">
    <thead>
      <tr>
        <th>ID</th><th>Nombre</th><th>Tipo</th><th>Acceso</th><th>Encontrado en S3</th><th>Es sistema</th>
        <th>Parent</th><th>Root</th><th>Bucket</th><th>Creado</th><th>Actualizado</th><th>Eliminado</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($folders === []): ?>
        <tr><td colspan="13">Sin carpetas para este usuario/tenant.</td></tr>
      <?php else: ?>
        <?php foreach ($folders as $folder): ?>
          <tr>
            <td><?= e((string)($folder['id'] ?? '')) ?></td>
            <td><?= e((string)($folder['name'] ?? '')) ?></td>
            <td><span class="eco-badge"><?= e((string)($folder['folder_type'] ?? '')) ?></span></td>
            <td><?= e((string)($folder['access_type'] ?? '')) ?></td>
            <td><span class="eco-badge"><?= !empty($folder['found_in_s3']) ? 'Sí' : 'No' ?></span></td>
            <td><span class="eco-badge"><?= !empty($folder['is_system']) ? 'Sí' : 'No' ?></span></td>
            <td><?= e((string)($folder['parent_folder_id'] ?? '')) ?></td>
            <td><?= e((string)($folder['root_id'] ?? '')) ?></td>
            <td><?= e((string)($folder['bucket_id'] ?? '')) ?></td>
            <td><?= e((string)($folder['created_at'] ?? '')) ?></td>
            <td><?= e((string)($folder['updated_at'] ?? '')) ?></td>
            <td><?= e((string)($folder['deleted_at'] ?? '')) ?></td>
            <td><a class="eco-button btn" href="/cloud/drive/folders/<?= e((string)($folder['id'] ?? '0')) ?>">Ver detalle</a></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
