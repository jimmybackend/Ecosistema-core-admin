# Seguimiento PR #231 — Auditar Landing Pages contra tablas landing_* reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| landing_pages | id, tenant_id, campaign_id, template_id, owner_user_id, title, slug, status, page_type, seo_*, custom_* | R | app/Core/Landing/EcosistemaLandingPageRepository.php, routes/web.php | OK |
| landing_page_versions | id, tenant_id, landing_page_id, version_no, title, layout_json, custom_css/js, is_published | R | app/Core/Landing/EcosistemaLandingPageRepository.php | OK |
| landing_page_blocks | id, tenant_id, landing_page_id, version_id, block_type, settings_json, content_json | R | app/Core/Landing/EcosistemaLandingPageRepository.php | OK |
| landing_forms | id, tenant_id, landing_page_id, campaign_id, name, submit_button_text, success_message, redirect_url, is_active | R | app/Core/Landing/EcosistemaLandingFormRepository.php | OK |
| landing_form_fields | id, tenant_id, form_id, field_key, label, field_type, options_json, validation_json | R | app/Core/Landing/EcosistemaLandingFormRepository.php, services/views landing | Corregido (field_key masked) |
| landing_form_submissions | tenant_id, form_id, landing_page_id, raw_data_json + campos de contexto | C/R | app/Core/Landing/EcosistemaLandingFormSubmitRepository.php, app/Core/Landing/EcosistemaLandingSubmissionRepository.php | OK |
| landing_form_submission_values | tenant_id, submission_id, field_key, value_*, file_path, s3_key | C/R | app/Core/Landing/EcosistemaLandingFormSubmitRepository.php, app/Core/Landing/EcosistemaLandingSubmissionRepository.php | Corregido (field_key masked en UI) |
| landing_visits | tenant_id, landing_page_id, campaign_id, visitor/session/ip/user_agent, geo, device, visited_at | R | app/Core/Landing/EcosistemaLandingVisitRepository.php | OK |
| landing_templates | id, name, slug, template_json | R (join) | app/Core/Landing/EcosistemaLandingPageRepository.php | OK |
| landing_conversions | contrato revisado | N/A en scope auditado | N/A | Sin uso en rutas/servicios landing actuales |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Media | resources/views/pages/landing/form-detail.php | Vista detalle | landing_form_fields | field_key | Exposición completa de campo sensible | Se reemplazó por preview enmascarado |
| Media | resources/views/pages/landing/submission-detail.php | Vista detalle | landing_form_submission_values | field_key | Exposición completa de campo sensible | Se reemplazó por preview enmascarado |
| Media | routes/web.php | POST /l/{slug}/forms/{id}/submit | landing_form_submissions | (escritura) | Faltaba validación CSRF explícita | Se agregó `ensureValidCsrfToken` y bloqueo temprano |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [x] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| landing_form_submissions | tenant_id, form_id, landing_page_id, raw_data_json | Sí | tenant desde config controlada; form/page desde lookup por tenant; raw_data desde payload validado | Sin tenant libre por request |
| landing_form_submission_values | tenant_id, submission_id, field_key | Sí | tenant/submission desde contexto interno; field_key desde definición de campo de formulario | field_key ahora no se expone completo en UI |

## 6. Reglas tenant/user verificadas
- [x] `tenant_id` se toma de sesión/contexto validado cuando aplica
- [x] `user_id`/`owner_user_id`/`created_by_user_id` no se aceptan libremente desde request cuando aplica
- [x] Lecturas administrativas filtran por tenant cuando la tabla es tenant-aware
- [x] Escrituras administrativas llenan tenant desde contexto seguro

## 7. Campos sensibles revisados
- [x] No se imprimen hashes completos
- [x] No se imprimen tokens completos
- [x] No se imprime `s3_key`, rutas internas o secretos
- [x] JSON sensible se muestra como preview, máscara o `*_present`

## 8. Validaciones ejecutadas
- [x] `composer dump-autoload`
- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `composer smoke`
- [ ] `composer schema:usage` si existe

## 9. Resultado
- Estado: `Go con advertencias`
- Warnings aceptados: `composer schema:usage` no está definido en este repositorio.
- Pendientes que pasan al backlog: Sin pendientes críticos nuevos; `landing_conversions` no tiene uso activo en módulos landing auditados.
- Evidencia principal: `docs/schema-usage/landing_pages_pr231_audit.md`
