# Ecosistema Drive Dry-Run Adapter (PR #48)

## Qué hace
- Define un adaptador `App\Core\Cloud\EcosistemaDriveAdapter` orientado a contrato (`contract-only`).
- Expone estado seguro (`getStatus`) y capacidades (`getCapabilities`) para la integración futura.
- Permite visualización administrativa en Core Admin sin depender de infraestructura externa.

## Qué NO hace
- No conecta a AWS ni a S3 real.
- No realiza llamadas HTTP externas.
- No genera signed URLs.
- No sube ni descarga archivos remotos.
- No ejecuta SQL ni requiere conexión a base de datos para operar el adaptador.
- No modifica archivos de configuración ni `.env`.

## Por qué no conecta a AWS
Este PR está diseñado como etapa de contrato y seguridad operacional. El objetivo es validar estructura, permisos, ruta administrativa y exposición de estado sin riesgo operativo ni fuga de secretos.

## Por qué no llama al repo `s3`
`jimmybackend/s3` se mantiene como referencia técnica/funcional. En esta etapa no existe acoplamiento directo entre repositorios ni integración runtime.

## Uso en futuros PRs
En PRs siguientes se podrá:
1. Implementar un gateway real para operaciones remotas controladas.
2. Habilitar capacidades de forma progresiva mediante flags de configuración.
3. Añadir validaciones de integridad y auditoría asociadas al flujo cloud.

## Validaciones previstas después
- Verificaciones de conectividad explícita por ambiente.
- Manejo de errores de red y timeouts controlados.
- Validación de generación/expiración de URLs firmadas en entorno habilitado.
- Controles de permisos y auditoría por operación remota.

## Estado de `mailit-click`
`jimmybackend/mailit-click` queda para una etapa posterior (URLs cortas, tracking y multilenguaje) y no participa en este PR.

## Listado read-only de archivos (PR #49)
- Se agrega listado administrativo `GET /cloud/drive/files`.
- Lee únicamente metadata desde la tabla real `cloud_files` (aislada por `tenant_id` y `user_id`).
- No muestra `s3_key` completa, bucket ni rutas internas.
- No activa AWS/S3, no llama HTTP externo y no genera signed URLs.
- No sube/descarga archivos remotos.

- Se agrega listado administrativo `GET /cloud/drive/folders` (metadata read-only de `cloud_folders`, sin AWS/S3 real ni exposición de `prefix` o rutas internas).
- Se agrega detalle administrativo `GET /cloud/drive/folders/{id}` (metadata read-only por carpeta, con aislamiento por tenant/usuario, sin exponer `prefix`, rutas internas o secretos).
- Se mantiene `remote_calls=false`, `signed_urls=false`, `remote_uploads=false`, `remote_downloads=false` y `aws_connection=false`.

## Navegador Drive (dry-run)
- Se agregó navegación read-only de carpetas y archivos por metadata DB (`cloud_folders`, `cloud_files`).
- No hay llamadas a bucket real ni generación de signed URLs.
- Se mantiene bloqueo de AWS/S3, uploads, downloads y operaciones mutables.

- Capability agregada: `read_user_root=true` para habilitar resumen read-only desde `cloud_user_roots`.
- Se mantiene dry-run: sin llamadas AWS/S3, sin signed URLs, sin uploads/downloads remotos y sin cambios de base de datos.


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


## Capacidad adicional

- `read_access_policy`: habilitada para exponer política interna de acceso read-only sin AWS/S3 real.
- El adapter declara capability `read_only_audit=true` para trazabilidad administrativa read-only en `core_audit`.


- Referencia de contrato de descarga futura: `docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md`.


- Capability adicional: `safe_s3_key_validation=true` para validar forma de key sin exponer su valor.

- Referencia: `docs/project/ECOSISTEMA_DRIVE_SIGNED_URL_DRY_RUN.md`.
\n- AWS/S3 config preparada y apagada: ver docs/project/ECOSISTEMA_DRIVE_AWS_S3_CONFIG.md
