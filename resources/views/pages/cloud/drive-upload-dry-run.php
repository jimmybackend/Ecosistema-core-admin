<?php
$uploadDryRun = isset($contentData['uploadDryRun']) && is_array($contentData['uploadDryRun']) ? $contentData['uploadDryRun'] : [];
?>
<div class="eco-card">
  <h1>Subida S3 dry-run</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive/aws-config">AWS config</a>
    <a class="eco-button btn" href="/cloud/drive/download-contract">Contrato descarga</a>
    <a class="eco-button btn" href="/cloud/drive/summary">Resumen</a>
    <a class="eco-button btn" href="/cloud/drive">Drive</a>
  </p>
  <div class="eco-alert eco-alert--warning">No se subió ningún archivo.</div>
  <div class="eco-alert eco-alert--info">AWS/S3 sigue apagado.</div>
  <div class="eco-alert eco-alert--info">No hay escritura en DB.</div>
  <div class="eco-alert eco-alert--info">No hay escritura en storage.</div>

  <table class="eco-table"><tbody>
    <tr><th>Mode</th><td><?= e((string)($uploadDryRun['mode'] ?? 'dry-run')) ?></td></tr>
    <tr><th>upload_enabled</th><td><?= !empty($uploadDryRun['upload_enabled']) ? 'true' : 'false' ?></td></tr>
    <tr><th>remote_upload_attempted</th><td><?= !empty($uploadDryRun['remote_upload_attempted']) ? 'true' : 'false' ?></td></tr>
    <tr><th>remote_upload_allowed</th><td><?= !empty($uploadDryRun['remote_upload_allowed']) ? 'true' : 'false' ?></td></tr>
    <tr><th>remote_uploads</th><td>false</td></tr>
    <tr><th>remote_calls</th><td>false</td></tr>
    <tr><th>aws_connection</th><td><?= !empty($uploadDryRun['aws_connection']) ? 'true' : 'false' ?></td></tr>
    <tr><th>storage_write</th><td><?= !empty($uploadDryRun['storage_write']) ? 'true' : 'false' ?></td></tr>
    <tr><th>db_write</th><td><?= !empty($uploadDryRun['db_write']) ? 'true' : 'false' ?></td></tr>
    <tr><th>max_upload_mb_preview</th><td><?= e((string)($uploadDryRun['max_upload_mb_preview'] ?? '10')) ?></td></tr>
    <tr><th>allowed_extensions_preview</th><td><?= e(implode(', ', (array)($uploadDryRun['allowed_extensions_preview'] ?? []))) ?></td></tr>
    <tr><th>eligibility_status</th><td><?= e((string)($uploadDryRun['eligibility_status'] ?? 'blocked')) ?></td></tr>
    <tr><th>next_step</th><td><?= e((string)($uploadDryRun['next_step'] ?? '')) ?></td></tr>
  </tbody></table>

  <h3>Validaciones futuras requeridas</h3>
  <ul><?php foreach ((array)($uploadDryRun['required_checks'] ?? []) as $check): ?><li><?= e((string)$check) ?></li><?php endforeach; ?></ul>
  <h3>Operaciones bloqueadas</h3>
  <ul><?php foreach ((array)($uploadDryRun['blocked_operations'] ?? []) as $blocked): ?><li><?= e((string)$blocked) ?></li><?php endforeach; ?></ul>
  <h3>Razones seguras</h3>
  <ul><?php foreach ((array)($uploadDryRun['safe_reasons'] ?? []) as $reason): ?><li><?= e((string)$reason) ?></li><?php endforeach; ?></ul>
</div>
