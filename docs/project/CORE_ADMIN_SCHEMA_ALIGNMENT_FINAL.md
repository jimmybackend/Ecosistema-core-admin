# CORE_ADMIN_SCHEMA_ALIGNMENT_FINAL

Fecha: 2026-05-18  
Repositorio: `jimmybackend/Ecosistema-core-admin`  
Fuente de verdad declarada: `adbbmis1_eco.sql` (`schema_contracts/`)

## Resultado ejecutivo
- Estado: **Go con advertencias**.
- Sin hallazgos críticos nuevos en los artefactos auditados para gate final (`scripts/schema-usage-check.php`, `composer.json`, `docs/schema-usage/`, `README.md`).
- Se habilitó comando dedicado `composer schema:usage` como gate final de compatibilidad read-only.

## Advertencias
- En este árbol no está versionado `schema_contracts/` ni `adbbmis1_eco.sql`; la validación se apoya en el chequeo existente de columnas críticas y en la documentación de auditorías previas por módulo.

## Evidencia
- Script gate: `scripts/schema-usage-check.php` (wrapper estable del check de compatibilidad).
- Composer script: `schema:usage` en `composer.json`.
- Checklist PR #237 en `docs/schema-usage/checklists/`.

- PR #238: se corrigió el set crítico de `scripts/schema-compatibility-check.php` para usar columnas reales en `core_audit` (`entity_table`) y `cloud_folders` (sin `status`, usando columnas reales de contrato).
