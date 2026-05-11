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
