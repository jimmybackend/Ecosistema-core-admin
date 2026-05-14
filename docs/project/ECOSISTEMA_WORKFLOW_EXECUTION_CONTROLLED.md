# ECOSISTEMA Workflow execution controlled

- Requiere `ECOSISTEMA_WORKFLOW_ENABLED=true` y `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=true`.
- Todas las flags por acción quedan en `false` por defecto en `.env.example`.
- Escrituras permitidas: `workflow_runs`, `workflow_run_logs`, `module_workflow_links` (opcional).
- `custom`, `webhook`, `update_record`, `send_email`, `create_task`, `create_ticket`, `create_agenda_event` quedan bloqueadas por defecto.
- `create_notification` sólo delega si flag activa; sin módulo dueño seguro se bloquea con warning.
- Workflow orquesta: no duplica lógica de negocio de módulos dueños.
- Rollback: apagar flags de workflow.
- Seguridad: sanitiza `input_json/output_json/context_json`, no expone secretos ni stack traces completos.

## Pruebas manuales
1. Flags apagadas: bloquea POST execute, sin escrituras.
2. Workflow on y acciones off: crea run/logs y bloquea acciones.
3. `create_notification` on: delega seguro o bloquea con warning.
4. Error controlado: `failed/canceled` con mensaje seguro.
