# ECOSISTEMA_WORKFLOW_TEMPLATE_INSTALL_DRY_RUN

PR #135 agrega simulación de instalación de plantilla de workflow sin escrituras SQL.

## Rutas
- `GET /workflow/templates/{key}/install-dry-run`
- `POST /workflow/templates/{key}/install-dry-run`

## Fuente canónica y alcance
- Base canónica: `adbbmis1_eco`.
- No existe tabla `workflow_templates` confirmada, por lo que se usa catálogo estático en `EcosistemaWorkflowTemplateCatalog`.
- Esta entrega **no escribe** `workflow_rules` ni `workflow_actions`; sólo muestra preview de lo que se crearía.

## Seguridad
- `tenant_id` se toma de sesión/auth context.
- No se acepta `tenant_id` desde request.
- No expone `conditions_json` ni `config_json` crudos; sólo flags `*_present`/`*_exposed=false`.
- No ejecuta acciones externas (`send_email`, `webhook`, etc.).

## Flag
- `.env.example`: `ECOSISTEMA_WORKFLOW_TEMPLATE_INSTALL_DRY_RUN=false` por defecto.

## Resultado esperado
- Admin puede seleccionar plantilla y ver rule/actions simuladas.
- Flujo permanece bloqueado para escritura por diseño (`db_write=false`).
