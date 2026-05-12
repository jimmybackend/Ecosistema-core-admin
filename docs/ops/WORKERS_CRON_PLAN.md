# Plan Operativo Seguro: Workers/Cron (futuro)

> Estado actual (PR #33): **primer job controlado habilitado** para health checks de DB.
> Este plan **no activa crontab automáticamente**, **no crea colas**, **no envía correos**, **no procesa archivos** y **no conecta a AWS**.

## 1. Objetivo
Definir y habilitar la primera ejecución controlada de cron en Core Admin con enfoque seguro, trazable y acotado.

## 2. Principios de seguridad
- Ejecutar con usuario Linux dedicado (no root).
- Ejecutar siempre desde la raíz del proyecto.
- Mantener `.env` fuera de control de versiones.
- No exponer secretos (`DB_PASSWORD`, `MAIL_PASSWORD`, `AWS_SECRET_ACCESS_KEY`) en logs ni salidas.
- No habilitar tareas reales sin validación previa (`composer smoke`, permisos y configuración).

## 3. Jobs y estado
1. **System health checks periódicos**
   - Estado: **disponible (primer job controlado)**.
   - Comando manual: `php scripts/cron-runner.php --run=health-checks`.
   - Alcance: solo checks seguros existentes con `check_type` `db/database`.
2. **Limpieza de sesiones expiradas**
   - Estado: **disponible (job controlado)**.
   - Comando manual: `php scripts/cron-runner.php --run=session-cleanup`.
   - Alcance: revoca sesiones de `core_sessions` vencidas por `expires_at` usando `SESSION_IDLE_TIMEOUT` (sin borrar usuarios/roles/permisos).
3. **Limpieza/revisión de archivos temporales/locales**
   - Estado: pendiente/no implementado.
4. **Procesamiento de mail saliente**
   - Estado: pendiente/no implementado.
5. **Sincronización futura de S3**
   - Estado: pendiente/no implementado.
6. **Mantenimiento de logs/auditoría**
   - Estado: pendiente/no implementado.
7. **Onboarding con aprovisionamiento real**
   - Estado: pendiente/no implementado.
8. **Backups/verificaciones operativas**
   - Estado: pendiente/no implementado (este PR #39 solo documenta y agrega checks seguros no destructivos).

## 4. Estructura operativa mínima (actual)
- `scripts/cron-runner.php`
  - `--check`: validación segura de estructura (sin DB).
  - `--run=health-checks`: ejecuta job controlado de health checks.
  - `--run=session-cleanup`: ejecuta job controlado de limpieza de sesiones expiradas.
  - Job desconocido => error controlado y `exit 1`.
- `app/Core/System/CronHealthCheckRunner.php`
  - Runner acotado para ejecutar health checks seguros del módulo System.

## 5. No alcance explícito
- No se modifica esquema de DB.
- No se crean migraciones, seeds, tablas ni campos.
- No se activa crontab automáticamente.
- No se envían correos.
- No se suben/descargan archivos desde cron.
- No se conecta a AWS.
- No se instala supervisor ni systemd.

## 6. Operación manual recomendada
1. Ejecutar `composer cron:check`.
2. Validar `.env` y conectividad DB real (`adbbmis1_eco`).
3. Ejecutar `composer cron:health` manualmente.
4. Ejecutar `composer cron:sessions` manualmente.
5. Revisar salida CLI y logs.

> La activación de cron real en servidor queda para una fase posterior, después de validar manualmente en entorno objetivo.

## Estado mail saliente
- Mail saliente como worker **sigue pendiente**.
- PR #35 sólo prepara envío individual manual (preview/dry-run).


## Estado adicional Mail (PR #36)
- Se habilita únicamente envío manual individual desde preview (sin worker).
- Mail worker/cola/cron para saliente masivo sigue pendiente.

- Mail worker sigue pendiente (no implementado).
- Adjuntos salientes automáticos siguen pendientes (sin colas/workers).

## Estado mail adjuntos (PR #38)
- Mail worker/cola sigue pendiente (no implementado).
- El envío con adjuntos continúa siendo **manual e individual** desde preview.


## Nota PR #39
- Se agrega documentación de backup/restore y check pasivo de estructura.
- Backups automáticos por cron: **pendiente/no implementado**.
- No activar cron de backups todavía.

## Nota de monitoreo operativo (PR #40)
- Referencia de operación diaria: `docs/ops/MONITORING_OPERATIONS_PLAN.md`.
- Este repositorio mantiene monitoreo básico/manual y checks locales no destructivos.
- Monitoreo automático avanzado (alertas externas/APM) sigue pendiente.
- En este PR no se activa ningún cron nuevo.
