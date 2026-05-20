<?php
$status = (array)($contentData['status'] ?? []);
$capabilities = (array)($contentData['capabilities'] ?? []);

$capabilityMeta = [
    'read_metadata' => ['type' => 'read-only', 'route' => '/cloud/drive/files', 'permission' => 'cloud.view', 'note' => 'Metadata DB segura; sin AWS/S3.'],
    'read_file_detail' => ['type' => 'read-only', 'route' => '/cloud/drive/files/{id}', 'permission' => 'cloud.view', 'note' => 'Detalle seguro sin campos sensibles.'],
    'read_folders_metadata' => ['type' => 'read-only', 'route' => '/cloud/drive/folders', 'permission' => 'cloud.view', 'note' => 'Listado de carpetas por DB.'],
    'read_folder_detail' => ['type' => 'read-only', 'route' => '/cloud/drive/folders/{id}', 'permission' => 'cloud.view', 'note' => 'Sin exponer prefix/rutas internas.'],
    'read_folder_navigation' => ['type' => 'read-only', 'route' => '/cloud/drive/browse', 'permission' => 'cloud.view', 'note' => 'Navegación por metadata.'],
    'read_user_root' => ['type' => 'read-only', 'route' => '/cloud/drive/root', 'permission' => 'cloud.view', 'note' => 'Resumen de raíz sin root_prefix.'],
    'read_buckets_metadata' => ['type' => 'read-only', 'route' => '/cloud/drive/buckets', 'permission' => 'cloud.view', 'note' => 'Solo metadata de buckets.'],
    'read_drive_summary' => ['type' => 'read-only', 'route' => '/cloud/drive/summary', 'permission' => 'cloud.view', 'note' => 'Resumen operativo seguro.'],
    'read_access_policy' => ['type' => 'read-only', 'route' => '/cloud/drive/access', 'permission' => 'cloud.view', 'note' => 'Política de acceso visible.'],
    'read_only_audit' => ['type' => 'read-only', 'route' => '/cloud/drive/access', 'permission' => 'cloud.view', 'note' => 'Auditoría read-only drive.*.'],
    'download_contract' => ['type' => 'contract', 'route' => '/cloud/drive/download-contract', 'permission' => 'cloud.view', 'note' => 'Contrato sin descarga real.'],
    'safe_s3_key_validation' => ['type' => 'dry-run', 'route' => '/cloud/drive/files/{id}/s3-key-validation', 'permission' => 'cloud.view', 'note' => 'Valida sin exponer s3_key.'],
    'signed_url_dry_run' => ['type' => 'dry-run', 'route' => '/cloud/drive/files/{id}/signed-url-dry-run', 'permission' => 'cloud.view', 'note' => 'No genera URL real.'],
    'aws_s3_config_prepared' => ['type' => 'contract', 'route' => '/cloud/drive/aws-config', 'permission' => 'cloud.view', 'note' => 'Configuración preparada y apagada.'],
    'controlled_download' => ['type' => 'controlled', 'route' => '/cloud/drive/files/{id}/download', 'permission' => 'cloud.view', 'note' => 'Descarga real controlada habilitada por flags explícitas.'],
    'upload_dry_run' => ['type' => 'dry-run', 'route' => '/cloud/drive/upload-dry-run', 'permission' => 'cloud.view', 'note' => 'Simulación de subida.'],
    'controlled_upload' => ['type' => 'controlled', 'route' => '/cloud/drive/upload', 'permission' => 'cloud.view', 'note' => 'Subida real controlada habilitada por flags explícitas.'],
    'read_file_versions' => ['type' => 'read-only', 'route' => '/cloud/drive/files/{id}/versions', 'permission' => 'cloud.view', 'note' => 'Versiones read-only.'],
    'share_contract' => ['type' => 'contract', 'route' => '/cloud/drive/files/{id}/share-contract', 'permission' => 'cloud.view', 'note' => 'Sin share real.'],
    'access_logs_read' => ['type' => 'read-only', 'route' => '/cloud/drive/access-logs', 'permission' => 'cloud.view', 'note' => 'Logs saneados.'],
    'storage_usage_read' => ['type' => 'read-only', 'route' => '/cloud/drive/storage-usage', 'permission' => 'cloud.view', 'note' => 'Uso consolidado read-only.'],
    'repair_jobs_read' => ['type' => 'read-only', 'route' => '/cloud/drive/repair-jobs', 'permission' => 'cloud.view', 'note' => 'Visibilidad de jobs.'],
    'repair_logs_read' => ['type' => 'read-only', 'route' => '/cloud/drive/repair-jobs', 'permission' => 'cloud.view', 'note' => 'Logs de reparación sin keys.'],
];

