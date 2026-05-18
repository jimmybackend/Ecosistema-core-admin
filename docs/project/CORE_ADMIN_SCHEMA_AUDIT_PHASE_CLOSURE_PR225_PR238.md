# Cierre de fase — Auditoría de uso de esquema real (PR #225 a PR #238)

- **Fecha (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Fuente DB real canónica:** `adbbmis1_eco.sql`
- **Fase consolidada:** PR #225 → PR #238
- **Naturaleza del cierre:** documental (sin cambios de lógica productiva)

## 1) Alcance consolidado de la fase

Esta fase consolidó la auditoría técnica de Core Admin contra la base real `adbbmis1_eco.sql`, incluyendo contrato de esquema, correcciones por módulos y gate final de verificación (`composer schema:usage`) en modo **read-only**.

Incluye:
- PR base de contrato/herramienta (`#225`).
- Ajustes Core/Auth/RBAC y System/Onboarding/Platform (`#226`, `#227`).
- Auditorías por módulos y cobertura documental (`#228` a `#236`).
- Integración del gate final (`#237`).
- Corrección de columnas críticas del gate frente al dump real (`#238`).

No incluye:
- Declaratoria de producción SaaS pública.
- Cambios de esquema, migraciones o alteraciones del dump real.

## 2) Resumen por PR (GitHub #225–#238)

| PR | Tema / título resumido | Estado | Resultado | Archivos principales (evidencia) | Hallazgos relevantes | Advertencias aceptadas |
|---|---|---|---|---|---|---|
| #225 | Contrato de esquema real y herramienta base | Mergeado | Go con advertencias | `scripts/schema-compatibility-check.php`, `scripts/smoke-check.php`, checklist PR 225 | Ajuste de cobertura contractual para evitar falsos FAIL | Warnings documentales no bloqueantes en smoke |
| #226 | Core/Auth/RBAC contra `core_*` reales | Mergeado | Go con advertencias | `app/Core/System/AuditLogger.php`, `app/Core/System/AuditRepository.php`, `routes/web.php`, checklist PR 226 | Reemplazo de columnas no reales en `core_audit`; endurecimiento tenant de sesión | `schema:usage` aún no estaba definido en esa etapa |
| #227 | System/Onboarding/Platform contra tablas reales | Mergeado | Go con advertencias | `app/Core/System/HealthRepository.php`, `app/Core/System/LogRepository.php`, checklist PR 227 | Corrección de columnas no existentes en health/logs; refuerzo tenant | `schema:usage` no existente en esa etapa |
| #228 | Cloud/Drive contra tablas reales | Mergeado | Go con advertencias | checklist PR 228 | Cobertura del módulo cloud/documental según alcance | Warnings operativos controlados |
| #229 | Mail/Notifications contra tablas reales | Mergeado | Go con advertencias | checklist PR 229 | Alineación de uso SQL y trazabilidad documental | Warnings de entorno no críticos |
| #230 | URL Locator contra tablas reales | Mergeado | Go con advertencias | checklist PR 230 | Alineación de tablas URL y validaciones | Warnings de entorno no críticos |
| #231 | Landing Pages contra tablas reales | Mergeado | Go con advertencias | checklist PR 231 | Alineación de tablas landing y evidencias | Warnings de entorno no críticos |
| #232 | Browser Analytics contra `browser_analytics_*` reales | Mergeado | Go con advertencias | `scripts/smoke-check.php`, checklist PR 232 | Se amplió cobertura de inventario a 8 tablas reales | `schema:usage` no definido entonces |
| #233 | CRM/Campaigns contra `crm_*` reales | Mergeado | Go con advertencias | checklist PR 233 | Sin hallazgos críticos nuevos en alcance | Warnings informativos preexistentes |
| #234 | Workflow/Reports contra `workflow_*` y `reports_*` reales | Mergeado | Go con advertencias | repositorios workflow/reports, checklist PR 234 | `reports_exports.status` ajustado; eliminación de `workflow_runs.campaign_id` inexistente | `schema:usage` no definido entonces |
| #235 | Security/Privacy/IAM/Audit contra tablas reales | Mergeado | Go con advertencias | repositorios/security service, checklist PR 235 | `security_incidents.source_id` tipado compatible (`NULL` cuando aplica) | Cobertura IAM/Privacy parcial pasa a backlog |
| #236 | AI/VitaOS/Chat/Knowledge/Documents contra tablas reales | Mergeado | Go con advertencias | repositorios AI, vista AI, checklist PR 236 | `os_ai_proposals.proposal_id` obligatorio; `boot_id` string; columnas reales en `service_event_logs` | `schema:usage` no definido entonces |
| #237 | Gate final `composer schema:usage` + reporte alineación | Mergeado | Go con advertencias | `composer.json`, `scripts/schema-usage-check.php`, checklist PR 237 | Se formalizó gate de alineación código↔DB real | Sin DB en entorno local puede dar warning/skip controlado |
| #238 | Corrección de `schema:usage` contra dump real | Mergeado | Go con advertencias | `scripts/schema-compatibility-check.php`, checklist PR 238 | `core_audit.entity_type`→`entity_table`; ajuste de columnas reales en `cloud_folders` | Warning controlado si no hay DB local disponible |

## 3) Estado actual del gate y validaciones de cierre

### 3.1 Gate técnico
- `composer smoke`: **OK** en esta corrida de cierre, sin fallos críticos nuevos.
- `composer schema:usage`: **OK con advertencias controladas** (si entorno no tiene DB accesible, el comportamiento esperado es warning/skip read-only documentado).
- `scripts/schema-compatibility-check.php`: vigente y alineado al ajuste de PR #238.
- `scripts/schema-usage-check.php`: wrapper de gate activo en `composer.json`.

### 3.2 Confirmaciones de control
- El gate opera en modo **read-only** (lecturas/verificación de esquema, sin DDL/DML destructivo).
- **No** se cambió el esquema real de base de datos.
- **No** se crearon migraciones en este cierre.
- **No** se tocaron otros repositorios (`Ecosistema-presentacion`, `Ecosistema-bd`).

## 4) Conclusión formal de fase

Resultado consolidado de la fase PR #225–#238: **Go con advertencias**.

Conclusión operativa:
- Core Admin queda **listo para demo privada controlada**.
- Core Admin **NO** queda declarado listo para producción SaaS pública.

## 5) Pendientes reales para backlog

1. Versionar o proveer artefacto canónico trazable del contrato SQL (`adbbmis1_eco.sql` o derivado controlado) en pipeline de CI para comparación automática.
2. Fortalecer automatización de cobertura por columnas críticas para módulos con cobertura parcial histórica (especialmente IAM/Privacy/Compliance fuera de rutas activas).
3. Definir política de revalidación periódica de `$criticalColumns` contra dump canónico para prevenir deriva manual.
4. Mantener auditoría de documentación cruzada para evitar divergencias entre inventarios de `docs/project/` y chequeos en `scripts/`.

## 6) Próximos pasos sugeridos

1. Ejecutar gate en CI contra entorno con DB de verificación controlada (sin datos sensibles de clientes).
2. Registrar baseline de salida esperada de `composer smoke` y `composer schema:usage` para detectar regresiones.
3. Abrir épicas de hardening pre-producción (observabilidad, seguridad, tenancy, operación) separadas de esta fase documental.
4. Mantener criterio de despliegue: demo privada controlada sí, producción SaaS pública no, hasta cerrar backlog crítico.
