<?php
/** @var array<string,mixed> $contentData */
$templates = (array) ($contentData['templates'] ?? []);
?>
<section class="eco-card">
  <h1>Notification Templates</h1>
  <p><strong>Aviso:</strong> listado en modo read-only.</p>
  <?php if ($templates === []): ?>
    <p>No hay plantillas de notificación para este tenant.</p>
  <?php else: ?>
    <table class="eco-table"><thead><tr><th>ID</th><th>Canal</th><th>Código</th><th>Nombre</th><th>Subject</th><th>Body present</th><th>Variables present</th><th>Activo</th><th>Updated at</th><th>Detalle</th></tr></thead><tbody>
    <?php foreach ($templates as $template): ?>
      <tr>
        <td><?= (int) $template['id'] ?></td>
        <td><?= htmlspecialchars((string) ($template['channel_name'] ?? '')) ?> (<?= htmlspecialchars((string) ($template['channel_code'] ?? '')) ?>)</td>
        <td><?= htmlspecialchars((string) ($template['code'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($template['name'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($template['subject'] ?? '')) ?></td>
        <td><?= !empty($template['body_present']) ? 'true' : 'false' ?></td>
        <td><?= !empty($template['variables_json_present']) ? 'true' : 'false' ?></td>
        <td><?= !empty($template['is_active']) ? 'true' : 'false' ?></td>
        <td><?= htmlspecialchars((string) ($template['updated_at'] ?? '')) ?></td>
        <td><a href="/mail-notifications/templates/<?= (int) $template['id'] ?>">Ver</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</section>
