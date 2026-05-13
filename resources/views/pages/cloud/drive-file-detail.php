<?php
$file = isset($contentData['file']) && is_array($contentData['file']) ? $contentData['file'] : null;
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;

$formatBytes = static function (int $bytes): string {
    if ($bytes < 1024) {
        return $bytes . ' B';
    }

    $units = ['KB', 'MB', 'GB', 'TB'];
    $value = (float)$bytes;
    foreach ($units as $unit) {
        $value /= 1024;
        if ($value < 1024 || $unit === 'TB') {
            return number_format($value, 2) . ' ' . $unit;
        }
    }

    return $bytes . ' B';
};
?>
<div class="eco-card">
  <h1>Detalle de archivo Ecosistema Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive/files">Volver al listado</a>
    <?php if ($file !== null): ?><a class="eco-button btn" href="/cloud/drive/files/<?= e((string)($file['id'] ?? '0')) ?>/versions">Ver versiones</a><?php endif; ?>
  </p>

  <div class="eco-alert eco-alert--warning">
    Esta vista muestra únicamente metadata segura de <code>cloud_files</code>. No consulta AWS/S3 real, no genera signed URLs y no habilita descargas/subidas.
  </div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <?php if ($file !== null): ?>
    <table class="eco-table">
      <tbody>
        <tr><th>ID</th><td><?= e((string)($file['id'] ?? '')) ?></td></tr>
        <tr><th>Nombre original</th><td><?= e((string)($file['original_name'] ?? '')) ?></td></tr>
        <tr><th>MIME</th><td><?= e((string)($file['mime_type'] ?? '')) ?></td></tr>
        <tr><th>Extensión</th><td><?= e((string)($file['extension'] ?? '')) ?></td></tr>
        <tr><th>Tamaño</th><td><?= e($formatBytes((int)($file['size_bytes'] ?? 0))) ?> (<?= e((string)($file['size_bytes'] ?? 0)) ?> bytes)</td></tr>
        <tr><th>Estado</th><td><span class="eco-badge"><?= e((string)($file['status'] ?? '')) ?></span></td></tr>
        <tr><th>Estado antivirus</th><td><?= e((string)($file['virus_scan_status'] ?? '')) ?></td></tr>
        <tr><th>Tipo de acceso</th><td><?= e((string)($file['access_type'] ?? '')) ?></td></tr>
        <tr><th>Encriptado</th><td><span class="eco-badge"><?= !empty($file['encrypted']) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><th>Encontrado en S3</th><td><span class="eco-badge"><?= !empty($file['found_in_s3']) ? 'Sí' : 'No' ?></span></td></tr>
        <tr><th>Módulo origen</th><td><?= e((string)($file['origin_module'] ?? '')) ?></td></tr>
        <tr><th>Tabla origen</th><td><?= e((string)($file['origin_table'] ?? '')) ?></td></tr>
        <tr><th>ID origen</th><td><?= e((string)($file['origin_id'] ?? '')) ?></td></tr>
        <tr><th>Subido por usuario</th><td><?= e((string)($file['uploaded_by_user_id'] ?? '')) ?></td></tr>
        <tr><th>Subido en</th><td><?= e((string)($file['uploaded_at'] ?? '')) ?></td></tr>
        <tr><th>Actualizado en</th><td><?= e((string)($file['updated_at'] ?? '')) ?></td></tr>
        <?php if (!empty($file['deleted_at'])): ?>
          <tr><th>Eliminado en</th><td><?= e((string)$file['deleted_at']) ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
