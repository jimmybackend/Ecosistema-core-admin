# ECOSISTEMA_REPORT_EXPORT_DRY_RUN

## Objetivo
Simular exportación de reportes (CSV/XLSX) sin generar archivos ni escribir en `reports_exports`.

## Rutas
- `GET /reports/exports/dry-run`
- `POST /reports/exports/dry-run`

## Seguridad
- Toma `tenant_id` únicamente desde sesión (`auth_tenant_id`).
- No acepta `tenant_id` desde request.
- Sin `INSERT/UPDATE/DELETE`.
- Preview limitado a columnas permitidas por tipo de reporte.
- No expone `query_sql`, `query_json`, `layout_json`, `config_json`, `metadata_json` ni PII completa.

## Flag
- `.env.example`: `ECOSISTEMA_REPORT_EXPORT_DRY_RUN=false` (default apagado).

## Notas
- Si el flag está apagado, el flujo queda bloqueado con `blocked_reason=feature_disabled`.
- La simulación sólo devuelve `allowed_columns` y `rows_preview` sanitizado.
