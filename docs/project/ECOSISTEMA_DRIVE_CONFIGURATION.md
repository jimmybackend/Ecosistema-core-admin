# Configuración Segura de Ecosistema Drive (PR #46)

## 1) ¿Qué es Ecosistema Drive?
Ecosistema Drive es el servicio propio futuro del ecosistema para capacidades de archivos cloud bajo gobierno de Core Admin y del contrato operativo del proyecto.

## 2) Fuente canónica de estructura
- Las tablas `cloud_*` en `adbbmis1_eco` (repositorio `jimmybackend/Ecosistema-bd`) son la estructura canónica.
- Este repositorio no crea tablas/campos ni modifica esquema de base de datos para Drive en esta etapa.

## 3) Relación con `jimmybackend/s3`
- El repositorio `jimmybackend/s3` se usa sólo como referencia técnica/funcional.
- No existe dependencia directa de código entre repositorios en este PR.
- No se hacen llamadas al repo `s3` desde Core Admin.

## 4) Variables `ECOSISTEMA_DRIVE_*`
Variables agregadas en `.env.example`:
- `ECOSISTEMA_DRIVE_ENABLED=false`
- `ECOSISTEMA_DRIVE_MODE=contract`
- `ECOSISTEMA_DRIVE_REFERENCE_REPO=s3`
- `ECOSISTEMA_DRIVE_API_TIMEOUT=5`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false`
- `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=false`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=false`

## 5) Valores por defecto seguros
- Ecosistema Drive queda deshabilitado (`enabled=false`).
- El modo operativo es `contract`: preparación documental/técnica sin integración activa.
- Llamadas remotas bloqueadas.
- Signed URLs bloqueadas.
- Upload/Download remoto bloqueados.

## 6) Significado de `ECOSISTEMA_DRIVE_MODE=contract`
Este modo indica que Core Admin sólo mantiene contrato de integración y configuración segura; no ejecuta integración real con AWS/S3 ni con el repo `s3`.

## 7) Límites explícitos de esta etapa
- No hay llamadas reales a S3.
- No hay conexión AWS real.
- No hay generación de signed URLs.
- No hay subida remota de archivos.
- No hay descarga remota de archivos.
- No hay cambios de base de datos.

## 8) Mailit-click
La integración con mailit-click queda fuera de alcance y se mantiene para una etapa posterior.

## 9) Listado read-only de archivos Drive (PR #49)
- Ruta: `GET /cloud/drive/files` (protegida por sesión y permiso `cloud.view`).
- Fuente: metadata en `cloud_files` (sin lectura de bucket real).
- Seguridad: aislamiento por `tenant_id`/`user_id`, límite de resultados y sin exposición de `s3_key` completa.
- Operación: sin AWS/S3 real, sin signed URLs, sin uploads/downloads remotos, sin llamadas HTTP externas.
- Base de datos: sin migraciones, sin cambios de esquema, sin seeds.

## 10) Referencias externas
- `jimmybackend/s3` sigue como referencia técnica/funcional (no dependencia runtime).
- `jimmybackend/mailit-click` queda para etapa posterior (short URLs, tracking y multilenguaje).

## 11) Detalle read-only de archivo Drive (PR #50)
- Ruta: `GET /cloud/drive/files/{id}` (protegida por sesión y permiso `cloud.view`).
- Fuente: metadata de `cloud_files` filtrada por `tenant_id`, `user_id` e `id`, excluyendo `status = 'deleted'`.
- Seguridad: no expone `s3_key`, `stored_name`, hashes sensibles, secretos de cifrado ni `metadata_json` crudo.
- Operación: sin AWS/S3 real, sin signed URLs, sin preview/descarga/subida remota y sin llamadas HTTP externas.


