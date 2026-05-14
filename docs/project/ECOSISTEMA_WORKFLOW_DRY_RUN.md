# ECOSISTEMA Workflow Dry-run

PR #114 agrega simulación administrativa de Workflow.

## Qué simula
- Coincidencia de regla por `rule_id` o por `trigger_module` + `trigger_event`.
- Presencia de `conditions_json` (sin exponer contenido crudo).
- Lista de acciones con `would_execute` y `executed=false`.

## Qué NO ejecuta
- No crea `workflow_runs`.
- No crea `workflow_run_logs`.
- No crea `module_workflow_links`.
- No crea notifications/tasks/tickets/eventos.
- No envía email.
- No llama webhooks.
- No realiza escrituras DB.

## Seguridad
- Aislamiento por tenant desde sesión.
- `tenant_id` no se acepta desde request.
- CSRF obligatorio en POST.
- Sin `eval`, sin ejecución de `conditions_json`/`config_json`.
- Exposición de JSON sensible deshabilitada.

## Preparación PR #115
- Deja DTO estable para habilitar ejecución controlada por flags en siguiente PR.
