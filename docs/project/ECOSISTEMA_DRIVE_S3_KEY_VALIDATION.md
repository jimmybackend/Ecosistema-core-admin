# ECOSISTEMA DRIVE — S3 KEY VALIDATION (PR #64)

## Propósito
Agregar una validación segura y **dry-run** de `s3_key` sobre `cloud_files`, sin exponer la key real ni datos sensibles.

## Qué valida
- Aislamiento por `tenant_id` y `user_id`.
- `file_id` entero positivo.
- `bucket_id` presente.
- `status` (preferencia `active`).
- `deleted_at` vacío.
- `found_in_s3` solo como metadata de DB.
- Presencia de `s3_key`.
- Forma interna de key:
  - no `http://`, `https://`, `s3://`
  - no inicia con `/`
  - sin `..`
  - sin `\`
  - sin caracteres de control
  - máximo 1024 chars

## Qué NO valida todavía
- No descarga archivos.
- No genera signed URLs.
- No conecta AWS/S3.
- No verifica existencia real en S3.

## Seguridad
`s3_key` nunca se imprime ni se retorna en la vista. Tampoco se exponen `stored_name`, prefixes, rutas internas, `config_json`, secretos ni URLs internas.

## Relación con PR #63
Este documento extiende el contrato de `ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md` como paso previo a descarga controlada.

## Siguiente paso (PR #65)
Implementar contrato de signed URLs **dry-run** (sin AWS real).
