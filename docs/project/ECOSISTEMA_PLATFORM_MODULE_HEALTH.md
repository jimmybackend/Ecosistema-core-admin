# Ecosistema Platform Module Health

## Alcance
- Rutas read-only: `GET /platform/health` y `GET /platform/health/modules/{code}`.
- Consulta salud operativa por módulo para el tenant autenticado actual (sesión).
- No acepta `tenant_id` por request.
- No ejecuta checks ni jobs; sólo lectura de:
  - `core_modules`
  - `system_health_check_definitions`
  - `system_health_check_runs`
  - `system_health_check_findings`
  - `system_jobs`
  - `system_workers`

## Seguridad
- No se exponen `check_sql`, `remediation_hint`, `details_json`, `metadata_json`, `payload_json`, `error_message`, `password_hash`.
- Vista muestra contadores y timestamps agregados únicamente.

## Componentes
- `app/Core/Platform/EcosistemaPlatformHealthRepository.php`
- `app/Core/Platform/EcosistemaPlatformHealthService.php`
- `resources/views/pages/platform/health.php`
- `resources/views/pages/platform/module-health.php`
