# Mapeo DB Cloud/S3 para Integración Core Admin ↔ S3 Drive (PR #44)

## 1) Propósito
Este documento mapea **tablas reales de referencia Cloud/S3** de `adbbmis1_eco` hacia la futura integración `Core Admin ↔ S3 Drive`, sin cambios funcionales, sin activar AWS/S3 real y sin cambios de esquema.

Base de validación usada en este PR:
- Código actual de Core Admin (consultas SQL existentes).
- Contrato e inventario documental vigentes.
- Sin modificar `jimmybackend/s3` ni `jimmybackend/Ecosistema-bd`.


## 1.1) Aclaración canónica de propiedad Cloud/S3 (PR #45)
- `jimmybackend/Ecosistema-bd` contiene la base real/canónica de referencia del ecosistema (`adbbmis1_eco`).
- Las tablas `cloud_*` pertenecen funcionalmente al sistema **Cloud/S3 Drive** (repo `jimmybackend/s3`, ArcadeCloud Drive) dentro de `adbbmis1_eco`.
- Core Admin consume/administra partes de esa estructura **solo por contrato**; no debe duplicarla ni redefinirla en este repositorio.
- Core Admin **no debe modificar** `Ecosistema-bd` desde este repo.
- Cualquier cambio estructural (`cloud_*`) debe hacerse en `jimmybackend/Ecosistema-bd` mediante PR separado y explícito.

## 2) Reglas críticas (obligatorias)
1. No duplicar tablas Cloud dentro de Core Admin.
2. No inventar columnas ni tipos fuera del esquema real.
3. No guardar secretos AWS en tablas Cloud (keys, tokens, secretos).
4. No exponer `s3_key` completa en UI ni logs funcionales.
5. No aceptar rutas/keys desde query string como fuente confiable.
6. Toda operación futura debe validar `tenant_id` y `user_id`.
7. Toda subida/descarga futura debe quedar auditada.
8. S3 Drive debe respetar contrato y datos reales de `adbbmis1_eco`.
9. No modificar estructura `cloud_*` desde Core Admin.

## 3) Tabla por tabla

> Estados posibles: **usado** / **pendiente** / **sólo referencia**.

### 3.1 `cloud_files`
- **Propósito**: metadatos de archivos cloud y vínculo con módulos origen (p. ej. Mail).
- **Columnas principales confirmadas (por consultas reales en Core Admin)**:
  - `id`, `tenant_id`, `user_id`
  - `original_name`, `stored_name`, `s3_key`
  - `mime_type`, `extension`, `size_bytes`
  - `checksum_sha256`, `etag`, `storage_class`
  - `origin_module`, `origin_table`, `origin_id`
  - `access_type`, `secure_hint`, `encrypted`, `encryption_key_ref`
  - `found_in_s3`, `virus_scan_status`, `status`
  - `uploaded_at`, `updated_at`, `deleted_at`
- **Relación con Core Admin**: tabla cloud principal ya utilizada en listados, detalle, upload local controlado, descarga controlada y adjuntos Mail.
- **Relación futura con S3 Drive**: fuente principal para sincronizar estado lógico vs estado real en S3, sin romper aislamiento tenant/usuario.
- **Riesgos**: exposición de `s3_key`, bypass de autorización cruzada entre tenants, inconsistencia de estado.
- **Qué NO asumir**: no asumir signed URLs, ni disponibilidad automática en S3 cuando `found_in_s3` cambie.
- **Estado actual**: **usado**.

### 3.2 `cloud_buckets`
- **Propósito**: catálogo lógico de buckets usados por raíces/carpetas/archivos cloud.
- **Columnas principales confirmadas**: no confirmadas directamente por consulta SQL en este repo para PR #44 (solo tabla referenciada por documentación/relaciones lógicas).
- **Relación con Core Admin**: referencial indirecta vía `bucket_id` en tablas activas.
- **Relación futura con S3 Drive**: mapear bucket lógico ↔ bucket físico S3 por entorno y políticas.
- **Riesgos**: asumir nombres/regiones sin validación del esquema real.
- **Qué NO asumir**: no asumir columna de credenciales AWS por bucket.
- **Estado actual**: **sólo referencia**.

### 3.3 `cloud_folders`
- **Propósito**: jerarquía lógica de carpetas cloud por tenant/usuario.
- **Columnas principales confirmadas**:
  - `id`, `tenant_id`, `user_id`
  - `bucket_id`, `root_id`, `parent_folder_id`
  - `name`, `prefix`
  - `folder_type`, `access_type`
  - `found_in_s3`, `is_system`, `is_deleted`
  - `created_at`, `updated_at`, `deleted_at`
