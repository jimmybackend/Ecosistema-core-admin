# ECOSISTEMA Workflow template install controlled

PR #136 habilita instalación controlada por bandera para plantillas Workflow.

## Ruta
- `POST /workflow/templates/{key}/install`

## Controles de seguridad
- Fuente de templates: catálogo estático PHP (`EcosistemaWorkflowTemplateCatalog`), sin tabla `workflow_templates`.
- `tenant_id` y `user_id` se toman de sesión autenticada; no se reciben desde request.
- Escrituras permitidas solo en:
  - `workflow_rules`
  - `workflow_actions`
- Flag requerida:
  - `ECOSISTEMA_WORKFLOW_TEMPLATE_INSTALL_WRITE=false` por defecto.
- La regla y acciones creadas quedan inactivas (`is_active=0`) para activación manual posterior.
- No se exponen `conditions_json` ni `config_json` completos en vistas.

## Diferencias documentadas
- `adbbmis1_eco` no confirma tabla `workflow_templates`; se mantiene catálogo estático documental.
