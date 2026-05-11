<?php

declare(strict_types=1);

$statusMessage = $statusMessage ?? null;
?>
<?php if ($statusMessage !== null): ?>
  <div class="eco-card" style="margin-bottom: 1rem; border-left: 4px solid #2f7cf6;">
    <p style="margin: 0;"><?= e($statusMessage) ?></p>
  </div>
<?php endif; ?>

<form method="post" action="/login" class="eco-form" style="display: grid; gap: 1rem;">
  <div>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" class="eco-input" autocomplete="email" placeholder="admin@ecosistema.local">
  </div>

  <div>
    <label for="password">Password</label>
    <input id="password" name="password" type="password" class="eco-input" autocomplete="current-password" placeholder="••••••••">
  </div>

  <label style="display: inline-flex; gap: .5rem; align-items: center;">
    <input type="checkbox" name="remember" value="1">
    <span>Mantener sesión</span>
  </label>

  <button type="submit" class="btn">Entrar</button>
</form>

<p style="margin-top: 1rem; font-size: .95rem; opacity: .85;">
  Autenticación real pendiente de conectar con core_users.
</p>

<p style="margin-top: .5rem;">
  <a href="/">Volver al inicio</a>
</p>
