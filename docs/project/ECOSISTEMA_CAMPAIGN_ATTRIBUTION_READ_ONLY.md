# Ecosistema Campaign Attribution Read-only (PR #124)

## Objetivo
Mostrar campañas del tenant autenticado y su embudo de atribución en modo lectura:
`clicks -> visits -> submissions -> leads -> conversions`.

## Rutas
- `GET /attribution/campaigns`
- `GET /attribution/campaigns/{id}`

## Origen de datos canónico
Se consulta únicamente `adbbmis1_eco` con tablas reales:
- `crm_marketing_campaigns`
- `url_clicks`
- `landing_visits`
- `landing_form_submissions`
- `crm_campaign_leads`
- `crm_lead_conversions`

## Seguridad
- Tenant aplicado desde sesión (`auth_tenant_id` / `tenant_id`), nunca desde request.
- Sólo lecturas `SELECT` con PDO prepared statements.
- Vista de detalle expone sólo conteos agregados y campos no sensibles de campaña.
- Sin escritura de DB, sin migraciones, sin seeds, sin cambios de esquema.

## Limitaciones conocidas
- Si una campaña no existe para el tenant, responde estado vacío seguro en vista detalle.
- No recalcula ni persiste atribución; sólo muestra estado actual agregado.
