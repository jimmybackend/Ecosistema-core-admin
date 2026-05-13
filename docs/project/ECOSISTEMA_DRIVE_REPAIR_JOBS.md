# Ecosistema Drive — Jobs de reparación (read-only)

## Alcance
Esta vista administrativa usa tablas canónicas de `adbbmis1_eco` para observabilidad:
- `cloud_repair_jobs`
- `cloud_repair_logs`
- `cloud_buckets`

## Qué muestra
- Listado reciente de jobs por tenant.
- Resumen por `status`.
- Detalle de job y logs asociados.
- Indicadores seguros (`prefix_present`, `old_s3_key_present`, `new_s3_key_present`).

## Qué oculta
- `prefix` crudo.
- `old_s3_key` / `new_s3_key` crudos.
- `detail` completo potencialmente sensible.
- Cualquier secreto (`.env`, AWS keys, tokens, credenciales).

## Garantías de seguridad
- Modo **read-only** visual.
- No ejecuta reparación real.
- No crea ni actualiza jobs/logs.
- No modifica `cloud_files`.
- No conecta AWS/S3.
- No realiza escaneo ni comparación contra S3 real.

## Próximos pasos sugeridos
1. Agregar dry-run controlado de reparación con bandera explícita y auditoría.
2. Definir contrato de ejecución real bajo permiso admin dedicado.
3. Incorporar validaciones adicionales por bucket/prefix sin exponer rutas internas.
