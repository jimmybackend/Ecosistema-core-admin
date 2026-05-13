<?php
$summary = (array)($contentData['summary'] ?? []);
$visits = (array)($contentData['visits'] ?? []);
?>
<section class="eco-card">
  <h1>Landing Visits</h1>
  <p><strong>Aviso:</strong> consulta administrativa read-only con privacidad reforzada. No registra visitas ni tracking.</p>
  <p>Total visitas: <?= (int)($summary['total'] ?? 0) ?></p>
  <h2>Resumen por país</h2><ul><?php foreach (($summary['by_country'] ?? []) as $row): ?><li><?= htmlspecialchars((string)$row['label']) ?>: <?= (int)$row['total'] ?></li><?php endforeach; ?></ul>
  <h2>Resumen por dispositivo</h2><ul><?php foreach (($summary['by_device_type'] ?? []) as $row): ?><li><?= htmlspecialchars((string)$row['label']) ?>: <?= (int)$row['total'] ?></li><?php endforeach; ?></ul>
  <h2>Resumen por campaña</h2><ul><?php foreach (($summary['by_campaign'] ?? []) as $row): ?><li><?= htmlspecialchars((string)$row['label']) ?>: <?= (int)$row['total'] ?></li><?php endforeach; ?></ul>

  <?php if ($visits === []): ?>
    <p>No hay visitas para este tenant.</p>
  <?php else: ?>
  <table class="eco-table"><thead><tr><th>visited_at</th><th>landing_page</th><th>campaign</th><th>short_link</th><th>country/region/city</th><th>device_type</th><th>browser</th><th>os</th><th>ip</th><th>user_agent</th><th>referer</th><th>full_url</th><th>utm</th></tr></thead><tbody>
    <?php foreach ($visits as $visit): ?><tr>
      <td><?= htmlspecialchars((string)($visit['visited_at'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($visit['landing_page_title'] ?? '')) ?> (<?= htmlspecialchars((string)($visit['landing_page_slug'] ?? '')) ?>)</td>
      <td><?= htmlspecialchars((string)($visit['campaign_name'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($visit['short_link_slug'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($visit['country'] ?? '')) ?>/<?= htmlspecialchars((string)($visit['region'] ?? '')) ?>/<?= htmlspecialchars((string)($visit['city'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($visit['device_type'] ?? '')) ?></td><td><?= htmlspecialchars((string)($visit['browser_name'] ?? '')) ?></td><td><?= htmlspecialchars((string)($visit['os_name'] ?? '')) ?></td>
      <td><?= !empty($visit['ip_address_present']) ? 'present' : 'empty' ?> <?= htmlspecialchars((string)($visit['ip_address_preview'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($visit['user_agent_preview'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($visit['referer_preview'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($visit['full_url_preview'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($visit['utm_source'] ?? '')) ?>/<?= htmlspecialchars((string)($visit['utm_medium'] ?? '')) ?>/<?= htmlspecialchars((string)($visit['utm_campaign'] ?? '')) ?></td>
    </tr><?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
</section>
