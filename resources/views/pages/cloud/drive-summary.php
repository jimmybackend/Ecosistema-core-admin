<?php
$summary = (array)($contentData['summary'] ?? []);
$rootSummary = isset($summary['root_summary']) && is_array($summary['root_summary']) ? $summary['root_summary'] : null;
$warnings = isset($summary['warnings']) && is_array($summary['warnings']) ? $summary['warnings'] : [];
?>
<div class="eco-card">
  <h1>Resumen operativo Drive</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Panel operativo Drive</a>
    <a class="eco-button btn" href="/cloud/drive/files">Ver archivos Drive</a>
    <a class="eco-button btn" href="/cloud/drive/download-contract">Contrato descarga Drive</a>
    <a class="eco-button btn" href="/cloud/drive/folders">Ver carpetas Drive</a>
    <a class="eco-button btn" href="/cloud/drive/browse">Navegar Drive</a>
    <a class="eco-button btn" href="/cloud/drive/root">Ver raíz Drive</a>
    <a class="eco-button btn" href="/cloud/drive/buckets">Ver buckets Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">
    Modo read-only / contract / dry-run. Sin AWS/S3 real, sin llamadas remotas y sin cambios de base de datos.
  </div>

  <?php foreach ($warnings as $warning): ?>
    <div class="eco-alert"><?= e((string)$warning) ?></div>
  <?php endforeach; ?>

  <table class="eco-table">
    <thead><tr><th>Métrica</th><th>Valor</th></tr></thead>
    <tbody>
      <tr><td>Archivos visibles</td><td><span class="eco-badge"><?= e((string)($summary['file_count'] ?? 0)) ?></span></td></tr>
      <tr><td>Carpetas visibles</td><td><span class="eco-badge"><?= e((string)($summary['folder_count'] ?? 0)) ?></span></td></tr>
      <tr><td>Buckets informativos</td><td><span class="eco-badge"><?= e((string)($summary['bucket_count'] ?? 0)) ?></span></td></tr>
      <tr><td>Raíz configurada</td><td><span class="eco-badge"><?= $rootSummary !== null ? 'sí' : 'no' ?></span></td></tr>
      <tr><td>Cuota</td><td><?= isset($summary['quota_bytes']) ? e((string)$summary['quota_bytes']).' bytes' : 'No disponible' ?></td></tr>
      <tr><td>Uso</td><td><?= isset($summary['used_bytes']) ? e((string)$summary['used_bytes']).' bytes' : 'No disponible' ?></td></tr>
      <tr><td>Modo</td><td><?= e((string)($summary['mode'] ?? 'contract/dry-run')) ?></td></tr>
      <tr><td>Read-only</td><td><span class="eco-badge"><?= !empty($summary['read_only']) ? 'true' : 'false' ?></span></td></tr>
    </tbody>
  </table>
</div>
