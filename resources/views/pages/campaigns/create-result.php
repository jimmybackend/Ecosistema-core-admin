<?php declare(strict_types=1); $result = is_array($result ?? null) ? $result : null; ?>
<section class="eco-card stack">
  <h1>Campaign creation</h1>
  <p>Creación base de campaña controlada por flags. No publica automáticamente landing ni short link.</p>
</section>

<?php if ($result !== null): ?>
<section class="eco-card stack">
  <h2>Resultado</h2>
  <p><strong>Created:</strong> <?= !empty($result['created']) ? 'sí' : 'no' ?></p>
  <p><strong>Campaign ID:</strong> <?= e((string) ($result['campaign_id'] ?? 'n/a')) ?></p>
  <?php if (!empty($result['blocked_reasons'])): ?><div class="eco-alert eco-alert--warning"><strong>Bloqueado:</strong> <?= e(implode(', ', (array) $result['blocked_reasons'])) ?></div><?php endif; ?>
  <?php if (!empty($result['warnings'])): ?><div class="eco-alert eco-alert--warning"><strong>Warnings:</strong> <?= e(implode(', ', (array) $result['warnings'])) ?></div><?php endif; ?>
</section>
<?php endif; ?>
