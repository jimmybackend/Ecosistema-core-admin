# Ecosistema Core Admin

Aplicación administrativa interna del ecosistema.

## 1) Resumen corto
Core Admin tiene un núcleo estable para autenticación/sesión y administración base (tenants, users, roles, permissions y modules), con módulos adicionales en diferentes estados operativos (`read-only`, `dry-run`, `controlled` por flags o `roadmap`).

El estado canónico por módulo está documentado en:
- `docs/project/CORE_ADMIN_MODULE_STATUS.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`

## 2) Estado operativo real
- **Base estable**: Auth/sesión, dashboard, health DB, núcleo CRUD administrativo y auditoría/logs del sistema.
- **Parcial/controlado**: Mail, Notifications, Cloud local, Ecosistema Drive, URL Locator, Landing, Browser Analytics, CRM/Campaigns, Workflow, Reports, Security (rate limit), Audit unificada, AI assistance.
- **No productivo completo / pendiente**: Workers/Cron productivos completos, Billing, Integrations, Support end-to-end.

Este repositorio **no debe interpretarse** como integración productiva completa con AWS/S3, SMTP, IA externa, workers o billing cuando las flags están apagadas o en modo contractual/dry-run.

## 3) Estado por módulo

| Módulo | Estado real | Nota operativa |
|---|---|---|
| Auth / sesión | Estable / base operativa | Login/logout/sesión/timeout y dashboard operativo. |
| Core tenants/users/roles/permissions/modules | Estable / base operativa | CRUD administrativo del núcleo. |
| System health/logs/audit | Estable / base operativa | Health, logs y auditoría del core disponibles. |
| Mail | Controlled por flags + dry-run | Envío real sujeto a `MAIL_SEND_ENABLED`, `ECOSISTEMA_MAIL_SEND_ENABLED`, `ECOSISTEMA_SMTP_ENABLED`. |
| Notifications | Controlled por flags + dry-run | Cola/preview/envío condicionados por flags de mail notifications. |
| Cloud local | Read-only / controlled | Navegación/resumen disponible; upload/download real bloqueado por default. |
| Ecosistema Drive | Controlled + contractual/dry-run | Adaptador preparado; remoto/S3 y signed URLs sujetos a flags. |
| URL Locator | Read-only + dry-run + controlled | Redirect público/track/escritura condicionados por flags. |
| Landing | Read-only + dry-run + controlled | Render público/submit/uploads controlados por flags. |
| Browser Analytics | Read-only + dry-run + controlled | Collector/write y recolección de IP/UA apagados por default. |
| CRM / Campaigns | Read-only + dry-run + controlled | Escritura de leads/campañas sujeta a flags. |
| Workflow | Read-only + dry-run + controlled | Ejecución real y acciones externas desactivadas por default. |
| Reports | Read-only + dry-run + controlled | Export write e inclusión de PII condicionadas por flags. |
| Security / Rate limit | Controlled por flags | Rate limit bloqueante desactivado por default. |
| Audit unificada | Estable / base operativa | Superficie de auditoría unificada disponible en core. |
| AI assistance | Dry-run + controlled por flags | Proveedor externo y escritura de propuestas apagados por default. |
| Workers/Cron | Roadmap / pendiente (parcial mínimo actual) | Estado actual: jobs controlados, sin workers productivos completos. |
| Billing | Roadmap / pendiente | Documental/parcial; no declarar producción completa. |
| Integrations | Roadmap / pendiente | Integraciones parciales/controladas, no end-to-end productivo. |
| Support | Roadmap / pendiente | Estado documental/parcial. |

## 4) Rutas principales agrupadas por estado

### Estable / base operativa
- Auth/base: `/login`, `POST /login`, `POST /logout`, `/dashboard`, `/health/db`
- Core admin: `/tenants`, `/users`, `/roles`, `/permissions`, `/modules`
- System: `/system/health`, `/system/logs`, `/system/audit`, `/audit/events`

### Read-only / consulta operativa
- Cloud/Drive: `/cloud`, `/cloud/drive`, `/cloud/drive/files`, `/cloud/drive/folders`, `/cloud/drive/summary`
- Workflow: `/workflow`, `/workflow/runs`
- Reports/Campaigns/CRM/Analytics/Landing/URL: `/reports/marketing-funnel`, `/reports/lead-performance`, `/campaigns`, `/crm`, `/browser/analytics`, `/landing`, `/url/locator`

### Dry-run / controlled
- Workflow dry-run: `/workflow/dry-run`
- AI assistance (controlado por flags): `POST /ai/assist`

Referencia completa y vigente: `routes/web.php`.

