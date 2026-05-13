<?php
$usage = (array) ($contentData['usage'] ?? []);
?>
<div class="eco-card">
  <h1>Uso de almacenamiento Drive</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
  </p>
  <div class="eco-alert eco-alert--warning">Vista read-only: sin escrituras DB, sin scans de storage/S3, sin conexión AWS/S3.</div>

  <table class="eco-table">
    <tbody>
      <tr><td>Total archivos</td><td><span class="eco-badge"><?= e((string)($usage['total_files'] ?? 0)) ?></span></td></tr>
      <tr><td>Total bytes</td><td><?= e((string)($usage['total_bytes'] ?? 0)) ?> (<?= e((string)($usage['total_human'] ?? '0 B')) ?>)</td></tr>
      <tr><td>Activos / Archivados / Missing / Deleted</td><td><?= e((string)($usage['active_files'] ?? 0)) ?> / <?= e((string)($usage['archived_files'] ?? 0)) ?> / <?= e((string)($usage['missing_files'] ?? 0)) ?> / <?= e((string)($usage['deleted_files'] ?? 0)) ?></td></tr>
      <tr><td>found_in_s3 / not_found_in_s3</td><td><?= e((string)($usage['found_in_s3_count'] ?? 0)) ?> / <?= e((string)($usage['not_found_in_s3_count'] ?? 0)) ?></td></tr>
    </tbody>
  </table>

  <h2>Por bucket</h2>
  <table class="eco-table"><thead><tr><th>bucket_id</th><th>bucket_name</th><th>provider</th><th>status</th><th>file_count</th><th>total</th></tr></thead><tbody>
  <?php foreach (($usage['by_bucket'] ?? []) as $row): ?><tr><td><?= e((string)($row['bucket_id'] ?? '')) ?></td><td><?= e((string)($row['bucket_name'] ?? '')) ?></td><td><?= e((string)($row['provider'] ?? '')) ?></td><td><?= e((string)($row['status'] ?? '')) ?></td><td><?= e((string)($row['file_count'] ?? 0)) ?></td><td><?= e((string)($row['total_human'] ?? '0 B')) ?></td></tr><?php endforeach; ?>
  </tbody></table>

  <h2>Por usuario</h2>
  <table class="eco-table"><thead><tr><th>user_id</th><th>email / display_name</th><th>file_count</th><th>total</th></tr></thead><tbody>
  <?php foreach (($usage['by_user'] ?? []) as $row): ?><tr><td><?= e((string)($row['user_id'] ?? '')) ?></td><td><?= e(trim((string)($row['email'] ?? ''))) ?> <?= e(trim((string)($row['display_name'] ?? ''))) ?></td><td><?= e((string)($row['file_count'] ?? 0)) ?></td><td><?= e((string)($row['total_human'] ?? '0 B')) ?></td></tr><?php endforeach; ?>
  </tbody></table>

  <h2>Por extensión</h2>
  <table class="eco-table"><thead><tr><th>extension</th><th>file_count</th><th>total</th></tr></thead><tbody>
  <?php foreach (($usage['by_extension'] ?? []) as $row): ?><tr><td><?= e((string)($row['extension'] ?? '')) ?></td><td><?= e((string)($row['file_count'] ?? 0)) ?></td><td><?= e((string)($row['total_human'] ?? '0 B')) ?></td></tr><?php endforeach; ?>
  </tbody></table>

  <h2>Uso diario</h2>
  <?php if (empty($usage['daily_usage'])): ?>
    <p>No hay histórico diario disponible en cloud_storage_usage_daily.</p>
  <?php else: ?>
  <table class="eco-table"><thead><tr><th>usage_date</th><th>total</th><th>file_count</th><th>mail_attachment_bytes</th><th>cloud_document_bytes</th><th>other_bytes</th></tr></thead><tbody>
  <?php foreach (($usage['daily_usage'] ?? []) as $row): ?><tr><td><?= e((string)($row['usage_date'] ?? '')) ?></td><td><?= e((string)($row['total_human'] ?? '0 B')) ?></td><td><?= e((string)($row['file_count'] ?? 0)) ?></td><td><?= e((string)($row['mail_attachment_human'] ?? '0 B')) ?></td><td><?= e((string)($row['cloud_document_human'] ?? '0 B')) ?></td><td><?= e((string)($row['other_human'] ?? '0 B')) ?></td></tr><?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
</div>
