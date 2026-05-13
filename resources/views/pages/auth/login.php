<?php

declare(strict_types=1);

$statusMessage = $statusMessage ?? null;
$csrfToken = $csrfToken ?? '';
?>
<?php if ($statusMessage !== null): ?>
  <div class="eco-card" style="margin-bottom: 1rem; border-left: 4px solid #2f7cf6;">
    <p style="margin: 0;"><?= e($statusMessage) ?></p>
  </div>
<?php endif; ?>

<form method="post" action="/login" class="eco-form" style="display: grid; gap: 1rem;">
  <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">

  <div>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" class="eco-input" autocomplete="email" placeholder="admin@ecosistema.local" required>
  </div>

  <div>
    <label for="password">Password</label>
    <input id="password" name="password" type="password" class="eco-input" autocomplete="current-password" placeholder="••••••••" required>
  </div>

  <button type="submit" class="btn">Entrar</button>
</form>

<div class="eco-card" style="margin-top: 1rem; border-left: 4px solid #2f7cf6;">
  <p style="margin: 0 0 0.5rem 0; font-weight: 600;">¿Primera vez en esta VM?</p>
  <p style="margin: 0 0 0.75rem 0;">Usa el registro inicial controlado para crear la primera cuenta administrativa.</p>
  <p style="margin: 0;"><a href="/register">Crear cuenta inicial</a></p>
</div>
