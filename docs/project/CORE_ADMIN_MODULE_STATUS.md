# Core Admin — Estado por módulo (operativo real)

Fuente rápida para auditoría/demo. Esta tabla separa claramente operación estable, read-only, dry-run, controlled por flags y estado documental/roadmap.

| Módulo | Estado principal | Nota operativa real |
|---|---|---|
| Core/Auth | Operativo base | Login/logout/sesión/dashboard funcionales. |
| Tenants/Users/Roles/Permissions | Operativo base | Núcleo administrativo funcional. |
| Cloud/Drive | Read-only + Controlled por flags | Superficies de consulta disponibles; remoto real/AWS sujeto a flags. |
| URL Locator | Read-only / Dry-run / Controlled | Simulación disponible; operación de escritura/redirección real condicionada. |
| Landing Pages | Read-only / Dry-run / Controlled | Rutas de prueba y simulación; publicación real condicionada. |
| Browser Analytics | Read-only / Controlled | Consulta/diagnóstico predominante; tracking efectivo según flags/políticas. |
| CRM/Campaigns | Read-only / Dry-run / Controlled | Partes visibles para consulta/simulación; ejecución real condicionada. |
| Mail/Notifications | Controlled por flags | Sin SMTP real por defecto; envío efectivo requiere habilitación explícita. |
| Workflow | Read-only / Dry-run / Controlled | Vistas de runs y simulación; ejecución real en flag. |
| Reports | Read-only / Dry-run / Controlled | Reportería de diagnóstico y exportaciones de prueba; productivo condicionado. |
| Security/Audit | Operativo base | Health/logs/audit disponibles para operación administrativa. |
| AI | Dry-run / Controlled por flags | Integración externa y escrituras desactivadas por defecto. |
| Billing | Documental / roadmap | No declarar completo/productivo en este repositorio. |
| Integrations | Documental / parcial | No asumir operación productiva end-to-end. |
| Support | Documental / parcial | Sin promesa de completitud operativa. |
| Jobs/Workers | Documental / roadmap | No hay workers productivos completos activos. |
| Privacy/Compliance | Documental + controles parciales | Lineamientos presentes; hardening y activación dependen del entorno. |

## Limitaciones vigentes (obligatorias)
- No SMTP real por defecto.
- No AWS/S3 real por defecto.
- No workers productivos completos.
- Flags avanzados en `false` por defecto.
- No datos reales ni secretos en repositorio/entornos de demo.

## Referencias
- Matriz extendida: `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`.
- Estado workers/cron: `docs/ops/WORKERS_CRON_CURRENT_STATE.md`.
- Seguridad y flags: `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`.
- Defaults seguros: `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`.
