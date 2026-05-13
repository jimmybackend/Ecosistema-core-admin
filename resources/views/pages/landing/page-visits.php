<?php
$summary = (array)($contentData['summary'] ?? []);
$visits = (array)($contentData['visits'] ?? []);
$pageId = (int)($contentData['id'] ?? 0);
?>
<section class="eco-card">
  <h1>Landing Page Visits</h1>
  <p><strong>Landing page ID:</strong> <?= $pageId ?></p>
  <p><strong>Aviso:</strong> modo read-only, sin tracking write.</p>
  <p>Total visitas: <?= (int)($summary['total'] ?? 0) ?></p>
  <?php if ($visits === []): ?><p>Sin visitas para esta landing page.</p><?php endif; ?>
  <table class="eco-table"><thead><tr><th>visited_at</th><th>campaign</th><th>short_link</th><th>geo</th><th>device</th><th>browser</th><th>os</th><th>ip</th><th>user_agent</th><th>referer</th><th>full_url</th><th>utm</th></tr></thead><tbody>
  <?php foreach ($visits as $visit): ?><tr>
    <td><?= htmlspecialchars((string)($visit['visited_at'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['campaign_name'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['short_link_slug'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['country'] ?? '')) ?>/<?= htmlspecialchars((string)($visit['region'] ?? '')) ?>/<?= htmlspecialchars((string)($visit['city'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['device_type'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['browser_name'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['os_name'] ?? '')) ?></td>
    <td><?= !empty($visit['ip_address_present']) ? 'present' : 'empty' ?> <?= htmlspecialchars((string)($visit['ip_address_preview'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['user_agent_preview'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['referer_preview'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['full_url_preview'] ?? '')) ?></td>
    <td><?= htmlspecialchars((string)($visit['utm_source'] ?? '')) ?>/<?= htmlspecialchars((string)($visit['utm_medium'] ?? '')) ?>/<?= htmlspecialchars((string)($visit['utm_campaign'] ?? '')) ?></td>
  </tr><?php endforeach; ?>
  </tbody></table>
</section>
