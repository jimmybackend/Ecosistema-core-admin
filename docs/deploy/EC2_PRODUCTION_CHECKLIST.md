# EC2 Production Deployment Checklist (Core Admin)

> Objetivo: preparar y validar despliegue seguro de **Ecosistema Core Admin** en entorno EC2/producción.
> Alcance: guía operativa y de verificación. **No reemplaza** hardening completo, monitoreo continuo ni pruebas integrales de negocio.

## 1) Requisitos mínimos
- PHP **8.3+** (compatible con `composer.json`).
- Composer instalado en servidor.
- Servidor web: Nginx o Apache.
- Extensiones PHP necesarias: al menos `pdo_mysql`.
- MySQL/MariaDB accesible para la app.

## 2) Preparar código
```bash
git clone <repo-url>
cd Ecosistema-core-admin
cp .env.example .env
```

## 3) Configurar variables de entorno (`.env`)
Revisar y ajustar como mínimo:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://tu-dominio.example`
- `TIMEZONE=America/Mexico_City` (si aplica a tu operación)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE=adbbmis1_eco`, `DB_USERNAME`, `DB_PASSWORD`
- `SESSION_NAME=ecosistema_core_admin`
- `SESSION_SECURE=true` (requiere HTTPS activo)
- `SESSION_SAMESITE=Lax`
- `SESSION_IDLE_TIMEOUT=1800`

## 4) Instalar dependencias y validar autoload
```bash
composer install --no-dev --optimize-autoloader
composer dump-autoload
composer smoke
```

## 5) Configuración web server
- Apuntar `DocumentRoot` (Apache) o `root` (Nginx) a `public/`.
- No exponer la raíz del repositorio.
- Confirmar HTTPS operativo **antes** de usar `SESSION_SECURE=true`.

## 6) Permisos mínimos de carpetas
- Aplicar permisos mínimos requeridos por tu distro/stack para lectura de código y ejecución del runtime PHP.
- Evitar permisos globales (ej. `777`) en producción.

## 7) Verificaciones funcionales mínimas post-deploy
- Validar `/login`.
- Validar que `/dashboard` sin sesión redirige a `/login`.
- Validar `/health/db`.
- Revisar logs del servidor web y logs de PHP ante cualquier error.

## 8) Seguridad de red (Security Group / firewall)
- 80/443 públicos solo según necesidad de exposición.
- 22 (SSH) restringido a IP administrativa.
- MySQL/MariaDB no público salvo diseño explícito y seguro.


## 9) SMTP (preparación segura)
Configurar en `.env`:
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`

Validaciones y seguridad:
- Confirmar `MAIL_SEND_ENABLED=false` hasta validar toda la configuración.
- No usar credenciales personales de correo.
- No poner secretos SMTP en README ni documentación pública.
- Si más adelante se habilita SMTP real, validar firewall/reglas de salida del servidor hacia el proveedor SMTP.

## 10) Respaldo y operación
- Realizar backup antes de cambios relevantes.
- Usar usuario de DB con privilegios mínimos para la aplicación.

