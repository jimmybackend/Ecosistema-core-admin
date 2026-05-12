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
