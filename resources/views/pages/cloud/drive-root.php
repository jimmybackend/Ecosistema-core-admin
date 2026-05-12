<?php
$root = isset($contentData['root']) && is_array($contentData['root']) ? $contentData['root'] : null;
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
$formatBytes = static function (int $bytes): string {
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    $units = ['KB', 'MB', 'GB', 'TB'];
    $value = $bytes / 1024;
    $unit = 0;
    while ($value >= 1024 && $unit < count($units) - 1) {
        $value /= 1024;
        $unit++;
    }

    return number_format($value, 2) . ' ' . $units[$unit];
};
?>
<div class="eco-card">
  <h1>Raíz de usuario Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
    <a class="eco-button btn" href="/cloud/drive/browse">Abrir navegador Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">Vista segura de metadata en <code>cloud_user_roots</code>. Sin AWS/S3 real y sin cambios de base de datos.</div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <?php if ($root === null): ?>
    <div class="eco-alert">No hay raíz Drive configurada para este usuario todavía.</div>
  <?php else: ?>
    <table class="eco-table">
      <thead><tr><th>Campo</th><th>Valor</th></tr></thead>
      <tbody>
        <tr><td>ID</td><td><?= e((string)$root['id']) ?></td></tr>
        <tr><td>Bucket</td><td><?= e((string)$root['bucket_id']) ?></td></tr>
        <tr><td>Nombre</td><td><?= e((string)$root['display_name']) ?></td></tr>
        <tr><td>Cuota</td><td><?= e($formatBytes((int)$root['quota_bytes'])) ?></td></tr>
        <tr><td>Uso</td><td><?= e($formatBytes((int)$root['used_bytes'])) ?></td></tr>
        <tr><td>Archivos</td><td><?= e((string)$root['file_count']) ?></td></tr>
        <tr><td>Estado</td><td><span class="eco-badge"><?= e((string)$root['status']) ?></span></td></tr>
        <tr><td>Creado</td><td><?= e((string)$root['created_at']) ?></td></tr>
        <tr><td>Actualizado</td><td><?= e((string)$root['updated_at']) ?></td></tr>
      </tbody>
    </table>
  <?php endif; ?>
</div>
