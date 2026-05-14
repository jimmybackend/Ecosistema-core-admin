# CRM followup task dry-run

PR #128 agrega simulación de creación de tarea CRM para lead sin escribir en `crm_tasks`.

Rutas:
- `GET /crm/leads/{id}/followup-task-dry-run`
- `POST /crm/leads/{id}/followup-task-dry-run`

Flag:
- `ECOSISTEMA_CRM_FOLLOWUP_TASK_DRY_RUN=false` (default).

Reglas:
- tenant tomado de sesión.
- no acepta `tenant_id` desde request.
- valida lead, assigned_user_id, due_at, priority y title.
- `db_write=false` siempre.
