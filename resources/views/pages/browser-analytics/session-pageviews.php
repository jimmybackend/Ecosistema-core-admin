<?php
$pageviews = (array) ($contentData['pageviews'] ?? []);
$items = (array) ($pageviews['items'] ?? []);
$sessionId = (int) ($pageviews['session_id'] ?? 0);
?>
<section class="eco-card">
  <h1>Pageviews por sesión #<?= e((string) $sessionId) ?> (read-only)</h1>
  <p><a href="/browser/analytics/pageviews">← Volver a pageviews</a></p>
  <table class="eco-table"><thead><tr><th>ID</th><th>Campaign</th><th>Landing</th><th>Short link</th><th>Path</th><th>Page URL</th><th>Referrer</th><th>Viewed at</th></tr></thead><tbody>
  <?php if ($items === []): ?><tr><td colspan="8">Sin pageviews para esta sesión.</td></tr><?php else: foreach ($items as $item): ?>
    <tr>
      <td><?= e((string) ($item['id'] ?? 0)) ?></td>
      <td><?= e((string) ($item['campaign_id'] ?? '')) ?></td>
      <td><?= e((string) ($item['landing_page_id'] ?? '')) ?></td>
      <td><?= e((string) ($item['short_link_id'] ?? '')) ?></td>
      <td><?= e((string) ($item['path'] ?? '')) ?></td>
      <td><?= e((string) ($item['page_url_preview'] ?? '')) ?></td>
      <td><?= e((string) ($item['referrer_url_preview'] ?? '')) ?></td>
      <td><?= e((string) ($item['viewed_at'] ?? '')) ?></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody></table>
</section>
