<?php
$dashboard = (array) ($contentData['dashboard'] ?? []);
$capabilities = (array) ($dashboard['capabilities'] ?? []);
?>
<section class="eco-card">
  <h1>Browser Analytics Dashboard</h1>
  <p><strong>Modo:</strong> read-only (protected privacy, sin collector ni escrituras).</p>
  <table class="eco-table"><tbody>
    <tr><td>Total sesiones</td><td><?= e((string) ($dashboard['total_sessions'] ?? 0)) ?></td></tr>
    <tr><td>Total pageviews</td><td><?= e((string) ($dashboard['total_pageviews'] ?? 0)) ?></td></tr>
    <tr><td>Total eventos</td><td><?= e((string) ($dashboard['total_events'] ?? 0)) ?></td></tr>
    <tr><td>Total conversiones</td><td><?= e((string) ($dashboard['total_conversions'] ?? 0)) ?></td></tr>
    <tr><td>Duración promedio (ms)</td><td><?= e((string) ($dashboard['avg_duration_ms'] ?? 0)) ?></td></tr>
    <tr><td>Scroll promedio (%)</td><td><?= e((string) ($dashboard['avg_scroll_depth_percent'] ?? 0)) ?></td></tr>
  </tbody></table>

  <p><a class="eco-btn eco-btn--ghost" href="/browser/analytics/pageviews">Ver pageviews read-only</a> · <a class="eco-btn eco-btn--ghost" href="/browser/analytics/events">Ver eventos read-only</a></p>
  <h2>Capacidades</h2>
  <ul>
    <li>dashboard_read: <?= !empty($capabilities['dashboard_read']) ? 'true' : 'false' ?></li>
    <li>sessions_read: <?= !empty($capabilities['sessions_read']) ? 'true' : 'false' ?></li>
    <li>pageviews_read: <?= !empty($capabilities['pageviews_read']) ? 'true' : 'false' ?></li>
    <li>collector_write: <?= !empty($capabilities['collector_write']) ? 'true' : 'false' ?></li>
    <li>daily_rollups_read: <?= !empty($capabilities['daily_rollups_read']) ? 'true' : 'false' ?></li>
  </ul>
</section>
