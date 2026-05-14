<?php
$detail = (array)($contentData['detail'] ?? []);
$lead = (array)($detail['lead'] ?? []);
$campaignLinks = (array)($detail['campaign_links'] ?? []);
$conversions = (array)($detail['conversions'] ?? []);
$attributions = (array)($detail['analytics_attribution'] ?? []);
$submissions = (array)($detail['landing_submissions_summary'] ?? []);
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<section class="stack">
<h1>Lead CRM detalle (read-only)</h1>
<?php if (is_string($errorMessage) && $errorMessage !== ''): ?><p><?= htmlspecialchars($errorMessage) ?></p><?php else: ?>
<p>Lead #<?= (int)($lead['id'] ?? 0) ?> · Status: <?= htmlspecialchars((string)($lead['status'] ?? '')) ?></p>
<p>Empresa: <?= htmlspecialchars((string)($lead['company_name_preview'] ?? '')) ?> · Contacto: <?= htmlspecialchars((string)($lead['contact_name_preview'] ?? '—')) ?></p>
<p><a href="/crm/leads/<?= (int)($lead['id'] ?? 0) ?>/followups">Ver followups del lead</a></p>
<h2>Campañas vinculadas</h2><ul><?php foreach ($campaignLinks as $row): ?><li>#<?= (int)$row['campaign_id'] ?> <?= htmlspecialchars((string)($row['campaign_name'] ?? '')) ?> · <?= htmlspecialchars((string)($row['status'] ?? '')) ?></li><?php endforeach; ?></ul>
<h2>Conversiones</h2><ul><?php foreach ($conversions as $row): ?><li>#<?= (int)$row['id'] ?> · <?= htmlspecialchars((string)($row['conversion_type'] ?? '')) ?> · <?= htmlspecialchars((string)($row['converted_at'] ?? '')) ?></li><?php endforeach; ?></ul>
<h2>Attribution</h2><ul><?php foreach ($attributions as $row): ?><li>#<?= (int)$row['id'] ?> · <?= htmlspecialchars((string)($row['utm_source'] ?? '')) ?> / <?= htmlspecialchars((string)($row['utm_campaign'] ?? '')) ?> · <?= htmlspecialchars((string)($row['attributed_at'] ?? '')) ?></li><?php endforeach; ?></ul>
<h2>Landing submissions</h2><ul><?php foreach ($submissions as $row): ?><li>#<?= (int)$row['id'] ?> · <?= htmlspecialchars((string)($row['submitted_at'] ?? '')) ?> · clicks <?= (int)($row['url_clicks_count'] ?? 0) ?></li><?php endforeach; ?></ul>
<?php endif; ?>
</section>
