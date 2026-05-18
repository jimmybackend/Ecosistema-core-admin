# Ecosistema Core Admin

Aplicación administrativa interna y base documental técnico-operativa del ecosistema.

> Este repositorio **no** es la landing pública/comercial.

## Documentación pública / presentación

La documentación pública, narrativa comercial y material de presentación viven en:

- [`jimmybackend/Ecosistema-presentacion`](https://github.com/jimmybackend/Ecosistema-presentacion)

Punteros y auditoría de separación core/presentación:

- [`docs/project/PRESENTATION_REPOSITORY_POINTERS.md`](docs/project/PRESENTATION_REPOSITORY_POINTERS.md)
- [`docs/project/CORE_ADMIN_PRESENTATION_DOCS_AUDIT.md`](docs/project/CORE_ADMIN_PRESENTATION_DOCS_AUDIT.md)
- [`docs/project/REPAIR_PRESENTATION_DOCS_FINAL_REPORT.md`](docs/project/REPAIR_PRESENTATION_DOCS_FINAL_REPORT.md)

> Nota técnica: estos documentos son de frontera Core/Presentación y se mantienen por trazabilidad operativa. Ver auditoría de enlaces: [`docs/project/CORE_ADMIN_DOC_LINK_AUDIT.md`](docs/project/CORE_ADMIN_DOC_LINK_AUDIT.md).

Guardrails documentales de contribución:

- [`docs/project/CORE_ADMIN_DOCS_BOUNDARIES.md`](docs/project/CORE_ADMIN_DOCS_BOUNDARIES.md)
- [`docs/project/CORE_ADMIN_CONTRIBUTING_NOTES.md`](docs/project/CORE_ADMIN_CONTRIBUTING_NOTES.md)

## Alcance de este repositorio (técnico-operativo)

Core Admin concentra:

- operación administrativa interna;
- rutas técnicas y validación de permisos;
- estado real por módulo (operativo/read-only/dry-run/controlled/roadmap);
- inventarios de tablas y contratos de integración;
- guías de despliegue en VM, smoke checks y seguridad.

## Instalación local

Referencia operativa de instalación y arranque:

- [`docs/deploy/CORE_ADMIN_VM_RUNBOOK.md`](docs/deploy/CORE_ADMIN_VM_RUNBOOK.md)

Comandos base documentados en runbook:

```bash
composer install
cp .env.example .env
```

## Variables de entorno

- Configuración de VM y reglas de `.env` real: [`docs/deploy/CORE_ADMIN_VM_RUNBOOK.md`](docs/deploy/CORE_ADMIN_VM_RUNBOOK.md)
- Defaults seguros y flags: [`docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`](docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md)
- Matriz de flags/permisos/seguridad: [`docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`](docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md)

## Rutas principales

- Mapa ruta → servicio → tabla: [`docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`](docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md)
- Matriz de vistas/rutas por módulo: [`docs/project/ECOSISTEMA_ROUTE_SERVICE_VIEW_MATRIX.md`](docs/project/ECOSISTEMA_ROUTE_SERVICE_VIEW_MATRIX.md)
- Contrato técnico de descarga controlada (Drive): [`docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md`](docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md)

## Tablas reales usadas

- Inventario y compatibilidad DB real: [`docs/project/ECOSISTEMA_DB_SCHEMA_COMPATIBILITY_REPORT.md`](docs/project/ECOSISTEMA_DB_SCHEMA_COMPATIBILITY_REPORT.md)
- Nombre canónico de base y lineamientos operativos: [`docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md`](docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md)

## Estado técnico por módulo

Fuente de estado operativo por módulo:

- [`docs/project/CORE_ADMIN_MODULE_STATUS.md`](docs/project/CORE_ADMIN_MODULE_STATUS.md)
- (Complementario) [`docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`](docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md)
- (Auditoría actual) [`docs/project/CORE_ADMIN_CURRENT_STATE_AUDIT.md`](docs/project/CORE_ADMIN_CURRENT_STATE_AUDIT.md)
- (Cierre post-PR #208) [`docs/project/CORE_ADMIN_POST_208_VERIFICATION_REPORT.md`](docs/project/CORE_ADMIN_POST_208_VERIFICATION_REPORT.md)
- (Cierre Go/No-Go demo privada, PR #224) [`docs/project/CORE_ADMIN_DEMO_GO_NO_GO.md`](docs/project/CORE_ADMIN_DEMO_GO_NO_GO.md)
- (Cierre fase auditoría schema PR #225–#238) [`docs/project/CORE_ADMIN_SCHEMA_AUDIT_PHASE_CLOSURE_PR225_PR238.md`](docs/project/CORE_ADMIN_SCHEMA_AUDIT_PHASE_CLOSURE_PR225_PR238.md)
- (Demo técnica Core Admin) [`docs/project/CORE_ADMIN_TECHNICAL_DEMO_GUIDE.md`](docs/project/CORE_ADMIN_TECHNICAL_DEMO_GUIDE.md)
- (Checklist demo privada controlada, PR #240) [`docs/demo/CORE_ADMIN_PRIVATE_DEMO_CHECKLIST.md`](docs/demo/CORE_ADMIN_PRIVATE_DEMO_CHECKLIST.md)
- (Runbook demo privada controlada, PR #240) [`docs/demo/CORE_ADMIN_PRIVATE_DEMO_RUNBOOK.md`](docs/demo/CORE_ADMIN_PRIVATE_DEMO_RUNBOOK.md)
- (Guion ejecutivo/técnico demo privada 10–15 min, PR #244) [`docs/demo/CORE_ADMIN_PRIVATE_DEMO_SCRIPT_10_15_MIN.md`](docs/demo/CORE_ADMIN_PRIVATE_DEMO_SCRIPT_10_15_MIN.md)
- (Checklist final día de demo privada, PR #245) [`docs/demo/CORE_ADMIN_PRIVATE_DEMO_DAY_CHECKLIST.md`](docs/demo/CORE_ADMIN_PRIVATE_DEMO_DAY_CHECKLIST.md)

Convenciones de estado usadas en Core Admin:

- **Operativo**: funcionalidad activa para uso administrativo.
- **Read-only**: consulta/visualización sin escritura.
- **Dry-run**: simulación sin efecto final.
- **Controlled**: escritura/ejecución limitada por flags, permisos y contexto.
- **Roadmap**: alcance aún no disponible en operación real.

## Flags

- Inventario de defaults seguros: [`docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`](docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md)
- Matriz técnica completa de flags y riesgos: [`docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`](docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md)

### Flags críticas apagadas por defecto

Estas flags deben permanecer en `false` por defecto en `.env.example` y en cualquier bootstrap de entorno hasta que exista una habilitación explícita con hardening:

- `MAIL_SEND_ENABLED`
- `MAIL_ALLOW_TEST_SEND`
- `CLOUD_S3_ENABLED`
- `CLOUD_ALLOW_UPLOADS`
- `CLOUD_ALLOW_DOWNLOADS`
- `ECOSISTEMA_DRIVE_AWS_ENABLED`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS`
- `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS`
- `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS`
- `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED`
- `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED`
- `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED`
- `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE`
- `ECOSISTEMA_AI_PROVIDER_ENABLED`
- `ECOSISTEMA_AI_WRITE_PROPOSALS`
- `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED`
- `ECOSISTEMA_REPORT_EXPORT_WRITE`

## Deploy / VM

- Runbook principal de despliegue en VM: [`docs/deploy/CORE_ADMIN_VM_RUNBOOK.md`](docs/deploy/CORE_ADMIN_VM_RUNBOOK.md)
- Verificación de readiness operativa: [`docs/ops/ECOSISTEMA_OPERATIONAL_READINESS_VERIFICATION.md`](docs/ops/ECOSISTEMA_OPERATIONAL_READINESS_VERIFICATION.md)

## Smoke checks

- Runbook unificado de verificación local: [`docs/project/CORE_ADMIN_LOCAL_VERIFICATION_RUNBOOK.md`](docs/project/CORE_ADMIN_LOCAL_VERIFICATION_RUNBOOK.md)
- Checklist QA/manual de entorno demo-operativo: [`docs/qa/ECOSISTEMA_MANUAL_QA_END_TO_END.md`](docs/qa/ECOSISTEMA_MANUAL_QA_END_TO_END.md)
- Comando operativo:

```bash
composer smoke
composer schema:usage
```

## Seguridad

- Hardening de producción: [`docs/security/ECOSISTEMA_PRODUCTION_HARDENING_CHECKLIST.md`](docs/security/ECOSISTEMA_PRODUCTION_HARDENING_CHECKLIST.md)
- Flags, permisos y superficie de riesgo: [`docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`](docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md)
- Auditoría de exposición privacidad/seguridad: [`docs/security/ECOSISTEMA_PRIVACY_SECURITY_EXPOSURE_AUDIT.md`](docs/security/ECOSISTEMA_PRIVACY_SECURITY_EXPOSURE_AUDIT.md)
