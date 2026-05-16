# ECOSISTEMA Feature Flags Audit

Fecha de auditoría: 2026-05-16

## Alcance revisado

- `.env.example`
- `.env.vm.example`
- `config/app.php`
- `config/cloud.php`
- `config/mail.php`
- `config/ecosistema_drive.php`
- `config/url_locator.php`
- `routes/web.php`
- Servicios con write/call externo en `app/Core/*`

## Resumen

- Todas las flags críticas de ejecución real y escritura sensible están en `false` por defecto en `.env.example`.
- `.env.vm.example` usa placeholders (`change-me`, `CAMBIAR_EN_VM_NO_COMMIT`) y no incluye secretos reales.
- Rutas públicas sensibles (`/u/{slug}`, `/l/{slug}`, submit de landing, collector write) pasan por flags y devuelven bloqueo cuando están apagadas.

## Inventario de flags críticas

| Flag | Default | Módulo | Acción que habilita | Riesgo | Debe estar en false por defecto | Evidencia de respeto en código |
|---|---:|---|---|---|---|---|
| `CLOUD_S3_ENABLED` | false | Cloud/S3 | Habilita integración S3 | Exfiltración/IO externo | Sí | `config/cloud.php` + `EcosistemaDriveS3UploadService::validateRuntimeFlags` exige true explícito |
| `CLOUD_ALLOW_UPLOADS` | false | Cloud/S3 | Upload de archivos | Escritura externa | Sí | `EcosistemaDriveS3UploadService::validateRuntimeFlags` bloquea si está false |
| `CLOUD_ALLOW_DOWNLOADS` | false | Cloud/S3 | Download remoto | Egreso no controlado | Sí | `EcosistemaDriveS3DownloadService::download` valida flags y retorna blocked |
| `MAIL_SEND_ENABLED` | false | Mail | Envío real SMTP | Envíos accidentales | Sí | `config/mail.php` + servicios de send dependen de flag |
| `ECOSISTEMA_MAIL_SEND_ENABLED` | false | Mail notifications | Envío notificaciones | Spam / fuga de datos | Sí | `EcosistemaMailNotificationsAdapter::mailSendEnabled` |
| `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS` | false | URL Locator | Redirect público `/u/{slug}` | Open redirect/tracking | Sí | `EcosistemaUrlLocatorPublicRedirectService` sólo permite redirect cuando `allowed=true`; ruta muestra bloqueado |
| `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED` | false | URL Locator | Tracking de clicks | Riesgo privacidad | Sí | `config/url_locator.php` y adapter capabilities |
| `ECOSISTEMA_URL_LOCATOR_COLLECT_IP` | false | URL Locator | Captura IP | PII | Sí | `config/url_locator.php` gating |
| `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED` | false | Landing | Render landing pública | Exposición pública no deseada | Sí | `GET /l/{slug}` renderiza vista blocked cuando flag false |
| `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED` | false | Landing | Submit de formulario | Escritura de leads | Sí | `POST /l/{slug}/forms/{id}/submit` pasa flag a service |
| `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE` | false | Browser analytics | Escritura collector | Captura analítica/PII | Sí | Ruta collector write valida flag y adapter usa enabled&&write |
| `ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_IP` | false | Browser analytics | Captura IP collector | PII | Sí | `EcosistemaBrowserAnalyticsCollectorService` limpia IP cuando false |
| `ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE` | false | CRM | Conversión submit->lead | Escritura CRM | Sí | `EcosistemaCrmAdapter` requiere `ECOSISTEMA_CRM_ENABLED && ...WRITE` |
| `ECOSISTEMA_CAMPAIGN_CREATION_WRITE` | false | Campaign | Escritura campañas | Mutación de datos | Sí | `config/app.php` (ecosistema_crm/campaign flags) y servicios controlados por adapter |
| `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED` | false | Workflow | Ejecución de workflow | Acciones encadenadas | Sí | `config/app.php` workflow execution/actions en false |
| `ECOSISTEMA_REPORT_EXPORT_WRITE` | false | Reports | Persistencia de export | Fuga de reportes | Sí | ruta export pasa `writeEnabled` y responde bloqueado cuando false |
| `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII` | false | Reports | Inclusión de PII en export | Exposición PII | Sí | ruta export inyecta flag específico |
| `ECOSISTEMA_RATE_LIMIT_WRITE_BLOCKS` | false | Security | Escritura de bloqueos rate limit | Bloqueo accidental de usuarios | Sí | `config/app.php` seguridad + servicios dry-run |
| `ECOSISTEMA_AI_PROVIDER_ENABLED` | false | AI | Llamadas a proveedor AI | Salida de datos externos/costo | Sí | `config/app.php` + provider write proposal flag |
| `ECOSISTEMA_AI_WRITE_PROPOSALS` | false | AI | Escritura propuestas AI | Mutación automática | Sí | `config/app.php` ecosistema_ai.write_proposals |
| `CORE_REGISTRATION_ENABLED` | false | Core registration | Registro inicial | Alta no controlada | Sí | `config/app.php` core_registration.enabled |

## Observaciones de seguridad por default

- `config/*` consume flags con `Env::get(..., 'false')` + `FILTER_VALIDATE_BOOL`, reforzando default seguro aunque no exista variable.
- `routes/web.php` para módulos públicos usa patrón *allow then execute*, y fallback a vista bloqueada si no está permitido.
- No se detectaron secretos reales en ejemplos de entorno; se observan placeholders consistentes.

## Pendientes técnicos (si aplica)

- Ninguno crítico encontrado en este alcance. Si se agregan nuevos módulos con writes/llamadas externas, deben incorporarse a `scripts/smoke-check.php` en la lista de `requiredDisabled`.
