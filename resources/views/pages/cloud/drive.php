<?php
$status = (array)($contentData['status'] ?? []);
$capabilities = (array)($contentData['capabilities'] ?? []);
?>
<div class="eco-card">
  <h1>Ecosistema Drive</h1>
  <p>
    <a class="eco-button btn" href="/cloud">Volver a Cloud</a>
    <a class="eco-button btn" href="/cloud/drive/files">Ver archivos Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">
    Ecosistema Drive está en modo contract/dry-run. No hay AWS/S3 real ni llamadas remotas.
  </div>
  <div class="eco-alert">
    El listado de archivos Drive usa metadata en DB (`cloud_files`) y no enumera buckets/keys reales.
  </div>

  <h2>Estado seguro de integración</h2>
  <table class="eco-table">
    <thead><tr><th>Clave</th><th>Valor</th></tr></thead>
    <tbody>
      <?php foreach ($status as $key => $value): ?>
        <tr>
          <td><?= e((string)$key) ?></td>
          <td>
            <?php if (is_bool($value)): ?>
              <span class="eco-badge"><?= $value ? 'true' : 'false' ?></span>
            <?php else: ?>
              <?= e((string)$value) ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h2>Capacidades</h2>
  <table class="eco-table">
    <thead><tr><th>Capacidad</th><th>Estado</th><th>Descripción</th></tr></thead>
    <tbody>
      <?php foreach ($capabilities as $key => $capability): ?>
        <tr>
          <td><?= e((string)$key) ?></td>
          <td>
            <span class="eco-badge"><?= !empty($capability['enabled']) ? 'habilitada' : 'bloqueada' ?></span>
          </td>
          <td><?= e((string)($capability['description'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
