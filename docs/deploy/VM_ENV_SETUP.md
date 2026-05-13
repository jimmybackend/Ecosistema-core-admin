# Configuración de entorno en VM (segura)

Esta guía permite generar o actualizar `.env` en la VM sin commitear secretos reales.

## 1) Clonar repositorio

```bash
git clone https://github.com/jimmybackend/Ecosistema-core-admin.git
cd Ecosistema-core-admin
composer install
```

## 2) Generar/actualizar `.env` con script seguro

Ejecuta manualmente en la VM:

```bash
bash scripts/setup-vm-env.sh
```

Comportamiento del script:
- Si `.env` **no existe**, lo crea desde `.env.vm.example`.
- Si `.env` **ya existe**, crea respaldo `.env.bak.FECHA-HORA` antes de modificar.
- Conserva variables extra, comentarios y configuración adicional del usuario.
- Solo gestiona actualización de:
  - `DB_PASSWORD` (prompt oculto)
  - `CORE_REGISTRATION_INVITE_CODE` (prompt oculto)
- Opcionalmente permite actualizar:
  - `APP_URL`
  - `SESSION_SECURE`
  - `SESSION_IDLE_TIMEOUT`

## 3) Actualizar `.env` existente sin perder personalizaciones

Puedes volver a ejecutar el script cuando lo necesites. No sobreescribe todo el archivo, solo variables gestionadas.

## 4) Edición manual (si hace falta)

Si necesitas ajustes avanzados, edita `.env` manualmente y conserva valores reales solo en VM/local.

## 5) Reiniciar servicios

```bash
sudo systemctl restart php8.5-fpm
sudo systemctl restart nginx
```

## 6) Configuración HTTP temporal

Mientras no haya certificado HTTPS:

```env
SESSION_SECURE=false
APP_URL=http://localhost
```

## 7) Configuración HTTPS futura

Cuando exista dominio/certificado real:

```env
SESSION_SECURE=true
APP_URL=https://dominio-real
```

## Reglas de seguridad obligatorias

- Nunca commitear `.env` real.
- Nunca pegar passwords en PRs, issues, logs o chats.
- Nunca publicar tokens, AWS keys o secrets SMTP.
- Si un secreto se expuso, rotar credenciales inmediatamente.

## Referencia de validación Drive
- Validación operativa/rollback de Drive: `docs/project/ECOSISTEMA_DRIVE_PRODUCTION_READINESS_CHECKLIST.md`.
