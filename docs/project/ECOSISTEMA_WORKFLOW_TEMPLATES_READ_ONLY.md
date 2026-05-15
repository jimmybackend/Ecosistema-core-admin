# Ecosistema Workflow Templates (read-only)

- Fecha: 2026-05-15
- PR: #134
- Fuente canónica: `adbbmis1_eco`

## Decisión canónica

No existe tabla `workflow_templates` confirmada en `adbbmis1_eco`. Por seguridad y trazabilidad, el catálogo de plantillas se implementa estático/documental en PHP (`EcosistemaWorkflowTemplateCatalog`) y **sin escrituras SQL**.

## Rutas

- `GET /workflow/templates`
- `GET /workflow/templates/{key}`

## Seguridad

- No se acepta `tenant_id` desde request.
- Se usa `auth_tenant_id` de sesión.
- No se exponen `conditions_json`, `config_json`, `input_json`, `output_json`, `context_json`, `metadata_json`.
- Sin `INSERT/UPDATE/DELETE`.
