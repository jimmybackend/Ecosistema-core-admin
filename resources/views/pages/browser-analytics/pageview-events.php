<?php
$events = (array) ($contentData['events'] ?? []);
$pageviewId = (int) ($events['pageview_id'] ?? 0);
$items = (array) ($events['items'] ?? []);
?>
<section class="eco-card">
  <h1>Eventos por Pageview #<?= e((string) $pageviewId) ?> (read-only)</h1>
  <p>No se expone metadata_json ni URLs/textos completos sensibles.</p>
  <p><a href="/browser/analytics/events">← Volver a eventos</a></p>
  <table class="eco-table"><thead><tr><th>ID</th><th>Tipo</th><th>Nombre</th><th>Element URL</th><th>Value Text</th><th>Metadata</th><th>Ocurrió</th></tr></thead><tbody>
  <?php if ($items === []): ?><tr><td colspan="7">Sin eventos para este pageview en el tenant actual.</td></tr><?php else: foreach ($items as $item): ?>
    <tr>
      <td><?= e((string) ($item['id'] ?? 0)) ?></td>
      <td><?= e((string) ($item['event_type'] ?? '')) ?></td>
      <td><?= e((string) ($item['event_name'] ?? '')) ?></td>
      <td><?= e((string) ($item['element_url_preview'] ?? '')) ?></td>
      <td><?= e((string) ($item['value_text_preview'] ?? '')) ?></td>
      <td><?= (($item['metadata_json_present'] ?? false) ? 'Sí' : 'No') ?></td>
      <td><?= e((string) ($item['occurred_at'] ?? '')) ?></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody></table>
</section>
