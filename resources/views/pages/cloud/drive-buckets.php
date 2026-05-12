<?php
$buckets = (array)($contentData['buckets'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Buckets Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
    <a class="eco-button btn" href="/cloud/drive/browse">Volver a navegación Drive</a>
    <a class="eco-button btn" href="/cloud/drive/folders">Volver a carpetas Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">
    Vista informativa read-only desde <code>cloud_buckets</code>. No hay conexión AWS/S3 real, ni creación/edición/borrado de buckets.
  </div>

  <?php if ($errorMessage !== null && $errorMessage !== ''): ?>
    <div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div>
  <?php endif; ?>

  <?php if ($buckets === []): ?>
    <div class="eco-alert">No hay buckets Drive configurados para este tenant todavía.</div>
  <?php else: ?>
    <table class="eco-table">
      <thead>
      <tr>
        <th>ID</th><th>Nombre</th><th>Provider</th><th>Región</th><th>Status</th><th>Default</th><th>Creado</th><th>Actualizado</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($buckets as $bucket): ?>
        <tr>
          <td><?= e((string)($bucket['id'] ?? '')) ?></td>
          <td><?= e((string)($bucket['name'] ?? '')) ?></td>
          <td><?= e((string)($bucket['provider'] ?? '')) ?></td>
          <td><?= e((string)($bucket['region'] ?? '')) ?></td>
          <td><span class="eco-badge"><?= e((string)($bucket['status'] ?? 'n/a')) ?></span></td>
          <td><span class="eco-badge"><?= !empty($bucket['is_default']) ? 'Sí' : 'No' ?></span></td>
          <td><?= e((string)($bucket['created_at'] ?? '')) ?></td>
          <td><?= e((string)($bucket['updated_at'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
