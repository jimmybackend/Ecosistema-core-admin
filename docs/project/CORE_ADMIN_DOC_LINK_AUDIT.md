# CORE_ADMIN_DOC_LINK_AUDIT

Fecha de auditoría: 2026-05-17

## Alcance

Auditoría de enlaces internos de `README.md` (secciones operativas/documentales) para validar existencia, tipo documental y acción recomendada en Core Admin.

## Resultado general

- Enlaces verificados en README: **24**
- Enlaces inexistentes: **0**
- Documentos técnicos/operativos: **20**
- Documentos mixtos o de frontera Core/Presentación: **4**

## Matriz de enlaces auditados

| Enlace en README | ¿Existe? | Tipo | Acción recomendada |
|---|---|---|---|
| `docs/project/PRESENTATION_REPOSITORY_POINTERS.md` | Sí | Mixto | Mantener como puntero técnico de separación entre repositorios. |
| `docs/project/CORE_ADMIN_PRESENTATION_DOCS_AUDIT.md` | Sí | Mixto | Mantener temporalmente; registrar en pendiente de migración cuando exista cierre definitivo en repositorio de presentación. |
| `docs/project/REPAIR_PRESENTATION_DOCS_FINAL_REPORT.md` | Sí | Mixto | Mantener temporalmente como trazabilidad histórica; mover a presentación/archivo cuando se cierre ciclo de migración documental. |
| `docs/project/CORE_ADMIN_DOCS_BOUNDARIES.md` | Sí | Técnico | Mantener. |
| `docs/project/CORE_ADMIN_CONTRIBUTING_NOTES.md` | Sí | Técnico | Mantener. |
| `docs/deploy/CORE_ADMIN_VM_RUNBOOK.md` | Sí | Técnico | Mantener (referencia principal de instalación/VM). |
| `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md` | Sí | Técnico | Mantener. |
| `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md` | Sí | Técnico | Mantener. |
| `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md` | Sí | Técnico | Mantener. |
| `docs/project/ECOSISTEMA_ROUTE_SERVICE_VIEW_MATRIX.md` | Sí | Técnico | Mantener. |
| `docs/project/ECOSISTEMA_DB_SCHEMA_COMPATIBILITY_REPORT.md` | Sí | Técnico | Mantener. |
| `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md` | Sí | Técnico | Mantener. |
| `docs/project/CORE_ADMIN_MODULE_STATUS.md` | Sí | Técnico | Mantener. |
| `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md` | Sí | Técnico | Mantener. |
| `docs/project/CORE_ADMIN_CURRENT_STATE_AUDIT.md` | Sí | Técnico | Mantener. |
| `docs/ops/ECOSISTEMA_OPERATIONAL_READINESS_VERIFICATION.md` | Sí | Técnico | Mantener. |
| `docs/qa/ECOSISTEMA_MANUAL_QA_END_TO_END.md` | Sí | Técnico | Mantener. |
| `docs/security/ECOSISTEMA_PRODUCTION_HARDENING_CHECKLIST.md` | Sí | Técnico | Mantener. |
| `docs/security/ECOSISTEMA_PRIVACY_SECURITY_EXPOSURE_AUDIT.md` | Sí | Técnico | Mantener. |

## Pendiente de migración (comercial/documental de frontera)

Los siguientes documentos **no bloquean operación técnica** de Core Admin, pero contienen trazabilidad de separación con repositorio de presentación y deberían revisarse para migración/archivo en el entorno de presentación cuando corresponda:

1. `docs/project/PRESENTATION_REPOSITORY_POINTERS.md`
2. `docs/project/CORE_ADMIN_PRESENTATION_DOCS_AUDIT.md`
3. `docs/project/REPAIR_PRESENTATION_DOCS_FINAL_REPORT.md`

Criterio aplicado:
- Si el documento funciona como **control técnico de límites** entre repositorios, puede permanecer en Core Admin.
- Si pasa a ser puramente narrativo/comercial o histórico sin valor operativo actual, debería migrarse a presentación o a archivo documental fuera del core técnico.
