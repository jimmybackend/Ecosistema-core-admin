# CORE Admin RBAC Tenant Alignment (`core_role_permissions`)

## Objetivo

Verificar que la asignación rol-permiso en Core Admin sea **tenant-safe** y que la documentación refleje el comportamiento real del código.

## Evidencia verificada en código

### 1) Derivación del tenant en flujo de asignación de permisos a rol

- Ruta `POST /roles/{id}/permissions`: obtiene tenant de sesión (`AuthSession::getAuth()`), priorizando `tenant_id` y fallback a `auth_tenant_id`; no lee `tenant_id` desde request libre.  
  Archivo: `routes/web.php`.
- `RolePermissionService::replaceRolePermissions(...)`: vuelve a cargar el rol y valida consistencia estricta `sessionTenantId === roleTenantId`. Si no coincide, retorna “No autorizado para modificar este rol.”  
  Archivo: `app/Core/Permissions/RolePermissionService.php`.

### 2) Aislamiento por tenant en `core_role_permissions`

- `PermissionRepository::listRolePermissionIds(...)` ejecuta `SELECT ... WHERE role_id=:role_id AND tenant_id=:tenant_id`.
- `PermissionRepository::replaceRolePermissions(...)` ejecuta:
  - `DELETE ... WHERE role_id=:role_id AND tenant_id=:tenant_id`
  - `INSERT INTO core_role_permissions (tenant_id, role_id, permission_id, created_at) ...`

Archivo: `app/Core/Permissions/PermissionRepository.php`.

## Resultado

- No hay `INSERT` en `core_role_permissions` sin `tenant_id` en el repositorio de permisos actual.
- `DELETE` y `SELECT` usados para rol↔permiso están acotados por `tenant_id`.
- El tenant usado para escritura no proviene de un campo libre del request.

## Guardas de regresión (smoke-check)

Se reforzaron validaciones en `scripts/smoke-check.php` para fallar si:

- aparece `INSERT INTO core_role_permissions` sin `tenant_id` en columnas,
- el `DELETE` de role-permissions no incluye `role_id + tenant_id`,
- el `SELECT` de role-permissions no incluye `role_id + tenant_id`.

Con esto, una regresión de aislamiento multi-tenant en RBAC queda detectable en `composer smoke`.
