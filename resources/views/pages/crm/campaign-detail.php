<?php
$campaign = $contentData['campaign'] ?? null;
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<section class="stack">
    <h1>Detalle campaña CRM</h1>
    <?php if ($errorMessage): ?><p><?= htmlspecialchars((string)$errorMessage) ?></p><?php endif; ?>
    <?php if (is_array($campaign)): ?>
        <ul>
            <li>ID: <?= (int)$campaign['id'] ?></li>
            <li>Nombre: <?= htmlspecialchars((string)$campaign['name']) ?></li>
            <li>Código: <?= htmlspecialchars((string)$campaign['code']) ?></li>
            <li>Descripción: <?= htmlspecialchars((string)($campaign['description_preview'] ?? '')) ?></li>
            <li>Landing: <?= htmlspecialchars((string)($campaign['landing_url_preview'] ?? '')) ?></li>
            <li>Visitas: <?= (int)($campaign['total_visits'] ?? 0) ?></li>
            <li>Clicks: <?= (int)($campaign['total_clicks'] ?? 0) ?></li>
            <li>Submissions: <?= (int)($campaign['total_submissions'] ?? 0) ?></li>
            <li>Modo: <?= htmlspecialchars((string)$campaign['mode']) ?></li>
        </ul>
    <?php endif; ?>
</section>
