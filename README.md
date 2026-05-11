# Ecosistema Core Admin

Aplicación administrativa operativa del ecosistema para gestión interna (etapa 1 + endurecimiento inicial de autorización).

## Índice
- [Resumen](#resumen)
- [Instalación local](#instalación-local)
- [Rutas principales](#rutas-principales)
- [Módulos implementados](#módulos-implementados)
- [Tablas reales usadas](#tablas-reales-usadas)
- [Limitaciones actuales](#limitaciones-actuales)
- [Documentación del proyecto](#documentación-del-proyecto)

## Resumen
Incluye autenticación real, sesión persistida, dashboard, gestión base de tenants/usuarios/roles/permisos/módulos y módulos mínimos de system, mail, cloud y onboarding.

## Instalación local
```bash
composer install
cp .env.example .env
# configurar variables de DB en .env
php -S 127.0.0.1:8000 -t public
```

Comando recomendado tras cambios estructurales de clases:
```bash
composer dump-autoload
```

## Rutas principales
- Auth: `/login`, `POST /logout`
- Dashboard: `/dashboard`
- Tenants: `/tenants`
- Usuarios: `/users`, `/users/{id}/roles`
- Roles: `/roles`
- Permisos: `/permissions`
- Módulos: `/modules`
- System: `/system/health`, `/system/logs`, `/system/audit`
- Mail: `/mail`
- Cloud: `/cloud`
- Onboarding: `/onboarding`
- Health técnico DB: `/health/db`

## Módulos implementados
- Auth (login real con `core_users` + sesión en `core_sessions`)
- Dashboard
- Tenants
- Usuarios
- Roles
- Permisos
- Módulos
- Health / Logs / Auditoría
- Mail mínimo
- Cloud mínimo
- Onboarding base

## Tablas reales usadas
- `core_users`, `core_sessions`, `core_tenants`, `core_roles`, `core_user_roles`, `core_permissions`, `core_role_permissions`, `core_modules`
- `system_health_check_definitions`, `system_health_check_results`, `system_logs`, `core_audit`
- `mail_messages`, `mail_mailboxes`, `mail_folders`
- `cloud_files`, `cloud_folders`, `cloud_buckets`, `cloud_user_roots`
- `onboarding_flows`, `onboarding_runs`, `onboarding_run_steps`

## Limitaciones actuales
- Se agregó autorización fina por permisos en rutas administrativas existentes mediante `requirePermission($config, $code)` con validación por `auth_user_id` y `auth_tenant_id` en sesión.
- La asignación de roles de usuario usa la tabla real `core_user_roles` y reemplaza asignaciones dentro de transacción (DELETE + INSERT).
- Mail **no** realiza envío real (sin SMTP/IMAP/POP productivo).
- Cloud **no** integra S3 real ni AWS SDK.
- Onboarding no ejecuta aprovisionamiento real.
- No hay workers/cron ni API separada en este repositorio.

- Auditoría automática mínima en acciones críticas de core administrativo (tenants/users/roles/permissions/modules) usando la tabla real `core_audit`.
- Seguridad de auditoría: no se registran contraseñas, `password_hash`, `session_token_hash`, `refresh_token_hash` ni secretos de entorno.
- Limitación vigente: Mail, Cloud y Onboarding quedan fuera de esta ampliación de auditoría; tampoco se agregan exportaciones ni filtros avanzados nuevos.


## Notas de autorización por permisos
- La validación consulta únicamente tablas reales: `core_user_roles`, `core_roles`, `core_role_permissions`, `core_permissions`.
- Los permisos deben existir en `core_permissions` y estar asignados a roles en `core_role_permissions`.
- Este repositorio **no** crea seeds automáticos, migraciones ni alta automática de permisos/roles/usuarios.
- No se crean roles nuevos desde la pantalla de asignación.
- No se crean permisos automáticamente ni seeds.
- No hay auditoría automática específica para la asignación de roles todavía.
- No hay UI avanzada de perfiles, grupos o jerarquías.

## Documentación del proyecto
- `docs/project/ECOSISTEMA_FUENTE_MAESTRA.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_ESTADO_ACTUAL.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_QA_CHECKLIST.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_RUTAS.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_PENDIENTES.md`

## Smoke checks básicos (PR #22)
Ejecutar:
```bash
composer install
composer dump-autoload
composer smoke
```

### Qué valida
- Presencia de archivos críticos de Core Admin (autoload, bootstrap, rutas, index público, assets, vistas clave y README).
- Carga de `bootstrap/app.php` y `routes/web.php` sin error fatal.
- Carga de clases críticas de autorización, auditoría y roles de usuario.
- Sintaxis PHP (`php -l`) sobre `app`, `bootstrap`, `config`, `public`, `routes` y `resources/views` (ignorando `vendor`).
- Búsqueda de cadenas sensibles en `resources/views` y `routes/web.php` (`password_hash`, `session_token_hash`, `refresh_token_hash`, `DB_PASSWORD`, `AWS_SECRET`, `SECRET`).

### Qué no valida
- No reemplaza pruebas funcionales end-to-end.
- No valida reglas de negocio profundas ni cobertura completa de permisos.
- No crea migraciones, seeds ni datos de prueba.
- No requiere conexión obligatoria a DB para ejecutarse.

### Validación HTTP manual (opcional)
```bash
php -S 127.0.0.1:8000 -t public
curl -I http://127.0.0.1:8000/login
curl -I http://127.0.0.1:8000/dashboard
curl -I http://127.0.0.1:8000/health/db
```

Esperado:
- `/login` responde `200`.
- `/dashboard` sin sesión redirige a `/login`.
- `/health/db` puede responder `200` o `500` según la DB local, pero no debe exponer secretos.

> Nota: la validación funcional completa requiere DB real `adbbmis1_eco` con datos y permisos poblados.

## Manejo centralizado de errores seguros (PR #23)
- Se agregó una capa mínima de respuesta de errores en `App\Http\Response\ErrorResponder` para estandarizar respuestas HTML seguras en códigos `403`, `404`, `419` y `500`.
- Se agregaron vistas dedicadas en `resources/views/pages/errors/{403,404,419,500}.php`.
- Las vistas usan layout administrativo con sesión autenticada y layout de auth sin sesión, evitando exponer trazas, SQL, paths internos, credenciales o secretos.
- Se redujo repetición en rutas con helpers (`renderError`, `ensureValidCsrfToken`) para respuestas seguras de autorización/CSRF.

Limitaciones vigentes:
- No se agregó monitoreo externo.
- No se implementó tracking avanzado de excepciones.
- No se implementó observabilidad completa.