## 7) Listado read-only de carpetas Drive (PR #51)
- Ruta: `GET /cloud/drive/folders` (protegida por sesión y permiso `cloud.view`).
- Fuente: metadata de `cloud_folders` filtrada por `tenant_id`, `user_id`, `is_deleted=0` y límite seguro.
- Seguridad: no expone `prefix`, `prefix_hash`, `password_hash`, `secure_hint` ni rutas internas.
- Operación: no lista S3 real, no activa AWS/S3 y no crea/edita/borra carpetas.

## 12) Detalle read-only de carpeta Drive (PR #52)
- Ruta: `GET /cloud/drive/folders/{id}` (protegida por sesión y permiso `cloud.view`).
- Fuente: metadata de `cloud_folders` filtrada por `tenant_id`, `user_id`, `id`, `is_deleted=0`.
- Seguridad: no expone `prefix`, `prefix_hash`, `password_hash`, `secure_hint`, rutas internas ni secretos.
- Operación: no habilita navegación de carpetas, no crea/edita/borra, no activa AWS/S3 real y no hace llamadas remotas.

## Navegación read-only de carpetas
- Ruta protegida: `GET /cloud/drive/browse` con `folder_id` opcional entero positivo.
- Solo lee metadata de `cloud_folders` y `cloud_files`.
- No expone `prefix`, `prefix_hash`, `s3_key`, `s3_key_hash` ni rutas internas.
- No permite crear/editar/borrar/subir/descargar ni activa AWS/S3 real.

## Vista read-only de raíz de usuario
- Ruta: `GET /cloud/drive/root` (sesión autenticada + permiso `cloud.view`).
- Fuente: metadata de `cloud_user_roots`.
- Campos visibles: `id`, `bucket_id`, `display_name`, `quota_bytes`, `used_bytes`, `file_count`, `status`, `created_at`, `updated_at`.
- Seguridad: no expone `root_prefix`, `root_prefix_hash`, rutas internas, secretos ni config cruda.
- Operación: no crea raíz automática, no edita raíz, no activa AWS/S3 y no modifica DB.


## Buckets Drive (read-only)
- Existe vista administrativa protegida en `/cloud/drive/buckets`.
- Lee metadata informativa desde `cloud_buckets` para el tenant autenticado.
- No lista buckets reales de AWS ni activa AWS/S3 real.
- No expone credenciales, tokens, `config_json`/`policy_json` crudos ni rutas internas.
- No crea, edita ni borra buckets (solo lectura).

## Resumen operativo Drive (read-only)
- Ruta protegida: `GET /cloud/drive/summary` (sesión autenticada + permiso `cloud.view`).
- Usa metadata segura de `cloud_files`, `cloud_folders`, `cloud_user_roots` y `cloud_buckets` para conteos/estado general.
- No expone `s3_key`, `prefix`, `root_prefix`, rutas internas ni JSON crudo sensible.
- No activa AWS/S3 real, no hace llamadas remotas y no modifica base de datos.


## Política de acceso read-only

Ver `docs/project/ECOSISTEMA_DRIVE_ACCESS_POLICY.md` para reglas de tenant/user y operaciones bloqueadas.
- Se incorpora auditoría read-only de visualización Drive sobre `core_audit` (eventos `drive.*.viewed/listed`) sin registrar keys/prefixes ni secretos.


- Referencia de contrato de descarga futura: `docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md`.


- Ruta informativa adicional: `GET /cloud/drive/files/{id}/s3-key-validation` (sin descarga real).

- Referencia: `docs/project/ECOSISTEMA_DRIVE_SIGNED_URL_DRY_RUN.md`.
\n- AWS/S3 config preparada y apagada: ver docs/project/ECOSISTEMA_DRIVE_AWS_S3_CONFIG.md
\n- Controlled download S3 backend route añadida: /cloud/drive/files/{id}/download (bloqueada por defecto).

## Upload dry-run
Variables usadas en previsualización segura: `CLOUD_MAX_UPLOAD_MB`, `CLOUD_ALLOWED_EXTENSIONS`, `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS`, `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS`, `ECOSISTEMA_DRIVE_AWS_ENABLED`.
