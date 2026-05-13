# Logs de acceso Drive (read-only)

Este módulo habilita la consulta administrativa **read-only** de eventos existentes en `cloud_file_access_logs` (base canónica `adbbmis1_eco`).

## Tabla real usada
- `cloud_file_access_logs`
- Join opcional para contexto:
  - `cloud_files` por `file_id` (solo `original_name`)
  - `core_users` por `user_id` (solo `email`)

## Columnas seguras mostradas
- `id`, `file_id`, `user_id`, `action`, `created_at`
- `country`, `region`, `city` (si existen)
- indicadores de presencia para IP, user-agent y metadata
- contexto opcional: `file_original_name`, `user_email`

## Campos bloqueados/no expuestos
- `s3_key`, `stored_name`, `config_json`
- `metadata_json` crudo
- IP completa
- user-agent completo (solo preview)
- secretos/tokens/credenciales

## Relación con core_audit
Las vistas registran auditoría read-only en `core_audit` usando el patrón existente (`EcosistemaDriveAuditLogger`) para eventos de visualización administrativa.

## Garantías de este PR
- No crea logs nuevos en `cloud_file_access_logs`.
- No ejecuta `INSERT/UPDATE/DELETE` sobre logs de acceso.
- No modifica schema, migraciones ni seeds.
- No habilita AWS/S3 real ni signed URLs reales.

## Checklist de seguridad
- [x] Rutas protegidas por sesión + `cloud.view`
- [x] Aislamiento por `tenant_id`
- [x] Validación de `file_id` positivo
- [x] PDO + prepared statements
- [x] Sanitización de salida HTML
- [x] Sin exposición de metadata sensible

## Próximos pasos futuros (si se habilita registro real)
1. Definir contrato explícito de escritura con permiso `cloud.manage`.
2. Auditar taxonomía de acciones y retención.
3. Incorporar anonimización avanzada de red/dispositivo.
4. Agregar controles operativos para ingestión y monitoreo.
