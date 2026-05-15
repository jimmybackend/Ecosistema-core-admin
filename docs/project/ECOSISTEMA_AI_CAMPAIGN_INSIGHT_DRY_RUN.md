# ECOSISTEMA AI Campaign Insight Dry-Run (PR #148)

## Objetivo
Preparar contexto seguro de campaña y métricas agregadas para insight IA en modo **dry-run**, sin llamada IA externa y sin escrituras DB.

## Ruta
- `GET /ai/campaigns/{id}/insight-dry-run`

## Flags requeridas (default seguro)
- `ECOSISTEMA_AI_ENABLED=false`
- `ECOSISTEMA_AI_CAMPAIGN_INSIGHT_DRY_RUN=false`

Con ambos flags en `false`, la ruta mantiene el flujo bloqueado con `blocked_reason=feature_disabled_by_flags`.

## Seguridad aplicada
- Tenant desde sesión autenticada (`auth_tenant_id`), no desde request.
- `id` validado como entero positivo.
- SQL en repository con PDO prepared statements.
- Contexto sanitizado por previews/banderas de presencia.
- Sin JSON crudo sensible, sin PII completa, sin prompts/responses de proveedor.
- Sin INSERT/UPDATE/DELETE.
- Sin uso de `os_ai_proposals` ni llamadas externas.
