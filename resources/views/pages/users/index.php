<?php

declare(strict_types=1);

$users = is_array($users ?? null) ? $users : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
?>
<section>
  <h1>Usuarios</h1>
  <p>Gestión básica sobre <code>core_users</code>. La autorización fina queda pendiente para un PR posterior.</p>
  <?php if ($statusMessage !== null): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
  <?php if ($errorMessage !== null): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <article class="eco-card" style="margin-top:1rem;">
    <div style="margin-bottom:.75rem;"><a class="eco-button btn" href="/users/create">Crear usuario</a></div>
    <table class="eco-table" style="width:100%;">
      <thead><tr><th>ID</th><th>Tenant</th><th>Email</th><th>Username</th><th>Display Name</th><th>Tipo</th><th>Status</th><th>Último login</th><th>Creado</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php if ($users === []): ?><tr><td colspan="10">No hay usuarios para mostrar.</td></tr>
      <?php else: foreach ($users as $user): ?>
      <tr>
        <td><?= e((string) ($user['id'] ?? '')) ?></td>
        <td><?= e(trim((string) (($user['tenant_name'] ?? '') . ' (' . ($user['tenant_slug'] ?? '') . ')'))) ?></td>
        <td><?= e((string) ($user['email'] ?? '')) ?></td>
        <td><?= e((string) ($user['username'] ?? '')) ?></td>
        <td><?= e((string) ($user['display_name'] ?? '')) ?></td>
        <td><span class="eco-badge"><?= e((string) ($user['user_type'] ?? '')) ?></span></td>
        <td><span class="eco-badge"><?= e((string) ($user['status'] ?? '')) ?></span></td>
        <td><?= e((string) ($user['last_login_at'] ?? '')) ?></td>
        <td><?= e((string) ($user['created_at'] ?? '')) ?></td>
        <td>
          <a class="eco-button btn" href="/users/<?= e((string) ($user['id'] ?? '0')) ?>/edit">Editar</a>
          <form method="post" action="/users/<?= e((string) ($user['id'] ?? '0')) ?>/status" style="display:inline-block;">
            <input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
            <select class="eco-form-control" name="status"><?php foreach (['active','inactive','suspended','deleted'] as $status): ?><option value="<?= e($status) ?>" <?= (($user['status'] ?? '') === $status) ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select>
            <button class="eco-button btn" type="submit">Cambiar</button>
          </form>
        </td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </article>
</section>
