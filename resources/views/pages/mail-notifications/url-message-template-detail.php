<?php
/** @var array<string,mixed> $contentData */
$template = $contentData['template'] ?? null;
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<section class="eco-card">
  <h1>URL Message Template Detail</h1>
  <p><strong>Modo:</strong> read-only. No envía correos ni descarga adjuntos.</p>
  <?php if ($errorMessage): ?><p><?= htmlspecialchars((string) $errorMessage) ?></p><?php endif; ?>
  <?php if (!is_array($template)): ?>
    <p>No se encontró la plantilla solicitada.</p>
  <?php else: ?>
    <ul>
      <li>ID: <?= (int) ($template['id'] ?? 0) ?></li>
      <li>short_link_id: <?= htmlspecialchars((string) ($template['short_link_id'] ?? '')) ?></li>
      <li>campaign_id: <?= htmlspecialchars((string) ($template['campaign_id'] ?? '')) ?></li>
      <li>landing_page_id: <?= htmlspecialchars((string) ($template['landing_page_id'] ?? '')) ?></li>
      <li>Template: <?= htmlspecialchars((string) ($template['template_name'] ?? '')) ?></li>
      <li>Subject: <?= htmlspecialchars((string) ($template['subject'] ?? '')) ?></li>
      <li>From: <?= htmlspecialchars((string) ($template['from_name'] ?? '')) ?> · <?= htmlspecialchars((string) ($template['from_email_preview'] ?? '')) ?></li>
      <li>Reply-To preview: <?= htmlspecialchars((string) ($template['reply_to_email_preview'] ?? '')) ?></li>
      <li>Header present: <?= !empty($template['header_html_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>Body present: <?= !empty($template['body_html_present']) ? 'true' : 'false' ?> (exposed=false)</li>
      <li>Body preview: <?= htmlspecialchars((string) ($template['body_html_preview'] ?? '')) ?></li>
      <li>Footer present: <?= !empty($template['footer_html_present']) ? 'true' : 'false' ?></li>
      <li>Plain text present: <?= !empty($template['plain_text_present']) ? 'true' : 'false' ?></li>
      <li>Language: <?= htmlspecialchars((string) ($template['language_code'] ?? '')) ?></li>
      <li>Status: <?= htmlspecialchars((string) ($template['status'] ?? '')) ?></li>
      <li>Views: <?= (int) ($template['view_count'] ?? 0) ?> / <?= (int) ($template['unique_view_count'] ?? 0) ?></li>
    </ul>

    <?php $attachments = (array) ($template['attachments'] ?? []); ?>
    <h2>Adjuntos (metadata segura)</h2>
    <?php if ($attachments === []): ?><p>Sin adjuntos.</p><?php else: ?>
      <table class="eco-table"><thead><tr><th>Filename</th><th>Display</th><th>MIME</th><th>Size</th><th>Path/S3</th><th>Open/Download</th></tr></thead><tbody>
      <?php foreach ($attachments as $attachment): ?>
        <tr>
          <td><?= htmlspecialchars((string) ($attachment['filename'] ?? '')) ?></td>
          <td><?= htmlspecialchars((string) ($attachment['display_name'] ?? '')) ?></td>
          <td><?= htmlspecialchars((string) ($attachment['mime_type'] ?? '')) ?></td>
          <td><?= (int) ($attachment['size_bytes'] ?? 0) ?></td>
          <td>file_path_present=<?= !empty($attachment['file_path_present']) ? 'true' : 'false' ?>, s3_key_present=<?= !empty($attachment['s3_key_present']) ? 'true' : 'false' ?></td>
          <td><?= (int) ($attachment['open_count'] ?? 0) ?> / <?= (int) ($attachment['download_count'] ?? 0) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table>
    <?php endif; ?>
  <?php endif; ?>
</section>
