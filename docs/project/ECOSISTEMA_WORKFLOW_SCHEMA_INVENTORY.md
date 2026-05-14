# ECOSISTEMA WORKFLOW SCHEMA INVENTORY

## Propósito del módulo Workflow
Workflow en Ecosistema Core Admin actúa como **orquestador** entre módulos existentes (URL Locator, Landing, CRM, Mail/Notifications y Browser Analytics), sin duplicar lógica de negocio ni ejecutar acciones reales en esta etapa. Este documento inventaria el esquema real previo a UI/lógica funcional.

## Fuentes revisadas
- `jimmybackend/Ecosistema-core-admin` (README, rutas, smoke check y documentación de módulos relacionados).
- `jimmybackend/Ecosistema-bd` (referencia de mapeos SQL/documentación cuando aplica).
- `jimmybackend/mailit-click` sólo como referencia funcional legacy (sin copiar código).
- **Catálogo/Dump real de `adbbmis1_eco` como fuente canónica de tablas y columnas.**

> En caso de discrepancia entre fuentes, prevalece `adbbmis1_eco`.

## Tabla `workflow_rules`
Columnas reales:
- `id`
- `tenant_id`
- `name`
- `description`
- `trigger_module`
- `trigger_event`
- `conditions_json` *(sensible)*
- `is_active`
- `created_by_user_id`
- `created_at`
- `updated_at`

Rol técnico:
- Define la regla de orquestación por tenant.
- Identifica evento disparador (módulo/evento) y condiciones serializadas.

## Tabla `workflow_actions`
Columnas reales:
- `id`
- `tenant_id`
- `rule_id`
- `sort_order`
- `action_type`
- `target_module`
- `config_json` *(sensible)*
- `is_active`
- `created_at`

Acciones reales permitidas en `action_type`:
- `create_notification`
- `create_agenda_event`
- `create_ticket`
- `send_email`
- `webhook`
- `update_record`
- `create_task`
- `custom`

## Tabla `workflow_runs`
Columnas reales:
- `id`
- `tenant_id`
- `rule_id`
- `triggered_by_user_id`
- `source_module`
- `source_table` *(sensible)*
- `source_id` *(sensible)*
- `status`
- `input_json` *(sensible)*
- `output_json` *(sensible)*
- `error_message` *(sensible)*
- `started_at`
- `finished_at`
- `created_at`

Estados reales permitidos en `status`:
- `queued`
- `running`
- `success`
- `failed`
- `canceled`

## Tabla `workflow_run_logs`
Columnas reales:
- `id`
- `tenant_id`
- `run_id`
- `action_id`
- `level`
- `message` *(sensible)*
- `context_json` *(sensible)*
- `created_at`

Niveles reales en `level`:
- `debug`
- `info`
- `warning`
- `error`

## Tabla `module_workflow_links`
Columnas reales:
- `id`
- `tenant_id`
- `module_code`
- `entity_table` *(sensible)*
- `entity_id` *(sensible)*
- `workflow_rule_id`
- `workflow_run_id`
- `relation_type`
- `metadata_json` *(sensible)*
- `created_by_user_id`
- `created_at`

Valores reales en `relation_type`:
- `trigger`
- `action`
- `condition`
- `result`
- `error`
- `manual`
- `system`
- `other`

## Relación con otros módulos
Workflow debe enlazar entidades sin reemplazar ownership funcional:
- `notifications_queue`: acciones `create_notification` deben delegar en módulo Notifications.
- `mail_messages`: acciones `send_email` deben delegar en módulo Mail.
- `crm_leads`: acciones tipo `create_ticket`/`create_task`/`update_record` según contrato CRM.
- `landing_form_submissions`: evento disparador frecuente para pasar de submit a lead/notificación.
- `browser_analytics_events`: fuente potencial de triggers analíticos.
- `url_clicks`: fuente potencial de trigger inicial (click tracking).

`module_workflow_links` permite trazabilidad de relación trigger/acción/resultado entre entidades de estos módulos y una regla/run de workflow.

## Campos sensibles y reglas de exposición
Campos de cuidado:
- `conditions_json`, `config_json`, `input_json`, `output_json`, `context_json`, `metadata_json`
- `error_message`, `message`
- `source_table`, `source_id`, `entity_table`, `entity_id`
- configuración de `webhook`, `send_email`, `update_record`, `custom`

Reglas:
- No exponer JSON crudo en vistas administrativas por defecto.
- Aplicar redacción/masking de payloads, endpoints, headers, credenciales y PII.
- Nunca exponer secretos (`tokens`, passwords, keys, SMTP creds).
- Aislamiento estricto por tenant desde sesión/contexto (no aceptar `tenant_id` desde request).

## Reglas de seguridad de ejecución
- Workflow **orquesta**; no duplica lógica de negocio de módulos dueños.
- Cada acción real debe invocar servicio/repository del módulo dueño.
- Acciones reales deben estar protegidas por feature flags en `false` por defecto.
- Ejecutar **dry-run** antes de habilitar ejecución real.
- En esta etapa PR #111: sin rutas funcionales workflow, sin ejecución de reglas y sin escrituras en `workflow_runs`.

## Roadmap controlado
- **PR #112**: rules read-only.
- **PR #113**: runs read-only.
- **PR #114**: workflow dry-run.
- **PR #115**: execution controlled (flags + controles de seguridad + trazabilidad).
