# Ecosistema Platform Cockpit

## Alcance
- Rutas read-only: `GET /platform` y `GET /platform/cockpit`.
- Cockpit para tenant autenticado actual (sesión), sin aceptar `tenant_id` por request.
- Sin escrituras a BD: sólo `SELECT` sobre `core_modules`, `core_feature_flags`, `core_tenant_feature_flags`, `core_roles`, `core_users`, `system_health_check_definitions`, `system_health_check_runs`, `system_jobs`.

## Seguridad
- No se exponen `rules_json`, `details_json`, `metadata_json`, `payload_json`, `error_message`, `password_hash` ni SQL interno de checks.
- Se muestran únicamente resúmenes operativos y enlaces internos.

## Componentes
- `app/Core/Platform/EcosistemaPlatformCockpitRepository.php`
- `app/Core/Platform/EcosistemaPlatformCockpitService.php`
- `app/Core/Platform/EcosistemaPlatformAdapter.php`
- `resources/views/pages/platform/cockpit.php`
