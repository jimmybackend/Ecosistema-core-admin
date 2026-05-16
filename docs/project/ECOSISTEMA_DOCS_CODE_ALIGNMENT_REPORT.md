# ECOSISTEMA_DOCS_CODE_ALIGNMENT_REPORT

Fecha de verificación: 2026-05-16.

## Objetivo
Alinear README y documentación del repositorio con el estado **real** observado en código (principalmente `routes/web.php` y servicios/repositorios cableados allí), evitando promesas de operación productiva completa cuando el módulo está en `read-only`, `dry-run` o `controlled`.

## Criterios de estado usados
- `stable`: funcionalidad base operativa y no dependiente de flags de activación funcional principal.
- `read-only`: superficie de consulta/listado/detalle sin escritura operativa principal.
- `dry-run`: simulación explícita sin escritura real o con `db_write=false`.
- `controlled`: escritura/ejecución real posible, pero condicionada por flags/permisos/guardas.
- `planned`: visión futura o pendiente sin operación productiva completa en este repo.
- `unknown`: no usado en este reporte (se resolvieron todos los módulos solicitados).

## Matriz de alineación docs vs código

| Módulo | README (claim actual) | Docs (claim actual) | Rutas reales (`routes/web.php`) | Services / repositories reales observados | Estado real |
|---|---|---|---|---|---|
| Drive | Controlled + contractual/dry-run | `ECOSISTEMA_DRIVE_*` (read-only, dry-run, controlled S3/upload/download, access logs, repair jobs) | `/cloud/drive`, `/cloud/drive/files`, `/cloud/drive/folders`, `/cloud/drive/summary`, `/cloud/drive/s3-upload*`, `/cloud/drive/signed-url-dry-run` | `EcosistemaDrive*Service`, `EcosistemaDrive*Repository`, `EcosistemaDriveS3UploadService`, `EcosistemaDriveS3UploadDryRunService`, `EcosistemaDriveSignedUrlDryRunService` | controlled |
| URL Locator | Read-only + dry-run + controlled | `ECOSISTEMA_URL_LOCATOR_*` (links/clicks read-only, redirect dry-run, create/edit controlled) | `GET /u/{slug}`, `/url/locator*`, `/mail-notifications/url-message-templates*` | `EcosistemaUrlLocator*Service`, `EcosistemaUrlLocator*Repository`, `EcosistemaUrlLocatorPublicRedirectService`, `EcosistemaUrlLocatorRedirectDryRunService` | controlled |
| Landing | Read-only + dry-run + controlled | `ECOSISTEMA_LANDING_*` (pages/forms/visits/submissions, public render dry-run/controlled) | `GET /l/{slug}`, `POST /l/{slug}/forms/{id}/submit`, `/landing*` | `EcosistemaLanding*Service`, `EcosistemaLanding*Repository`, `EcosistemaLandingPublicRenderDryRunService`, `EcosistemaLandingFormSubmitDryRunService` | controlled |
| Browser Analytics | Read-only + dry-run + controlled | `ECOSISTEMA_BROWSER_ANALYTICS_*` | `/browser/analytics*`, `GET/POST /browser/analytics/collector-dry-run`, `POST /browser/analytics/collect` | `EcosistemaBrowserAnalytics*Service`, `EcosistemaBrowserAnalytics*Repository`, `EcosistemaBrowserAnalyticsCollectorDryRunService`, `EcosistemaBrowserAnalyticsCollectorService` | controlled |
| CRM/Campaigns | Read-only + dry-run + controlled | CRM read-only + submit-to-lead dry-run/controlled + campaign creation dry-run/controlled | `/crm*`, `/campaigns*` | `EcosistemaCrm*Service`, `EcosistemaCrm*Repository`, `EcosistemaCrmSubmissionToLeadDryRunService`, `EcosistemaCampaign*Service`, `EcosistemaCampaign*Repository` | controlled |
| Mail/Notifications | Mail controlled + dry-run; Notifications controlled + dry-run | Notificaciones y templates/queue en read-only; send/message preview en dry-run/controlled | `/mail*`, `/mail-notifications*` | `MailService`, `MailSendService`, `MailAttachmentService`, `EcosistemaNotification*Service`, `EcosistemaSendNotificationDryRunService`, `EcosistemaSendNotificationService` | controlled |
| Workflow | Read-only + dry-run + controlled | templates/rules/runs read-only + dry-run + execution controlled | `/workflow`, `/workflow/templates*`, `/workflow/rules*`, `/workflow/dry-run`, `/workflow/runs*`, `POST /workflow/*/execute` | `EcosistemaWorkflow*Service`, `EcosistemaWorkflow*Repository`, `EcosistemaWorkflowDryRunService`, `EcosistemaWorkflowExecutionService` | controlled |
| Reports | Read-only + dry-run + controlled | reportes read-only, export dry-run/controlled | `/reports/marketing-funnel`, `/reports/lead-performance`, `/reports/export*` | `EcosistemaMarketingFunnelReportService`, `EcosistemaLeadPerformanceReportService`, `EcosistemaReportExportDryRunService`, `EcosistemaReportExportService` | controlled |
| Audit | Audit unificada estable | `ECOSISTEMA_UNIFIED_AUDIT_READ_ONLY` + docs de permisos/auditoría | `/audit/events`, `/security/module-permissions-audit*`, rutas core con `auditLog(...)` | `EcosistemaUnifiedAuditService`, `EcosistemaUnifiedAuditRepository`, `AuditLogger` | stable |
| Security | Rate limit controlled por flags | security matrix + hardening checklist + permission audit | `/security/module-permissions-audit*`, `/security/rate-limit*` | `EcosistemaPermissionAuditService`, `EcosistemaRateLimitDryRunService` | controlled |
| AI | Dry-run + controlled | AI assistance schema inventory + lead summary / campaign insight dry-run | `/ai/assist*`, `/ai/lead-summary*`, `/ai/campaign-insight*` | `EcosistemaAiAssistanceService`, `EcosistemaAiLeadSummaryDryRunService`, `EcosistemaAiCampaignInsightDryRunService` | controlled |
| Billing | Roadmap / pendiente | sin evidencia de módulo productivo completo en docs/rutas actuales | (sin superficie operativa equivalente a módulo completo) | (sin service/repository operativo equivalente en rutas) | planned |
| Integrations | Roadmap / pendiente | integración parcial/documental en varios módulos | indirecto vía adapters (`*Adapter`) y flags | `Ecosistema*Adapter` (Drive/CRM/Landing/Analytics/Platform/MailNotifications) | planned |
| Support | Roadmap / pendiente | no se documenta operación productiva end-to-end en este repo | (sin módulo dedicado completo) | (sin service/repository dedicado completo en rutas) | planned |
| Jobs/Workers | Roadmap / pendiente (parcial mínimo) | `WORKERS_CRON_CURRENT_STATE` y plan de workers/cron | no hay superficie de worker productivo full en web | jobs puntuales (ej. repair jobs de Drive) pero no stack de workers productivos completa | planned |

## Ajustes requeridos en lenguaje documental

1. Mantener verbos como “gestiona/genera/automatiza” **solo** cuando el texto indique explícitamente que la operación está en `controlled` o `dry-run` (flags/permisos).
2. Cuando exista solo consulta, usar “consulta/lista/detalla” y marcar `read-only`.
3. Conservar visión futura como roadmap con etiqueta `planned`.

## Resultado de esta verificación
- No se encontró ningún módulo del alcance con estado ambiguo.
- El estado del README es consistente con las rutas y wiring reales en `routes/web.php`.
- Se mantiene la visión futura (Billing/Integrations/Support/Workers) como `planned`.

## Pendientes técnicos explícitos
- Verificación funcional end-to-end con DB/datos reales no cubierta por esta alineación documental.
- Activaciones controladas deben validarse en entorno de prueba antes de declarar operación productiva.