- **Relación con Core Admin**: listado, búsqueda por usuario, creación y soft-delete controlado.
- **Relación futura con S3 Drive**: resolución de prefijos/rutas lógicas para navegación y sincronización.
- **Riesgos**: path traversal lógico, borrados cruzados por tenant, inconsistencias en árbol.
- **Qué NO asumir**: no asumir que `prefix` es path local del servidor.
- **Estado actual**: **usado**.

### 3.4 `cloud_user_roots`
- **Propósito**: raíz cloud por usuario/tenant (cuotas y uso).
- **Columnas principales confirmadas**:
  - `id`, `tenant_id`, `user_id`, `bucket_id`
  - `root_prefix`, `display_name`
  - `quota_bytes`, `used_bytes`, `file_count`
  - `status`
- **Relación con Core Admin**: consulta de raíces activas y detalle de raíz por usuario.
- **Relación futura con S3 Drive**: boundary de namespace por usuario para operaciones de Drive.
- **Riesgos**: desalinear cuotas lógicas vs uso real S3.
- **Qué NO asumir**: no asumir que cuota implica enforcement automático en S3.
- **Estado actual**: **usado**.

### 3.5 `cloud_file_shares`
- **Propósito**: compartir archivos (permisos/alcance) entre actores.
- **Columnas principales confirmadas**: no confirmadas por SQL en Core Admin para este PR.
- **Relación con Core Admin**: aún no consumida funcionalmente.
- **Relación futura con S3 Drive**: control de acceso compartido sin exponer keys internas.
- **Riesgos**: fuga de acceso por scopes ambiguos.
- **Qué NO asumir**: no asumir modelo público por defecto.
- **Estado actual**: **pendiente**.

### 3.6 `cloud_file_versions`
- **Propósito**: historial/versionado de archivos cloud.
- **Columnas principales confirmadas**: no confirmadas por SQL en Core Admin para este PR.
- **Relación con Core Admin**: sin consumo actual.
- **Relación futura con S3 Drive**: versionado lógico alineado con objeto/etag/version-id real.
- **Riesgos**: divergencia entre versión lógica y versión física.
- **Qué NO asumir**: no asumir versionado S3 habilitado en todos los buckets.
- **Estado actual**: **pendiente**.

### 3.7 `cloud_file_access_logs`
- **Propósito**: trazabilidad de accesos a archivos cloud.
- **Columnas principales confirmadas**: no confirmadas por SQL en Core Admin para este PR.
- **Relación con Core Admin**: auditoría hoy se registra en `core_audit`; esta tabla queda para integración especializada cloud.
- **Relación futura con S3 Drive**: registro de eventos de subida/descarga/lectura.
- **Riesgos**: auditoría incompleta si se omite correlación tenant/usuario.
- **Qué NO asumir**: no asumir que logs cloud reemplazan `core_audit`.
- **Estado actual**: **pendiente**.

### 3.8 `cloud_storage_usage_daily`
- **Propósito**: agregación diaria de consumo de storage.
- **Columnas principales confirmadas**: no confirmadas por SQL en Core Admin para este PR.
- **Relación con Core Admin**: no explotada funcionalmente hoy.
- **Relación futura con S3 Drive**: conciliación de capacidad/costos por tenant.
- **Riesgos**: métricas inconsistentes por timezone/corte diario.
- **Qué NO asumir**: no asumir job de cálculo ya activo en Core Admin.
- **Estado actual**: **sólo referencia**.

### 3.9 `cloud_repair_jobs`
- **Propósito**: trabajos de reparación/sincronización cloud.
- **Columnas principales confirmadas**: no confirmadas por SQL en Core Admin para este PR.
- **Relación con Core Admin**: sin workers activos de reparación en este repo.
- **Relación futura con S3 Drive**: orquestación de reconciliación de inconsistencias.
- **Riesgos**: ejecuciones sin idempotencia o sin límites por tenant.
- **Qué NO asumir**: no asumir cron/job real activo actualmente.
- **Estado actual**: **sólo referencia**.

### 3.10 `cloud_repair_logs`
- **Propósito**: bitácora técnica de jobs de reparación cloud.
- **Columnas principales confirmadas**: no confirmadas por SQL en Core Admin para este PR.
- **Relación con Core Admin**: sin consumo actual.
- **Relación futura con S3 Drive**: evidencia de ejecución, resultado y error-control.
- **Riesgos**: logging excesivo con datos sensibles.
- **Qué NO asumir**: no asumir presencia de secretos/redacción automática.
- **Estado actual**: **sólo referencia**.

## 4) Límites explícitos de este PR
- No activa AWS/S3 real.
- No crea endpoints nuevos.
- No crea migraciones, tablas, campos ni seeds.
- No modifica repos `jimmybackend/s3` ni `jimmybackend/Ecosistema-bd`.
- No modifica lógica funcional de Core Admin.
