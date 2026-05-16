# Estado Actual: Workers y Cron (real operativo)

Fecha de corte: 2026-05-16.

## Resumen ejecutivo
- **No hay workers productivos activos todavía**.
- Hay un `cron-runner` con ejecución manual para dos jobs acotados y seguros.
- Las colas y workers de procesamiento productivo siguen en estado pendiente/no implementado.

## Comandos existentes y seguros
- `composer cron:check` → valida estructura/autoload/bootstrap sin ejecutar jobs ni consultas DB.
- `composer cron:health` → ejecuta `health-checks` (checks de salud de DB/sistema ya existentes).
- `composer cron:sessions` → ejecuta `session-cleanup` (revoca sesiones expiradas en `core_sessions`).

## Jobs actuales (alcance real)
1. `health-checks`
   - Tipo: verificación de salud.
   - Alcance: revisión de checks registrados; no habilita flujos de negocio.
2. `session-cleanup`
   - Tipo: mantenimiento de sesiones.
   - Alcance: revocación de sesiones vencidas según `SESSION_IDLE_TIMEOUT`.

Estos jobs **no equivalen a procesamiento productivo end-to-end**.

## Workers y colas
- **Workers NO activos todavía** para procesamiento productivo.
- **Colas NO productivas todavía** para mail, IA, webhooks o archivos.
- No hay supervisor/systemd configurado desde este repo para workers productivos.

## Acciones que NO se ejecutan desde cron/jobs actuales
- **No ejecuta AWS/S3 real**.
- **No envía correos masivos**.
- No ejecuta procesamiento real de archivos (uploads/descargas remotas por lote).
- No ejecuta IA productiva (solo rutas y flujos controlados/dry-run según flags).
- No ejecuta webhooks productivos.

## Límites y lectura correcta
- Que exista `scripts/cron-runner.php` **no** implica plataforma de jobs productiva completa.
- La operación actual es de mantenimiento/control básico y validación segura.
- Cualquier activación productiva requiere fase posterior (hardening, observabilidad, colas reales y runbook operativo).
