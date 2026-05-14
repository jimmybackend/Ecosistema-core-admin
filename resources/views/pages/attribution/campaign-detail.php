<?php declare(strict_types=1); $detail=(array)($detail??[]); $campaign=(array)($detail['campaign']??[]); $funnel=(array)($detail['funnel']??[]); ?>
<h1>Attribution Campaign Detail (read-only)</h1>
<p><a href="/attribution/campaigns">← Volver</a></p>
<?php if (!(bool)($detail['found'] ?? false)): ?>
    <p>Campaña no encontrada para este tenant.</p>
<?php else: ?>
    <ul>
        <li>ID: <?= (int)($campaign['id'] ?? 0) ?></li>
        <li>Nombre: <?= htmlspecialchars((string)($campaign['name'] ?? '')) ?></li>
        <li>Código: <?= htmlspecialchars((string)($campaign['code'] ?? '')) ?></li>
        <li>Estado: <?= htmlspecialchars((string)($campaign['status'] ?? '')) ?></li>
        <li>Objetivo: <?= htmlspecialchars((string)($campaign['objective'] ?? '')) ?></li>
        <li>Descripción (preview): <?= htmlspecialchars((string)($campaign['description_preview'] ?? '')) ?></li>
    </ul>
    <h2>Embudo</h2>
    <ol>
        <li>Clicks: <?= (int)($funnel['clicks'] ?? 0) ?></li>
        <li>Visits: <?= (int)($funnel['visits'] ?? 0) ?></li>
        <li>Submissions: <?= (int)($funnel['submissions'] ?? 0) ?></li>
        <li>Leads: <?= (int)($funnel['leads'] ?? 0) ?></li>
        <li>Conversions: <?= (int)($funnel['conversions'] ?? 0) ?></li>
    </ol>
<?php endif; ?>
