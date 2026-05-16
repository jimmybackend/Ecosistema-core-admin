# ECOSISTEMA Route → Permission → Service → View Matrix

Matriz de trazabilidad para auditoría/QA/demo, construida desde `routes/web.php`, repositorios/servicios referenciados allí y documentos existentes de `docs/project`.

## Convenciones
- **Requiere sesión**: `sí` cuando pasa por `startAuthSession()` y exige autenticación; `no` en rutas públicas.
- **CSRF (POST)**: `sí` cuando aparece `ensureValidCsrfToken(...)` dentro de la ruta POST; `no` en endpoints públicos técnicos o formularios públicos.
- **Tipo**: `read-only`, `dry-run`, `controlled write`, `public blocked-by-flag`, `public enabled-by-flag`.
- **Tablas**: solo tablas confirmables por repositorios/docs ya existentes; cuando no es verificable en este PR se marca `no confirmado`.

## Matriz prioritaria (rutas solicitadas)

| Ruta | Método | Módulo | Requiere sesión | Permiso requerido | CSRF (POST) | Service usado | Repository usado | View usada | Tablas consultadas/escritas | Tipo | Documento relacionado |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `/u/{slug}` | GET | URL Locator (public) | no | pública | n/a | `EcosistemaUrlLocatorPublicRedirectService` | `EcosistemaUrlLocatorLinkRepository`, `EcosistemaUrlLocatorClickRepository` | `pages/url-locator/public-redirect-blocked` (fallback) | `no confirmado` | public blocked-by-flag | `docs/project/ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECT.md` |
| `/url/locator` | GET | URL Locator | sí | `url_locator.view` | n/a | `EcosistemaUrlLocatorLinkService` | `EcosistemaUrlLocatorLinkRepository` | `pages/url-locator/index` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_URL_LOCATOR_READ_ONLY_LINKS.md` |
| `/url/locator/links` | GET | URL Locator | sí | `url_locator.view` | n/a | `EcosistemaUrlLocatorLinkService` | `EcosistemaUrlLocatorLinkRepository` | `pages/url-locator/links` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_URL_LOCATOR_READ_ONLY_LINKS.md` |
| `/url/locator/clicks` | GET | URL Locator | sí | `url_locator.view` | n/a | `EcosistemaUrlLocatorClickService` | `EcosistemaUrlLocatorClickRepository` | `pages/url-locator/clicks` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_URL_LOCATOR_CLICKS_READ_ONLY.md` |
| `/url/locator/links/{id}/redirect-dry-run` | GET | URL Locator | sí | `url_locator.view` | n/a | `EcosistemaUrlLocatorRedirectDryRunService` | `EcosistemaUrlLocatorLinkRepository` | `pages/url-locator/redirect-dry-run` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_URL_LOCATOR_REDIRECT_DRY_RUN.md` |
| `/url/locator/links` | POST | URL Locator | sí | `url_locator.manage` | sí | `EcosistemaUrlLocatorLinkWriteService` | `EcosistemaUrlLocatorLinkWriteRepository` | `pages/url-locator/link-form` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_URL_LOCATOR_SCHEMA_INVENTORY.md` |
| `/url/locator/links/{id}/edit` | POST | URL Locator | sí | `url_locator.manage` | sí | `EcosistemaUrlLocatorLinkWriteService` | `EcosistemaUrlLocatorLinkWriteRepository` | `pages/url-locator/link-form` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_URL_LOCATOR_SCHEMA_INVENTORY.md` |
| `/l/{slug}` | GET | Landing (public) | no | pública | n/a | `EcosistemaLandingPublicRenderService` | `EcosistemaLandingPageRepository` | `pages/landing/public-page`, `public-page-blocked` | `no confirmado` | public enabled-by-flag | `docs/project/ECOSISTEMA_LANDING_PUBLIC_RENDER_CONTROLLED.md` |
| `/l/{slug}/forms/{id}/submit` | POST | Landing (public) | no | pública | no | `EcosistemaLandingFormSubmitService` | `EcosistemaLandingFormSubmitRepository`, `EcosistemaLandingFormRepository` | `pages/landing/form-submit-result` | `no confirmado` | public enabled-by-flag | `docs/project/ECOSISTEMA_LANDING_FORM_SUBMIT_CONTROLLED.md` |
| `/landing/*` | GET | Landing | sí | `landing.view` | n/a | `EcosistemaLanding*Service` | `EcosistemaLanding*Repository` | `pages/landing/*` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_LANDING_PAGES_READ_ONLY.md` |
| `/landing/forms/{id}/submit-dry-run` | GET/POST | Landing | sí | `landing.manage` | POST: sí | `EcosistemaLandingFormSubmitDryRunService` | `EcosistemaLandingFormSubmitRepository` | `pages/landing/form-submit-dry-run` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_LANDING_FORM_SUBMIT_DRY_RUN.md` |
| `/crm/*` | GET | CRM | sí | `crm.view` | n/a | `EcosistemaCrmLeadService`, `EcosistemaCrmFollowupService`, `EcosistemaCrmCampaignService` | `EcosistemaCrmLeadRepository`, `EcosistemaCrmFollowupRepository`, `EcosistemaCrmCampaignRepository` | `pages/crm/*` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_CRM_LEADS_READ_ONLY.md` |
| `/crm/submission-to-lead/{id}` | POST | CRM | sí | `crm.manage` | sí | `EcosistemaCrmSubmissionToLeadService` | `EcosistemaCrmLeadWriteRepository` | `pages/crm/submission-to-lead-result` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_CONTROLLED.md` |
| `/crm/leads/{id}/followup-task-dry-run` | GET/POST | CRM | sí | `crm.manage` | POST: sí | `EcosistemaCrmFollowupTaskDryRunService` | `EcosistemaCrmFollowupTaskDryRunRepository` | `pages/crm/followup-task-dry-run` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_CRM_FOLLOWUP_TASK_DRY_RUN.md` |
| `/crm/leads/{id}/followup-tasks` | POST | CRM | sí | `crm.manage` | sí | `EcosistemaCrmFollowupTaskService` | `EcosistemaCrmFollowupTaskRepository` | `pages/crm/followup-task-result` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_CRM_FOLLOWUP_TASK_CONTROLLED.md` |
| `/campaigns`, `/campaigns/{id}/cockpit` | GET | Campaigns | sí | `campaigns.view` | n/a | `EcosistemaCampaignCockpitService` | `EcosistemaCampaignCockpitRepository` | `pages/campaigns/index`, `pages/campaigns/cockpit` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_CAMPAIGN_COCKPIT_READ_ONLY.md` |
| `/campaigns/new/dry-run` | GET/POST | Campaigns | sí | `campaigns.manage` | POST: sí | `EcosistemaCampaignCreationDryRunService` | `EcosistemaCampaignCreationRepository` | `pages/campaigns/create-dry-run` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_CAMPAIGN_CREATION_DRY_RUN.md` |
| `/campaigns` | POST | Campaigns | sí | `campaigns.manage` | sí | `EcosistemaCampaignCreationService` | `EcosistemaCampaignCreationRepository` | `pages/campaigns/create-result` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_CAMPAIGN_CREATION_CONTROLLED.md` |
| `/workflow/*` (consulta) | GET | Workflow | sí | `workflow.view` | n/a | `EcosistemaWorkflow*Service` | `EcosistemaWorkflow*Repository` | `pages/workflow/*` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_WORKFLOW_RUNS_READ_ONLY.md` |
| `/workflow/*/install-dry-run`, `/workflow/*/dry-run` | GET/POST | Workflow | sí | `workflow.manage` | POST: sí | `EcosistemaWorkflowTemplateInstallDryRunService`, `EcosistemaWorkflowDryRunService` | repos dry-run workflow | `pages/workflow/*dry-run*` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_WORKFLOW_DRY_RUN.md` |
| `/workflow/templates/{key}/install`, `/workflow/rules/{id}/execute`, `/workflow/events/execute` | POST | Workflow | sí | `workflow.manage` | sí | `EcosistemaWorkflowTemplateInstallService`, `EcosistemaWorkflowExecutionService` | repos de instalación/ejecución | `pages/workflow/*result*` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_WORKFLOW_EXECUTION_CONTROLLED.md` |
| `/reports/lead-performance`, `/reports/marketing-funnel` | GET | Reports | sí | `reports.view` | n/a | `EcosistemaLeadPerformanceReportService`, `EcosistemaMarketingFunnelReportService` | repos reportes | `pages/reports/lead-performance`, `marketing-funnel` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_LEAD_PERFORMANCE_REPORT.md`, `ECOSISTEMA_MARKETING_FUNNEL_REPORT.md` |
| `/reports/exports/dry-run` | GET/POST | Reports | sí | `reports.manage` | POST: sí | `EcosistemaReportExportDryRunService` | `EcosistemaReportExportDryRunRepository` | `pages/reports/export-dry-run` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_REPORT_EXPORT_DRY_RUN.md` |
| `/reports/exports` | POST | Reports | sí | `reports.manage` | sí | `EcosistemaReportExportService` | `EcosistemaReportExportRepository` | `pages/reports/export-result` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_REPORT_EXPORT_CONTROLLED.md` |
| `/security/permissions-audit*` | GET | Security | sí | `security.view` | n/a | `EcosistemaPermissionAuditService` | `EcosistemaPermissionAuditRepository` | `pages/security/permissions-audit`, `module-permissions-audit` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_PERMISSIONS_AUDIT.md` |
| `/security/rate-limit/dry-run` | GET/POST | Security | sí | `permissions.view` | POST: sí | `EcosistemaRateLimitDryRunService` | `EcosistemaRateLimitDryRunRepository` | `pages/security/rate-limit-dry-run` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_RATE_LIMIT_DRY_RUN.md` |
| `/security/rate-limit/enforce` | POST | Security | sí | `permissions.view` | sí | `EcosistemaRateLimitService` | `EcosistemaRateLimitRepository` | `pages/security/rate-limit-result` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_RATE_LIMIT_CONTROLLED.md` |
| `/audit`, `/audit/events*` | GET | Audit | sí | `audit.view` | n/a | `EcosistemaUnifiedAuditService` | `EcosistemaUnifiedAuditRepository` | `pages/audit/index`, `events`, `event-show` | `no confirmado` | read-only | `docs/project/ECOSISTEMA_UNIFIED_AUDIT_READ_ONLY.md` |
| `/ai/campaigns/{id}/insight-dry-run`, `/ai/leads/{id}/summary-dry-run` | GET | AI | sí | `ai.use` | n/a | `EcosistemaAiCampaignInsightDryRunService`, `EcosistemaAiLeadSummaryDryRunService` | repos AI dry-run | `pages/ai/*dry-run` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_AI_CAMPAIGN_INSIGHT_DRY_RUN.md`, `ECOSISTEMA_AI_LEAD_SUMMARY_DRY_RUN.md` |
| `/ai/assist` | POST | AI | sí | `ai.use` | sí | `EcosistemaAiAssistanceService` | `EcosistemaAiAssistanceRepository` | `pages/ai/assist-result` | `no confirmado` | controlled write | `docs/project/ECOSISTEMA_AI_ASSISTANT_CONTROLLED.md` |
| `/cloud/drive/*` | GET | Cloud/Drive | sí | `cloud.view` | n/a | `CloudService`, `CloudStorageService`, `EcosistemaDrive*Service` | `Cloud*Repository`, `EcosistemaDrive*Repository` | `pages/cloud/*` | `cloud_files`, `cloud_folders`, `cloud_buckets`, `cloud_user_roots` | read-only | `docs/project/ECOSISTEMA_DRIVE_READ_ONLY_AUDIT.md`, `ECOSISTEMA_DRIVE_OPERATIONAL_COCKPIT.md` |
| `/cloud/drive/upload-dry-run`, `/cloud/drive/files/{id}/signed-url-dry-run` | GET | Cloud/Drive | sí | `cloud.manage` | n/a | `EcosistemaDriveS3UploadDryRunService`, `EcosistemaDriveSignedUrlDryRunService` | repos drive dry-run | `pages/cloud/*dry-run*` | `no confirmado` | dry-run | `docs/project/ECOSISTEMA_DRIVE_S3_UPLOAD_DRY_RUN.md`, `ECOSISTEMA_DRIVE_SIGNED_URL_DRY_RUN.md` |
| `/cloud/drive/upload` | POST | Cloud/Drive | sí | `cloud.manage` | sí | `EcosistemaDriveS3UploadService` | `EcosistemaDriveFileRepository`, `EcosistemaDriveRootRepository` | `pages/cloud/drive-upload-result` | `cloud_files` (write), `cloud_user_roots` (read) | controlled write | `docs/project/ECOSISTEMA_DRIVE_CONTROLLED_S3_UPLOAD.md` |

## Hallazgos de consistencia (sin cambios de lógica)

1. **CSRF en POST**
   - Las rutas POST administrativas revisadas en el alcance (`/url/locator/*`, `/landing/*` internas, `/crm/*`, `/campaigns/*`, `/workflow/*`, `/reports/*`, `/security/*`, `/ai/*`, `/cloud/drive/upload`) están planteadas como **CSRF sí**.
   - Excepciones públicas esperadas: `POST /l/{slug}/forms/{id}/submit` (flujo público) con **CSRF no**.

2. **Rutas públicas y flags/tracking/estado**
   - `/u/{slug}`: ruta pública, comportamiento de bloqueo por flag/validación y tracking de click en servicio/repositorio del módulo URL Locator.
   - `/l/{slug}` y `POST /l/{slug}/forms/{id}/submit`: rutas públicas condicionadas por flags (`*_ENABLED`) y metadatos de tracking (`ip/user-agent`) en submit.

3. **Pendientes técnicos/documentales detectados**
   - Persisten múltiples entradas con **tablas `no confirmado`** (requiere trazado SQL repositorio por repositorio).
   - La documentación operativa está distribuida por documento de feature; esta matriz consolida pero no reemplaza inventarios específicos.
