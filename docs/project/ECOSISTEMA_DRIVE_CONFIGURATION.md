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
