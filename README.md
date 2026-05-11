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
