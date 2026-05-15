# Ecosistema Core Admin

Aplicación administrativa interna del ecosistema. Este README resume **estado operativo real** (lo que funciona hoy) y separa claramente lo que está en read-only, dry-run, controlado por flags o en roadmap.

## Resumen operativo real
- Core administrativo funcional: autenticación/sesión, dashboard, tenants, users, roles, permissions, modules, system health/logs/audit.  
- Hay módulos adicionales visibles en rutas (Mail, Cloud/Drive, URL Locator, Browser Analytics, Landing, CRM, Workflow, Reports, Campaigns, AI), pero su operación real está mayormente limitada por modo read-only, dry-run o flags desactivados por defecto.  
- Matriz canónica de estado por módulo: `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`.

## Instalación local
```bash
composer install
cp .env.example .env
# Configura DB_* según tu entorno
php -S 127.0.0.1:8000 -t public
```

Comandos útiles:
```bash
composer dump-autoload
composer smoke
```

## Estado por módulo (fuente oficial)
Para evitar duplicidad y promesas incorrectas, usa la matriz oficial:
- `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`

Ahí se distingue explícitamente:
- Implementado estable
- Read-only
- Dry-run
- Controlled por flags
- Documental / roadmap

## Rutas principales reales
- Auth y base: `/login`, `POST /login`, `POST /logout`, `/dashboard`, `/health/db`
- Core admin: `/tenants`, `/users`, `/roles`, `/permissions`, `/modules`
- System: `/system/health`, `/system/logs`, `/system/audit`, `/audit/events`
- Cloud/Drive: `/cloud`, `/cloud/drive`, `/cloud/drive/download-contract, /cloud/drive/files`, `/cloud/drive/folders`, `/cloud/drive/summary`
- Mail: `/mail`, `/mail/compose`, `/mail-notifications`
- Workflow: `/workflow`, `/workflow/runs`, `/workflow/dry-run`
- Reports/Campaigns: `/reports/marketing-funnel`, `/reports/lead-performance`, `/campaigns`
- Landing/URL/Analytics/CRM/AI (operación condicionada): `/landing`, `/url/locator`, `/browser/analytics`, `/crm`, `POST /ai/assist`

Referencia completa de rutas: `routes/web.php`.

## Features read-only
Ejemplos de superficies de consulta o detalle sin operación productiva completa:
- Workflow runs y vistas operativas (`/workflow/runs`, `/workflow/runs/{id}`)
- Reportes administrativos (`/reports/*`) como lectura/diagnóstico principal
- Partes de Cloud/Drive (incluyendo contrato de descarga), URL Locator, Browser Analytics, Landing, CRM según matriz y flags

Ver detalle por módulo en la matriz: `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`.

## Features dry-run
Ejemplos explícitos de simulación:
- Workflow: `/workflow/dry-run`, `/workflow/rules/{id}/dry-run`
- URL Locator redirect: `/url/locator/links/{id}/redirect-dry-run`
- Landing submit/render: rutas `*dry-run*`
- Reports export dry-run, Campaign creation dry-run, AI insights dry-run

No implican ejecución productiva ni escrituras completas si flags de write/enable no están activos.

## Features controlled por flags
Por defecto `.env.example` mantiene la mayoría de integraciones avanzadas en `false`:
- Mail/SMTP real: `MAIL_SEND_ENABLED=false`, `ECOSISTEMA_SMTP_ENABLED=false`
- Cloud/AWS/S3 real: `CLOUD_S3_ENABLED=false`, `ECOSISTEMA_DRIVE_AWS_ENABLED=false`, `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false`
- IA externa/escrituras IA: `ECOSISTEMA_AI_ENABLED=false`, `ECOSISTEMA_AI_PROVIDER_ENABLED=false`, `ECOSISTEMA_AI_WRITE_PROPOSALS=false`
- Workflow ejecución real: `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=false`
- URL Locator, Landing, Campaigns, Reports, CRM, Notifications: flags `*_ENABLED`, `*_DRY_RUN`, `*_WRITE` en `false` por defecto.

## Features roadmap / no activas
No asumir productivo por existencia de vistas, docs o rutas:
- Envío masivo productivo
- Workers/cron productivos end-to-end
- S3/AWS real por defecto
- IA autónoma externa por defecto

Estado y límites reales: `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`.

## Seguridad y flags
- No subir secretos ni commitear `.env`; usar `.env.example` como plantilla.
- Configuración por defecto segura: sin SMTP real, sin AWS/S3 real, sin proveedor IA externo.
- Registro inicial controlado por flag: `CORE_REGISTRATION_ENABLED=false` por defecto.

Referencia: `.env.example`.

## Pruebas / smoke
Ejecuta:
```bash
composer smoke
# o directamente
php scripts/smoke-check.php
```

El smoke valida estructura/carga/sintaxis y controles básicos; no reemplaza pruebas funcionales completas ni activa integraciones externas.

## Limitaciones actuales
- Este repositorio no garantiza operación productiva de Mail, Cloud/S3, IA, Workflow, Campaigns, Reports, Landing sin habilitación explícita de flags y hardening adicional.
- Hay capacidades administrativas útiles hoy, pero parte relevante del ecosistema está en modo controlado (read-only/dry-run/flags).
- La narrativa operativa detallada vive en documentación de `docs/project/*`; este README se mantiene breve para onboarding rápido.
