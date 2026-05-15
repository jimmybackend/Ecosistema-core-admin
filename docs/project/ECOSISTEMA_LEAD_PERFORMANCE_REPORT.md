# ECOSISTEMA LEAD PERFORMANCE REPORT (PR #139)

## Objetivo
Habilitar vista administrativa **read-only** de desempeño de leads en `/reports/lead-performance`, con agregados por fuente, campaña, status, score/temperature y conversiones.

## Fuente canónica
- Base canónica: `adbbmis1_eco`.
- Tablas usadas: `crm_leads`.
- No se crean ni alteran tablas, campos, migraciones o seeds.

## Seguridad y alcance
- Tenant aplicado desde sesión autenticada (`auth_tenant_id`).
- No se acepta `tenant_id` desde request.
- Consultas sólo con `PDO::prepare` + parámetros enlazados.
- No se muestran PII de leads (email, teléfono, payloads, etc.).
- Reporte sin exportaciones y sin escrituras en base de datos.

## Implementación
- Repository: `app/Core/Reports/EcosistemaLeadPerformanceReportRepository.php`
- Service: `app/Core/Reports/EcosistemaLeadPerformanceReportService.php`
- Ruta: `GET /reports/lead-performance`
- Vista: `resources/views/pages/reports/lead-performance.php`

## Notas
- En caso de dataset vacío, la vista responde con estado seguro (`Sin datos para los filtros seleccionados`).
- Permiso usado: `campaigns.view` (permiso existente), sin creación de nuevos permisos.
