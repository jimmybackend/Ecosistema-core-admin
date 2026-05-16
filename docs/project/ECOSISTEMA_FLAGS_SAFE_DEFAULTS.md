# ECOSISTEMA Flags: Safe Defaults Matrix

Objetivo: asegurar que las capacidades **controlled** permanezcan desactivadas por defecto en plantillas de entorno (`.env.example` y `.env.vm.example`) para reducir riesgo operativo en producción.

## Principio
- **Controlled no significa activo**: toda capacidad con impacto en escritura, proveedores externos, redirección pública, tracking o transferencias remotas debe iniciar en `false`.
- La habilitación debe ser explícita, por entorno y con hardening previo.

## Matriz de flags críticas (default esperado: `false`)

| Flag | Categoría | `.env.example` | `.env.vm.example` | Config relacionada | Estado esperado |
|---|---|---:|---:|---|---|
| `MAIL_SEND_ENABLED` | envío real de correo | `false` | `false` | `config/mail.php` | Seguro |
| `MAIL_ALLOW_TEST_SEND` | envío real de correo | `false` | `false` | `config/mail.php` | Seguro |
| `CLOUD_S3_ENABLED` | S3/AWS real | `false` | `false` | `config/cloud.php` | Seguro |
| `CLOUD_ALLOW_UPLOADS` | subida/descarga de archivos | `false` | `false` | `config/cloud.php` | Seguro |
| `CLOUD_ALLOW_DOWNLOADS` | subida/descarga de archivos | `false` | `false` | `config/cloud.php` | Seguro |
| `ECOSISTEMA_DRIVE_AWS_ENABLED` | S3/AWS real | `false` | `false` | `config/ecosistema_drive.php` | Seguro |
| `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS` | proveedor externo | `false` | `false` | `config/ecosistema_drive.php` | Seguro |
| `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS` | proveedor externo | `false` | `false` | `config/ecosistema_drive.php` | Seguro |
| `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS` | redirección pública | `false` | `false` | `config/url_locator.php` | Seguro |
| `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED` | tracking/analytics | `false` | `false` | `config/url_locator.php` | Seguro |
| `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED` | redirección pública | `false` | `false` | `config/app.php` | Seguro |
| `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED` | escritura DB | `false` | `false` | `config/app.php` | Seguro |
| `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE` | tracking/analytics | `false` | `false` | `config/app.php` | Seguro |
| `ECOSISTEMA_AI_PROVIDER_ENABLED` | IA/proveedor externo | `false` | `false` | `config/app.php` | Seguro |
| `ECOSISTEMA_AI_WRITE_PROPOSALS` | IA/proveedor externo | `false` | `false` | `config/app.php` | Seguro |
| `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED` | escritura DB | `false` | `false` | `config/app.php` | Seguro |
| `ECOSISTEMA_REPORT_EXPORT_WRITE` | escritura DB | `false` | `false` | `config/app.php` | Seguro |

## Flags adicionales auditadas en configs
- `config/s3_drive.php`: `S3_DRIVE_ALLOW_REMOTE_CALLS`, `S3_DRIVE_ALLOW_SIGNED_URLS`, `S3_DRIVE_ALLOW_REMOTE_UPLOADS`, `S3_DRIVE_ALLOW_REMOTE_DOWNLOADS` con fallback `false`.
- `config/cloud.php`: `CLOUD_S3_ENABLED`, `CLOUD_ALLOW_UPLOADS`, `CLOUD_ALLOW_DOWNLOADS` con fallback `false`.
- `config/url_locator.php`: `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS`, `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED`, `ECOSISTEMA_URL_LOCATOR_COLLECT_USER_AGENT` con fallback `false`.

## Smoke-check de regresión
`scripts/smoke-check.php` valida que la plantilla `.env.example` mantenga en `false` las flags críticas de esta matriz.

Si alguna flag crítica aparece en `true` o no existe con su valor esperado, el smoke debe fallar con `FAIL`.
