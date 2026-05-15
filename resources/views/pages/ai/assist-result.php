<?php
/** @var array<string,mixed>|null $result */
/** @var string|null $errorMessage */
$result = isset($result) && is_array($result) ? $result : null;
?>
<section class="space-y-4">
  <h1 class="text-xl font-semibold">AI Assist Result</h1>
  <?php if (!empty($errorMessage)): ?><p><?= htmlspecialchars((string)$errorMessage, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
  <?php if ($result !== null): ?>
    <ul>
      <li>ok: <?= !empty($result['ok']) ? 'true' : 'false' ?></li>
      <li>allowed: <?= !empty($result['allowed']) ? 'true' : 'false' ?></li>
      <li>provider: <?= htmlspecialchars((string)($result['provider'] ?? 'none'), ENT_QUOTES, 'UTF-8') ?></li>
      <li>proposal_persisted: <?= !empty($result['proposal_persisted']) ? 'true' : 'false' ?></li>
      <li>proposal_id: <?= isset($result['proposal_id']) ? (int)$result['proposal_id'] : 0 ?></li>
      <li>pii_preview_only: <?= !empty($result['pii_preview_only']) ? 'true' : 'false' ?></li>
    </ul>
  <?php else: ?>
    <p>Sin resultado.</p>
  <?php endif; ?>
</section>
