# ECOSISTEMA Tenant/AuthZ Verification (PR #194)

Fecha de verificación: 2026-05-16 (UTC)

## Alcance revisado

Se revisaron rutas administrativas y servicios/repositorios de:

- Tenants, Users, Roles, Permissions, Modules
- Drive (Cloud)
- Mail / Mail Notifications
- URL Locator
- Landing
- Browser Analytics
- CRM
- Campaigns
- Workflow
- Reports
- Security
- Audit
- AI

## Evidencia de controles (rutas)

### 1) Sesión requerida en rutas administrativas

Patrón consistente encontrado en `routes/web.php`:

- `startAuthSession($config)`
- `if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }`

Aplicado en rutas de administración (tenants, users, roles, permissions, modules, cloud, mail, crm, reports, workflow, security, ai, audit, etc.).

### 2) Permiso requerido

Se verificó el uso de `requirePermission($config, '<permiso>')` en rutas admin.
Ejemplos representativos:

- `tenants.view` / `tenants.manage`
- `users.view` / `users.manage`
- `roles.view` / `roles.manage`
- `permissions.view` / `permissions.manage`
- `modules.view` / `modules.manage`
- permisos de dominio (`mail.*`, `cloud.*`, `campaigns.*`, `security.*`, etc.)

### 3) CSRF en POST

Rutas POST administrativas validan token con:

- `$_POST['_csrf'] ?? null`
- `ensureValidCsrfToken($config, $csrfToken)`

Se confirmó este patrón en operaciones write de tenants/users/roles/permisos/módulos y módulos revisados.

## Tenancy y authorization por servicio/repositorio

### 4) `tenant_id` / `user_id` desde sesión

- Las rutas pasan `tenant_id`/`user_id` desde `AuthSession::getAuth()` hacia servicios en módulos tenant-scoped.
- Los repositorios usan consultas parametrizadas con filtros `WHERE tenant_id = :tenant_id` y, cuando aplica, `AND user_id = :user_id`.

### 5) No aceptar `tenant_id` inseguro desde request

- En módulos tenant-scoped revisados, no se detectó lectura directa de `tenant_id` desde payload público para filtrar datos sensibles.
- Se observan `tenant_id` en algunos payloads de entidades globales de core admin (p.ej. administración de usuarios/roles), esperable por diseño de consola super-admin.

## Verificación especial solicitada

### `core_role_permissions.tenant_id`

- La sustitución de permisos de rol se ejecuta con tenant de contexto de sesión en la ruta `POST /roles/{id}/permissions` y la operación delegada en `RolePermissionService::replaceRolePermissions(...)`.
- Se mantiene el principio de asignación por tenant para `core_role_permissions`.

### Reemplazo de permisos de rol

- Flujo validado:
  - sesión + permiso `roles.manage`
  - CSRF
  - carga del rol/pantalla
  - reemplazo controlado de permisos
  - auditoría (`role.permissions_replaced`)

### Asignación de roles a usuario

- Flujo validado:
  - sesión + permiso `users.manage`
  - CSRF
  - usuario objetivo
  - tenant del usuario para lista/replace de roles
  - auditoría (`user.roles_replaced`)

### Permisos de módulos administrativos

- Se mantiene control por permisos explícitos por módulo/área en rutas administrativas.

## Hallazgo y corrección aplicada en este PR (falla pequeña)

### Hallazgo

`AuthSession::getAuth()` devolvía únicamente llaves con prefijo `auth_*`.
Sin embargo, varias rutas consumían también llaves legacy (`tenant_id`, `user_id`) como fallback principal en ciertas operaciones, pudiendo derivar en `0` cuando no existían aliases.

Impacto potencial:

- contexto tenant/user incorrecto en operaciones de módulos que esperaban llaves legacy.

### Corrección

Se agregó compatibilidad explícita en `AuthSession::getAuth()` devolviendo aliases:

- `user_id` => `auth_user_id`
- `tenant_id` => `auth_tenant_id`

Con esto se evita desalineación entre rutas existentes y sesión real, sin cambiar lógica funcional de negocio.

## Pendientes / Riesgos grandes

No se detectó una falla grande que amerite PR separado para bloqueo inmediato.

## Checklist manual (si DB no disponible)

Si alguna validación depende de datos reales:

- [ ] Confirmar login y navegación dashboard con usuario real.
- [ ] Confirmar POST críticos con CSRF inválido (esperar 419).
- [ ] Confirmar reemplazo de permisos de rol en tenant A no afecta tenant B.
- [ ] Confirmar asignación de roles de usuario respeta tenant del usuario.
- [ ] Confirmar consultas de mail/drive/url-locator/landing/crm/reportes solo retornan datos del tenant de sesión.

## Comandos de verificación ejecutados

- `composer dump-autoload`
- `php -l routes/web.php`
- `php -l scripts/smoke-check.php`
- `composer smoke`

Si algún comando falla por entorno/DB, documentar explícitamente en el resultado del PR.
