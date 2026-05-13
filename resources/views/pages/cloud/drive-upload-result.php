<?php
$uploadResult = isset($contentData['uploadResult']) && is_array($contentData['uploadResult']) ? $contentData['uploadResult'] : [];
?>
<div class="eco-card">
  <h1>Resultado subida S3 controlada</h1>
  <p><a class="eco-button btn" href="/cloud/drive/upload">Volver</a></p>
  <table class="eco-table"><tbody>
    <tr><th>success</th><td><?= !empty($uploadResult['success']) ? 'true' : 'false' ?></td></tr>
    <tr><th>upload_enabled</th><td><?= !empty($uploadResult['upload_enabled']) ? 'true' : 'false' ?></td></tr>
    <tr><th>sdk_available</th><td><?= !empty($uploadResult['sdk_available']) ? 'true' : 'false' ?></td></tr>
    <tr><th>blocked_reason</th><td><?= e((string)($uploadResult['blocked_reason'] ?? 'none')) ?></td></tr>
    <tr><th>created_file_id</th><td><?= e((string)($uploadResult['created_file_id'] ?? '')) ?></td></tr>
  </tbody></table>
  <ul><?php foreach ((array)($uploadResult['missing_flags'] ?? []) as $flag): ?><li><?= e((string)$flag) ?></li><?php endforeach; ?></ul>
</div>
