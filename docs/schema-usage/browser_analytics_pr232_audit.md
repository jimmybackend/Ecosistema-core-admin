# PR #232 — Auditoría Browser Analytics vs `adbbmis1_eco.sql`

## Alcance
- Repositorio auditado: `jimmybackend/Ecosistema-core-admin`.
- Fuente de verdad de esquema: `adbbmis1_eco.sql` (contrato entregado en PR).
- Rutas inspeccionadas:
  - `app/Core/BrowserAnalytics/`
  - `routes/web.php`
  - `resources/views/pages/browser-analytics/`

## Hallazgos
- **Sin hallazgos críticos de columnas inexistentes** en consultas `SELECT` y escrituras `INSERT` sobre:
  - `browser_analytics_sessions`
  - `browser_analytics_pageviews`
  - `browser_analytics_events`
  - `browser_analytics_daily_rollups`
- `tenant_id` se filtra en lecturas administrativas (`WHERE tenant_id=:tenant_id`) y se llena en escrituras del collector desde parámetro de servicio (no desde request libre).
- Los `INSERT` de collector incluyen campos mínimos requeridos:
  - `browser_analytics_sessions`: `tenant_id`, `browser_session_uuid`
  - `browser_analytics_pageviews`: `tenant_id`, `session_id`, `page_url`
  - `browser_analytics_events`: `tenant_id`, `session_id`, `event_type`
- En vistas read-only no se exponen en crudo `query_string`, `hash_fragment`, `meta_json`, `metadata_json`.

## Acción correctiva realizada en este PR
- Se reforzó `scripts/smoke-check.php` para validar que el inventario de Browser Analytics incluya las **8 tablas reales** del contrato, no solo un subconjunto.

## Evidencia rápida (archivo/función/query)
- `EcosistemaBrowserAnalyticsDashboardRepository::summarizeTenant/summarizeDailyRollups/summarizeByCampaign/summarizeByLandingPage` usa `browser_analytics_daily_rollups` con filtro por `tenant_id`.
- `EcosistemaBrowserAnalyticsPageviewRepository::*` usa `browser_analytics_pageviews` con filtro por `tenant_id` y `session_id` cuando aplica.
- `EcosistemaBrowserAnalyticsEventRepository::*` usa `browser_analytics_events` con filtro por `tenant_id` y `pageview_id` cuando aplica.
- `EcosistemaBrowserAnalyticsCollectorRepository::*` escribe únicamente en `browser_analytics_sessions`, `browser_analytics_pageviews`, `browser_analytics_events`.
- `routes/web.php` protege `POST /browser/analytics/collector-dry-run` con sesión + permiso + CSRF.