$quickLinks = [
    '/cloud/drive/files', '/cloud/drive/folders', '/cloud/drive/browse', '/cloud/drive/root', '/cloud/drive/buckets', '/cloud/drive/summary', '/cloud/drive/access', '/cloud/drive/download-contract', '/cloud/drive/aws-config', '/cloud/drive/upload-dry-run', '/cloud/drive/upload', '/cloud/drive/access-logs', '/cloud/drive/storage-usage', '/cloud/drive/repair-jobs',
];

$statusMatrix = [
    'modo actual' => (string)($status['mode'] ?? 'contract'),
    'read_only disponible' => !empty($status['contract_only']) || !empty($status['read_only']) || true,
    'aws_s3 conectado' => (bool)($status['aws_connected'] ?? false),
    'remote_calls' => (bool)($status['remote_calls'] ?? false),
    'db_writes' => (bool)($status['db_writes'] ?? false),
    'remote_uploads' => (bool)($status['remote_uploads'] ?? false),
    'remote_downloads' => (bool)($status['remote_downloads'] ?? false),
    'signed_urls' => (bool)($status['signed_urls'] ?? false),
];
?>
<div class="eco-card">
  <h1>Ecosistema Drive · Panel operativo</h1>
  <p>
    <a class="eco-button btn" href="/cloud">Volver a Cloud</a>
    <a class="eco-button btn" href="/cloud/drive/summary">Resumen Drive</a>
    <a class="eco-button btn" href="/cloud/drive/access">Política de acceso</a>
  </p>

  <div class="eco-alert eco-alert--warning">Panel consolidado en modo seguro: contract/dry-run/controlled, sin AWS/S3 real, sin llamadas remotas y sin cambios de DB fuera de flujos controlados.</div>

  <h2>A) Estado general</h2>
  <table class="eco-table">
    <thead><tr><th>Clave</th><th>Valor</th></tr></thead>
    <tbody>
      <?php foreach ($statusMatrix as $key => $value): ?>
        <tr>
          <td><?= e((string)$key) ?></td>
          <td><?= is_bool($value) ? '<span class="eco-badge">' . ($value ? 'true' : 'false') . '</span>' : e((string)$value) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h2>B) Capacidades disponibles</h2>
  <table class="eco-table">
    <thead><tr><th>capability</th><th>estado</th><th>tipo</th><th>ruta relacionada</th><th>permiso esperado</th><th>observación segura</th></tr></thead>
    <tbody>
      <?php foreach ($capabilityMeta as $name => $meta): $cap = (array)($capabilities[$name] ?? []); ?>
        <tr>
          <td><?= e($name) ?></td>
          <td><span class="eco-badge"><?= !empty($cap['enabled']) ? 'habilitada' : 'bloqueada' ?></span></td>
          <td><?= e((string)$meta['type']) ?></td>
          <td><code><?= e((string)$meta['route']) ?></code></td>
          <td><?= e((string)$meta['permission']) ?></td>
          <td><?= e((string)$meta['note']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h2>C) Accesos rápidos</h2>
  <p>
    <?php foreach ($quickLinks as $link): ?>
      <a class="eco-button btn" href="<?= e($link) ?>"><?= e($link) ?></a>
    <?php endforeach; ?>
  </p>

  <h2>D) Operaciones bloqueadas</h2>
  <ul>
    <li>no delete real</li><li>no restore real</li><li>no repair real</li><li>no public share real</li><li>no public links</li><li>no signed URLs reales</li><li>no descarga real si las flags no están completas</li><li>no subida real si las flags no están completas</li><li>no escaneo S3</li><li>no listado S3</li><li>no exposición de s3_key/stored_name/prefix/config_json/metadata_json crudos</li>
  </ul>

  <h2>E) Checklist rápido VM</h2>
  <ul>
    <li><code>composer dump-autoload</code></li>
    <li><code>composer smoke</code></li>
    <li><code>sudo systemctl restart php8.5-fpm</code></li>
    <li><code>sudo systemctl restart nginx</code></li>
    <li>validar <code>/login</code></li>
    <li>validar <code>/dashboard</code></li>
    <li>validar <code>/cloud/drive</code></li>
  </ul>
</div>
