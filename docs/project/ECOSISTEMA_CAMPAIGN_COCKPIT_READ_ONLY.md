# ECOSISTEMA CAMPAIGN COCKPIT READ-ONLY

PR #131 agrega cockpit de campañas en modo solo lectura.

## Rutas
- `GET /campaigns`
- `GET /campaigns/{id}/cockpit`

## Seguridad
- Tenant aplicado desde sesión (`auth_tenant_id`).
- No se acepta `tenant_id` desde request.
- Consultas con PDO prepared statements.
- Sin escrituras DB.
- Campos sensibles protegidos: budget como `budget_present`, landing URL en preview.

## Fuentes canónicas
Se prioriza `adbbmis1_eco` para tablas CRM/Campaign, URL, Landing, Analytics y Workflow.
