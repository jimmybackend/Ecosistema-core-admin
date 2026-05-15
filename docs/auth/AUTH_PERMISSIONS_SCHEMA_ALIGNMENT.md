# AUTH Permissions Schema Alignment

## Contexto
La base canónica `adbbmis1_eco` usa `core_roles.slug` como identificador semántico del rol.

La verificación de acceso en rutas protegidas debe validar permisos por `core_permissions.code`, enlazando asignaciones por tenant y usuario.

## Regla aplicada en `requirePermission`
`requirePermission` toma el usuario autenticado desde `AuthSession::getAuth()` y valida:

- `auth_user_id`
- `auth_tenant_id`
- `core_user_roles`
- `core_role_permissions`
- `core_permissions.code`

Consulta conceptual:

```sql
SELECT COUNT(*)
FROM core_user_roles ur
JOIN core_role_permissions rp
  ON rp.tenant_id = ur.tenant_id
 AND rp.role_id = ur.role_id
JOIN core_permissions p
  ON p.id = rp.permission_id
WHERE ur.tenant_id = :tenant_id
  AND ur.user_id = :user_id
  AND p.code = :permission_code;
```

No se requiere `core_roles.status` ni `core_permissions.status` para autorizar rutas.

## Diagnóstico de 403
Si una ruta protegida responde 403, validar en orden:

1. Existe sesión autenticada (`auth_user_id`, `auth_tenant_id`).
2. El usuario tiene al menos un rol en `core_user_roles` para su tenant.
3. El permiso solicitado existe en `core_permissions.code`.
4. La relación rol-permiso existe en `core_role_permissions` para el mismo tenant.

SQL de diagnóstico manual sugerido:

```sql
SELECT
  u.id AS user_id,
  u.email,
  r.id AS role_id,
  r.name AS role_name,
  r.slug AS role_slug,
  p.code
FROM core_users u
JOIN core_user_roles ur ON ur.user_id = u.id AND ur.tenant_id = u.tenant_id
JOIN core_roles r ON r.id = ur.role_id
JOIN core_role_permissions rp ON rp.role_id = r.id AND rp.tenant_id = ur.tenant_id
JOIN core_permissions p ON p.id = rp.permission_id
WHERE u.id = 1
ORDER BY p.code;
```


## Actualización PR #157 (tenant_id obligatorio en asignación rol↔permiso)
- La asignación de permisos de rol se alinea con esquema canónico: `core_role_permissions.tenant_id` **siempre** se inserta.
- El tenant para asignación se toma del rol (`core_roles.tenant_id`) cargado en repositorio/servicio.
- No se acepta `tenant_id` desde request para este flujo.
- Las lecturas y borrados de `core_role_permissions` en la pantalla de asignación filtran por `role_id` + `tenant_id`.
