<?php declare(strict_types=1);
$filters = (array)($filters ?? []);
$bySource = (array)($by_source ?? []);
$byCampaign = (array)($by_campaign ?? []);
$byStatus = (array)($by_status ?? []);
$score = (array)($score_temperature ?? []);
?>
<h1>Reporte de desempeño de leads</h1>
<p>Vista read-only agregada. No expone PII ni permite exportación.</p>
<form method="get" action="/reports/lead-performance" class="eco-form">
  <label>Desde <input type="date" name="from" value="<?= htmlspecialchars((string)($filters['from'] ?? '')) ?>"></label>
  <label>Hasta <input type="date" name="to" value="<?= htmlspecialchars((string)($filters['to'] ?? '')) ?>"></label>
  <button type="submit">Aplicar</button>
</form>
<h2>Score / temperature</h2>
<ul>
  <li>Total leads: <?= (int)($score['leads'] ?? 0) ?></li>
  <li>Score promedio: <?= number_format((float)($score['avg_score'] ?? 0), 2) ?></li>
  <li>Hot: <?= (int)($score['hot_leads'] ?? 0) ?> | Warm: <?= (int)($score['warm_leads'] ?? 0) ?> | Cold: <?= (int)($score['cold_leads'] ?? 0) ?></li>
</ul>
<h2>Leads por fuente</h2>
<table class="eco-table"><thead><tr><th>Fuente</th><th>Leads</th><th>Conversiones</th></tr></thead><tbody>
<?php foreach ($bySource as $row): ?><tr><td><?= htmlspecialchars((string)($row['source_module'] ?? 'unknown')) ?></td><td><?= (int)($row['leads'] ?? 0) ?></td><td><?= (int)($row['conversions'] ?? 0) ?></td></tr><?php endforeach; ?>
</tbody></table>
<h2>Leads por campaña</h2>
<table class="eco-table"><thead><tr><th>Campaign ID</th><th>Leads</th><th>Conversiones</th></tr></thead><tbody>
<?php foreach ($byCampaign as $row): ?><tr><td><?= (int)($row['campaign_id'] ?? 0) ?></td><td><?= (int)($row['leads'] ?? 0) ?></td><td><?= (int)($row['conversions'] ?? 0) ?></td></tr><?php endforeach; ?>
</tbody></table>
<h2>Leads por status</h2>
<table class="eco-table"><thead><tr><th>Status</th><th>Leads</th><th>Conversiones</th></tr></thead><tbody>
<?php foreach ($byStatus as $row): ?><tr><td><?= htmlspecialchars((string)($row['status'] ?? 'unknown')) ?></td><td><?= (int)($row['leads'] ?? 0) ?></td><td><?= (int)($row['conversions'] ?? 0) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php if (count($bySource)===0 && count($byCampaign)===0 && count($byStatus)===0): ?><p>Sin datos para los filtros seleccionados.</p><?php endif; ?>
