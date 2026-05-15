# ECOSISTEMA_RATE_LIMIT_DRY_RUN

Simulación interna de rate limiting por módulo usando datos existentes (`system_api_requests`, `security_login_attempts`) sin bloquear requests ni escribir en base de datos.

## Rutas
- `GET /security/rate-limit/dry-run`
- `POST /security/rate-limit/dry-run`

## Flags
- `ECOSISTEMA_RATE_LIMIT_ENABLED=false`
- `ECOSISTEMA_RATE_LIMIT_DRY_RUN=false`

## Seguridad
- Tenant aplicado desde sesión autenticada.
- `tenant_id` desde request se ignora explícitamente.
- Sin `INSERT/UPDATE/DELETE`.
- Previews enmascaradas de `ip_address` y `path`.
- No se exponen `user_agent`, emails, hashes ni payloads sensibles crudos.

## Resultado
Retorna estado `mode=dry-run`, `would_block` y métricas agregadas dentro de la ventana simulada.
