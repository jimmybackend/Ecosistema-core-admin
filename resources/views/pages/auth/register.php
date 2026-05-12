<?php

declare(strict_types=1);

$statusMessage = $statusMessage ?? null;
$csrfToken = $csrfToken ?? '';
$old = is_array($old ?? null) ? $old : [];
?>
<?php if ($statusMessage !== null): ?>
  <div class="eco-card" style="margin-bottom: 1rem; border-left: 4px solid #2f7cf6;">
    <p style="margin: 0;"><?= e($statusMessage) ?></p>
  </div>
<?php endif; ?>

<form method="post" action="/register" class="eco-form" style="display: grid; gap: 1rem;">
  <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
  <div>
    <label for="name">Nombre</label>
    <input id="name" name="name" type="text" class="eco-input" required value="<?= e((string) ($old['name'] ?? '')) ?>">
  </div>
  <div>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" class="eco-input" required value="<?= e((string) ($old['email'] ?? '')) ?>">
  </div>
  <div>
    <label for="password">Password</label>
    <input id="password" name="password" type="password" class="eco-input" autocomplete="new-password" required minlength="12">
  </div>
  <div>
    <label for="password_confirmation">Confirmar password</label>
    <input id="password_confirmation" name="password_confirmation" type="password" class="eco-input" autocomplete="new-password" required minlength="12">
  </div>
  <div>
    <label for="invite_code">Invite code</label>
    <input id="invite_code" name="invite_code" type="password" class="eco-input" required>
  </div>
  <button type="submit" class="btn">Crear cuenta inicial</button>
  <p style="margin:0;"><a href="/login">Volver a login</a></p>
</form>
