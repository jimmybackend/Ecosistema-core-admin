# ECOSISTEMA AI Lead Summary Dry-Run (PR #147)

## Objetivo
Preparar un contexto seguro y sanitizado para resumen IA de lead en modo **dry-run**, sin llamadas a proveedor externo y sin escrituras en base de datos.

## Ruta
- `GET /ai/leads/{id}/summary-dry-run`

## Flags requeridas (default seguro)
- `ECOSISTEMA_AI_ENABLED=false`
- `ECOSISTEMA_AI_LEAD_SUMMARY_DRY_RUN=false`

Con ambos flags en `false`, la ruta construye contexto pero mantiene el flujo bloqueado con `blocked_reason=feature_disabled_by_flags`.

## Seguridad aplicada
- Tenant tomado de sesión autenticada (`auth_tenant_id`), no desde request.
- Validación de `id` como entero positivo.
- SQL sólo en repository con PDO prepared statements.
- Respuesta con previews/máscaras (email/teléfono/contacto/notas), sin JSON crudo.
- Sin INSERT/UPDATE/DELETE.
- Sin escritura en `os_ai_proposals`.
- Sin llamadas IA externas.

## Diferencias canónicas
Se usa `adbbmis1_eco` como referencia canónica. Si alguna estructura esperada no existe, el flujo debe quedar bloqueado de forma segura (sin inventar campos/tablas).
