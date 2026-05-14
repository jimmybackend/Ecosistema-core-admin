<?php
/** @var array<string,mixed> $contentData */
$templates = (array) ($contentData['templates'] ?? []);
?>
<section class="eco-card">
  <h1>URL Message Templates</h1>
  <p><strong>Modo:</strong> read-only.</p>
  <?php if ($templates === []): ?>
    <p>No hay plantillas URL para este tenant.</p>
  <?php else: ?>
    <table class="eco-table"><thead><tr><th>ID</th><th>Template</th><th>Subject</th><th>From</th><th>Body</th><th>Status</th><th>Views</th><th>Detalle</th></tr></thead><tbody>
    <?php foreach ($templates as $template): ?>
      <tr>
        <td><?= (int) ($template['id'] ?? 0) ?></td>
        <td><?= htmlspecialchars((string) ($template['template_name'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($template['subject'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($template['from_name'] ?? '')) ?> · <?= htmlspecialchars((string) ($template['from_email_preview'] ?? 'hidden')) ?></td>
        <td><?= !empty($template['body_html_present']) ? 'present (preview only)' : 'empty' ?></td>
        <td><?= htmlspecialchars((string) ($template['status'] ?? '')) ?></td>
        <td><?= (int) ($template['view_count'] ?? 0) ?> / <?= (int) ($template['unique_view_count'] ?? 0) ?></td>
        <td><a href="/mail-notifications/url-message-templates/<?= (int) ($template['id'] ?? 0) ?>">Ver</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</section>