## 11) Cloud/S3 (preparación segura)
Configurar en `.env`:
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`

Validaciones y seguridad:
- Confirmar `CLOUD_S3_ENABLED=false` hasta validar toda la implementación real.
- Confirmar `CLOUD_ALLOW_DOWNLOADS=false` y `CLOUD_ALLOW_UPLOADS=false` hasta implementación real.
- No usar usuario root de AWS.
- Usar IAM dedicado con permisos mínimos.
- No poner secretos en README ni documentación pública.
- Revisar políticas de bucket antes de habilitar producción.

## 12) No hacer en producción
- No commitear `.env`.
- No usar `APP_DEBUG=true`.
- No dejar `DB_PASSWORD=change-me`.
- No exponer `/vendor` públicamente.
- No apuntar servidor web a la raíz del repo.
- No abrir MySQL al mundo.
- No usar credenciales root de MySQL para la app.
- No guardar secretos en README ni documentación pública.
- No ejecutar seeds de prueba en producción.

## 13) Cloud uploads controlados (PR #29)
- Crear/verificar directorio `storage/app/cloud` cuando se use modo local.
- Confirmar que `storage/` no esté bajo DocumentRoot público.
- Aplicar permisos mínimos de escritura para usuario web (sin `777`).
- Mantener `CLOUD_ALLOW_UPLOADS=false` por defecto en producción hasta validar operación.
- Al habilitar S3 real, usar IAM de mínimo privilegio (sin usuario root).

## 14) Descargas Cloud controladas (PR #30)
- Confirmar que `storage/app/cloud` no esté bajo `DocumentRoot`/`root` público.
- Confirmar permisos de lectura mínimos solo para usuario web/proceso PHP.
- Mantener `CLOUD_ALLOW_DOWNLOADS=false` hasta validar operación completa.
- Verificar que no exista listado de directorio público para `storage`.
- Monitorear auditoría (`cloud.file_downloaded`) y logs de errores de descarga.

## 15) Cron/Workers futuros (PR #31)
- Estado actual: solo preparación documental y check pasivo; **no hay cron activo**.
- Ejecutar cron/worker con usuario Linux dedicado (no root).
- Ejecutar siempre desde la raíz del proyecto.
- Redirigir salida a `storage/logs` o a `syslog` según política operativa.
- No activar cron hasta validar permisos, `.env` y `composer smoke`.

Ejemplo futuro **comentado** (no activar sin revisión):
```cron
# * * * * * cd /var/www/ecosistema-core-admin && php scripts/cron-runner.php --check >> storage/logs/cron.log 2>&1
```

> Este ejemplo es de referencia futura y no debe activarse en producción sin revisión técnica y de seguridad.


## 16) Cron health checks controlados (PR #33)
- Probar primero en forma manual:
  - `composer cron:check`
  - `composer cron:health`
- `cron:health` requiere DB real configurada en `.env` (`DB_DATABASE=adbbmis1_eco`).
- Ejecutar cron con usuario Linux dedicado (no root).
- Verificar permisos de escritura en `storage/logs` antes de redirigir salida.
- No activar cron en producción sin validar previamente `.env`, conectividad DB y salida del comando manual.

Ejemplo de cron (activar solo después de validar manualmente):
```cron
* * * * * cd /var/www/ecosistema-core-admin && php scripts/cron-runner.php --run=health-checks >> storage/logs/cron-health.log 2>&1
```


## 17) Cron limpieza de sesiones controlado (PR #34)
- Probar primero en forma manual:
  - `composer cron:check`
  - `composer cron:sessions`
- `cron:sessions` requiere DB real configurada en `.env` (`DB_DATABASE=adbbmis1_eco`).
- Ejecutar cron con usuario Linux dedicado (**no root**).
- Verificar permisos de escritura en `storage/logs` antes de redirigir salida.
- No activar cron sin validar backup/configuración revisada y salida del comando manual.

Ejemplo de cron (activar solo después de validar manualmente):
```cron
*/15 * * * * cd /var/www/ecosistema-core-admin && php scripts/cron-runner.php --run=session-cleanup >> storage/logs/cron-sessions.log 2>&1
```

## Mail saliente (PR #35)
- Verificar SMTP técnico antes de habilitar envío real.
- No usar credenciales personales en SMTP.
- Mantener `MAIL_SEND_ENABLED=false` hasta validación funcional/auditoría.
- Mantener `MAIL_ALLOW_TEST_SEND=false` en producción inicial.
- Revisar logs y auditoría al preparar envío individual.


## 18) Mail envío individual controlado (PR #36)
- Verificar SMTP en entorno controlado antes de habilitar producción.
- No usar credenciales personales; usar cuenta SMTP dedicada.
- Mantener límites de envío bajos en proveedor SMTP.
- Revisar auditoría de `mail.send_attempted`, `mail.sent`, `mail.send_failed`, `mail.send_blocked_by_config`.
- Después de pruebas iniciales, evaluar volver `MAIL_ALLOW_TEST_SEND=false`.

- Verificar permisos de `storage/` antes de habilitar envío real con adjuntos.
- Confirmar que `storage/` no sea público.
- Confirmar límites de tamaño para adjuntos y upload cloud.
- Confirmar registro de auditoría para `mail.attachments_updated`.

## 19) Mail adjuntos locales salientes (PR #38)
- Revisar `CLOUD_LOCAL_STORAGE_PATH` y confirmar que exista.
- Confirmar que `storage/` no sea público.
- Confirmar permisos mínimos de lectura para usuario web/PHP.
- Configurar límites: `MAIL_MAX_ATTACHMENTS`, `MAIL_MAX_ATTACHMENT_MB`, `MAIL_MAX_TOTAL_ATTACHMENT_MB`.
- Probar SMTP con archivo pequeño antes de uso operativo.
- Revisar auditoría de `mail.send_attempted`, `mail.sent`, `mail.send_failed`, `mail.send_blocked_by_attachments`.
