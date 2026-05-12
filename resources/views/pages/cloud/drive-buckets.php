<?php
$buckets = (array)($contentData['buckets'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Buckets de Ecosistema Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
    <a class="eco-button btn" href="/cloud/drive/browse">Navegar Drive</a>
    <a class="eco-button btn" href="/cloud/drive/folders">Ver carpetas Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">
    Vista informativa read-only con metadata desde <code>cloud_buckets</code>. No activa AWS/S3 real, no crea/edita/borra buckets y no expone datos sensibles.
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
          <th>ID</th><th>Name</th><th>Provider</th><th>Region</th><th>Status</th><th>Default</th><th>Creado</th><th>Actualizado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($buckets as $bucket): ?>
          <tr>
            <td><?= isset($bucket['id']) ? e((string)$bucket['id']) : '-' ?></td>
            <td><?= isset($bucket['name']) && $bucket['name'] !== null ? e((string)$bucket['name']) : '-' ?></td>
            <td><?= isset($bucket['provider']) && $bucket['provider'] !== null ? e((string)$bucket['provider']) : '-' ?></td>
            <td><?= isset($bucket['region']) && $bucket['region'] !== null ? e((string)$bucket['region']) : '-' ?></td>
            <td><?php if (isset($bucket['status']) && $bucket['status'] !== null): ?><span class="eco-badge"><?= e((string)$bucket['status']) ?></span><?php else: ?>-<?php endif; ?></td>
            <td><?php if (array_key_exists('is_default', $bucket) && $bucket['is_default'] !== null): ?><span class="eco-badge"><?= !empty($bucket['is_default']) ? 'Sí' : 'No' ?></span><?php else: ?>-<?php endif; ?></td>
            <td><?= isset($bucket['created_at']) && $bucket['created_at'] !== null ? e((string)$bucket['created_at']) : '-' ?></td>
            <td><?= isset($bucket['updated_at']) && $bucket['updated_at'] !== null ? e((string)$bucket['updated_at']) : '-' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
