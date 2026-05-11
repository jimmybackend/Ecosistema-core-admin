<?php

declare(strict_types=1);

$user = is_array($user ?? null) ? $user : [];
$roles = is_array($roles ?? null) ? $roles : [];
$assignedRoleIds = is_array($assignedRoleIds ?? null) ? $assignedRoleIds : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
$assignedMap = [];
foreach ($assignedRoleIds as $roleId) { $assignedMap[(int) $roleId] = true; }
?>
<section>
  <h1>Roles de usuario #<?= e((string) ($user['id'] ?? '')) ?></h1>
  <p>Asignaciones guardadas en <code>core_user_roles</code>.</p>

  <?php if ($statusMessage !== null): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
  <?php if ($errorMessage !== null): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>

  <article class="eco-card" style="margin-top:1rem;">
    <p><strong>Email:</strong> <?= e((string) ($user['email'] ?? '')) ?></p>
    <p><strong>Username:</strong> <?= e((string) ($user['username'] ?? '')) ?></p>
    <p><strong>Display name:</strong> <?= e((string) ($user['display_name'] ?? '')) ?></p>
    <p><strong>Tenant ID:</strong> <span class="eco-badge"><?= e((string) ($user['tenant_id'] ?? '')) ?></span></p>
  </article>

  <article class="eco-card" style="margin-top:1rem;">
    <form method="post" action="/users/<?= e((string) ($user['id'] ?? '0')) ?>/roles">
      <input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
      <table class="eco-table" style="width:100%;">
        <thead><tr><th>Asignar</th><th>ID</th><th>Código</th><th>Nombre</th><th>Status</th></tr></thead>
        <tbody>
        <?php if ($roles === []): ?>
          <tr><td colspan="5">No hay roles disponibles para este tenant.</td></tr>
        <?php else: foreach ($roles as $role): $roleId = (int) ($role['id'] ?? 0); ?>
          <tr>
            <td><input type="checkbox" name="role_ids[]" value="<?= e((string) $roleId) ?>" <?= isset($assignedMap[$roleId]) ? 'checked' : '' ?>></td>
            <td><?= e((string) $roleId) ?></td>
            <td><?= e((string) ($role['code'] ?? '')) ?></td>
            <td><?= e((string) ($role['name'] ?? '')) ?></td>
            <td><span class="eco-badge"><?= e((string) ($role['status'] ?? '')) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
      <p style="margin-top:1rem;">
        <button class="eco-button btn" type="submit">Guardar roles</button>
        <a class="eco-button btn" href="/users">Volver a usuarios</a>
      </p>
    </form>
  </article>
</section>
