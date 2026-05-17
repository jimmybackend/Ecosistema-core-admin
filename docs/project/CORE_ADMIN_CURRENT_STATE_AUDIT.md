# CORE Admin — Current State Audit (post PR #208)

Fecha de corte: **2026-05-17**.

Objetivo: fotografía técnica del estado actual real de Core Admin usando únicamente fuentes del repositorio.

## Fuentes auditadas

- `README.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`
- `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- `docs/ops/WORKERS_CRON_CURRENT_STATE.md`
- `routes/web.php`
- `scripts/smoke-check.php`
- `.env.example`

## Resumen ejecutivo (estado real)

- **Base estable**: Auth/Core/System como núcleo administrativo funcional.
- **Parcial / read-only / dry-run / controlled**: Mail, Cloud/Drive, URL Locator, Landing, Browser Analytics, CRM, Workflow, Reports, Security, AI.
- **No productivo completo / roadmap**: Billing, Integrations, Support, Workers productivos.
- **Guardrail operativo crítico**: flags avanzados y de escritura/remoto están en `false` por defecto en `.env.example`.

## Tabla de estado actual por módulo

| Módulo | Estado declarado en README | Estado declarado en module status | Rutas principales | Flags críticas | Evidencia de archivo | Riesgo pendiente |
|---|---|---|---|---|---|---|
| Auth/Core | Operativo (base) | `Core/Auth: Operativo base` | `/login`, `POST /login`, `POST /logout`, `/dashboard` | `CORE_REGISTRATION_ENABLED`, `SESSION_*` | README + module status + status matrix + route/service map + `.env.example` | Registro público sólo si flag se habilita; sin MFA descrito.
| System (Health/Logs/Audit) | Operativo | `Security/Audit: Operativo base` | `/system/health`, `/system/logs`, `/system/audit`, `/audit/events` | N/A de activación masiva (predomina control por permisos) | module status matrix + route/service map | Riesgo bajo; verificar alcance de checks y permisos por tenant.
| Mail/Notifications | Controlled (por flags) | `Mail/Notifications: Controlled / read-only / dry-run` | `/mail*`, `/mail-notifications*`, `POST /mail-notifications/send`, `POST /mail-notifications/send-dry-run` | `MAIL_SEND_ENABLED`, `MAIL_ALLOW_TEST_SEND`, `ECOSISTEMA_MAIL_SEND_ENABLED`, `ECOSISTEMA_SMTP_ENABLED` | module status + status matrix + security matrix + `.env.example` | Riesgo de envío/PII si se habilita SMTP sin hardening.
| Cloud/Drive | Parcial (read-only + controlled) | `Cloud/Drive: Read-only + Controlled` | `/cloud*`, `/cloud/drive*`, `POST /cloud/drive/upload` + dry-run | `CLOUD_*`, `S3_DRIVE_*`, `ECOSISTEMA_DRIVE_*` | module status + status matrix + route/service map + `.env.example` | Riesgo alto de que se perciba como “S3 productivo” cuando está condicionado.
| URL Locator | Parcial (read-only/dry-run/controlled) | `URL Locator: Read-only / Dry-run / Controlled` | `/url/locator*`, `/u/{slug}`, rutas dry-run y write | `ECOSISTEMA_URL_LOCATOR_*` | module status + status matrix + route/service map + `.env.example` | Riesgo de redirect/tracking público si flags se habilitan sin controles.
| Landing | Parcial (read-only/dry-run/controlled) | `Landing Pages: Read-only / Dry-run / Controlled` | `/landing*`, `/l/{slug}`, `POST /l/{slug}/forms/{id}/submit` | `ECOSISTEMA_LANDING_*` | module status + status matrix + route/service map + `.env.example` | Riesgo de ingesta pública/PII y spam en formularios si se habilita submit.
| Browser Analytics | Parcial (read-only/controlled) | `Browser Analytics: Read-only / Controlled` | `/browser/analytics*`, `POST /browser/analytics/collect`, collector dry-run | `ECOSISTEMA_BROWSER_ANALYTICS_*` | module status + status matrix + route/service map + `.env.example` | Riesgo regulatorio/privacidad por recolección (IP/UA/eventos).
| CRM | Parcial (read-only/dry-run/controlled) | `CRM/Campaigns: Read-only / Dry-run / Controlled` | `/crm*`, `POST /crm/submission-to-lead/{id}`, dry-runs | `ECOSISTEMA_CRM_*` | module status + status matrix + route/service map + `.env.example` | Riesgo de contaminación de pipeline por escrituras no gobernadas.
| Workflow | Parcial (read-only/dry-run/controlled) | `Workflow: Read-only / Dry-run / Controlled` | `/workflow*`, `POST /workflow/rules/{id}/execute`, `POST /workflow/events/execute` | `ECOSISTEMA_WORKFLOW_*` | module status + status matrix + route/service map + `.env.example` | Riesgo alto por side effects si execution/write se habilita.
| Reports | Parcial (read-only/dry-run/controlled) | `Reports: Read-only / Dry-run / Controlled` | `/reports/*`, `/reports/exports/dry-run`, `POST /reports/exports` | `ECOSISTEMA_REPORT_EXPORT_*` | module status + status matrix + route/service map + `.env.example` | Riesgo de exfiltración/PII en exportaciones.
| Security (rate-limit/audit permisos) | Parcial (read-only + dry-run + controlled) | `Security: mixto` | `/security/permissions-audit*`, `/security/rate-limit/dry-run`, `POST /security/rate-limit/enforce` | `ECOSISTEMA_RATE_LIMIT_*` | status matrix + route/service map + security matrix + `.env.example` | Riesgo de falsos positivos de bloqueo o enforcement incompleto.
| AI | Parcial (dry-run/controlled) | `AI: Dry-run / Controlled` | `POST /ai/assist`, `/ai/*dry-run` | `ECOSISTEMA_AI_*` | module status + status matrix + route/service map + `.env.example` | Riesgo de fuga a proveedor externo y decisiones con alucinación.
| Billing | Roadmap/no productivo completo | `Billing: Documental / roadmap` | Sin rutas productivas confirmadas aquí | N/A | module status + status matrix | No afirmar billing productivo end-to-end.
| Integrations | Roadmap/parcial | `Integrations: Documental / parcial` | No consolidado como productivo | N/A | module status + status matrix | No prometer integraciones productivas completas.
| Support | Roadmap/parcial | `Support: Documental / parcial` | No consolidado como productivo | N/A | module status + status matrix | No prometer soporte operativo completo como producto.
| Workers productivos | Roadmap/no productivo completo | `Jobs/Workers: Documental / roadmap` | jobs actuales: `composer cron:check`, `composer cron:health`, `composer cron:sessions` | N/A | workers/cron current state | No hay workers productivos ni colas productivas activas.

## Estado explícito solicitado (checklist)

### Base estable
- **Auth/Core/System**: base estable administrativa confirmada por README + status docs + rutas administrativas.

### Parcial / Read-only / Dry-run / Controlled
- **Mail/Cloud/Drive/URL/Landing/Analytics/CRM/Workflow/Reports/Security/AI**: se mantienen como parciales y/o condicionados por flags/permisos.

### No productivo completo / Roadmap
- **Billing/Integrations/Support/Workers productivos**: no declarar como productivo completo.

## No afirmar en demo (claims prohibidos)

1. “AWS/S3 real está activo por defecto”.
2. “SMTP real está activo por defecto”.
3. “IA externa está activa por defecto”.
4. “Workers productivos completos ya están activos”.
5. “Billing es productivo completo en este repo”.

## Evidencia transversal de flags y defaults seguros

- `.env.example` mantiene en `false` flags de envío real, remoto S3/Drive, landing pública, collector write, workflow execution, report export write y AI provider/write.
- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md` y `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md` documentan estos defaults y riesgos de activación.
- `docs/ops/WORKERS_CRON_CURRENT_STATE.md` documenta explícitamente ausencia de workers productivos completos.

## Conclusión operativa

Core Admin es **administrativo interno** con base estable para operación de núcleo (Auth/Core/System), y múltiples dominios avanzados en modalidad **read-only / dry-run / controlled por flags y permisos**. Para demo/QA, las afirmaciones deben respetar este estado y evitar claims de operación productiva completa en dominios roadmap/parciales.
