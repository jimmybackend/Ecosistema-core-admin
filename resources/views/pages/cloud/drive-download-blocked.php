<?php
$download = $contentData['result'] ?? null;
$errorMessage = $contentData['errorMessage'] ?? null;
$fileId = isset($download['file_id']) ? (int)$download['file_id'] : 0;
?>
<div class="eco-card">
  <h1>Descarga bloqueada (modo seguro)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$fileId) ?>">Detalle del archivo</a>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$fileId) ?>/s3-key-validation">Validación s3_key</a>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$fileId) ?>/signed-url-dry-run">Signed URL dry-run</a>
    <a class="eco-button btn" href="/cloud/drive/aws-config">AWS config</a>
    <a class="eco-button btn" href="/cloud/drive/download-contract">Contrato de descarga</a>
  </p>

  <?php if ($errorMessage): ?>
    <div class="eco-alert eco-alert--danger"><?= e((string)$errorMessage) ?></div>
  <?php elseif (!is_array($download)): ?>
    <div class="eco-alert eco-alert--warning">No se encontró contexto seguro para la descarga.</div>
  <?php else: ?>
    <div class="eco-alert eco-alert--warning">La descarga remota permanece bloqueada por política de seguridad.</div>
    <ul>
      <li><strong>Razón:</strong> <?= e((string)($download['blocked_reason'] ?? 'blocked_by_default')) ?></li>
      <li><strong>aws_connection:</strong> <?= !empty($download['aws_connection']) ? 'true' : 'false' ?></li>
      <li><strong>remote_downloads:</strong> <?= !empty($download['remote_downloads']) ? 'true' : 'false' ?></li>
      <li><strong>signed_urls:</strong> <?= !empty($download['signed_urls']) ? 'true' : 'false' ?></li>
      <li><strong>s3_key_validated:</strong> <?= !empty($download['s3_key_validated']) ? 'true' : 'false' ?></li>
      <li><strong>s3_key_exposed:</strong> false</li>
      <li><strong>sdk_available:</strong> <?= !empty($download['sdk_available']) ? 'true' : 'false' ?></li>
    </ul>

    <?php $missing = $download['missing_flags'] ?? []; if (is_array($missing) && $missing !== []): ?>
      <h3>Flags requeridas faltantes</h3>
      <ul><?php foreach ($missing as $flag): ?><li><?= e((string)$flag) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
  <?php endif; ?>
</div>
