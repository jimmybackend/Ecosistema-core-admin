# ECOSISTEMA Workflow Runs Read-only

PR #113 habilita consulta administrativa read-only de ejecuciones Workflow.

## Tablas reales usadas
- `workflow_runs`
- `workflow_run_logs`
- `workflow_rules`
- `workflow_actions`
- `module_workflow_links` (resumen)
- `core_users` (label seguro de `triggered_by_user_id`)

## Campos visibles
- Runs: id, regla, módulo origen, estado, banderas de presencia de JSON, preview seguro de error, timestamps, conteos de logs/links.
- Logs: id, run_id, action_id, action_type, level, `message_preview`, `context_json_present`, created_at.
- Links: id, module_code, `entity_table_preview`, `entity_id_present`, relation_type, `metadata_json_present`, created_at.

## Campos protegidos
No se expone crudo: `input_json`, `output_json`, `context_json`, `metadata_json`, `error_message`, `message`.

## Seguridad operativa
- Solo `SELECT` con PDO prepared statements y filtro por `tenant_id`.
- Validación de `id` entero positivo y límites en rango 1..300.
- No crea runs.
- No cambia status.
- No reintenta workflow.
- No ejecuta acciones.
