# ECOSISTEMA_RATE_LIMIT_CONTROLLED

Enforcement de rate limiting controlado por flags para admin.

## Ruta
- `POST /security/rate-limit/enforce`

## Flags
- `ECOSISTEMA_RATE_LIMIT_ENABLED=false`
- `ECOSISTEMA_RATE_LIMIT_WRITE_BLOCKS=false`

## Comportamiento
- Usa `tenant_id` y `user_id` solo desde sesión autenticada.
- Ignora `tenant_id` recibido en request.
- Solo escribe en `security_blocked_ips` y `security_incidents` cuando ambas flags están activas.
- Si flags están apagadas, permanece en modo seguro sin escrituras.

## Datos sensibles
- Solo previews enmascaradas de IP/path.
- No muestra JSONs completos, hashes, tokens, user_agent ni emails.
