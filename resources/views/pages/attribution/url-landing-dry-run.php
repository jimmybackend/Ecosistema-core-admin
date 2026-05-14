<?php declare(strict_types=1); $result=$result??null; $errorMessage=$errorMessage??null; ?>
<h1>Attribution URL → Landing (dry-run)</h1>
<p><strong>Modo:</strong> simulación segura sin escrituras en DB.</p>
<?php if ($errorMessage): ?><p style="color:#a00;"><?= e((string)$errorMessage) ?></p><?php endif; ?>
<form method="post" action="/attribution/url-landing/dry-run" class="eco-form">
  <input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
  <label for="click_id">Click ID (url_clicks.id)</label>
  <input type="number" min="1" step="1" required id="click_id" name="click_id" value="<?= e((string)($_POST['click_id'] ?? '')) ?>">
  <button type="submit">Simular vínculo</button>
</form>
<?php if (is_array($result)): ?>
<hr>
<p><strong>enabled:</strong> <?= !empty($result['enabled']) ? 'true' : 'false' ?> · <strong>write_enabled:</strong> <?= !empty($result['write_enabled']) ? 'true' : 'false' ?> · <strong>db_write:</strong> false</p>
<p><strong>eligible:</strong> <?= !empty($result['eligible']) ? 'true' : 'false' ?> · <strong>blocked_reason:</strong> <?= e((string)($result['blocked_reason'] ?? 'none')) ?></p>
<p><strong>matches:</strong> visitas=<?= (int)($result['match_summary']['visits'] ?? 0) ?>, sesiones=<?= (int)($result['match_summary']['sessions'] ?? 0) ?></p>
<?php endif; ?>
