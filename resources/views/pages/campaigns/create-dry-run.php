<?php declare(strict_types=1); $result = is_array($result ?? null) ? $result : null; ?>
<section class="eco-card stack">
  <h1>Campaign creation dry-run</h1>
  <p>Simulación segura: no se escribe campaña, landing ni short link.</p>
  <form method="post" action="/campaigns/new/dry-run" class="eco-form">
    <input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
    <label>Nombre <input type="text" name="name" required></label>
    <label>Código <input type="text" name="code" placeholder="CMP-2026-01" required></label>
    <label>Tipo
      <select name="campaign_type" required>
        <option value="awareness">awareness</option><option value="traffic">traffic</option><option value="conversion">conversion</option><option value="retention">retention</option>
      </select>
    </label>
    <label>Objetivo <input type="text" name="objective" required></label>
    <label>Descripción <textarea name="description" rows="3"></textarea></label>
    <label>Budget (opcional) <input type="text" name="budget"></label>
    <label>Currency (ISO3) <input type="text" name="currency" maxlength="3" placeholder="USD"></label>
    <label>Inicio <input type="date" name="starts_at"></label>
    <label>Fin <input type="date" name="ends_at"></label>
    <label>Landing title <input type="text" name="landing_title" required></label>
    <label>Landing slug <input type="text" name="landing_slug" placeholder="campaign-spring-2026" required></label>
    <label>Short slug <input type="text" name="short_slug" placeholder="spring-2026" required></label>
    <button type="submit">Simular creación</button>
  </form>
</section>

<?php if ($result !== null): ?>
<section class="eco-card stack">
  <h2>Resultado</h2>
  <p><strong>Modo:</strong> <?= e((string)($result['mode'] ?? 'dry-run')) ?> · <strong>DB write:</strong> <?= !empty($result['db_write']) ? 'sí' : 'no' ?></p>
  <?php if (!empty($result['blocked_reasons'])): ?><div class="eco-alert eco-alert--warning"><strong>Bloqueado:</strong> <?= e(implode(', ', (array)$result['blocked_reasons'])) ?></div><?php endif; ?>
  <?php if (!empty($result['warnings'])): ?><div class="eco-alert eco-alert--warning"><strong>Warnings:</strong> <?= e(implode(', ', (array)$result['warnings'])) ?></div><?php endif; ?>
  <ul>
    <li>would_create_campaign: <?= !empty($result['would_create_campaign']) ? 'sí' : 'no' ?></li>
    <li>would_create_landing_page: <?= !empty($result['would_create_landing_page']) ? 'sí' : 'no' ?></li>
    <li>would_create_short_link: <?= !empty($result['would_create_short_link']) ? 'sí' : 'no' ?></li>
  </ul>
  <?php $campaignPreview = (array)($result['campaign_preview'] ?? []); $landingPreview = (array)($result['landing_preview'] ?? []); $shortPreview = (array)($result['short_link_preview'] ?? []); ?>
  <h3>Campaign preview</h3><p><?= e((string)($campaignPreview['name_preview'] ?? '')) ?> (<?= e((string)($campaignPreview['code'] ?? '')) ?>) · status <?= e((string)($campaignPreview['status'] ?? 'draft')) ?></p>
  <h3>Landing draft preview</h3><p><?= e((string)($landingPreview['title_preview'] ?? '')) ?> · slug <?= e((string)($landingPreview['slug'] ?? '')) ?> · status <?= e((string)($landingPreview['status'] ?? 'draft')) ?></p>
  <h3>Short link inactive preview</h3><p>slug <?= e((string)($shortPreview['slug'] ?? '')) ?> · status <?= e((string)($shortPreview['status'] ?? 'inactive')) ?></p>
</section>
<?php endif; ?>
