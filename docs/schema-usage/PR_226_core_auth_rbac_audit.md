# PR 226 — Auditoría Core/Auth/RBAC contra tablas core_* reales

## Correcciones aplicadas

1. **`core_audit`**
   - Se corrigió `AuditLogger` para usar columnas reales: `entity_table`, `old_values`, `new_values`, `module_code`.
   - Se mantuvo compatibilidad con payload legado (`entity_type`) mapeándolo a `entity_table`.
   - Se corrigió `AuditRepository::listRecent` para seleccionar columnas reales de `core_audit`.

2. **Regla tenant en escrituras administrativas (`core_roles`, `core_users`)**
   - En rutas `POST /roles`, `POST /roles/{id}`, `POST /users`, `POST /users/{id}` se dejó de confiar en `tenant_id` del request.
   - `tenant_id` ahora se deriva de sesión autenticada (`tenant_id`/`auth_tenant_id`) y se inyecta al payload interno del servicio.

## Evidencia resumida

- Archivo: `app/Core/System/AuditLogger.php` — función `log` — `INSERT core_audit` corregido a contrato real.
- Archivo: `app/Core/System/AuditRepository.php` — función `listRecent` — `SELECT core_audit` corregido a contrato real.
- Archivo: `routes/web.php` — rutas POST de roles/usuarios — `tenant_id` ahora desde sesión/contexto autenticado.

## Observaciones

- No se agregaron migraciones ni cambios de esquema.
- No se detectó exposición de hashes sensibles en las vistas auditadas de users/roles/permissions/modules.
