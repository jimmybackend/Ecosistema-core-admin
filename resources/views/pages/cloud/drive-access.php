<?php
$policyDescription = (array)($contentData['policyDescription'] ?? []);
$blocked = (array)($policyDescription['blocked_operations'] ?? []);
?>
<div class="eco-card">
  <h1>Política de acceso Drive</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
    <a class="eco-button btn" href="/cloud/drive/summary">Resumen Drive</a>
    <a class="eco-button btn" href="/cloud/drive/browse">Navegar Drive</a>
    <a class="eco-button btn" href="/cloud/drive/files">Archivos Drive</a>
    <a class="eco-button btn" href="/cloud/drive/folders">Carpetas Drive</a>
    <a class="eco-button btn" href="/cloud/drive/root">Raíz Drive</a>
    <a class="eco-button btn" href="/cloud/drive/buckets">Buckets Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">Modo read-only / contract / dry-run. Sin AWS/S3 real.</div>

  <table class="eco-table">
    <thead><tr><th>Regla</th><th>Detalle</th></tr></thead>
    <tbody>
      <tr><td>Permiso requerido</td><td><span class="eco-badge"><?= e((string)($policyDescription['required_permission'] ?? 'cloud.view')) ?></span></td></tr>
      <tr><td>Permiso administrativo futuro</td><td><span class="eco-badge"><?= e((string)($policyDescription['future_admin_permission'] ?? 'cloud.manage')) ?></span></td></tr>
      <tr><td>Regla tenant_id</td><td><?= e((string)($policyDescription['tenant_boundary'] ?? 'tenant_id obligatorio')) ?></td></tr>
      <tr><td>Regla user_id</td><td><?= e((string)($policyDescription['user_boundary'] ?? 'user_id obligatorio cuando aplica')) ?></td></tr>
      <tr><td>access_type</td><td><?= e((string)($policyDescription['access_type_rule'] ?? 'No habilita acceso público')) ?></td></tr>
    </tbody>
  </table>

  <h2>Operaciones bloqueadas</h2>
  <table class="eco-table">
    <thead><tr><th>Operación</th><th>Estado</th></tr></thead>
    <tbody>
      <tr><td>uploads</td><td><span class="eco-badge"><?= !empty($blocked['uploads']) ? 'bloqueado' : 'habilitado' ?></span></td></tr>
      <tr><td>downloads</td><td><span class="eco-badge"><?= !empty($blocked['downloads']) ? 'bloqueado' : 'habilitado' ?></span></td></tr>
      <tr><td>signed URLs</td><td><span class="eco-badge"><?= !empty($blocked['signed_urls']) ? 'bloqueado' : 'habilitado' ?></span></td></tr>
      <tr><td>AWS/S3 real</td><td><span class="eco-badge"><?= !empty($blocked['aws_s3_real']) ? 'bloqueado' : 'habilitado' ?></span></td></tr>
      <tr><td>edición/borrado</td><td><span class="eco-badge"><?= !empty($blocked['edition_delete']) ? 'bloqueado' : 'habilitado' ?></span></td></tr>
    </tbody>
  </table>
</div>
