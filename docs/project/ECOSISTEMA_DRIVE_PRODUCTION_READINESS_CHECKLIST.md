# Ecosistema Drive — Production Readiness Checklist (sin activación real)

> Objetivo: preparar una activación futura y controlada de operaciones reales de Drive, sin habilitar producción real en este PR.

## A) Estado actual
- Drive está operativo en panel administrativo (`/cloud/drive`) con navegación central.
- Login, dashboard y permisos administrativos están operativos.
- Las rutas Drive existentes están protegidas por autenticación/permisos.
- La mayoría de capacidades siguen en modo read-only o dry-run.
- La subida real existe sólo como flujo controlado y bloqueado por defecto por flags.
- La descarga real permanece bloqueada/controlada según implementación y flags.
- Repair real no está implementado; solo existe visión/read-only operacional.
- Share real no está implementado; se mantiene contrato read-only.

## B) Variables/flags esperadas (solo nombres)

### Drive general
- `ECOSISTEMA_DRIVE_ENABLED`
- `ECOSISTEMA_DRIVE_MODE`

### AWS/S3
- `ECOSISTEMA_DRIVE_AWS_ENABLED`
- `ECOSISTEMA_DRIVE_AWS_REGION`
- `ECOSISTEMA_DRIVE_AWS_BUCKET`
- `ECOSISTEMA_DRIVE_AWS_ACCESS_KEY_ID`
- `ECOSISTEMA_DRIVE_AWS_SECRET_ACCESS_KEY`

### Llamadas remotas
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS`
- `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS`

### Legacy/Cloud (si aplica)
- `CLOUD_S3_ENABLED`
- `CLOUD_ALLOW_UPLOADS`
- `CLOUD_ALLOW_DOWNLOADS`
- `CLOUD_MAX_UPLOAD_MB`
- `CLOUD_ALLOWED_EXTENSIONS`

## C) Valores seguros por defecto
- Mantener todas las flags remotas en `false` en archivos de ejemplo.
- No subir `.env` al repositorio.
- No commitear respaldos `.env.bak.*`.
- No imprimir `DB_PASSWORD` ni otros secretos de DB.
- No imprimir AWS keys ni secretos AWS.
- No imprimir passwords SMTP.
- Después de crear admin inicial: `CORE_REGISTRATION_ENABLED=false`.

## D) Checklist antes de activar cualquier operación real
- [ ] Validar `main` actualizado en VM.
- [ ] Ejecutar `composer install --no-dev --optimize-autoloader`.
- [ ] Ejecutar `composer dump-autoload`.
- [ ] Ejecutar `composer smoke`.
- [ ] Ejecutar `php -l` de servicios críticos de Drive.
- [ ] Revisar `git status --ignored`.
- [ ] Revisar `git ls-files | grep -E '^\.env$|^\.env\.'`.
- [ ] Validar permisos `cloud.view` y `cloud.manage`.
- [ ] Validar login/sesión/dashboard.
- [ ] Confirmar que `.env` no está tracked por git.
- [ ] Confirmar que `.env.vm.example` no contiene secretos reales.
- [ ] Confirmar que errores/vistas no imprimen secretos.

## E) Checklist de subida real controlada (futuro)
- [ ] Confirmar flags completas de habilitación.
- [ ] Confirmar SDK AWS disponible.
- [ ] Tomar bucket/config desde config/DB; nunca desde request.
- [ ] No aceptar `s3_key` desde request.
- [ ] No aceptar `path`/`prefix` desde request.
- [ ] Validar tamaño máximo.
- [ ] Validar extensión permitida.
- [ ] Validar MIME de forma defensiva.
- [ ] Insertar en `cloud_files` solo después de éxito en S3.
- [ ] Registrar auditoría segura (sin secretos).
- [ ] No exponer `s3_key` ni `stored_name`.

## F) Checklist de descarga real futura
- [ ] **No implementar en este PR**.
- [ ] Requerir flags explícitas.
- [ ] Validar `tenant_id`, `user_id` y `file_id`.
- [ ] Validar estado del archivo.
- [ ] Validar `s3_key` internamente sin exponerlo.
- [ ] No usar signed URL pública por defecto.
- [ ] No hacer streaming sin controles.
- [ ] Registrar auditoría segura.

## G) Checklist de reparación futura
- [ ] No ejecutar repair real todavía.
- [ ] Diseñar primero repair dry-run.
- [ ] Comparar DB/S3 solo con flags explícitas.
- [ ] No exponer `prefix`, `old_s3_key`, `new_s3_key`.
- [ ] No modificar `cloud_files` sin plan de rollback.
- [ ] Registrar logs seguros.

## H) Rollback
- [ ] Apagar flags remotas.
- [ ] Reiniciar `php8.5-fpm` y `nginx`.
- [ ] Validar que `/cloud/drive/upload` vuelve a bloqueo seguro.
- [ ] Validar que no hay nuevas escrituras remotas.
- [ ] Revisar logs y confirmar ausencia de secretos.

## I) Validación manual en VM
```bash
cd /var/www/ecosistema-core-admin
git fetch origin
git checkout main
git pull --ff-only origin main
composer install --no-dev --optimize-autoloader
composer dump-autoload
composer smoke
sudo systemctl restart php8.5-fpm
sudo systemctl restart nginx
curl -I http://127.0.0.1/login
curl -I http://127.0.0.1/dashboard
```

## J) Decisión go / no-go (para pruebas reales controladas)
Marcar **GO** solo si todo lo siguiente se cumple:
- [ ] Smoke checks y lint sin fallas.
- [ ] Flags de seguridad con defaults seguros en ejemplos.
- [ ] Sin secretos en docs, logs, vistas ni commits.
- [ ] Rutas y permisos de Drive validados (`cloud.view`/`cloud.manage`).
- [ ] Rollback probado (apagado de flags + bloqueo de rutas controladas).
- [ ] Evidencia de validación en VM documentada por el equipo.

Si falta cualquier punto: **NO-GO** y mantener Drive en read-only/dry-run/contract.
