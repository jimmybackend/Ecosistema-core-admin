<?php

declare(strict_types=1);

$auth = is_array($auth ?? null) ? $auth : [];
$dashboard = is_array($contentData['dashboard'] ?? null) ? $contentData['dashboard'] : [];
$tenant = is_array($dashboard['tenant'] ?? null) ? $dashboard['tenant'] : null;
$modules = is_array($dashboard['modules'] ?? null) ? $dashboard['modules'] : [];
$hasError = (bool) ($dashboard['hasError'] ?? false);
?>
<section>
  <h1>Dashboard</h1>
  <p>Resumen inicial de Ecosistema Core Admin.</p>

  <?php if ($hasError): ?>
    <div class="eco-alert" role="alert">
      No fue posible cargar toda la información del dashboard en este momento.
    </div>
  <?php endif; ?>

  <div class="eco-alert" role="note" style="margin-top: 1rem;">
    Dashboard base. Roles, permisos, Tenants, Usuarios, Mail y Cloud se implementarán en PRs posteriores.
  </div>

  <div class="eco-grid" style="margin-top: 1rem;">
    <article class="eco-card eco-stat-card">
      <h3>Tenant actual</h3>
      <p class="eco-stat-value"><?= e((string) ($tenant['name'] ?? 'No disponible')) ?></p>
      <p>Slug: <?= e((string) ($tenant['slug'] ?? '-')) ?></p>
    </article>

    <article class="eco-card eco-stat-card">
      <h3>Usuarios activos (tenant)</h3>
      <p class="eco-stat-value"><?= e((string) ($dashboard['activeUsersByTenant'] ?? 0)) ?></p>
    </article>

    <article class="eco-card eco-stat-card">
      <h3>Módulos activos</h3>
      <p class="eco-stat-value"><?= e((string) ($dashboard['activeModules'] ?? 0)) ?></p>
    </article>

    <article class="eco-card eco-stat-card">
      <h3>Sesiones activas (usuario)</h3>
      <p class="eco-stat-value"><?= e((string) ($dashboard['activeSessionsByUser'] ?? 0)) ?></p>
    </article>
  </div>

  <article class="eco-card" style="margin-top: 1rem;">
    <h3>Módulos activos</h3>
    <table class="eco-table" style="margin-top:.75rem; width:100%;">
      <thead>
      <tr>
        <th>Código</th>
        <th>Nombre</th>
        <th>Core</th>
        <th>Facturable</th>
        <th>Estado</th>
      </tr>
      </thead>
      <tbody>
      <?php if ($modules === []): ?>
        <tr>
          <td colspan="5">No hay módulos activos para mostrar.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($modules as $module): ?>
          <tr>
            <td><?= e((string) ($module['code'] ?? '')) ?></td>
            <td><?= e((string) ($module['name'] ?? '')) ?></td>
            <td><span class="eco-badge"><?= e(((int) ($module['is_core'] ?? 0)) === 1 ? 'Sí' : 'No') ?></span></td>
            <td><span class="eco-badge"><?= e(((int) ($module['is_billable'] ?? 0)) === 1 ? 'Sí' : 'No') ?></span></td>
            <td><span class="eco-badge"><?= e((string) ($module['status'] ?? '')) ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </article>

  <article class="eco-card" style="margin-top: 1rem;">
    <h3>Usuario autenticado</h3>
    <p><strong>Nombre:</strong> <?= e((string) ($auth['auth_display_name'] ?? '')) ?></p>
    <p><strong>Email:</strong> <?= e((string) ($auth['auth_email'] ?? '')) ?></p>
    <p><strong>ID usuario:</strong> <?= e((string) ($auth['auth_user_id'] ?? '')) ?></p>
    <p><strong>ID tenant:</strong> <?= e((string) ($auth['auth_tenant_id'] ?? '')) ?></p>
  </article>
</section>
