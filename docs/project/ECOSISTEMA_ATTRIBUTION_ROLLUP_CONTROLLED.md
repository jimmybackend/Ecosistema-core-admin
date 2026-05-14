# ECOSISTEMA_ATTRIBUTION_ROLLUP_CONTROLLED

PR #126 agrega la ruta `POST /attribution/rollups/generate` con control estricto por flags.

## Flags
- `ECOSISTEMA_ATTRIBUTION_ENABLED=false`
- `ECOSISTEMA_ATTRIBUTION_ROLLUP_WRITE=false`

## Comportamiento de seguridad
- `tenant_id` se toma sólo de sesión autenticada.
- No acepta `tenant_id` por request.
- Usa consultas PDO preparadas.
- No imprime SQL errors/stack traces.
- No expone PII ni payloads sensibles.

## Estrategia de escritura
- Se permite evaluar métricas agregadas para un `rollup_date`.
- Escritura real permanece bloqueada (`blocked_reason=idempotency_not_guaranteed`) hasta confirmar estrategia idempotente segura basada en estructura real de `browser_analytics_daily_rollups`.
- No se crean migraciones, seeds, tablas ni columnas.
