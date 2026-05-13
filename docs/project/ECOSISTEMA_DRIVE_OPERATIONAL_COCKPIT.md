# ECOSISTEMA DRIVE OPERATIONAL COCKPIT

## ¿Qué es?
Panel operativo central en `/cloud/drive` para administración segura de Ecosistema Drive. Consolida estado actual, capacidades y navegación sin crear funcionalidades nuevas.

## Rutas agrupadas
- `/cloud/drive`
- `/cloud/drive/files`, `/cloud/drive/folders`, `/cloud/drive/browse`, `/cloud/drive/root`, `/cloud/drive/buckets`
- `/cloud/drive/summary`, `/cloud/drive/access`, `/cloud/drive/download-contract`
- `/cloud/drive/aws-config`, `/cloud/drive/upload-dry-run`, `/cloud/drive/upload`
- `/cloud/drive/access-logs`, `/cloud/drive/storage-usage`, `/cloud/drive/repair-jobs`

## Capacidades por tipo
- **read-only:** metadata/archivos/carpetas/navegación/root/buckets/resumen/política/auditoría/versiones/logs/usage/jobs.
- **dry-run:** validación segura de s3_key, signed-url dry-run, upload dry-run.
- **controlled:** controlled_download y controlled_upload (bloqueadas por defecto por flags).
- **contract:** download_contract, share_contract, aws_s3_config_prepared.

## Operaciones bloqueadas
No delete/restore/repair/share públicos reales, no public links, no signed URLs reales, no escaneo/listado S3, no subida/descarga real si faltan flags.

## Qué no expone
No expone `s3_key`, `stored_name`, `prefix`, `root_prefix`, `old_s3_key`, `new_s3_key`, `config_json`, `metadata_json` crudos ni secretos de entorno.

## Validación en VM
1. `composer dump-autoload`
2. `composer smoke`
3. `sudo systemctl restart php8.5-fpm`
4. `sudo systemctl restart nginx`
5. Validar `/login`, `/dashboard`, `/cloud/drive`

## Próximos pasos antes de producción real
- Definir gobernanza de activación de flags remotos.
- Endurecer observabilidad y alertas sobre rutas controladas.
- Pruebas de seguridad y revisión de permisos por tenant/usuario.
