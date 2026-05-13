# ECOSISTEMA DRIVE SHARE CONTRACT

## Objetivo
Definir el contrato técnico/documental para compartir archivos de Drive en modo **read-only/contract** sin crear shares reales.

## Relación con PR #70
Después de exponer versiones read-only (PR #70), este contrato agrega la vista de referencia para compartir futuro sin habilitar operaciones reales.

## Modos futuros contemplados (no activos)
- internal_user
- internal_role
- tenant_link
- time_limited_public_link

## Validaciones requeridas
- Sesión autenticada y permiso `cloud.view`.
- `file_id` entero positivo.
- Aislamiento por `tenant_id` y `user_id`.
- Archivo visible/no eliminado.

## Entradas prohibidas
No se aceptan `email`, `user_id`, `role_id`, `token`, `expires_at` ni permisos de share en request.

## Datos nunca expuestos
No exponer `s3_key`, `s3_version_id` crudo, `stored_name`, `root_prefix`, `prefix`, `base_prefix`, `config_json` crudo, AWS keys, tokens, signed URLs, `.env`.

## Auditoría esperada
`action=drive.file.share_contract.viewed` con metadata segura read-only, sin secretos y con banderas de no-creación de share/token/link/email.

## Por qué no se crean tokens/enlaces
El módulo está en etapa de contrato: seguridad primero, sin DB writes, sin AWS/S3 real, sin emails y sin enlaces públicos.

## Próximos PRs (futuros)
1. Definir modelo de permisos de share interno.
2. Definir esquema de expiración y revocación.
3. Activar integración segura de token/link con controles de auditoría reforzados.
