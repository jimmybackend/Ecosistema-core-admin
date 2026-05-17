# CORE Admin — Local Verification Runbook

Runbook único para verificar Core Admin de forma **repetible**, **segura** y **sin activar integraciones productivas**.

> Alcance: verificación local técnica/QA para Core Admin interno. No usar este flujo para habilitar operación comercial ni productiva.

## 1) Precondiciones

- **PHP y Composer** instalados en la máquina local.
- Dependencias del proyecto instaladas en el repositorio.
- Archivo `.env` creado desde `.env.example`.
- Base de datos de **demo/local** disponible si se quiere validar conexión o ejecutar checks opcionales de esquema.
- Prohibido usar datos reales de clientes, secretos reales o dumps SQL productivos.

Comandos iniciales recomendados:

```bash
composer install
cp .env.example .env
```

## 2) Guardrails obligatorios antes de validar

Core Admin documenta módulos en estado `read-only`, `dry-run`, `controlled` y `roadmap`; por lo tanto, la verificación local debe mantener integraciones externas desactivadas.

Checklist mínimo de flags en `false` (o equivalentes apagados):

- Mail/SMTP real
  - `MAIL_SEND_ENABLED=false`
  - `MAIL_ALLOW_TEST_SEND=false`
  - `ECOSISTEMA_SMTP_ENABLED=false`
  - `ECOSISTEMA_MAIL_SEND_ENABLED=false`
- S3/Drive remoto
  - `CLOUD_S3_ENABLED=false`
  - `S3_DRIVE_ALLOW_REMOTE_CALLS=false`
  - `S3_DRIVE_ALLOW_REMOTE_UPLOADS=false`
  - `S3_DRIVE_ALLOW_REMOTE_DOWNLOADS=false`
  - `ECOSISTEMA_DRIVE_AWS_ENABLED=false`
  - `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false`
  - `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false`
  - `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=false`
- URL Locator
  - `ECOSISTEMA_URL_LOCATOR_ENABLED=false`
  - `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS=false`
  - `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED=false`
- Landing
  - `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=false`
  - `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED=false`
- Browser Analytics
  - `ECOSISTEMA_BROWSER_ANALYTICS_ENABLED=false`
  - `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE=false`
- CRM
  - `ECOSISTEMA_CRM_ENABLED=false`
  - `ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE=false`
  - `ECOSISTEMA_CRM_FOLLOWUP_TASK_WRITE=false`
  - `ECOSISTEMA_CRM_LEAD_STATUS_WRITE=false`
- Workflow
  - `ECOSISTEMA_WORKFLOW_ENABLED=false`
  - `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=false`
- Reports
  - `ECOSISTEMA_REPORT_EXPORT_WRITE=false`
- Campaigns
  - `ECOSISTEMA_CAMPAIGN_CREATION_WRITE=false`
- Rate limit (enforcement)
  - `ECOSISTEMA_RATE_LIMIT_ENABLED=false`
  - `ECOSISTEMA_RATE_LIMIT_WRITE_BLOCKS=false`
- IA externa y escritura asociada
  - `ECOSISTEMA_AI_ENABLED=false`
  - `ECOSISTEMA_AI_PROVIDER_ENABLED=false`
  - `ECOSISTEMA_AI_WRITE_PROPOSALS=false`
- Registro
  - `CORE_REGISTRATION_ENABLED=false`

## 3) Secuencia estándar de verificación local

Ejecutar en este orden para que toda revisión se haga igual:

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
composer smoke
```

### Qué valida cada comando

1. `composer dump-autoload`
   - Reconstruye autoload y detecta problemas básicos de clases/carga.
2. `php -l routes/web.php`
   - Verifica sintaxis del archivo principal de rutas.
3. `php -l scripts/smoke-check.php`
   - Verifica sintaxis del script de smoke.
4. `composer smoke`
   - Ejecuta smoke checks del repositorio para validar estructura, archivos críticos y consistencia base.

## 4) Nota sobre `composer smoke` y `schema:check`

Según el README del repositorio, `composer smoke` es el comando operativo de verificación.

Cuando exista base de datos disponible en entorno local/demo, el `schema:check` puede utilizarse como validación adicional de compatibilidad de esquema en modo **read-only**. Si no hay DB disponible, esta validación se considera **opcional** y no bloquea la revisión documental/local básica.

## 5) Validación rápida de rutas (sanity check)

Además de lint/smoke, revisar de forma básica que los artefactos de ruteo y trazabilidad documental sigan consistentes:

- `routes/web.php` compila (lint OK).
- El mapa de rutas técnico (`CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`) sigue alineado con la intención de revisión.
- No se promueve como “productivo” lo que esté marcado como `read-only`, `dry-run`, `controlled` o `roadmap`.

## 6) Qué hacer si falla algo

1. Abrir issue pequeño y específico (1 problema por issue).
2. No parchear “a ciegas” sin ubicar causa raíz.
3. Copiar error exacto (stacktrace/salida) **sin secretos** ni credenciales.
4. Indicar:
   - comando ejecutado,
   - resultado esperado vs real,
   - contexto mínimo de entorno (PHP/Composer, DB sí/no).
5. Si el fallo implica flags o permisos, contrastar con la matriz de seguridad/flags antes de proponer cambios.

## 7) Fuentes de verdad para esta verificación

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
