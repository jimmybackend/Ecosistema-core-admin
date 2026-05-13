<?php
$fileId = (int)($contentData['fileId'] ?? 0);
$summary = (array)($contentData['summary'] ?? []);
$logs = (array)($contentData['logs'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Logs de acceso por archivo (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Drive</a>
    <a class="eco-button btn" href="/cloud/drive/files">Ver archivos</a>
    <a class="eco-button btn" href="/cloud/drive/access-logs">Logs globales</a>
    <a class="eco-button btn" href="/cloud/drive/files/<?= e((string)$fileId) ?>">Detalle archivo</a>
  </p>
  <div class="eco-alert eco-alert--warning">Vista read-only para archivo #<?= e((string)$fileId) ?>. Metadata sensible bloqueada.</div>
  <?php if ($errorMessage !== null && $errorMessage !== ''): ?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>

  <h2>Resumen por acción (tenant)</h2>
  <table class="eco-table"><thead><tr><th>Acción</th><th>Total</th></tr></thead><tbody>
  <?php if ($summary === []): ?><tr><td colspan="2">Sin resumen disponible.</td></tr><?php else: foreach ($summary as $item): ?>
    <tr><td><?= e((string)($item['action'] ?? '')) ?></td><td><?= e((string)($item['total'] ?? '0')) ?></td></tr>
  <?php endforeach; endif; ?>
  </tbody></table>

  <h2>Eventos del archivo</h2>
  <table class="eco-table"><thead><tr><th>Fecha</th><th>Acción</th><th>Usuario</th><th>IP</th><th>User-Agent</th><th>Ubicación</th><th>Metadata</th></tr></thead><tbody>
  <?php if ($logs === []): ?><tr><td colspan="7">Sin logs para este archivo.</td></tr><?php else: foreach ($logs as $log): ?>
    <tr>
      <td><?= e((string)($log['created_at'] ?? '')) ?></td>
      <td><?= e((string)($log['action_label'] ?? '')) ?></td>
      <td><?= e((string)($log['user_email'] ?? '')) ?> (<?= e((string)($log['user_id'] ?? '0')) ?>)</td>
      <td><?= !empty($log['ip_address_present']) ? 'present' : 'not-present' ?></td>
      <td><?= e((string)($log['user_agent_preview'] ?? 'n/a')) ?></td>
      <td><?= e(trim((string)($log['country'] ?? '') . ' ' . (string)($log['region'] ?? '') . ' ' . (string)($log['city'] ?? ''))) ?></td>
      <td>present=<?= !empty($log['metadata_present']) ? 'true' : 'false' ?> / exposed=false</td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody></table>
</div>
