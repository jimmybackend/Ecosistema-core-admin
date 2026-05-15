# ECOSISTEMA — Production Hardening Checklist

Checklist operativo para endurecer Core Admin antes de habilitar tráfico productivo.

Referencia de auditoría por módulo: `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`.

> Fuente canónica de datos/esquema: `adbbmis1_eco`.
> Este checklist **no** crea tablas/campos, no ejecuta migraciones y no modifica repos externos.

## 1) Transporte y perímetro
- [ ] Forzar HTTPS end-to-end (LB/Proxy + app) y redirigir HTTP → HTTPS.
- [ ] Habilitar HSTS con ventana inicial conservadora y subir gradualmente.
- [ ] Restringir puertos públicos a 80/443 (y 22 sólo por IP administrativa).
- [ ] Limitar acceso a panel administrativo por allowlist IP/VPN si el contexto lo permite.
- [ ] Confirmar que `APP_ENV=production` y `APP_DEBUG=false`.

## 2) Sesión, cookies y autenticación
- [ ] `SESSION_SECURE=true` en producción HTTPS.
- [ ] `SESSION_SAMESITE=Lax` (o `Strict` si el flujo lo permite).
- [ ] Definir `SESSION_IDLE_TIMEOUT` acorde a política de seguridad.
- [ ] Revisar rotación/caducidad de sesiones en `core_sessions`.
- [ ] Confirmar que no se exponen `session_token_hash` ni secretos relacionados.

## 3) CSRF, métodos y superficie HTTP
- [ ] Confirmar protección CSRF en formularios de escritura.
- [ ] Limitar métodos HTTP por ruta (GET sólo lectura; POST/PUT/DELETE controlados).
- [ ] Verificar respuestas 403/404/419/500 sin stack traces ni SQL errors.
- [ ] Verificar que no se acepta `tenant_id` desde request; debe salir de sesión/contexto.

## 4) Autorización por permisos
- [ ] Revisar `requirePermission(...)` en rutas administrativas críticas.
- [ ] Confirmar permisos mínimos por módulo (least privilege).
- [ ] Validar que usuarios sin permiso reciben 403 consistente.
- [ ] Revisar asignación real en `core_user_roles`, `core_role_permissions`, `core_permissions`.

## 5) Secretos y configuración (.env)
- [ ] No commitear `.env`; sólo `.env.example` / `.env.vm.example` con placeholders.
- [ ] Rotar claves sensibles al pasar a producción (DB/API/SMTP/AWS).
- [ ] Mantener flags de riesgo en `false` por defecto cuando aplique.
- [ ] Evitar logging de passwords, tokens, hashes y credenciales.

## 6) Headers de seguridad
- [ ] Configurar `X-Frame-Options` (`DENY` o `SAMEORIGIN`).
- [ ] Configurar `X-Content-Type-Options: nosniff`.
- [ ] Configurar `Referrer-Policy` mínima necesaria.
- [ ] Configurar `Content-Security-Policy` progresiva (iniciar en report-only si aplica).
- [ ] Configurar `Permissions-Policy` para deshabilitar features no usadas.

## 7) Rate limiting y abuso
- [ ] Definir throttling para login y endpoints sensibles.
- [ ] Monitorear `security_login_attempts` y bloquear patrones de abuso.
- [ ] Mantener listas de bloqueo con expiración (`security_blocked_ips`).
- [ ] Verificar que el bloqueo no afecte rutas internas de salud/operación.

## 8) Auditoría, trazabilidad y privacidad
- [ ] Validar escritura de auditoría en `core_audit` para acciones críticas.
- [ ] Correlacionar cambios con `audit_entity_changes` y `module_audit_links` cuando aplique.
- [ ] Enmascarar/recortar campos sensibles en vistas y logs:
  - `key_hash`, `scopes_json`
  - `old_values` / `new_values`
  - `before_json` / `after_json`
  - `metadata_json`
  - IP, `user_agent`, email, paths con query.
- [ ] Revisar consentimiento/preferencias (`privacy_consents`, `privacy_tracking_preferences`).

## 9) Backups, restore y continuidad
- [ ] Ejecutar y verificar backup de DB y artefactos críticos.
- [ ] Probar restore en entorno controlado (no productivo) con evidencia.
- [ ] Definir RPO/RTO y ventana de retención.
- [ ] Verificar runbooks operativos y responsables on-call.

## 10) Verificaciones técnicas mínimas (pre-release)
Ejecutar antes de desplegar:

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
composer smoke
```

Verificaciones manuales:
- Login + permisos por rol.
- Estado vacío seguro en pantallas read-only.
- Sin exposición de PII completa ni secretos.
- Flujos controlados por flags permanecen bloqueados cuando están apagados.

## 11) Criterio de salida (Go/No-Go)
- **GO**: checklist completo + evidencias + smoke checks en verde.
- **NO-GO**: cualquier exposición de secretos/PII sensible, bypass de permisos o rutas de escritura sin control.
