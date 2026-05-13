# Ecosistema Drive Storage Usage (PR #77)

Vista administrativa **read-only**: `/cloud/drive/storage-usage`.

## Tablas reales usadas (adbbmis1_eco)
- `cloud_files`: resumen actual de archivos/bytes por tenant, bucket, usuario y extensión.
- `cloud_buckets`: metadata segura (`name`, `provider`, `status`) para resumen por bucket.
- `core_users`: `email`/`display_name` para resumen por usuario.
- `cloud_storage_usage_daily`: histórico diario ya existente.

## Resumen actual vs histórico diario
- **Actual**: se calcula con `SELECT` sobre `cloud_files` al momento de consulta.
- **Histórico**: se lee desde `cloud_storage_usage_daily` cuando existan registros.

## Seguridad y límites
- No escribe snapshots ni recalcula históricos.
- No ejecuta `INSERT/UPDATE/DELETE` sobre `cloud_storage_usage_daily`.
- No escanea S3 ni storage local.
- No conecta AWS/S3.
- No expone `s3_key`, `stored_name`, `config_json`, `metadata_json` ni rutas internas.

## Validación manual
1. Entrar a `/cloud/drive/storage-usage` con usuario autenticado y permiso `cloud.view`.
2. Confirmar que muestra totales aunque no haya histórico.
3. Si `cloud_storage_usage_daily` está vacío, confirmar mensaje vacío sin error.
4. Confirmar ausencia de campos sensibles.
5. Confirmar modo read-only y sin llamadas AWS/S3.
