<?php
$browser = (array)($contentData['browser'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
$current = isset($browser['current_folder']) && is_array($browser['current_folder']) ? $browser['current_folder'] : null;
$parent = isset($browser['parent_folder']) && is_array($browser['parent_folder']) ? $browser['parent_folder'] : null;
$folders = (array)($browser['child_folders'] ?? []);
$files = (array)($browser['files'] ?? []);
$formatBytes = static fn (int $bytes): string => $bytes >= 1024 ? number_format($bytes / 1024, 2) . ' KB' : $bytes . ' B';
?>
<div class="eco-card">
  <h1>Navegador Drive</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
    <a class="eco-button btn" href="/cloud/drive/folders">Volver a carpetas Drive</a>
    <a class="eco-button btn" href="/cloud/drive/root">Ver raíz Drive</a>
    <?php if ($parent !== null): ?>
      <a class="eco-button btn" href="/cloud/drive/browse?folder_id=<?= e((string)$parent['id']) ?>">Volver a carpeta padre</a>
    <?php endif; ?>
  </p>

  <div class="eco-alert eco-alert--warning">Modo read-only. Metadata desde DB (cloud_folders/cloud_files), sin AWS/S3 real, sin descargas/subidas.</div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>

  <p><strong>Carpeta actual:</strong> <?= $current !== null ? e((string)$current['name']) . ' (#' . e((string)$current['id']) . ')' : 'Raíz (parent_folder_id IS NULL)' ?></p>

  <h2>Carpetas hijas</h2>
  <table class="eco-table">
    <thead><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Acceso</th><th>S3</th><th>Sistema</th><th>Creado</th><th>Actualizado</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php if ($folders === []): ?><tr><td colspan="9">Sin carpetas hijas.</td></tr><?php endif; ?>
      <?php foreach ($folders as $folder): ?>
        <tr>
          <td><?= e((string)$folder['id']) ?></td><td><?= e((string)$folder['name']) ?></td><td><span class="eco-badge"><?= e((string)$folder['folder_type']) ?></span></td>
          <td><?= e((string)$folder['access_type']) ?></td><td><span class="eco-badge"><?= !empty($folder['found_in_s3']) ? 'Sí' : 'No' ?></span></td><td><span class="eco-badge"><?= !empty($folder['is_system']) ? 'Sí' : 'No' ?></span></td>
          <td><?= e((string)$folder['created_at']) ?></td><td><?= e((string)$folder['updated_at']) ?></td>
          <td><a class="eco-button btn" href="/cloud/drive/browse?folder_id=<?= e((string)$folder['id']) ?>">Entrar</a> <a class="eco-button btn" href="/cloud/drive/folders/<?= e((string)$folder['id']) ?>">Ver detalle</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h2>Archivos de la carpeta</h2>
  <table class="eco-table">
    <thead><tr><th>ID</th><th>Nombre</th><th>MIME</th><th>Ext</th><th>Tamaño</th><th>Estado</th><th>Antivirus</th><th>Acceso</th><th>Encriptado</th><th>S3</th><th>Subido</th><th>Actualizado</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php if ($files === []): ?><tr><td colspan="13">Sin archivos en esta carpeta.</td></tr><?php endif; ?>
      <?php foreach ($files as $file): ?>
        <tr>
          <td><?= e((string)$file['id']) ?></td><td><?= e((string)$file['original_name']) ?></td><td><?= e((string)$file['mime_type']) ?></td><td><?= e((string)$file['extension']) ?></td>
          <td><?= e($formatBytes((int)$file['size_bytes'])) ?></td><td><span class="eco-badge"><?= e((string)$file['status']) ?></span></td><td><?= e((string)$file['virus_scan_status']) ?></td><td><?= e((string)$file['access_type']) ?></td>
          <td><span class="eco-badge"><?= !empty($file['encrypted']) ? 'Sí' : 'No' ?></span></td><td><span class="eco-badge"><?= !empty($file['found_in_s3']) ? 'Sí' : 'No' ?></span></td>
          <td><?= e((string)$file['uploaded_at']) ?></td><td><?= e((string)$file['updated_at']) ?></td>
          <td><a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$file['id']) ?>">Ver detalle</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
