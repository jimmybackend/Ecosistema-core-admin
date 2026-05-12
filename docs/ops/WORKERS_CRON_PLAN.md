# Plan Operativo Seguro: Workers/Cron (futuro)

> Estado actual (PR #31): **documentación y verificación pasiva solamente**.
> Este plan **no activa cron**, **no ejecuta jobs reales**, **no crea colas**, **no envía correos**, **no procesa archivos** y **no conecta a AWS**.

## 1. Objetivo
Definir la estructura mínima para preparar una operación futura de tareas periódicas en Core Admin, con enfoque seguro y trazable.

## 2. Principios de seguridad
- Ejecutar con usuario Linux dedicado (no root).
- Ejecutar siempre desde la raíz del proyecto.
- Mantener `.env` fuera de control de versiones.
- No exponer secretos (`DB_PASSWORD`, `MAIL_PASSWORD`, `AWS_SECRET_ACCESS_KEY`) en logs ni salidas.
- No habilitar tareas reales sin validación previa (composer smoke, permisos y configuración).

## 3. Jobs futuros (pendiente/no implementado)
Todos los siguientes puntos quedan en estado **pendiente/no implementado** en este PR:

1. **System health checks periódicos**
   - Verificaciones técnicas controladas y acotadas por tenant/contexto.
2. **Limpieza de sesiones expiradas**
   - Limpieza segura sobre tabla real de sesiones según políticas vigentes.
3. **Limpieza/revisión de archivos temporales/locales**
   - Rotación y limpieza de temporales sin exponer rutas sensibles.
4. **Procesamiento de mail saliente**
   - Flujo futuro de salida con controles de reintento y auditoría.
5. **Sincronización futura de S3**
   - Solo cuando S3 real esté aprobado y habilitado.
6. **Mantenimiento de logs/auditoría**
   - Rotación/retención de logs y mantenimiento mínimo de trazabilidad.
7. **Onboarding con aprovisionamiento real**
   - Ejecución real diferida a PR posterior.
8. **Backups/verificaciones operativas**
   - Controles de respaldo/restauración y verificaciones periódicas.

## 4. Estructura mínima propuesta (actual)
- `scripts/cron-runner.php` (placeholder seguro)
  - Modo `--check`: valida carga de autoload/bootstrap.
  - Lista jobs como pendiente/no implementado.
  - Retorna `0` si estructura base carga correctamente.
  - Retorna `1` si falta autoload/bootstrap o hay error crítico.

## 5. No alcance explícito de este PR
- No se modifica base de datos ni esquema.
- No se crean migraciones, seeds, tablas ni campos.
- No hay conexión obligatoria a DB para checks.
- No se envían correos.
- No se suben/descargan archivos.
- No se conecta a AWS.
- No se instala supervisor ni systemd.

## 6. Próximos pasos (PR futuros)
1. Definir contrato de cada job (input/output/errores).
2. Definir política de reintentos y observabilidad.
3. Acordar retención de logs y auditoría operativa.
4. Habilitar ejecución real por fases, con checklist de rollback.

## Actualización PR #32: Onboarding seguro manual
- Se habilitó únicamente ejecución manual/controlada desde UI para avanzar el siguiente paso seguro de una run existente.
- Tipos permitidos ahora: `null/empty`, `noop`, `manual`, `checklist`.
- Tipos no soportados: `skipped` con warning y sin ejecución externa.
- Sigue sin cron activo, sin workers, sin AWS y sin SMTP.
- La integración con `scripts/cron-runner.php` queda pendiente para PR futuro.
