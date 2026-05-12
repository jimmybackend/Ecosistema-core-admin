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
