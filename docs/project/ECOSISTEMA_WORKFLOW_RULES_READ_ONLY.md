# ECOSISTEMA Workflow Rules Read-only

PR #112 habilita una UI administrativa **read-only** para reglas de Workflow.

## Alcance
- Rutas protegidas: `/workflow`, `/workflow/rules`, `/workflow/rules/{id}`.
- Repositorio y servicio de solo lectura para `workflow_rules` y `workflow_actions`.
- Aislamiento por `tenant_id` tomado de sesión autenticada.
- Sin escrituras DB, sin ejecución de reglas ni acciones.

## Seguridad
- Solo `SELECT` con PDO prepared statements.
- No se exponen `conditions_json` ni `config_json` crudos.
- Se usa `workflow.view` si existe; fallback documentado a `modules.view`.
- DTOs incluyen `mode=read-only`, `db_write=false`, `execution_enabled=false`.

## Diferencias frente a etapas futuras
- No hay manejo de `workflow_runs` ni `workflow_run_logs`.
- No hay dry-run ni ejecución real.
