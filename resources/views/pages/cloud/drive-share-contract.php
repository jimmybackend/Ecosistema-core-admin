<?php
$shareContract = isset($contentData['shareContract']) && is_array($contentData['shareContract']) ? $contentData['shareContract'] : null;
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
$file = is_array($shareContract['file'] ?? null) ? $shareContract['file'] : [];
$contract = is_array($shareContract['contract'] ?? null) ? $shareContract['contract'] : [];
?>
<div class="eco-card">
  <h1>Contrato de compartir archivos Drive</h1>
  <p>
    <?php if (!empty($file['id'])): ?>
      <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$file['id']) ?>">Detalle del archivo</a>
      <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$file['id']) ?>/versions">Versiones del archivo</a>
      <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$file['id']) ?>/s3-key-validation">Validación s3_key</a>
      <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$file['id']) ?>/signed-url-dry-run">Signed URL dry-run</a>
      <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$file['id']) ?>/download">Descarga controlada</a>
    <?php endif; ?>
    <a class="eco-button btn" href="/cloud/drive/upload-dry-run">Subida dry-run</a>
    <a class="eco-button btn" href="/cloud/drive/summary">Resumen Drive</a>
  </p>
  <div class="eco-alert eco-alert--warning">No se creó ningún enlace. No se generó ningún token. No hay permisos compartidos activos en este PR. No se envió email. AWS/S3 sigue apagado.</div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <?php if ($shareContract !== null): ?>
    <table class="eco-table"><tbody>
      <tr><th>Archivo</th><td>#<?= e((string)($file['id'] ?? '')) ?> · <?= e((string)($file['original_name'] ?? '')) ?></td></tr>
      <tr><th>Modo</th><td><span class="eco-badge"><?= e((string)($contract['mode'] ?? 'contract/read-only')) ?></span></td></tr>
      <tr><th>Compartir habilitado</th><td><span class="eco-badge"><?= !empty($contract['share_enabled']) ? 'Sí' : 'No' ?></span></td></tr>
      <tr><th>Enlaces públicos habilitados</th><td><span class="eco-badge"><?= !empty($contract['public_links_enabled']) ? 'Sí' : 'No' ?></span></td></tr>
    </tbody></table>

    <h3>Validaciones futuras requeridas</h3>
    <ul><?php foreach ((array)($contract['required_checks'] ?? []) as $check): ?><li><?= e((string)$check) ?></li><?php endforeach; ?></ul>
    <h3>Modos futuros permitidos (no activos)</h3>
    <ul><?php foreach ((array)($contract['allowed_future_share_modes'] ?? []) as $mode): ?><li><?= e((string)$mode) ?></li><?php endforeach; ?></ul>
    <h3>Operaciones bloqueadas</h3>
    <ul><?php foreach ((array)($contract['blocked_operations'] ?? []) as $operation): ?><li><?= e((string)$operation) ?></li><?php endforeach; ?></ul>
    <h3>Entradas prohibidas</h3>
    <ul><?php foreach ((array)($contract['forbidden_inputs'] ?? []) as $input): ?><li><?= e((string)$input) ?></li><?php endforeach; ?></ul>
    <h3>Auditoría esperada</h3>
    <ul><?php foreach ((array)($contract['audit_expectations'] ?? []) as $key => $value): ?><li><strong><?= e((string)$key) ?>:</strong> <?= e(is_bool($value) ? ($value ? 'true' : 'false') : (string)$value) ?></li><?php endforeach; ?></ul>
  <?php endif; ?>
</div>
