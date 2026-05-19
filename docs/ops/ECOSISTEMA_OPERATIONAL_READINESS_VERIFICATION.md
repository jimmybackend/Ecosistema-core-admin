# ECOSISTEMA Operational Readiness Verification (demo/preproducción)

> Estado verificado para este repositorio: **operación controlada para demo/preproducción**.  
> Este documento evita interpretar los scripts/documentación actuales como operación productiva completa con workers, colas o automatización masiva.

## 1) Alcance y no-alcance

### Alcance (sí existe hoy)
- Verificaciones de entorno/autoload/bootstrap.
- Jobs manuales acotados vía `scripts/cron-runner.php` para:
  - `health-checks`
  - `session-cleanup`
- Checks operativos no destructivos (`smoke`, `backup-check`, `ops-monitor-check`, `cron --check`).

### No-alcance (no existe o no debe asumirse)
- **No hay workers productivos activos** (colas/supervisor/systemd de procesamiento continuo no implementados desde este repo).
- **No hay automatización productiva masiva** (mail masivo, procesamiento por lotes, webhooks productivos, pipelines IA/S3 reales).
- **No hay activación automática de crontab** desde scripts del repo.

## 2) Inventario de comandos actuales

## 2.1 Comandos de verificación de entorno (sin ejecución de jobs)
- `composer smoke` / `php scripts/smoke-check.php`  
  Valida archivos, autoload y estado base del proyecto.
- `composer cron:check` / `php scripts/cron-runner.php --check`  
  Valida estructura y bootstrap; **no ejecuta jobs ni consultas DB**.
- `composer backup:check` / `php scripts/backup-check.php`  
  Check documental/estructura para backup; **no hace backup real**.
- `composer ops:monitor` / `php scripts/ops-monitor-check.php`  
  Check operativo de archivos/directorios/espacio; no activa procesos.

## 2.2 Comandos que sí tocan DB
- `composer cron:health` / `php scripts/cron-runner.php --run=health-checks`.
- `composer cron:sessions` / `php scripts/cron-runner.php --run=session-cleanup`.

Ambos requieren DB real operativa en `.env` y se ejecutan de forma manual/controlada.

## 2.3 Comandos que no deben correrse en producción sin revisión previa
- Cualquier comando `--run=...` de `cron-runner` sin validar:
  - `.env` correcto
  - conectividad DB
  - privilegios mínimos de usuario Linux y DB
  - ruta de logs y política de retención
- Cualquier propuesta de entrada en crontab antes de pruebas manuales y rollback documentado.

## 3) Workers y procesamiento productivo: estado honesto

### Lo que **NO** existe todavía
- Workers de cola para mail saliente masivo.
- Workers de procesamiento batch de archivos (subidas/descargas remotas por lote).
- Workers de IA productiva con proveedor externo.
- Orquestación productiva de webhooks/eventos con reintentos y DLQ.
- Supervisión de workers (systemd/supervisor) versionada y validada end-to-end desde este repo.

### Traducción operativa de términos ambiguos
- “workers activos” → **No aplica aún**; sólo hay ejecución manual de jobs acotados.
- “automatización productiva” → **No aplica aún**; hay checks y runbooks de preparación.
- “envío masivo” → **No implementado** como pipeline/worker productivo.
- “procesamiento real” → limitado a jobs puntuales (`health-checks`, `session-cleanup`) bajo ejecución manual.
- “S3 real” → **deshabilitado por default** por flags; no asumir operación productiva.
- “IA real” → **deshabilitada por default**; sólo flujos controlados/dry-run según flags.

## 4) Cron jobs actualmente controlados

Estado actual controlado:
- `--check`: validación segura sin DB.
- `--run=health-checks`: ejecución puntual de health checks existentes.
- `--run=session-cleanup`: revocación puntual de sesiones expiradas.

No controlado/pendiente:
- Alta frecuencia productiva con monitoreo de SLO/alertas maduras.
- Escalamiento horizontal de jobs/workers.
- Gestión formal de fallas/reintentos/colas de alto volumen.

## 5) Backups: reglas explícitas

- Este repo **no debe almacenar**:
  - dumps de DB
  - snapshots con datos reales
  - secretos (`.env` reales, llaves, tokens, credenciales)
