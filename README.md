# Ecosistema Core Admin

Aplicación administrativa operativa del ecosistema para gestión interna (etapa 1).

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
- Usuarios: `/users`
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
- `core_users`, `core_sessions`, `core_tenants`, `core_roles`, `core_permissions`, `core_role_permissions`, `core_modules`
- `system_health_check_definitions`, `system_health_check_results`, `system_logs`, `core_audit`
- `mail_messages`, `mail_mailboxes`, `mail_folders`
- `cloud_files`, `cloud_folders`, `cloud_buckets`, `cloud_user_roots`
- `onboarding_flows`, `onboarding_runs`, `onboarding_run_steps`

## Limitaciones actuales
- No hay autorización fina global por permisos todavía.
- Mail **no** realiza envío real (sin SMTP/IMAP/POP productivo).
- Cloud **no** integra S3 real ni AWS SDK.
- Onboarding no ejecuta aprovisionamiento real.
- No hay workers/cron ni API separada en este repositorio.

## Documentación del proyecto
- `docs/project/ECOSISTEMA_FUENTE_MAESTRA.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_ESTADO_ACTUAL.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_QA_CHECKLIST.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_RUTAS.md`
- `docs/project/ECOSISTEMA_CORE_ADMIN_PENDIENTES.md`
