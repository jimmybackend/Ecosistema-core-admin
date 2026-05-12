<?php
$files = (array)($contentData['files'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Archivos de Ecosistema Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">
    Este listado usa metadata de <code>cloud_files</code> (DB). No consulta AWS/S3 real, no descarga/sube archivos y no genera signed URLs.
  </div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <table class="eco-table">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>MIME</th>
        <th>Tamaño (bytes)</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th>Origen</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($files === []): ?>
        <tr><td colspan="6">Sin archivos para este usuario/tenant.</td></tr>
      <?php else: ?>
        <?php foreach ($files as $file): ?>
          <tr>
            <td><?= e((string)($file['original_name'] ?? '')) ?></td>
            <td><?= e((string)($file['mime_type'] ?? '')) ?></td>
            <td><?= e((string)($file['size_bytes'] ?? '0')) ?></td>
            <td><span class="eco-badge"><?= e((string)($file['status'] ?? '')) ?></span></td>
            <td><?= e((string)($file['uploaded_at'] ?? '')) ?></td>
            <td><?= e((string)($file['origin_module'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
