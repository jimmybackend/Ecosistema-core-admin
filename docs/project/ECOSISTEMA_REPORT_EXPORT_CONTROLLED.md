# ECOSISTEMA_REPORT_EXPORT_CONTROLLED

PR #141 agrega exportación controlada con escritura opcional en `reports_exports`.

## Ruta
- `POST /reports/exports`

## Seguridad
- `tenant_id` se toma exclusivamente de sesión.
- `source_id` se valida como entero positivo.
- PII requiere confirmación explícita y flag `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII=true`.
- Sin flag de escritura, el flujo queda bloqueado en modo seguro (`write_disabled`).
- No se exponen `query_sql`, `query_json`, `layout_json`, `config_json`, ni `metadata_json` crudo.

## Flags
- `.env.example`: `ECOSISTEMA_REPORT_EXPORT_WRITE=false`
- `.env.example`: `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII=false`

## Tabla afectada
- `reports_exports`: `INSERT` controlado solo cuando `ECOSISTEMA_REPORT_EXPORT_WRITE=true`.
