# CORE ADMIN VM RUNBOOK

Guía operativa para clonar, configurar y levantar **Ecosistema Core Admin** en una VM controlada (prueba/producción), apuntando a una base real existente (`adbbmis1_eco`) sin exponer secretos ni modificar la base de datos.

## 1) Alcance y límites
- Este runbook es **independiente** de tareas activas de Drive.
- **No** depende de `cloud_user_roots`.
- **No** depende de `cloud_buckets`.
- **No** modifica lógica de Drive.
- **No** activa AWS/S3 real.
- **No** crea usuarios automáticamente.
- **No** modifica base de datos (sin migraciones, sin seeds, sin DDL/DML operativo).

## 2) Requisitos mínimos
- Linux VM con acceso SSH y permisos de despliegue.
- PHP **8.3**.
- Composer 2.x.
- Servidor web:
  - Nginx o Apache para operación normal, **o**
  - PHP built-in server solo para pruebas temporales.
- Acceso de red a MySQL/MariaDB de la base `adbbmis1_eco` (por ejemplo, alojada en `esforzados.com`).
- Extensiones PHP necesarias (verificar en la VM):
  - `pdo_mysql` (conectividad DB por PDO)
  - `mbstring`
  - `json`
  - `openssl`
  - `session`

> Nota: no guardar credenciales reales en archivos versionados.

## 3) Clonado e instalación
```bash
git clone https://github.com/jimmybackend/Ecosistema-core-admin.git
cd Ecosistema-core-admin
composer install
composer dump-autoload
cp .env.example .env
```

## 4) Configuración segura de `.env`
Editar `.env` localmente en la VM (nunca commitear `.env` real):

### Variables base
- `APP_ENV=production` (o `local` para pruebas).
- `APP_DEBUG=false` en producción.
- `APP_URL` con URL real del entorno.

### Sesiones
- `SESSION_SECURE=true` si el entorno usa HTTPS.
- `SESSION_SECURE=false` **solo** para pruebas locales HTTP.

### Base de datos (DB real existente)
Configurar conexión a `adbbmis1_eco`:
- `DB_CONNECTION=mysql`
- `DB_HOST=<host-db-real>`
- `DB_PORT=3306`
- `DB_DATABASE=adbbmis1_eco`
- `DB_USERNAME=<usuario-db>`
- `DB_PASSWORD=<password-db>`

### Endurecimiento Cloud/Drive (sin AWS/S3 real)
Asegurar:
- `CLOUD_S3_ENABLED=false`
- `CLOUD_ALLOW_UPLOADS=false`
- `CLOUD_ALLOW_DOWNLOADS=false`
- `ECOSISTEMA_DRIVE_MODE=contract`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false`
- `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=false`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=false`

## 5) Web server y DocumentRoot
Configurar el servidor web para que el **DocumentRoot/root** apunte a:
- `.../Ecosistema-core-admin/public`

No apuntar a la raíz del repositorio.

## 6) Arranque temporal para pruebas
Solo para validación rápida en VM:
```bash
php -S 0.0.0.0:8000 -t public
```

## 7) Validación funcional mínima
Validar acceso/rutas:
- `/login`
- `/dashboard`
- `/health/db`
- `/cloud/drive`
- `/cloud/drive/files`
- `/cloud/drive/folders`
- `/cloud/drive/browse`

Checks sugeridos con `curl` (códigos 200/302 según sesión):
```bash
curl -I http://127.0.0.1:8000/login
curl -I http://127.0.0.1:8000/dashboard
curl -I http://127.0.0.1:8000/health/db
```

## 8) Acceso de usuarios (importante)
- No existe registro público confirmado tipo `/register`.
- Para entrar se requiere un usuario existente en `core_users`.
- No crear usuarios manualmente sin `password_hash` válido, `tenant_id`, roles y permisos.
- Cualquier alta inicial de usuario debe hacerse en una tarea separada y controlada.

## 9) Pruebas operativas recomendadas
Ejecutar en la raíz del repositorio:
```bash
composer smoke
composer ops:monitor
php -l scripts/smoke-check.php
```

## 10) Errores comunes y diagnóstico rápido
1. **Error 500 por `.env` mal configurado**  
   Revisar sintaxis y claves obligatorias de `.env`.

2. **No conecta a DB**  
   Verificar `DB_HOST`, `DB_PORT`, firewall, usuario/password y permisos sobre `adbbmis1_eco`.

3. **`/dashboard` redirige a `/login`**  
   Comportamiento esperado cuando no hay sesión activa.

4. **Usuario existe pero sin permisos**  
   Revisar roles/permisos asociados al usuario en tablas de control.

5. **DocumentRoot mal apuntado**  
   Si apunta a la raíz del repo y no a `public/`, habrá errores de rutas/activos.

6. **`SESSION_SECURE=true` en HTTP local**  
   Puede impedir cookie de sesión en pruebas sin HTTPS.

7. **Permisos de `storage/logs`**  
   Asegurar permisos de escritura para el usuario del servicio web si aplica.

## 11) Seguridad y cumplimiento
- No guardar `.env` real en Git.
- No pegar contraseñas reales en documentación, tickets o commits.
- No activar AWS/S3 real en esta etapa.
- No ejecutar cambios de esquema ni datos como parte de este runbook.
