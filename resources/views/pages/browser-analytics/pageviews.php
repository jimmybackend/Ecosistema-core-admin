<?php
$pageviews = (array) ($contentData['pageviews'] ?? []);
$summary = (array) ($pageviews['summary'] ?? []);
$items = (array) ($pageviews['items'] ?? []);
?>
<section class="eco-card">
  <h1>Browser Analytics Pageviews (read-only)</h1>
  <p>Vista protegida: no expone query string, hash fragment ni meta_json.</p>
  <p><a href="/browser/analytics">← Volver al dashboard</a></p>
  <table class="eco-table"><tbody>
    <tr><td>Total pageviews</td><td><?= e((string) ($summary['total_pageviews'] ?? 0)) ?></td></tr>
    <tr><td>Total sesiones</td><td><?= e((string) ($summary['total_sessions'] ?? 0)) ?></td></tr>
    <tr><td>Última vista</td><td><?= e((string) ($summary['latest_viewed_at'] ?? '-')) ?></td></tr>
  </tbody></table>
  <table class="eco-table" style="margin-top:1rem;"><thead><tr><th>ID</th><th>Session</th><th>Path</th><th>Page URL</th><th>Referrer</th><th>Título</th><th>Viewed at</th><th>Detalle</th></tr></thead><tbody>
  <?php if ($items === []): ?><tr><td colspan="8">Sin pageviews para el tenant actual.</td></tr><?php else: foreach ($items as $item): ?>
    <tr>
      <td><?= e((string) ($item['id'] ?? 0)) ?></td>
      <td><?= e((string) ($item['session_id'] ?? 0)) ?></td>
      <td><?= e((string) ($item['path'] ?? '')) ?></td>
      <td><?= e((string) ($item['page_url_preview'] ?? '')) ?></td>
      <td><?= e((string) ($item['referrer_url_preview'] ?? '')) ?></td>
      <td><?= e((string) ($item['page_title'] ?? '')) ?></td>
      <td><?= e((string) ($item['viewed_at'] ?? '')) ?></td>
      <td><a href="/browser/analytics/sessions/<?= e((string) ($item['session_id'] ?? 0)) ?>/pageviews">Sesión</a></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody></table>
</section>
