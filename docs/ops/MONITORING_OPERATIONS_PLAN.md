# Plan Operativo: Monitoreo Básico Core Admin (EC2/Producción)

> PR #40. Alcance no destructivo: sin modificar DB, sin enviar correos, sin conectar AWS, sin instalar herramientas externas.

## 1) Objetivo
Definir una rutina mínima de monitoreo operativo para Core Admin en producción con validaciones manuales y checks locales seguros.

## 2) Controles básicos de salud
- Salud de aplicación:
  - Revisar `/login`, `/dashboard` y `/health/db`.
  - Si falla `/health/db`, revisar primero configuración DB en `.env` y conectividad.
- Health checks DB:
  - `composer cron:health` (usa job controlado existente).
  - Validar salida sin exponer secretos.

## 3) Logs a revisar
- Web server:
  - Nginx: `/var/log/nginx/error.log`, `/var/log/nginx/access.log`.
  - Apache: `/var/log/apache2/error.log`, `/var/log/apache2/access.log`.
- PHP:
  - Según `php.ini`/pool FPM (`error_log`).
- Cron:
  - Archivos definidos al redirigir `cron-runner.php` (ej. `storage/logs/cron-health.log`, `storage/logs/cron-sessions.log`).

## 4) Storage, disco y permisos
- Confirmar existencia de `storage/` (si aplica en instalación).
- Confirmar escritura en `storage/` para usuario runtime PHP.
- Confirmar disponibilidad de `storage/app/cloud` para modo local (o documentar ausencia si cloud local no está habilitado).
- Revisar espacio libre de disco de la partición del proyecto.

## 5) Configuración de entorno (.env)
Validar en producción:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `SESSION_SECURE=true` con HTTPS real
- `DB_DATABASE=adbbmis1_eco`
- Estado SMTP:
  - `MAIL_SEND_ENABLED=false` hasta validación operativa
  - `MAIL_ALLOW_TEST_SEND=false` en producción inicial
- Estado Cloud:
  - `CLOUD_S3_ENABLED` según estrategia real
  - `CLOUD_ALLOW_UPLOADS`/`CLOUD_ALLOW_DOWNLOADS` solo si operación validada

> No imprimir `.env` completo ni secretos (`DB_PASSWORD`, `MAIL_PASSWORD`, `AWS_SECRET_ACCESS_KEY`).

## 6) Backup/Restore operativo
- Ejecutar `composer backup:check`.
- Confirmar backup reciente de DB y archivos locales cloud (si aplica).
- Confirmar que pruebas de restore se realizan en entorno separado.

## 7) Comandos operativos sugeridos
```bash
composer smoke
composer backup:check
composer cron:check
composer cron:health
composer cron:sessions
composer ops:monitor
```

## 8) Incidentes comunes: qué revisar primero

### A. Login no carga
1. Web server/PHP logs.
2. `APP_URL` y DocumentRoot apuntando a `public/`.
3. Permisos de archivos de proyecto.

### B. Dashboard redirige incorrectamente
1. Estado de sesión (`SESSION_SECURE`, `SESSION_SAMESITE`).
2. HTTPS real activo si `SESSION_SECURE=true`.
3. Revisar expiración (`SESSION_IDLE_TIMEOUT`).

### C. DB no conecta
1. `DB_HOST`, `DB_PORT`, `DB_DATABASE=adbbmis1_eco`, `DB_USERNAME`.
2. Firewall/security group y reachability de DB.
3. `/health/db` y logs.

### D. health/db falla
1. Variables DB en `.env`.
2. Estado motor MySQL/MariaDB.
3. Permisos del usuario DB.

### E. cron:health falla
1. Ejecutar `composer cron:check`.
2. Confirmar autoload/bootstrap.
3. Confirmar conectividad DB y tablas reales de health.

### F. cron:sessions falla
1. Ejecutar `composer cron:check`.
2. Validar DB y permisos sobre `core_sessions`.
3. Revisar `SESSION_IDLE_TIMEOUT`.

### G. Mail no envía
1. Confirmar `MAIL_SEND_ENABLED`.
2. Revisar host/puerto/cifrado SMTP.
3. Revisar logs de aplicación y servidor.

### H. Adjuntos no se descargan
1. `CLOUD_ALLOW_DOWNLOADS`.
2. Existencia/permisos de `storage/app/cloud` en modo local.
3. Logs de cloud/mail y validación de tenant/usuario.

### I. Storage sin permisos
1. `is_writable(storage/)` para usuario runtime.
2. Propietario/grupo del directorio.
3. Evitar `777` en producción.

### J. Disco lleno
1. `df -h` en servidor.
2. Rotación/limpieza de logs.
3. Capacidad de partición para `storage` y backups.

### K. APP_DEBUG=true en producción
1. Cambiar a `APP_DEBUG=false`.
2. Limpiar cachés/config según flujo operativo.
3. Verificar que no se expongan trazas.

### L. SESSION_SECURE mal configurado
1. Si hay HTTPS, usar `SESSION_SECURE=true`.
2. Si no hay HTTPS temporalmente, documentar riesgo y plan de corrección.
3. Validar cookie de sesión en navegador.

## 9) Límites de este plan
- Monitoreo automático avanzado (APM/alertas externas) pendiente.
- No crea servicios systemd, Docker ni CI/CD.
- No habilita nuevos cron automáticamente.
