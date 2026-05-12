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
- [Estado operativo actual](#estado-operativo-actual)
- [Integración futura con S3 Drive](#integración-futura-con-s3-drive)
- [Inventario técnico S3 Drive](#inventario-técnico-s3-drive)
- [Mapeo DB Cloud/S3](#mapeo-db-clouds3)
- [Configuración compartida S3 Drive](#configuración-compartida-s3-drive)
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

## Estado operativo actual
- Estado consolidado: `docs/project/CORE_ADMIN_OPERATIONAL_CLOSURE.md`.
- Resumen operativo: módulos activos, comandos, rutas, variables, riesgos, limitaciones y pendientes mayores.
- Este README mantiene un resumen breve para evitar duplicidad; el detalle vive en el documento de cierre.


## Integración futura con S3 Drive
- Contrato documental de integración: `docs/project/CORE_ADMIN_S3_DRIVE_INTEGRATION_CONTRACT.md`.
- Core Admin mantiene operación Cloud local/controlada en estado actual.
- La integración con `jimmybackend/s3` se tratará como componente separado y sólo por contrato explícito en PRs futuros.
- Nota de propiedad canónica (PR #45): `cloud_*` en `adbbmis1_eco` (referencia en `jimmybackend/Ecosistema-bd`) es la estructura canónica del sistema `s3` / ArcadeCloud Drive; Core Admin no debe duplicarla ni modificarla desde este repositorio.

## Inventario técnico S3 Drive
- Inventario documental de preparación: `docs/project/S3_DRIVE_TECHNICAL_INVENTORY.md`.
- Este inventario no activa integración real ni modifica el repositorio `jimmybackend/s3`.

## Mapeo DB Cloud/S3
- Mapeo documental de tablas Cloud/S3 para integración futura: `docs/project/CLOUD_S3_DATABASE_MAPPING.md`.
- No activa AWS/S3 real ni modifica esquema de base de datos.

## Configuración compartida S3 Drive
- Configuración compartida y segura para integración futura: `docs/project/S3_DRIVE_SHARED_CONFIGURATION.md`.
- Modo por defecto `contract`, sin llamadas reales al repo `s3` y sin activación AWS/S3.

## Documentación del proyecto
- `docs/project/ECOSISTEMA_FUENTE_MAESTRA.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_ESTADO_ACTUAL.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_QA_CHECKLIST.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_RUTAS.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_PENDIENTES.md`
- `docs/project/CORE_ADMIN_OPERATIONAL_CLOSURE.md`
- `docs/project/CORE_ADMIN_S3_DRIVE_INTEGRATION_CONTRACT.md`
- `docs/project/S3_DRIVE_TECHNICAL_INVENTORY.md`
- `docs/project/CLOUD_S3_DATABASE_MAPPING.md`
- `docs/project/S3_DRIVE_SHARED_CONFIGURATION.md`
- `docs/ops/MONITORING_OPERATIONS_PLAN.md`

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

## Configuración segura Cloud/S3 (PR #28)
Variables disponibles en `.env`/`.env.example`:
- `CLOUD_DISK`
- `CLOUD_S3_ENABLED`
- `CLOUD_ALLOW_DOWNLOADS`
- `CLOUD_ALLOW_UPLOADS`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`
- `AWS_ENDPOINT`
- `AWS_USE_PATH_STYLE_ENDPOINT`

Notas clave:
- `CLOUD_S3_ENABLED=false` por defecto.
- Este PR **no conecta a AWS**.
- Este PR **no sube archivos reales**.
- Este PR **no descarga archivos reales**.
- S3 real se habilitará en un PR posterior.
- No commitear secretos ni llaves reales de AWS.
- Cuando se habilite S3 real, usar IAM dedicado con permisos mínimos (no root).

## Checklist de despliegue EC2/producción
- Ver guía: `docs/deploy/EC2_PRODUCTION_CHECKLIST.md`.

## Comandos rápidos
```bash
composer install
composer dump-autoload
composer smoke
composer cron:check
composer cron:health
composer cron:sessions
composer ops:monitor
```

### Cron seguro (jobs controlados)
- `composer cron:check`: valida autoload/bootstrap y modo seguro sin tocar DB.
- `composer cron:health`: ejecuta únicamente el job controlado `health-checks`.
- `composer cron:sessions`: ejecuta el job controlado `session-cleanup` para revocar sesiones expiradas en `core_sessions` según `SESSION_IDLE_TIMEOUT`.
- `cron:health` usa checks existentes del módulo System con `check_type` `db/database` y registra resultados/logs si las tablas reales están disponibles.
- `cron:sessions` usa UPDATE seguro (`revoked_at`) y no elimina usuarios/roles/permisos ni expone tokens/hashes.
- Requiere DB real configurada (`adbbmis1_eco`) en `.env`.
- **No** ejecuta AWS, SMTP, procesamiento de archivos, workers permanentes ni checks HTTP externos.

## Notas de seguridad para producción
- No commitear `.env` ni secretos reales.
- No publicar contraseñas, tokens o credenciales en README/documentación.
- Configurar el `DocumentRoot`/`root` del servidor web hacia `public/` (no a la raíz del repositorio).

## Subida controlada Cloud (PR #29)
- Rutas: `GET /cloud/files/upload` y `POST /cloud/files/upload`.
- Protecciones: sesión activa, permiso `cloud.manage`, CSRF en POST.
- Variables nuevas: `CLOUD_MAX_UPLOAD_MB`, `CLOUD_ALLOWED_EXTENSIONS`, `CLOUD_UPLOAD_PREFIX`, `CLOUD_LOCAL_STORAGE_PATH`.
- Si `CLOUD_ALLOW_UPLOADS=false`, la subida se bloquea.
- Si `CLOUD_S3_ENABLED=false`, se usa almacenamiento local controlado en `storage/app/cloud` (nunca en `public/`).
- Si `CLOUD_S3_ENABLED=true` sin AWS SDK, se muestra limitación segura y no se sube.
- Se registran metadatos en `cloud_files` con columnas reales usadas por el repositorio actual.
- No hay descarga pública ni signed URLs en este PR.
- No guardar secretos en código/documentación.
- Verificar permisos de escritura del directorio `storage/app/cloud` antes de habilitar uploads.

## Descarga controlada Cloud (PR #30)
- Ruta: `GET /cloud/files/{id}/download`.
- Protecciones: sesión, permiso fino (`cloud.view` o `cloud.manage`), validación por `tenant_id` y `user_id` del archivo.
- `CLOUD_ALLOW_DOWNLOADS=false` por defecto; con este valor la descarga se bloquea.
- En este PR solo se descarga almacenamiento local bajo `CLOUD_LOCAL_STORAGE_PATH`.
- No hay descarga S3 real, no hay signed URLs y no hay rutas públicas.
- Seguridad: resolución por `id` en `cloud_files`, validación anti path traversal, headers `attachment` y `X-Content-Type-Options: nosniff`.
- No exponer `storage/` públicamente.

## Plan operativo de workers/cron (PR #31)
- Documento: `docs/ops/WORKERS_CRON_PLAN.md`.
- Validación segura (sin ejecutar jobs reales):

```bash
composer cron:check
```

Estado actual:
- No hay workers activos todavía.
- No hay colas reales todavía.
- No se envían correos desde workers.
- No se procesan archivos desde workers.
- No hay sincronización AWS/S3 activa desde cron.

## Onboarding ejecución segura inicial (PR #32)
- Se agregó una capa de ejecución controlada para avanzar runs existentes paso a paso sin aprovisionamiento externo real.
- Tipos soportados en esta fase: `action_type` vacío/null, `noop`, `manual`, `checklist`.
- Tipos no soportados: se marcan como `skipped` con log de advertencia, sin ejecutar acciones externas.
- Se registra trazabilidad en `onboarding_run_logs` y auditoría administrativa (`onboarding.run_started`, `onboarding.step_completed`, `onboarding.step_skipped`, `onboarding.run_completed`).
- No hay AWS, SMTP, workers automáticos ni cron activo en esta fase.
- La automatización completa queda para PR posterior.

## Mail: envío individual (preparación PR #35)
- Estado: **infraestructura interna lista para preview/preparación**.
- `MAIL_SEND_ENABLED=false` por defecto.
- `MAIL_ALLOW_TEST_SEND=false` por defecto.
- Este PR **no envía correos reales por defecto** (modo dry-run/preparación).
- Sin envío masivo, sin campañas, sin workers/colas de mail.
- Adjuntos salientes: pendientes para PR posterior.


## Envío individual controlado de borradores (PR #36)
- Requiere `MAIL_SEND_ENABLED=true` y `MAIL_ALLOW_TEST_SEND=true`.
- Requiere SMTP válido (`MAIL_HOST`, `MAIL_PORT`, `MAIL_FROM_ADDRESS` y credenciales si el servidor las solicita).
- Ruta operativa: `GET /mail/messages/{id}/send-preview` y `POST /mail/messages/{id}/prepare-send`.
- Protecciones: sesión, permiso `mail.manage`, CSRF, aislamiento por `tenant_id` y `user_id`.
- Valida máximo 10 destinatarios, formato email válido, borrador no eliminado y contenido mínimo (asunto o cuerpo).
- Riesgo controlado: si flags están en `false`, el envío se bloquea y no intenta SMTP.
- Auditoría esperada: `mail.send_attempted`, `mail.sent`, `mail.send_failed`, `mail.send_blocked_by_config`.
- No incluye envío masivo, campañas, workers, colas, reintentos, tracking ni webhooks.
- Adjuntos salientes: pendientes para PR posterior.


## PR #37 — Adjuntos Cloud en borradores Mail
- Gestión de adjuntos lógicos por rutas protegidas: `GET /mail/messages/{id}/attachments` y `POST /mail/messages/{id}/attachments`.
- Relación usada: tabla real `cloud_files` con `origin_table = 'mail_messages'` y `origin_id = mail_messages.id`.
- Seguridad: sesión + permiso `mail.manage` + CSRF + aislamiento `tenant_id`/`user_id`; sólo se aceptan IDs (`cloud_file_ids[]`).
- No hay subida de archivos desde Mail, no hay envío masivo, campañas, workers ni colas.
- En este PR los adjuntos se preparan lógicamente para preview; el envío binario MIME queda pendiente para PR posterior.

## Mail envío individual con adjuntos locales (PR #38)
- Se habilita envío controlado de **un solo borrador** con adjuntos Cloud ya asociados.
- Requiere: `MAIL_SEND_ENABLED=true` y `MAIL_ALLOW_TEST_SEND=true`.
- Límites configurables: `MAIL_MAX_ATTACHMENTS`, `MAIL_MAX_ATTACHMENT_MB`, `MAIL_MAX_TOTAL_ATTACHMENT_MB`.
- Se bloquea envío si hay adjuntos inválidos (inexistentes, fuera de `CLOUD_LOCAL_STORAGE_PATH`, S3-only/remotos o tamaño/cantidad excedidos).
- No hay envío masivo, campañas, workers, colas, S3 real ni signed URLs.
- Recomendado: probar primero con archivos pequeños y SMTP controlado.


## Backup/Restore operativo (PR #39)
- Ver plan: `docs/ops/BACKUP_RESTORE_PLAN.md`.
- Check no destructivo: `composer backup:check`.
- **No guardar backups dentro de este repositorio**.
- **No commitear `.env` ni dumps SQL con datos reales**.
- Todo restore debe probarse primero en un ambiente separado de producción.


## Nota operativa de seguridad
- No imprimir ni commitear secretos (`DB_PASSWORD`, `MAIL_PASSWORD`, `AWS_SECRET_ACCESS_KEY`).
