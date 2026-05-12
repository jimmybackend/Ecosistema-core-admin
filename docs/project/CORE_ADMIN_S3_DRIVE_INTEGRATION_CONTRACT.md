# Contrato de Integración Core Admin ↔ S3 Drive (PR #42)

## 1) Propósito
Definir el **contrato documental** para una futura integración entre:
- `jimmybackend/Ecosistema-core-admin` (administración interna).
- `jimmybackend/s3` (ArcadeCloud Drive sobre Amazon S3).

Este PR no implementa integración técnica real; fija límites, responsabilidades y reglas de seguridad para evitar acoplamientos inseguros o suposiciones no verificadas.

## 1.1) Inventario técnico relacionado (PR #43)
- Documento de inventario técnico/documental: `docs/project/S3_DRIVE_TECHNICAL_INVENTORY.md`.
- Mapeo DB Cloud/S3 (tablas y límites): `docs/project/CLOUD_S3_DATABASE_MAPPING.md`.
- Este inventario amplía el descubrimiento y riesgos sin alterar el contrato funcional ni activar integración real.

## 2) Separación de repositorios

### Ecosistema-core-admin
Responsable de operación administrativa y flujos internos ya activos (Auth, Roles, Permissions, Mail/Cloud/Onboarding en modo controlado), además de integración controlada por contrato con Cloud/S3 Drive.

### s3 / ArcadeCloud Drive
Componente separado orientado a capacidades Drive sobre S3 (según README del repo s3: S3, Rekognition, Polly, Translate, KMS/Encrypt y ZIP), con su propio ciclo de vida, configuración y riesgos operativos. Es responsable operativo/funcional de la capa Drive sobre la estructura `cloud_*` canónica.

### Ecosistema-bd
Repositorio separado para dumps SQL y **fuente canónica de estructura**. La referencia operativa real se mantiene sobre la base `adbbmis1_eco`. No se asume que los dumps existan dentro de Core Admin ni dentro de s3.


## 2.1) Propiedad canónica de estructura Cloud/S3 (PR #45)
- `Ecosistema-bd` es la **fuente canónica de estructura** para `adbbmis1_eco`.
- Las tablas `cloud_*` son estructura funcional del sistema `s3` / ArcadeCloud Drive dentro de esa base real.
- Core Admin se integra por contrato; no debe crear estructura paralela, redefinir esquema ni asumir columnas fuera de referencia canónica.
- Este repositorio no modifica `Ecosistema-bd`; cualquier cambio estructural se gestiona en PR separado y explícito en `jimmybackend/Ecosistema-bd`.

## 3) Responsabilidades de Core Admin
- Mantener Cloud en modo local/controlado en la fase actual.
- Exponer únicamente puntos de integración futuros definidos por contrato (no por acceso interno a código del repo s3).
- Respetar aislamiento por tenant/usuario en metadatos cloud ya existentes.
- Mantener configuración segura por flags (`CLOUD_S3_ENABLED=false` por defecto).
- No activar AWS real/S3 real sin PR explícito de habilitación.

## 4) Responsabilidades de S3 Drive
- Operar como componente independiente, sin depender de secretos ni rutas internas de Core Admin.
- Definir interfaces estables de intercambio (contrato de datos y errores) para consumo por Core Admin.
- Gestionar su propia seguridad de acceso, cifrado y observabilidad técnica.
- No asumir acceso directo a storage local de Core Admin.

## 5) Tablas DB de referencia (solo si existen en esquema real)
Este contrato puede referenciar, sin crear ni alterar estructura:
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

> Importante: la validación definitiva de columnas/tipos debe hacerse contra esquema real de `adbbmis1_eco` (y/o dumps en `Ecosistema-bd` cuando estén disponibles).

## 6) Contrato de datos esperado (alto nivel, no vinculante de implementación)
Para futuras fases, cualquier integración debe manejar al menos:
- Identidad de archivo y jerarquía lógica (archivo/carpeta/root).
- Contexto de seguridad (`tenant_id`, `user_id` cuando aplique).
- Estado del archivo (ej. activo, pendiente, error) sin exponer secretos.
- Metadatos de tamaño/tipo/origen de forma auditable.
- Trazabilidad de eventos de acceso/reparación/versionado cuando aplique.

Este PR **no** define campos nuevos ni crea esquema; sólo delimita categorías de información para contrato futuro.

## 7) Variables de entorno esperadas (sin valores reales)
Referencia en Core Admin:
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

Reglas:
- Nunca commitear valores reales de llaves/tokens.
- Mantener `CLOUD_S3_ENABLED=false` hasta PR explícito de activación controlada.

## 8) Reglas de seguridad del contrato
- No compartir secretos entre repos (`core-admin` y `s3`).
- No duplicar lógica insegura ni bypass de autorización.
- No exponer storage local ni rutas internas sensibles.
- No exponer llaves completas de AWS en logs, vistas o auditoría.
- No habilitar llamadas AWS reales sin revisión y aprobación explícita por PR.

## 9) Permitido en fases futuras
- Definir interfaz de integración formal (API interna/adapter/cola), documentada y versionada.
- Futuras APIs/workers deberán consumir este contrato y la estructura canónica vigente, sin inventar esquema paralelo.
- Implementar observabilidad mínima de integración (logs técnicos sin secretos).
- Incorporar pruebas de contrato y validaciones de errores controlados.
- Habilitar S3 por entornos de forma gradual y reversible.

## 10) No implementado todavía (alcance excluido)
- Conexión S3 real.
- Subida/descarga real de archivos hacia AWS.
- Signed URLs.
- Workers/colas/cron de integración real.
- Cambios de DB (migraciones, seeds, campos/tablas nuevas).
- Modificaciones al repo `jimmybackend/s3` desde este PR.

## 11) Riesgos actuales
- Riesgo de acoplamiento entre repos si no se mantiene contrato explícito.
- Riesgo de exposición de secretos si se habilita AWS sin hardening previo.
- Riesgo de inconsistencias de metadatos si no se valida esquema real antes de implementar.
- Riesgo operativo por activar integraciones externas sin monitoreo/alertas previas.

## 12) Próximos PRs sugeridos
1. **PR de descubrimiento técnico**: matriz de endpoints/acciones de integración entre Core Admin y s3 (sin activar AWS).
2. **PR de contrato versionado**: especificación formal de payloads/respuestas/errores.
3. **PR de hardening**: máscara de secretos, auditoría técnica y políticas de rotación.
4. **PR de implementación controlada por feature flags**: entorno de staging con rollback claro.
5. **PR de validación operativa**: smoke ampliado, pruebas de contrato y checklist de salida.

---
Documento de alcance documental. No introduce cambios funcionales, no modifica base de datos y no activa AWS/S3 real.
