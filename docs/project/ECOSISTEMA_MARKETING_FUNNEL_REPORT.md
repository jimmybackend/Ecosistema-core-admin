# ECOSISTEMA — Marketing Funnel Report (read-only)

## Objetivo
Exponer `GET /reports/marketing-funnel` para visualizar un embudo de marketing por rango de fechas y filtros opcionales de campaña/landing, sin escrituras en base de datos.

## Fuente canónica
Se usa `adbbmis1_eco` como fuente canónica. Este PR no crea migraciones, seeds, tablas ni columnas.

## Tablas consultadas (read-only)
- `url_clicks`
- `landing_visits`
- `landing_form_submissions`
- `crm_leads`
- `crm_lead_conversions`
- Catálogos de filtro:
  - `crm_marketing_campaigns`
  - `landing_pages`

## Seguridad
- `tenant_id` se toma de la sesión autenticada (`auth_tenant_id`).
- No se acepta `tenant_id` desde request.
- Parámetros `campaign_id` y `landing_id` se validan como enteros positivos opcionales.
- Consultas con PDO prepared statements.
- Vista sin exposición de JSON crudo, PII completa ni SQL errors.

## Permisos
- Se reutiliza permiso existente: `campaigns.view`.

## Diferencias y alcance
- No depende de `reports_saved_queries` ni `reports_exports`.
- No guarda reporte ni dispara exportaciones.
- Si no hay datos, presenta estado vacío seguro.
