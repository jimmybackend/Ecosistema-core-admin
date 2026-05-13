<?php
$uploadStatus = isset($contentData['uploadStatus']) && is_array($contentData['uploadStatus']) ? $contentData['uploadStatus'] : [];
?>
<div class="eco-card">
  <h1>Subida S3 controlada</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive/upload-dry-run">Subida dry-run</a>
    <a class="eco-button btn" href="/cloud/drive">Drive</a>
    <a class="eco-button btn" href="/cloud/drive/aws-config">AWS config</a>
  </p>
  <table class="eco-table"><tbody>
    <tr><th>upload_enabled</th><td><?= !empty($uploadStatus['upload_enabled']) ? 'true' : 'false' ?></td></tr>
    <tr><th>sdk_available</th><td><?= !empty($uploadStatus['sdk_available']) ? 'true' : 'false' ?></td></tr>
    <tr><th>mode</th><td><?= e((string)($uploadStatus['mode'] ?? 'contract')) ?></td></tr>
    <tr><th>max_upload_mb</th><td><?= e((string)($uploadStatus['max_upload_mb'] ?? '10')) ?></td></tr>
    <tr><th>allowed_extensions</th><td><?= e(implode(', ', (array)($uploadStatus['allowed_extensions'] ?? []))) ?></td></tr>
    <tr><th>blocked_reason</th><td><?= e((string)($uploadStatus['blocked_reason'] ?? 'none')) ?></td></tr>
  </tbody></table>
  <h3>Flags requeridas</h3>
  <ul><?php foreach ((array)($uploadStatus['missing_flags'] ?? []) as $flag): ?><li><?= e((string)$flag) ?></li><?php endforeach; ?></ul>
  <form method="post" action="/cloud/drive/upload" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
    <label for="upload_file">Archivo</label>
    <input id="upload_file" type="file" name="upload_file" required>
    <button class="eco-button btn" type="submit">Intentar subida controlada</button>
  </form>
</div>
