# ECOSISTEMA Browser Analytics Dashboard Read-Only (PR #94)

## Objetivo
Habilitar `GET /browser/analytics` como panel administrativo read-only sobre `browser_analytics_daily_rollups`, aislado por tenant de sesión.

## Alcance
- Adapter: `EcosistemaBrowserAnalyticsAdapter` con capacidades explícitas read-only.
- Repository: sólo `SELECT` con PDO prepared statements.
- Service: DTO seguro de agregados sin exponer campos sensibles crudos.
- Vista: métricas agregadas, sin IPs, user agents completos, UUIDs, URLs completas ni JSON metadata crudo.
- Ruta protegida por login + `modules.view`.

## Seguridad y privacidad
- Tenant aplicado desde `AuthSession::getAuth()['auth_tenant_id']`.
- No recibe `tenant_id` desde request.
- No hay `INSERT/UPDATE/DELETE` sobre `browser_analytics_*`.
- `collector_enabled=false`, `db_write=false`, `mode=read-only`.

## Divergencias y canonicidad
- Fuente canónica aplicada: `adbbmis1_eco` (inventario en `ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md`).
- Este PR no modifica `Ecosistema-bd` ni repos externos.
