# ECOSISTEMA CRM Followup Task Controlled (PR #129)

## Objetivo
Habilitar creación controlada de tareas de seguimiento (`crm_tasks`) para leads CRM, usando CSRF, permiso existente y boundary de tenant desde sesión.

## Ruta
- `POST /crm/leads/{id}/followup-tasks`

## Seguridad y controles
- Flag por defecto apagada: `ECOSISTEMA_CRM_FOLLOWUP_TASK_WRITE=false`.
- Requiere autenticación y permiso existente `modules.manage`.
- Requiere token CSRF válido.
- No acepta `tenant_id` desde request; usa `auth_tenant_id` de sesión.
- Validaciones: `id`, `assigned_user_id` enteros positivos; `title` requerido; `due_at` (`Y-m-d\TH:i`); `priority` en `low|medium|high`.
- INSERT únicamente en `crm_tasks` desde `EcosistemaCrmFollowupTaskRepository`.
- Resultado y vistas muestran solo previews/estado, sin PII completa ni errores SQL.

## Diferencias con dry-run
- Dry-run (`/crm/leads/{id}/followup-task-dry-run`) no escribe en BD.
- Write controlado crea `crm_tasks` solo si flags/permisos/validaciones pasan.

## Fuente canónica de datos
Se usa estructura de `adbbmis1_eco` para `crm_tasks` y `crm_leads`.
