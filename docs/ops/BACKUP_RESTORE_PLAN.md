# Plan Operativo Seguro: Backup y Restore (Core Admin)

> Estado de este PR (#39): **documentación y checks seguros**.
> Este plan **no ejecuta backups reales**, **no ejecuta restores reales**, **no borra datos**, **no modifica DB** y **no activa cron de backups**.

## 1) Objetivo
Definir un procedimiento operativo seguro para respaldar y validar restore de Core Admin en entornos controlados, minimizando riesgo operativo y evitando exposición de secretos.

## 2) Alcance y límites
Incluye:
- Respaldo lógico de MySQL/MariaDB (estructura + datos) con placeholders.
- Respaldo de archivos locales Cloud en `storage/app/cloud`.
- Respaldo de `.env` fuera del repositorio.
- Respaldo de logs operativos (si aplica).
- Validaciones de integridad.
- Restore en ambiente de prueba (nunca directo en producción).

No incluye:
- Ejecución automática de cron de backups.
- Dumps reales en este repositorio.
- Cambios de estructura de base de datos.

## 3) Variables esperadas (sin secretos)
Usar variables de entorno o placeholders:
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `BACKUP_DIR` (ruta segura fuera del repo)
- `APP_ENV_FILE` (ruta de `.env` fuera del repo)

Ejemplo:
```bash
export DB_HOST="127.0.0.1"
export DB_PORT="3306"
export DB_DATABASE="adbbmis1_eco"
export DB_USERNAME="ecosistema_user"
export BACKUP_DIR="/srv/backups/ecosistema-core-admin"
export APP_ENV_FILE="/var/www/ecosistema-core-admin/.env"
```

## 4) Backup de base de datos (ejemplo seguro)
> No usar root como recomendación principal. Usar usuario dedicado con privilegios mínimos de lectura para backup.

```bash
mkdir -p "$BACKUP_DIR"
DATE_UTC="$(date -u +%Y%m%dT%H%M%SZ)"
DB_DUMP="$BACKUP_DIR/db_${DB_DATABASE}_${DATE_UTC}.sql.gz"

mysqldump \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USERNAME" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  --events \
  "$DB_DATABASE" | gzip -9 > "$DB_DUMP"
```

## 5) Backup de archivos Cloud locales
Si el despliegue usa almacenamiento local:

```bash
DATE_UTC="$(date -u +%Y%m%dT%H%M%SZ)"
CLOUD_SRC="/var/www/ecosistema-core-admin/storage/app/cloud"
CLOUD_TAR="$BACKUP_DIR/cloud_${DATE_UTC}.tar.gz"

# Opción A: tar
mkdir -p "$BACKUP_DIR"
tar -czf "$CLOUD_TAR" -C "$(dirname "$CLOUD_SRC")" "$(basename "$CLOUD_SRC")"

# Opción B: rsync a directorio versionado
rsync -a --delete "$CLOUD_SRC/" "$BACKUP_DIR/cloud_snapshot_${DATE_UTC}/"
```

## 6) Backup de .env (fuera del repo)
```bash
DATE_UTC="$(date -u +%Y%m%dT%H%M%SZ)"
ENV_BACKUP="$BACKUP_DIR/env_${DATE_UTC}.secure"

install -m 600 "$APP_ENV_FILE" "$ENV_BACKUP"
```

## 7) Backup de logs (si aplica)
```bash
DATE_UTC="$(date -u +%Y%m%dT%H%M%SZ)"
LOG_SRC="/var/www/ecosistema-core-admin/storage/logs"
LOG_TAR="$BACKUP_DIR/logs_${DATE_UTC}.tar.gz"

if [ -d "$LOG_SRC" ]; then
  tar -czf "$LOG_TAR" -C "$(dirname "$LOG_SRC")" "$(basename "$LOG_SRC")"
fi
```

## 8) Validación de integridad
- Verificar existencia y tamaño de artefactos.
- Calcular checksum y guardar en archivo separado.

```bash
cd "$BACKUP_DIR"
sha256sum "db_${DB_DATABASE}_${DATE_UTC}.sql.gz" > "db_${DB_DATABASE}_${DATE_UTC}.sha256"
sha256sum "cloud_${DATE_UTC}.tar.gz" > "cloud_${DATE_UTC}.sha256"
```

## 9) Checklist previo a restore (ambiente de prueba)
- [ ] Confirmar que el restore será en ambiente **separado** de producción.
- [ ] Confirmar ventana y responsables.
- [ ] Confirmar versión de PHP/app compatible.
- [ ] Confirmar disponibilidad de dump y checksum.
- [ ] Confirmar respaldo del entorno de prueba antes de sobrescribir.
- [ ] Confirmar `.env` seguro del ambiente de prueba.
- [ ] Confirmar que nadie ejecutará restore directo en producción.

## 10) Restore en ambiente de prueba (ejemplo seguro)
> No ejecutar en producción. Validar primero en entorno aislado.

```bash
# Preparar base destino (ya creada para pruebas)
gunzip -c "$BACKUP_DIR/db_${DB_DATABASE}_${DATE_UTC}.sql.gz" | \
mysql \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USERNAME" \
  "$DB_DATABASE"
```

Restore de archivos Cloud locales:
```bash
CLOUD_DEST="/var/www/ecosistema-core-admin/storage/app/cloud"
mkdir -p "$CLOUD_DEST"
tar -xzf "$BACKUP_DIR/cloud_${DATE_UTC}.tar.gz" -C "/var/www/ecosistema-core-admin/storage/app"
```

## 11) Checklist posterior a restore (pruebas)
- [ ] Login operativo (`/login`).
- [ ] Dashboard redirige correctamente con/sin sesión.
- [ ] `health/db` responde sin exponer secretos.
- [ ] Cloud local puede listar/usar rutas esperadas.
- [ ] Mail básico (preview/controlado) sigue operativo.
- [ ] Logs sin errores fatales nuevos.
- [ ] Validación manual de datos críticos.

## 12) Riesgos y mitigaciones
- Riesgo: dump incompleto por bloqueo/transacción larga.
  - Mitigar con `--single-transaction`, monitoreo y verificación de tamaño/checksum.
- Riesgo: fuga de secretos por manejo inseguro de `.env`.
  - Mitigar con permisos `600`, almacenamiento cifrado y acceso restringido.
- Riesgo: restore accidental en producción.
  - Mitigar con checklist obligatorio, doble validación humana y ambiente aislado.
- Riesgo: corrupción de artefactos de backup.
  - Mitigar con checksum y prueba periódica de restore en staging.

## 13) Qué NO hacer en producción
- No guardar backups dentro del repositorio Git.
- No commitear `.env`, dumps SQL ni archivos con datos reales.
- No usar usuario root de DB como cuenta principal de backup.
- No ejecutar restore directo sin prueba previa en ambiente separado.
- No compartir artefactos de backup por canales inseguros.
- No activar cron de backups sin diseño/validación operativa adicional.

## 14) Estado actual del proyecto (PR #39)
- Este PR **solo documenta** procedimiento y agrega checks no destructivos.
- No se ejecutaron `mysqldump` ni `mysql` restore reales desde scripts PHP.
- No hay modificación de DB por estos cambios.
