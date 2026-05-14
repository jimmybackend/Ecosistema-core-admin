<?php $result = $contentData['result'] ?? null; $id = (int)($contentData['id'] ?? 0); ?>
<section class="eco-card">
    <h1>CRM Submission to Lead Result</h1>
    <p><a href="/landing/submissions/<?= $id ?>">← Volver al submission detail</a></p>

    <?php if (!is_array($result)): ?>
        <p>No hay resultado para mostrar.</p>
    <?php else: ?>
        <ul>
            <li>ok: <?= !empty($result['ok']) ? 'true' : 'false' ?></li>
            <li>lead_id: <?= isset($result['lead_id']) ? (int)$result['lead_id'] : 0 ?></li>
            <li>campaign_lead_id: <?= isset($result['campaign_lead_id']) && $result['campaign_lead_id'] !== null ? (int)$result['campaign_lead_id'] : 'null' ?></li>
            <li>duplicate_candidates_count: <?= (int)($result['duplicate_candidates_count'] ?? 0) ?></li>
            <li>pii_preview_only: true</li>
        </ul>
        <?php if (!empty($result['error'])): ?>
            <p><?= htmlspecialchars((string)$result['error']) ?></p>
        <?php endif; ?>
    <?php endif; ?>
</section>
