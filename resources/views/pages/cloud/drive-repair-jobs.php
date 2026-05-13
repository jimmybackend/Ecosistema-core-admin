<?php
$summary = (array)($contentData['summary'] ?? []);
$jobs = (array)($contentData['jobs'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Jobs de reparación Drive</h1>
  <p><a class="eco-button btn" href="/cloud/drive">Volver a Drive</a></p>
  <div class="eco-alert eco-alert--warning">Vista read-only: no ejecuta reparación real, no conecta AWS/S3 y no escribe en DB.</div>
  <?php if ($errorMessage !== null && $errorMessage !== ''): ?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>

  <h2>Resumen por status</h2>
  <table class="eco-table"><thead><tr><th>Status</th><th>Total</th></tr></thead><tbody>
  <?php if ($summary === []): ?><tr><td colspan="2">Sin resumen disponible.</td></tr><?php else: foreach ($summary as $item): ?>
    <tr><td><?= e((string)($item['status'] ?? '')) ?></td><td><?= e((string)($item['total'] ?? '0')) ?></td></tr>
  <?php endforeach; endif; ?>
  </tbody></table>

  <h2>Listado reciente</h2>
  <table class="eco-table"><thead><tr><th>Job ID</th><th>Bucket</th><th>Status</th><th>Total S3</th><th>Total DB</th><th>Total Actions</th><th>Prefix Present</th><th>Last Message Preview</th><th>Started</th><th>Finished</th><th>Created</th></tr></thead><tbody>
  <?php if ($jobs === []): ?><tr><td colspan="11">No hay jobs de reparación para este tenant.</td></tr><?php else: foreach ($jobs as $job): ?>
    <tr>
      <td><a href="/cloud/drive/repair-jobs/<?= e((string)($job['id'] ?? '0')) ?>">#<?= e((string)($job['id'] ?? '0')) ?></a></td>
      <td><?= e((string)($job['bucket_name'] ?? 'n/a')) ?> (#<?= e((string)($job['bucket_id'] ?? '0')) ?>)</td>
      <td><?= e((string)($job['status'] ?? '')) ?></td>
      <td><?= e((string)($job['total_s3'] ?? '0')) ?></td><td><?= e((string)($job['total_db'] ?? '0')) ?></td><td><?= e((string)($job['total_actions'] ?? '0')) ?></td>
      <td><?= !empty($job['prefix_present']) ? 'true' : 'false' ?> / exposed=false</td>
      <td><?= e((string)($job['last_message_preview'] ?? '')) ?></td>
      <td><?= e((string)($job['started_at'] ?? '')) ?></td><td><?= e((string)($job['finished_at'] ?? '')) ?></td><td><?= e((string)($job['created_at'] ?? '')) ?></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody></table>
</div>
