<?php

declare(strict_types=1);

$tenants = is_array($tenants ?? null) ? $tenants : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
?>
<section>
  <h1>Tenants</h1>
  <p>Gestión básica de tenants usando únicamente <code>core_tenants</code>.</p>

  <?php if ($statusMessage !== null): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
  <?php if ($errorMessage !== null): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>

  <article class="eco-card" style="margin-top:1rem;">
    <div style="margin-bottom:.75rem;"><a class="eco-button btn" href="/tenants/create">Crear tenant</a></div>
    <table class="eco-table" style="width:100%;">
      <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Domain</th><th>Plan</th><th>Status</th><th>Timezone</th><th>Locale</th><th>Creado</th><th>Acciones</th></tr></thead>
      <tbody>
      <?php if ($tenants === []): ?>
        <tr><td colspan="10">No hay tenants para mostrar.</td></tr>
      <?php else: foreach ($tenants as $tenant): ?>
        <tr>
          <td><?= e((string) ($tenant['id'] ?? '')) ?></td><td><?= e((string) ($tenant['name'] ?? '')) ?></td><td><?= e((string) ($tenant['slug'] ?? '')) ?></td>
          <td><?= e((string) ($tenant['domain'] ?? '')) ?></td><td><?= e((string) ($tenant['plan_code'] ?? '')) ?></td>
          <td><span class="eco-badge"><?= e((string) ($tenant['status'] ?? '')) ?></span></td><td><?= e((string) ($tenant['timezone'] ?? '')) ?></td><td><?= e((string) ($tenant['locale'] ?? '')) ?></td>
          <td><?= e((string) ($tenant['created_at'] ?? '')) ?></td>
          <td>
            <a class="eco-button btn" href="/tenants/<?= e((string) ($tenant['id'] ?? '0')) ?>/edit">Editar</a>
            <form method="post" action="/tenants/<?= e((string) ($tenant['id'] ?? '0')) ?>/status" style="display:inline-block; margin-left:.25rem;">
              <input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
              <select class="eco-form-control" name="status">
                <?php foreach (['active','trial','suspended','canceled','deleted'] as $status): ?>
                  <option value="<?= e($status) ?>" <?= (($tenant['status'] ?? '') === $status) ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="eco-button btn" type="submit">Cambiar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </article>
</section>
