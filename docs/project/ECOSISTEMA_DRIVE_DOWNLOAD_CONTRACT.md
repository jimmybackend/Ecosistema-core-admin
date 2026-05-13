# ECOSISTEMA DRIVE DOWNLOAD CONTRACT

## Propósito
Definir el contrato documental y técnico para una **descarga controlada futura** de archivos Drive sin implementar descarga real en este PR.

## Estado de este PR
- Modo: `read-only`, `contract`, `dry-run`.
- Sin AWS/S3 real.
- Sin signed URLs.
- Sin lectura de storage.
- Sin cambios en base de datos.

## Flujo esperado (futuro)
1. Solicitud autenticada del usuario en Core Admin.
2. Validación de permisos (`cloud.view`).
3. Validación de alcance de tenant/usuario sobre `cloud_files`.
4. Validación de estado seguro del archivo.
5. Registro de auditoría del intento.
6. Resolución del mecanismo de entrega (local o S3) según configuración permitida.

## Validaciones requeridas antes de descargar
- Sesión autenticada.
- Permiso `cloud.view`.
- `tenant_id` coincide.
- `user_id` coincide cuando aplique.
- Archivo existe.
- Archivo no eliminado.
- Si existe `virus_scan_status`, no debe estar en estado inseguro.
- No aceptar `s3_key` por query string.
- No aceptar `stored_name` por query string.
- No aceptar paths internos por query string.
- No exponer bucket/key real.
- Bloquear descarga si configuración lo deshabilita.
- Auditar intento de descarga.

## Permisos requeridos
- `cloud.view` como permiso mínimo para iniciar flujo de descarga controlada.

## Auditoría esperada
- `drive.download.attempted`
- `drive.download.allowed` (futuro)
- `drive.download.denied` (futuro)

## Campos permitidos de cloud_files (solo referencia de negocio)
- `id`
- `tenant_id`
- `user_id` (cuando aplique)
- `original_name`
- `mime_type`
- `size_bytes`
- `created_at`
- `updated_at`
- `deleted_at`/estado equivalente si existe
- `virus_scan_status` si existe

## Campos prohibidos de exposición
- `s3_key`
- `s3_key_hash`
- `stored_name`
- `password_hash`
- `secure_hint`
- `encryption_key_ref`
- `etag`
- `checksum_sha256` (salvo política explícita posterior)
- `metadata_json` crudo
- rutas internas
- secretos

## Diferencias: descarga local vs descarga S3 futura
- Local: resolvería archivo interno controlado por backend sin exponer rutas.
- S3 futura: usaría resolución segura del objeto sin exponer bucket/key ni credenciales.
- En ambos casos: mismas validaciones de sesión, permisos, tenant/user y auditoría.

## Riesgos de seguridad
- IDOR por `file_id` sin validación tenant/user.
- Inyección de rutas internas via query string.
- Exposición de `s3_key`/`stored_name`.
- Descarga de archivos no escaneados o inseguros.
- Bypass de permisos por rutas no protegidas.

## Límites iniciales
- Sin botón de descarga funcional.
- Sin API pública de descarga.
- Sin preview/streaming.
- Sin signed URL.
- Sin AWS/S3 real.

## Próximos PRs sugeridos
1. Implementar endpoint de pre-validación con auditoría explícita.
2. Incorporar política de estado de malware/antivirus si `virus_scan_status` está disponible.
3. Implementar entrega controlada por modo local/S3, manteniendo secreto de infraestructura.
4. Añadir pruebas funcionales de autorización y auditoría.


- PR #64 añade validación segura de s3_key (dry-run) previa a signed URLs dry-run de PR #65.
