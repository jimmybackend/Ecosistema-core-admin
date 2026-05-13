<?php
$job = $contentData['job'] ?? null;
$logs = (array)($contentData['logs'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Detalle job de reparación Drive</h1>
  <p><a class="eco-button btn" href="/cloud/drive/repair-jobs">Volver a jobs</a></p>
  <div class="eco-alert eco-alert--warning">Detalle read-only: sin reparación real, sin AWS/S3 y sin escritura en DB.</div>
  <?php if ($errorMessage !== null && $errorMessage !== ''): ?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>
  <?php if (!is_array($job)): ?><div class="eco-alert">No se encontró el job solicitado.</div><?php else: ?>
  <h2>Metadata segura</h2>
  <ul>
    <li>Job ID: #<?= e((string)($job['id'] ?? '0')) ?></li><li>Tenant: <?= e((string)($job['tenant_id'] ?? '0')) ?></li><li>Bucket: <?= e((string)($job['bucket_name'] ?? 'n/a')) ?> (#<?= e((string)($job['bucket_id'] ?? '0')) ?>)</li>
    <li>Status: <?= e((string)($job['status'] ?? '')) ?></li><li>Total S3: <?= e((string)($job['total_s3'] ?? '0')) ?></li><li>Total DB: <?= e((string)($job['total_db'] ?? '0')) ?></li>
    <li>Total Actions: <?= e((string)($job['total_actions'] ?? '0')) ?></li><li>Prefix present: <?= !empty($job['prefix_present']) ? 'true' : 'false' ?> / exposed=false</li>
    <li>Last message preview: <?= e((string)($job['last_message_preview'] ?? '')) ?></li>
  </ul>
  <?php endif; ?>

  <h2>Logs</h2>
  <table class="eco-table"><thead><tr><th>Action</th><th>File ID</th><th>old_s3_key</th><th>new_s3_key</th><th>detail_preview</th><th>created_at</th></tr></thead><tbody>
  <?php if ($logs === []): ?><tr><td colspan="6">Sin logs para este job.</td></tr><?php else: foreach ($logs as $log): ?>
    <tr>
      <td><?= e((string)($log['action'] ?? '')) ?></td><td><?= e((string)($log['file_id'] ?? '0')) ?></td>
      <td>present=<?= !empty($log['old_s3_key_present']) ? 'true' : 'false' ?> / exposed=false</td>
      <td>present=<?= !empty($log['new_s3_key_present']) ? 'true' : 'false' ?> / exposed=false</td>
      <td><?= e((string)($log['detail_preview'] ?? '')) ?></td><td><?= e((string)($log['created_at'] ?? '')) ?></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody></table>
</div>
