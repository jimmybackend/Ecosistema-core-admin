<?php $result = $contentData['result'] ?? null; $id = (int)($contentData['id'] ?? 0); ?>
<section class="eco-card">
  <h1>CRM Followup Task Result</h1>
  <p><a href="/crm/leads/<?= $id ?>">← Volver al lead</a></p>
  <?php if (!is_array($result)): ?>
    <p>No hay resultado para mostrar.</p>
  <?php else: ?>
    <ul>
      <li>ok: <?= !empty($result['ok']) ? 'true' : 'false' ?></li>
      <li>task_id: <?= isset($result['task_id']) ? (int)$result['task_id'] : 'null' ?></li>
      <li>lead_id: <?= isset($result['lead_id']) ? (int)$result['lead_id'] : $id ?></li>
      <li>status: <?= htmlspecialchars((string)($result['status'] ?? 'pending')) ?></li>
      <li>pii_preview_only: true</li>
    </ul>
    <?php if (!empty($result['error'])): ?><p><?= htmlspecialchars((string)$result['error']) ?></p><?php endif; ?>
  <?php endif; ?>
</section>
