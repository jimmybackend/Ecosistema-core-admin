<?php $roots=(array)($contentData['roots']??[]); $errorMessage=isset($contentData['errorMessage'])?(string)$contentData['errorMessage']:null; ?>
<div class="eco-card">
  <h1>Raíz de usuario Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
    <a class="eco-button btn" href="/cloud/drive/browse">Navegar Drive</a>
    <a class="eco-button btn" href="/cloud/drive/buckets">Ver buckets Drive</a>
  </p>
  <div class="eco-alert eco-alert--warning">Vista read-only desde <code>cloud_user_roots</code> sin AWS/S3 real.</div>
  <?php if ($errorMessage): ?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>
  <table class="eco-table"><thead><tr><th>ID</th><th>Nombre</th><th>Bucket</th><th>Cuota</th><th>Usado</th><th>Archivos</th><th>Status</th></tr></thead><tbody>
  <?php if ($roots===[]): ?><tr><td colspan="7">Sin raíz Drive activa para este usuario.</td></tr><?php endif; ?>
  <?php foreach($roots as $root): ?><tr><td><?= e((string)$root['id']) ?></td><td><?= e((string)$root['display_name']) ?></td><td><?= e((string)($root['bucket_id']??'-')) ?></td><td><?= e((string)$root['quota_bytes']) ?></td><td><?= e((string)$root['used_bytes']) ?></td><td><?= e((string)$root['file_count']) ?></td><td><span class="eco-badge"><?= e((string)$root['status']) ?></span></td></tr><?php endforeach; ?>
  </tbody></table>
</div>
