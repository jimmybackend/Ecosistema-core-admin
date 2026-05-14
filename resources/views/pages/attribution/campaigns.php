<?php declare(strict_types=1); $summary = (array)($summary ?? []); $campaigns = (array)($campaigns ?? []); ?>
<h1>Attribution Campaigns (read-only)</h1>
<p>Total campañas: <strong><?= (int)($summary['total'] ?? 0) ?></strong></p>
<?php if ($campaigns === []): ?>
    <p>No hay campañas disponibles para este tenant.</p>
<?php else: ?>
<table class="table">
    <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Status</th><th>Type</th><th>Updated</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($campaigns as $row): ?>
        <tr>
            <td><?= (int)($row['id'] ?? 0) ?></td>
            <td><?= htmlspecialchars((string)($row['name'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($row['code'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($row['status'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($row['campaign_type'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($row['updated_at'] ?? '')) ?></td>
            <td><a href="/attribution/campaigns/<?= (int)($row['id'] ?? 0) ?>">Ver embudo</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
