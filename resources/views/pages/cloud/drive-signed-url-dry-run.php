<?php
$dryRun = isset($contentData['dryRun']) && is_array($contentData['dryRun']) ? $contentData['dryRun'] : null;
$validation = is_array($dryRun['validation'] ?? null) ? (array)$dryRun['validation'] : [];
$contract = is_array($dryRun['signed_url_dry_run'] ?? null) ? (array)$dryRun['signed_url_dry_run'] : [];
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Signed URL dry-run</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)($dryRun['file_id'] ?? '')) ?>">Detalle del archivo</a>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)($dryRun['file_id'] ?? '')) ?>/s3-key-validation">Validación s3_key</a>
    <a class="eco-button btn" href="/cloud/drive/download-contract">Contrato de descarga</a>
  </p>

  <div class="eco-alert eco-alert--warning">No se generó ninguna URL real.</div>
  <div class="eco-alert eco-alert--info">AWS/S3 sigue apagado.</div>
  <div class="eco-alert eco-alert--info">s3_key no se muestra por seguridad.</div>

  <?php if ($errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <?php if ($dryRun !== null): ?>
    <table class="eco-table">
      <tbody>
        <tr><th>Estado de elegibilidad</th><td><span class="eco-badge"><?= e((string)($contract['eligibility_status'] ?? 'blocked')) ?></span></td></tr>
        <tr><th>Mode</th><td><?= e((string)($contract['mode'] ?? 'dry-run')) ?></td></tr>
        <tr><th>Signed URL generated</th><td><?= !empty($contract['signed_url_generated']) ? 'true' : 'false' ?></td></tr>
        <tr><th>AWS connection</th><td><?= !empty($contract['aws_connection']) ? 'true' : 'false' ?></td></tr>
        <tr><th>Download enabled</th><td><?= !empty($contract['download_enabled']) ? 'true' : 'false' ?></td></tr>
        <tr><th>TTL futuro sugerido (segundos)</th><td><?= e((string)($contract['expires_in_seconds_preview'] ?? '900')) ?></td></tr>
        <tr><th>Next step</th><td><?= e((string)($contract['next_step'] ?? '')) ?></td></tr>
      </tbody>
    </table>

    <h3>Validaciones requeridas</h3>
    <ul><?php foreach ((array)($contract['required_checks'] ?? []) as $check): ?><li><?= e((string)$check) ?></li><?php endforeach; ?></ul>

    <h3>Operaciones bloqueadas</h3>
    <ul><?php foreach ((array)($contract['blocked_operations'] ?? []) as $blocked): ?><li><?= e((string)$blocked) ?></li><?php endforeach; ?></ul>

    <h3>Razones seguras</h3>
    <ul><?php foreach ((array)($contract['safe_reasons'] ?? $validation['warnings'] ?? []) as $reason): ?><li><?= e((string)$reason) ?></li><?php endforeach; ?></ul>
  <?php endif; ?>
</div>
