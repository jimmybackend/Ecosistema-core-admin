# PR 231 — Auditoría Landing Pages vs `adbbmis1_eco`

Fuente de verdad: `adbbmis1_eco.sql` (contrato de tablas landing_* provisto en el requerimiento).

## Resumen
- Se auditó `app/Core/Landing/`, `routes/web.php` y `resources/views/pages/landing/` para tablas `landing_*` objetivo.
- No se encontraron columnas inexistentes en SQL de repositories landing.
- Se corrigió exposición de `field_key` en vistas/DTOs (dato sensible).
- Se reforzó CSRF en `POST /l/{slug}/forms/{id}/submit`.

## Evidencia de hallazgos y acciones

| Archivo | Función/ruta | Tabla(s) | Hallazgo | Acción |
|---|---|---|---|---|
| `app/Core/Landing/EcosistemaLandingFormService.php` | `toFieldDto` | `landing_form_fields` | Se exponía `field_key` completo en DTO consumido por vista. | Se cambió a `field_key_preview` enmascarado y banderas `field_key_present/field_key_exposed=false`. |
| `app/Core/Landing/EcosistemaLandingSubmissionService.php` | `toValueDto` | `landing_form_submission_values` | Se exponía `field_key` completo en detalle de submission. | Se cambió a preview enmascarado y bandera `field_key_exposed=false`. |
| `resources/views/pages/landing/form-detail.php` | Vista detalle formulario | `landing_form_fields` | Renderizaba `field_key` completo. | Ahora renderiza `field_key_preview` con `exposed=false`. |
| `resources/views/pages/landing/submission-detail.php` | Vista detalle submission | `landing_form_submission_values` | Renderizaba `field_key` completo. | Ahora renderiza `field_key_preview` con `exposed=false`. |
| `routes/web.php` | `POST /l/{slug}/forms/{id}/submit` | `landing_form_submissions`, `landing_form_submission_values` | Ruta de escritura pública sin validación CSRF explícita. | Se agregó `ensureValidCsrfToken(...)` antes de ejecutar escritura. |

## Verificación de tenant y columnas
- Repositories Landing auditados mantienen filtros `tenant_id = :tenant_id` en lecturas administrativas de `landing_pages`, `landing_page_versions`, `landing_page_blocks`, `landing_forms`, `landing_form_fields`, `landing_form_submissions`, `landing_form_submission_values`, `landing_visits`.
- Inserciones en `EcosistemaLandingFormSubmitRepository` incluyen mínimos reales:
  - `landing_form_submissions`: `tenant_id`, `form_id`, `landing_page_id`, `raw_data_json`.
  - `landing_form_submission_values`: `tenant_id`, `submission_id`, `field_key`.

## Fuera de alcance / backlog
- No se añadieron escrituras administrativas nuevas ni migraciones.
- No se modificó `Ecosistema-presentacion` ni `Ecosistema-bd`.
