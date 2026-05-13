<?php
$summary = (array)($contentData['summary'] ?? []);
$logs = (array)($contentData['logs'] ?? []);
$errorMessage = isset($contentData['errorMessage']) ? (string)$contentData['errorMessage'] : null;
?>
<div class="eco-card">
  <h1>Logs de acceso Drive (read-only)</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Drive</a>
    <a class="eco-button btn" href="/cloud/drive/summary">Ver resumen</a>
    <a class="eco-button btn" href="/cloud/drive/files">Ver archivos</a>
  </p>
  <div class="eco-alert eco-alert--warning">Consulta read-only sobre <code>cloud_file_access_logs</code>. Sin AWS/S3, sin descargas/subidas y sin escrituras en DB.</div>
  <?php if ($errorMessage !== null && $errorMessage !== ''): ?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>

  <h2>Resumen por acción</h2>
  <table class="eco-table"><thead><tr><th>Acción</th><th>Total</th></tr></thead><tbody>
    <?php if ($summary === []): ?><tr><td colspan="2">Sin resumen disponible.</td></tr><?php else: foreach ($summary as $item): ?>
      <tr><td><?= e((string)($item['action'] ?? '')) ?></td><td><?= e((string)($item['total'] ?? '0')) ?></td></tr>
    <?php endforeach; endif; ?>
  </tbody></table>

  <h2>Eventos recientes</h2>
  <table class="eco-table"><thead><tr><th>Fecha</th><th>Acción</th><th>Archivo</th><th>Usuario</th><th>IP</th><th>User-Agent</th><th>Ubicación</th><th>Metadata</th></tr></thead><tbody>
    <?php if ($logs === []): ?><tr><td colspan="8">Sin logs para este tenant.</td></tr><?php else: foreach ($logs as $log): ?>
    <tr>
      <td><?= e((string)($log['created_at'] ?? '')) ?></td>
      <td><?= e((string)($log['action_label'] ?? '')) ?></td>
      <td><a href="/cloud/drive/files/<?= e((string)($log['file_id'] ?? '0')) ?>">#<?= e((string)($log['file_id'] ?? '0')) ?></a></td>
      <td><?= e((string)($log['user_email'] ?? '')) ?> (<?= e((string)($log['user_id'] ?? '0')) ?>)</td>
      <td><?= !empty($log['ip_address_present']) ? 'present' : 'not-present' ?></td>
      <td><?= e((string)($log['user_agent_preview'] ?? 'n/a')) ?></td>
      <td><?= e(trim((string)($log['country'] ?? '') . ' ' . (string)($log['region'] ?? '') . ' ' . (string)($log['city'] ?? ''))) ?></td>
      <td>present=<?= !empty($log['metadata_present']) ? 'true' : 'false' ?> / exposed=false</td>
    </tr>
    <?php endforeach; endif; ?>
  </tbody></table>
</div>
