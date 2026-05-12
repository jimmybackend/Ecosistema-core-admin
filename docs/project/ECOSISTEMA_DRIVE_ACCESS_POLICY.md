# Política de acceso Drive (read-only)

Ecosistema Drive mantiene una política interna en modo **read-only / contract / dry-run**.

- Permiso de lectura: `cloud.view`.
- Permiso `cloud.manage`: reservado para administración futura.
- Frontera principal: `tenant_id` para todos los recursos (`cloud_files`, `cloud_folders`, `cloud_user_roots`, `cloud_buckets`).
- Frontera por usuario: `user_id` cuando el recurso pertenece a usuario (archivos, carpetas, raíz).
- Buckets: metadata visible sólo por `tenant_id`.
- `access_type` se mantiene como metadata segura; **no** habilita acceso público todavía.
- `cloud_file_shares` queda fuera de este PR y se evaluará después.
- Operaciones bloqueadas: uploads, downloads, signed URLs, edición y borrado.
- No hay AWS/S3 real ni llamadas remotas.
- No hay cambios de base de datos, migraciones ni seeds.
