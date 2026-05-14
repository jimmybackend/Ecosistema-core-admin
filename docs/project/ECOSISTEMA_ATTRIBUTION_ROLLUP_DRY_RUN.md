# Ecosistema Attribution Rollup Dry-run

Se agrega simulación de rollups de atribución en memoria para admins.

## Rutas
- `GET /attribution/rollups/dry-run`
- `POST /attribution/rollups/dry-run`

## Flag
- `ECOSISTEMA_ATTRIBUTION_ROLLUP_DRY_RUN=false` por defecto en `.env.example`.

## Comportamiento
- Requiere sesión autenticada y permiso existente `modules.view`.
- Usa `tenant_id` desde sesión (`auth_tenant_id/tenant_id`), no desde request.
- Valida rango `start_date`/`end_date` con formato `YYYY-MM-DD`.
- Ejecuta consultas read-only con PDO prepared statements sobre:
  - `url_clicks`
  - `landing_visits`
  - `browser_analytics_sessions`
  - `landing_form_submissions`
  - `browser_analytics_attribution`
  - `crm_marketing_campaigns` (etiquetas)
- No escribe en `browser_analytics_daily_rollups` ni en `reports_exports`.

## Seguridad
- Vista sin exposición de IPs, user agents, URLs completas, tokens o payloads crudos.
- Errores internos devuelven mensaje genérico.
