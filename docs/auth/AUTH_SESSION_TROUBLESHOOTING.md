# Auth Session Troubleshooting (VM/HTTP)

## Señal de diagnóstico
- El login responde `302` con `Location: /dashboard` y emite cookie de sesión.
- Luego `/dashboard` redirige a `/login` y/o elimina la cookie.

## Qué revisar primero
1. **Idle timeout**
   - Revisar `SESSION_IDLE_TIMEOUT` en `.env`.
   - Revisar `startAuthSession()` en `routes/web.php`: sólo debe destruir sesión si `AuthSession::enforceIdleTimeout(...)` devuelve `false`.

2. **Configuración HTTP/HTTPS en VM**
   - Para pruebas en HTTP (VM local): `SESSION_SECURE=false`.
   - En HTTPS real: `SESSION_SECURE=true`.
   - Mantener `SESSION_SAMESITE=Lax` salvo requerimiento explícito distinto.

3. **Persistencia esperada de sesión**
   - Después de login exitoso, la sesión debe conservar:
     - `auth_user_id`
     - `auth_tenant_id`
     - `auth_email`
     - `auth_core_session_id`

## Prácticas seguras
- No publicar contraseñas en logs, issues, PRs o chat.
- No exponer cookies de sesión ni tokens completos.
- No compartir hashes sensibles completos.
