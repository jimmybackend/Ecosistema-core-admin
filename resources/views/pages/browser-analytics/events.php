<?php
$events = (array) ($contentData['events'] ?? []);
$summary = (array) ($events['summary'] ?? []);
$items = (array) ($events['items'] ?? []);
?>
<section class="eco-card">
  <h1>Browser Analytics Events (read-only)</h1>
  <p>Vista protegida: no expone metadata_json ni valores sensibles completos.</p>
  <p><a href="/browser/analytics">← Volver al dashboard</a></p>
  <table class="eco-table"><tbody>
    <tr><td>Total eventos</td><td><?= e((string) ($summary['total_events'] ?? 0)) ?></td></tr>
    <tr><td>Total pageviews</td><td><?= e((string) ($summary['total_pageviews'] ?? 0)) ?></td></tr>
    <tr><td>Último evento</td><td><?= e((string) ($summary['latest_occurred_at'] ?? '-')) ?></td></tr>
  </tbody></table>
  <table class="eco-table" style="margin-top:1rem;"><thead><tr><th>ID</th><th>Pageview</th><th>Tipo</th><th>Nombre</th><th>Element ID</th><th>Element Text</th><th>Element URL</th><th>Value Text</th><th>Metadata</th><th>Ocurrió</th><th>Detalle</th></tr></thead><tbody>
  <?php if ($items === []): ?><tr><td colspan="11">Sin eventos para el tenant actual.</td></tr><?php else: foreach ($items as $item): ?>
    <tr>
      <td><?= e((string) ($item['id'] ?? 0)) ?></td>
      <td><?= e((string) ($item['pageview_id'] ?? 0)) ?></td>
      <td><?= e((string) ($item['event_type'] ?? '')) ?></td>
      <td><?= e((string) ($item['event_name'] ?? '')) ?></td>
      <td><?= e((string) ($item['element_id_preview'] ?? '')) ?></td>
      <td><?= e((string) ($item['element_text_preview'] ?? '')) ?></td>
      <td><?= e((string) ($item['element_url_preview'] ?? '')) ?></td>
      <td><?= e((string) ($item['value_text_preview'] ?? '')) ?></td>
      <td><?= (($item['metadata_json_present'] ?? false) ? 'Sí' : 'No') ?></td>
      <td><?= e((string) ($item['occurred_at'] ?? '')) ?></td>
      <td><a href="/browser/analytics/pageviews/<?= e((string) ($item['pageview_id'] ?? 0)) ?>/events">Pageview</a></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody></table>
</section>
