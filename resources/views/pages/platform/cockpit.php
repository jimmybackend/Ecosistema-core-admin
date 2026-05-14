<?php
/** @var array<string,mixed> $cockpit */
$cockpit = is_array($cockpit ?? null) ? $cockpit : [];
$modules = is_array($cockpit['modules'] ?? null) ? $cockpit['modules'] : [];
$links = is_array($cockpit['links'] ?? null) ? $cockpit['links'] : [];
?>
<section class="card"><h1>Platform Cockpit</h1><p>Vista operativa de solo lectura del ecosistema por tenant actual.</p></section>
<section class="card"><h2>Resumen tenant</h2><p>Roles: <?= (int)($cockpit['tenant_summary']['roles_count'] ?? 0) ?> · Usuarios: <?= (int)($cockpit['tenant_summary']['users_count'] ?? 0) ?></p></section>
<section class="card"><h2>Módulos</h2><ul><?php foreach ($modules as $module): ?><li><strong><?= htmlspecialchars((string)($module['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars((string)($module['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>) - <?= htmlspecialchars((string)($module['status'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></section>
<section class="card"><h2>Accesos</h2><ul><?php foreach ($links as $link): ?><li><a href="<?= htmlspecialchars((string)($link['href'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)($link['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></li><?php endforeach; ?></ul></section>
