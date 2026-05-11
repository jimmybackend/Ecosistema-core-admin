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
- [Checklist de despliegue EC2/producción](#checklist-de-despliegue-ec2producción)
- [Comandos rápidos](#comandos-rápidos)
- [Notas de seguridad para producción](#notas-de-seguridad-para-producción)

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


## Variables de sesión (PR #24)
- `SESSION_NAME`: nombre de cookie de sesión PHP.
- `SESSION_SECURE`: usar `true` en producción/HTTPS para enviar cookie sólo por canal seguro.
- `SESSION_SAMESITE`: política SameSite de cookie (`Lax` por defecto).
- `SESSION_IDLE_TIMEOUT`: expiración por inactividad en segundos (por defecto `1800`).

Comportamiento de expiración por inactividad:
- Si una sesión autenticada supera `SESSION_IDLE_TIMEOUT`, se intenta revocar el registro en `core_sessions`, se destruye la sesión PHP y se redirige a `/login` sin exponer detalles internos.

Limitaciones vigentes de autenticación:
- No hay remember-me persistente.
- No hay MFA todavía.
- No hay rotación avanzada por dispositivo todavía.

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



## Configuración SMTP segura (PR #26)
Variables disponibles en `.env`/`.env.example`:
- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`
- `MAIL_SEND_ENABLED`
- `MAIL_ALLOW_TEST_SEND`

Notas clave:
- `MAIL_SEND_ENABLED=false` por defecto para mantener deshabilitado el envío real.
- Este PR **no envía correos reales** y **no implementa envío masivo**.
- SMTP real (conexión/envío de pruebas) se habilitará en un PR posterior.
- No commitear secretos ni contraseñas SMTP reales.
- Usar credenciales SMTP dedicadas y de bajo privilegio (no personales).


## Mail adjuntos lógicos (PR #27)
- Se agregó integración lógica de solo lectura en detalle de Mail (`GET /mail/messages/{id}`) para listar adjuntos cuando existen registros en tabla real `cloud_files` asociados por `origin_table = 'mail_messages'` y `origin_id = mail_messages.id`.
- Se mantiene aislamiento por `tenant_id` y `user_id`, con consultas PDO preparadas y límite de 100 resultados.
- Se muestran únicamente campos seguros: `original_name`, `mime_type`, `size_bytes`, `status`, `uploaded_at`.
- Si no hay adjuntos vinculados en datos reales, la vista muestra: `Adjuntos: no disponibles todavía en esta instalación.`

Limitaciones vigentes de esta integración:
- No hay subida de archivos.
- No hay descarga de archivos.
- No hay integración S3 real.
- No hay envío real de correos.
- No hay adjuntos salientes en compose/send.
- La validación funcional completa requiere datos reales de `mail_messages` y `cloud_files` en DB `adbbmis1_eco`.

## Checklist de despliegue EC2/producción
- Ver guía: `docs/deploy/EC2_PRODUCTION_CHECKLIST.md`.

## Comandos rápidos
```bash
composer install
composer dump-autoload
composer smoke
```

## Notas de seguridad para producción
- No commitear `.env` ni secretos reales.
- No publicar contraseñas, tokens o credenciales en README/documentación.
- Configurar el `DocumentRoot`/`root` del servidor web hacia `public/` (no a la raíz del repositorio).
