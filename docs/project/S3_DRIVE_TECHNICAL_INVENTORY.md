# Inventario Técnico S3 Drive (ArcadeCloud) para Core Admin (PR #43)

## 1) Propósito
Este documento consolida un inventario técnico/documental para preparar una futura integración entre `jimmybackend/Ecosistema-core-admin` y `jimmybackend/s3` (ArcadeCloud Drive), **sin implementar integración real** y **sin activar AWS/S3** en esta etapa.

Objetivos:
- Reducir riesgo de supuestos no verificados.
- Dejar trazabilidad de lo observado en el entorno actual.
- Definir qué falta confirmar antes de un PR de integración técnica.

## 2) Estado de acceso al repositorio `jimmybackend/s3`
Estado en este entorno Codex: **NO accesible**.

Validación realizada:
- No se encontró ruta local disponible para `jimmybackend/s3` en el filesystem del entorno actual.
- Por lo tanto, este inventario se basa en documentación de Core Admin y en el contrato de integración vigente (PR #42), sin inspección directa del código del repo `s3`.

Implicación:
- No se inventa estructura, endpoints ni clases internas del repo `s3`.
- Los puntos de integración quedan como hipótesis/pendientes de confirmación para el siguiente PR de descubrimiento técnico con acceso real al repo `s3`.

## 3) Resumen funcional conocido de ArcadeCloud Drive (fuente documental)
Con base en el contrato `CORE_ADMIN_S3_DRIVE_INTEGRATION_CONTRACT.md`, ArcadeCloud Drive se describe como componente separado con capacidades relacionadas a:
- Amazon S3
- Rekognition
- Polly
- Translate
- KMS/Encrypt
- ZIP

Al no existir acceso técnico al repo `s3` en este entorno, este resumen es **documental** y no constituye validación de implementación efectiva de cada capacidad.

## 4) Archivos/carpetas identificadas en `s3`
No disponibles en este entorno por falta de acceso al repositorio `jimmybackend/s3`.

Estado:
- Estructura de carpetas: pendiente por confirmar.
- Archivos PHP: pendiente por confirmar.
- Configuración (`.env.example`, config, bootstrap): pendiente por confirmar.
- Dependencias (`composer.json`/SDKs): pendiente por confirmar.

## 5) Funciones detectadas en `s3`
No detectables en este entorno (sin acceso al código del repo `s3`).

## 6) Posibles endpoints detectados
No detectables en este entorno (sin acceso a rutas/controladores del repo `s3`).

## 7) Servicios AWS y capacidades relacionadas (estado documental)
> Estado: **mencionados por contrato**, pendientes de verificación técnica directa en `s3`.

- **S3**: storage de objetos y operaciones de archivos.
- **Rekognition**: análisis/capacidades de visión (por confirmar casos de uso concretos).
- **Polly**: síntesis de voz (por confirmar flujos).
- **Translate**: traducción (por confirmar flujos).
- **KMS/Encrypt**: cifrado/gestión de claves o cifrado aplicativo (por confirmar estrategia real).
- **ZIP**: compresión o empaquetado (por confirmar endpoints o jobs asociados).

## 8) Referencias de tablas Cloud (solo documental, si existen en esquema real)
Las siguientes tablas pueden ser referenciadas por contrato y documentación previa, **sin crear ni alterar estructura**:
- `cloud_files`
- `cloud_buckets`
- `cloud_folders`
- `cloud_user_roots`
- `cloud_file_shares`
- `cloud_file_versions`
- `cloud_file_access_logs`
- `cloud_storage_usage_daily`
- `cloud_repair_jobs`
- `cloud_repair_logs`

Nota: validación final de columnas/tipos debe realizarse contra `adbbmis1_eco` y/o dumps oficiales en `jimmybackend/Ecosistema-bd` cuando estén disponibles en el entorno de trabajo correspondiente.

## 9) Riesgos de integración identificados
- Riesgo de acoplamiento entre repositorios si se omite contrato versionado.
- Riesgo de incompatibilidad de payloads/errores entre Core Admin y `s3`.
- Riesgo de exponer secretos si se habilita AWS sin hardening y mascarado de logs.
- Riesgo de divergencia respecto a esquema real (`adbbmis1_eco`) si se asumen campos no confirmados.
- Riesgo operativo al activar servicios externos (S3/Rekognition/Polly/Translate) sin observabilidad y rollback definidos.

## 10) Dependencias pendientes por confirmar
Pendiente de revisión técnica del repo `s3`:
- `composer.json` y librerías AWS SDK utilizadas.
- Versiones PHP requeridas.
- Módulos/extensiones PHP necesarias.
- Contratos de autenticación/autorización para llamadas entre repos.
- Convención de errores y códigos HTTP.
- Mecanismos de reintento, timeout y circuit breaking.

## 11) Variables de entorno esperadas (sin valores reales)
Con base en Core Admin/contrato actual:
- `CLOUD_DISK`
- `CLOUD_S3_ENABLED`
- `CLOUD_ALLOW_UPLOADS`
- `CLOUD_ALLOW_DOWNLOADS`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`
- `AWS_ENDPOINT`
- `AWS_USE_PATH_STYLE_ENDPOINT`

Regla vigente:
- Mantener `CLOUD_S3_ENABLED=false` hasta PR explícito de habilitación controlada.

## 12) Qué necesita Core Admin para integrarse en el futuro
- Definición de contrato técnico versionado (payloads, errores, headers, idempotencia).
- Matriz de endpoints/acciones del repo `s3` con semántica de permisos.
- Estrategia de autenticación inter-servicio (sin compartir secretos inseguros).
- Trazabilidad y observabilidad mínima (logs técnicos + auditoría sin secretos).
- Plan de feature flags por entorno (dev/staging/prod) con rollback.
- Validación contra DB real `adbbmis1_eco` para metadatos cloud.

## 13) Qué debe permanecer separado
- Código fuente y ciclo de release de `jimmybackend/s3`.
- Secretos/credenciales AWS por repositorio y entorno.
- Responsabilidad de operación interna de cada componente.
- Dumps SQL en `jimmybackend/Ecosistema-bd` como fuente separada.

## 14) Próximos PRs sugeridos
1. **PR de descubrimiento técnico con acceso a `s3`**: inventario real de estructura, endpoints, clases y dependencias.
2. **PR de contrato versionado**: especificación formal de request/response/errores Core Admin ↔ S3 Drive.
3. **PR de hardening de seguridad**: mascarado de secretos, política IAM mínima, validación de logging seguro.
4. **PR de integración controlada por flags**: sin activar productivo por defecto, con pruebas en staging.
5. **PR de validación operativa**: smoke ampliado, pruebas de contrato y checklist de salida.

## 15) Alcance y exclusiones de este PR
Incluye:
- Inventario documental y análisis de riesgos.

No incluye:
- Cambios funcionales.
- Cambios de base de datos.
- Integración real con `s3`.
- Activación AWS/S3 real.
- Modificaciones al repositorio `jimmybackend/s3`.