- El plan de backup/restore actual es de preparación y validación documental/técnica, no de ejecución automática productiva desde el repositorio.

## 6) Requisitos mínimos para EC2/VM (antes de producción real)

- PHP/Composer compatibles.
- Web server apuntando a `public/`.
- `.env` real sólo en servidor (no versionado), con `APP_DEBUG=false` en producción.
- DB accesible con usuario de privilegios mínimos.
- Logs con rotación y permisos correctos.
- HTTPS operativo antes de forzar `SESSION_SECURE=true`.
- Pruebas manuales exitosas de `smoke`, `cron:check` y jobs DB en ventana controlada.

## 7) Go-live mínimo / No-go / Pendientes

### Go-live mínimo (aceptable para demo/preproducción)
- `composer smoke` en verde.
- `composer cron:check` en verde.
- Validación manual de `/login`, `/dashboard`, `/health/db`.
- Flags de integraciones sensibles (S3/IA/SMTP masivo) en estado seguro por defecto.
- Sin claims de “procesamiento productivo completo”.

### No-go (bloquea salida a producción)
- Falta de validación DB para jobs que la requieren.
- Intento de habilitar cron real sin pruebas manuales previas ni rollback.
- Secretos versionados o evidencia de dumps en repo.
- Comunicación/documentación que afirme workers activos cuando no existen.

### Pendientes antes de producción real
- Diseño e implementación de workers/colas productivas (mail, archivos, integraciones, IA según alcance).
- Operación de cron con observabilidad completa (alertas, retención, runbooks de incidentes).
- Endurecimiento de seguridad y pruebas de carga/recuperación.
- Criterios de aceptación SLO/SLA para jobs críticos.

## 8) Conclusión

La base actual es **válida para demo y preproducción controlada**, pero **no debe presentarse como automatización productiva completa**. El estado correcto hoy es: checks seguros + dos jobs manuales acotados + pendientes explícitos de workers/colas/operación masiva.

## 9) Referencias
- `README.md`
- `docs/ops/WORKERS_CRON_CURRENT_STATE.md`
- `docs/ops/WORKERS_CRON_PLAN.md`
- `docs/ops/BACKUP_RESTORE_PLAN.md`
- `docs/ops/MONITORING_OPERATIONS_PLAN.md`
- `docs/deploy/EC2_PRODUCTION_CHECKLIST.md`
- `docs/deploy/CORE_ADMIN_VM_RUNBOOK.md`
- `scripts/cron-runner.php`
- `scripts/backup-check.php`
- `scripts/ops-monitor-check.php`
- `scripts/smoke-check.php`


## Actualización de ejecución real en VM controlada (2026-05-19)

- Repo actualizado y limpio en `main` (commit `836d0db`, PR #257).
- Nginx y PHP-FPM operativos (`fastcgi_pass unix:/run/php/php8.5-fpm.sock`).
- `GET /login` validado en local y público con `HTTP 200`.
- `POST /login` validado con `HTTP 302 Found` y `Location: /dashboard`.
- Dashboard confirmado visible en navegador.
- DB remota `adbbmis1_eco` autorizada por IP pública de la VM en Remote MySQL / Manage Access Hosts.
- Causa raíz del fallo inicial: `.env` ilegible para `www-data` por `chmod 600`.
- Corrección aplicada: owner deploy user + group `www-data` + `chmod 640` para `.env`.
- Pendiente obligatorio preprod/prod: rotar `DB_PASSWORD`, `APP_KEY` y `CORE_REGISTRATION_INVITE_CODE`.
- `composer schema:usage` en validación real reporta 5 incompatibilidades pendientes (`mail_messages.status`, `os_ai_proposals.id`, `os_ai_proposals.module_code`, `os_ai_proposals.entity_table`, `os_ai_proposals.entity_id`) sin bloquear login.

## Referencia cruzada VM demo privada (2026-05-19)

La evidencia formal de implementación y validación de login/dashboard sobre VM controlada se consolida en:

- `docs/demo/CORE_ADMIN_PRIVATE_DEMO_VM_IMPLEMENTATION_CHECKLIST.md`