## 5) Flags críticas apagadas por defecto
Fuente: `.env.example`.

- **Mail/SMTP**: `MAIL_SEND_ENABLED=false`, `ECOSISTEMA_MAIL_SEND_ENABLED=false`, `ECOSISTEMA_SMTP_ENABLED=false`
- **Cloud/S3/Drive remoto**: `CLOUD_S3_ENABLED=false`, `S3_DRIVE_ENABLED=false`, `S3_DRIVE_ALLOW_REMOTE_CALLS=false`, `ECOSISTEMA_DRIVE_ENABLED=false`, `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false`
- **URL Locator**: `ECOSISTEMA_URL_LOCATOR_ENABLED=false`, `ECOSISTEMA_URL_LOCATOR_ADMIN_WRITE_ENABLED=false`, `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS=false`
- **Landing**: `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=false`, `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED=false`
- **Browser Analytics**: `ECOSISTEMA_BROWSER_ANALYTICS_ENABLED=false`, `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE=false`
- **CRM/Workflow/Reports/Campaigns**: `ECOSISTEMA_CRM_ENABLED=false`, `ECOSISTEMA_WORKFLOW_ENABLED=false`, `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=false`, `ECOSISTEMA_REPORT_EXPORT_WRITE=false`, `ECOSISTEMA_CAMPAIGN_CREATION_WRITE=false`
- **Security / rate limit**: `ECOSISTEMA_RATE_LIMIT_ENABLED=false`, `ECOSISTEMA_RATE_LIMIT_WRITE_BLOCKS=false`
- **AI externa**: `ECOSISTEMA_AI_ENABLED=false`, `ECOSISTEMA_AI_PROVIDER_ENABLED=false`, `ECOSISTEMA_AI_WRITE_PROPOSALS=false`
- **Registro**: `CORE_REGISTRATION_ENABLED=false`

## 6) Qué sí valida `composer smoke`
```bash
composer smoke
# o
php scripts/smoke-check.php
```
- Carga/autoload y estructura base.
- Validaciones de sintaxis/arranque del core.
- Chequeos básicos operativos sin activar integraciones externas.
- Incluye `composer schema:check` como validación opcional read-only cuando hay DB disponible.

## 7) Qué no valida `composer smoke`
- No valida comportamiento funcional end-to-end de todos los módulos.
- No valida workers productivos, colas reales ni cron de producción completo.
- No valida integración real con SMTP/AWS/S3/IA externa.
- No valida performance, FKs, índices, triggers ni hardening completo.
- No reemplaza pruebas de integración, QA funcional ni auditoría de seguridad.

## 8) Limitaciones conocidas
- Sin SMTP real por defecto.
- Sin AWS/S3 real por defecto.
- Sin proveedor IA externo por defecto.
- Sin workers productivos completos activos.
- Múltiples módulos operan en modo `read-only`, `dry-run` o `controlled` según flags.
- Para demo/showcase, no usar datos reales ni secretos.

Estado explícito de cron/workers: `docs/ops/WORKERS_CRON_CURRENT_STATE.md`.

## 9) Checklist para demo/showcase
1. Confirmar flags en `.env` y mantener por defecto en modo seguro salvo prueba controlada.
2. Ejecutar `composer smoke` y revisar warnings no bloqueantes.
3. Verificar rutas demo que sí están estables (auth/core/system).
4. Si se demuestra módulo controlled, documentar qué flag se encendió y por cuánto tiempo.
5. Evitar claims de “producción completa” para módulos en read-only/dry-run/controlled.
6. Validar contacto y política pública vigentes (`contacto.md`, `docs/politica_contacto_publico.md`).

## Referencias operativas complementarias
- `docs/estado_modulos.md`
- `docs/project/CORE_ADMIN_SHOWCASE_DEMO_GUIDE.md`
- `docs/demo_guion.md`
- `docs/project/CORE_ADMIN_DEMO_READINESS_CHECKLIST.md`
- `docs/checklist_presentacion_publica.md`
- `docs/project/CORE_ADMIN_SHOWCASE_RELEASE_NOTES.md`
- `docs/project/ECOSISTEMA_RISK_H_CLOSURE.md`
- `docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md`
- `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- `docs/project/ECOSISTEMA_ANALYTICS_PRIVACY_CONSENT.md`
- `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md`

## Material visual
- Diagramas base para presentación (sin datos reales): `docs/diagramas.md`
- Reglas de assets y privacidad para material visual: `assets/README.md`

## Instalación local
```bash
composer install
cp .env.example .env
# Configura DB_* según tu entorno
php -S 127.0.0.1:8000 -t public
```
