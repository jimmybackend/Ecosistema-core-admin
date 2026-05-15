# ECOSISTEMA Campaign Creation Dry-Run

## Resumen
Se agrega simulación segura de creación de campaña en `/campaigns/new/dry-run` (GET/POST).

## Comportamiento
- Requiere sesión autenticada y permiso existente `modules.manage`.
- Toma `tenant_id` y `owner_user_id` desde sesión/contexto.
- No acepta `tenant_id` desde request.
- Valida payload base de campaña, landing draft y short link.
- No ejecuta INSERT/UPDATE/DELETE ni llamadas externas.
- Retorna previews seguros (sin PII completa ni secretos).

## Flags
- `ECOSISTEMA_CAMPAIGN_CREATION_DRY_RUN=false` por defecto.
- Si el flag está apagado, devuelve `feature_disabled` y bloquea el flujo.

## Divergencias y fuente canónica
- Este PR usa `adbbmis1_eco` como referencia canónica de tablas CRM/Campaigns.
- No crea tablas/campos/migraciones/seeds.
