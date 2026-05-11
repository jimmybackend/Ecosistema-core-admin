<?php

declare(strict_types=1);

$auth = $auth ?? [];
$displayName = $auth['auth_display_name'] ?? 'Usuario';
$csrfToken = $csrfToken ?? '';
?>
<header class="eco-header">
  <div class="eco-brand">Ecosistema Core Admin</div>
  <div style="display:flex; align-items:center; gap:.75rem;">
    <span><?= e((string) $displayName) ?></span>
    <form method="post" action="/logout" style="margin:0;">
      <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
      <button type="submit" class="btn">Salir</button>
    </form>
  </div>
</header>
