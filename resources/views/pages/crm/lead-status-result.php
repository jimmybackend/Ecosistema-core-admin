<?php $result=$contentData['result']??null; $id=(int)($contentData['id']??0); ?>
<section class="eco-card">
  <h1>CRM Lead Status Result</h1>
  <p><a href="/crm/leads/<?= $id ?>">← Volver al lead</a></p>
  <?php if (!is_array($result)): ?><p>Sin resultado.</p><?php else: ?>
  <ul>
    <li>ok: <?= !empty($result['ok']) ? 'true' : 'false' ?></li>
    <li>lead_id: <?= isset($result['lead_id']) ? (int)$result['lead_id'] : $id ?></li>
    <li>previous_status: <?= htmlspecialchars((string)($result['previous_status'] ?? '')) ?></li>
    <li>status: <?= htmlspecialchars((string)($result['status'] ?? '')) ?></li>
    <li>campaign_update_attempted: <?= !empty($result['campaign_update']['attempted']) ? 'true' : 'false' ?></li>
    <li>campaign_update_updated: <?= !empty($result['campaign_update']['updated']) ? 'true' : 'false' ?></li>
    <li>pii_preview_only: true</li>
  </ul>
  <?php if (!empty($result['error'])): ?><p><?= htmlspecialchars((string)$result['error']) ?></p><?php endif; ?>
  <?php endif; ?>
</section>
