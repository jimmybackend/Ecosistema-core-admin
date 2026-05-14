# Ecosistema Permissions Audit (read-only)

PR #118 agrega una auditoría de permisos por módulo para el tenant autenticado, sin escrituras en DB.

## Rutas
- `GET /security/permissions-audit`
- `GET /security/permissions-audit/modules/{code}`

## Alcance técnico
- Repositorio: `App\Core\Security\EcosistemaPermissionAuditRepository`
- Servicio: `App\Core\Security\EcosistemaPermissionAuditService`
- Sólo usa `SELECT` sobre:
  - `core_modules`
  - `core_permissions`
  - `core_roles`
  - `core_role_permissions`

## Seguridad
- `tenant_id` se toma únicamente de sesión (`AuthSession`), nunca desde request.
- Consultas PDO preparadas.
- Sin exposición de campos sensibles (no JSON crudo, no hashes, no payloads).
- Sin cambios de roles/permisos; únicamente observabilidad.
