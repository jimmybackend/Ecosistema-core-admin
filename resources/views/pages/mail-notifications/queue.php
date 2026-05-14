<?php
/** @var array<string,mixed> $contentData */
$items = (array) ($contentData['items'] ?? []);
$summary = (array) ($contentData['summary'] ?? []);
?>
<section class="eco-card">
  <h1>Notifications Queue</h1>
  <p><strong>Modo:</strong> read-only. No se procesa, no se envía y no se reintenta.</p>
  <ul>
    <li>Total: <?= (int) ($summary['total'] ?? 0) ?></li>
    <li>Pending: <?= (int) ($summary['pending_total'] ?? 0) ?></li>
    <li>Sent: <?= (int) ($summary['sent_total'] ?? 0) ?></li>
    <li>Failed: <?= (int) ($summary['failed_total'] ?? 0) ?></li>
  </ul>
  <?php if ($items === []): ?>
    <p>No hay elementos en notifications_queue para este tenant.</p>
  <?php else: ?>
    <table class="eco-table"><thead><tr><th>ID</th><th>User</th><th>Channel</th><th>Template</th><th>Módulo</th><th>Entidad</th><th>Status</th><th>Body</th><th>Payload</th><th>Created</th><th>Detalle</th></tr></thead><tbody>
    <?php foreach ($items as $item): ?>
      <tr>
        <td><?= (int) ($item['id'] ?? 0) ?></td>
        <td><?= (int) ($item['user_id'] ?? 0) ?></td>
        <td><?= (int) ($item['channel_id'] ?? 0) ?></td>
        <td><?= (int) ($item['template_id'] ?? 0) ?></td>
        <td><?= htmlspecialchars((string) ($item['module_code'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($item['entity_table'] ?? '')) ?>#<?= (int) ($item['entity_id'] ?? 0) ?></td>
        <td><?= htmlspecialchars((string) ($item['status'] ?? '')) ?></td>
        <td><?= !empty($item['body_present']) ? 'present' : 'empty' ?></td>
        <td><?= !empty($item['payload_json_present']) ? 'present (exposed=false)' : 'empty' ?></td>
        <td><?= htmlspecialchars((string) ($item['created_at'] ?? '')) ?></td>
        <td><a href="/mail-notifications/queue/<?= (int) ($item['id'] ?? 0) ?>">Ver</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</section>
