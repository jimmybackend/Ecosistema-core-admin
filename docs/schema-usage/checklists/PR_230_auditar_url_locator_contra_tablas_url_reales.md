# Seguimiento PR #230 — Auditar URL Locator contra tablas url_* reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `url_short_links` | `id,tenant_id,slug,target_url,language_fallback_url,status,expires_at,max_clicks,click_count,campaign_id,landing_page_id,smart_type,requires_access_token,access_token_hash,...` | SELECT/INSERT/UPDATE | `EcosistemaUrlLocatorPublicRedirectService.php`, `EcosistemaUrlLocatorLinkRepository.php`, `EcosistemaUrlLocatorLinkWriteRepository.php` | OK |
| `url_short_link_languages` | `tenant_id,short_link_id,language_code,target_url,is_active,priority,is_default_for_language,click_count` | SELECT | `EcosistemaUrlLocatorPublicRedirectService.php`, `EcosistemaUrlLocatorLinkRepository.php` | Corregido (tenant filter) |
| `url_clicks` | `tenant_id,short_link_id,campaign_id,landing_page_id,visitor_uuid,ip_address,user_agent,accept_language_header,detected_language,selected_language,referer,clicked_url,...` | SELECT/INSERT | `EcosistemaUrlLocatorPublicRedirectService.php`, `EcosistemaUrlLocatorClickRepository.php` | OK |
| `url_languages` | `code,is_active` | SELECT | `EcosistemaUrlLocatorLinkWriteRepository.php`, `EcosistemaUrlLocatorLinkRepository.php` | OK |
| `url_smart_link_settings` | `tenant_id,short_link_id,show_access_counter,track_location,track_attachments,track_final_click,allow_indexing,require_consent,custom_css,custom_js` | SELECT | `EcosistemaUrlLocatorLinkRepository.php` | Corregido (tenant filter) |
| `url_message_templates` | `tenant_id,short_link_id,id,template_name,language_code,status,view_count,body_html` | SELECT | `EcosistemaUrlLocatorLinkRepository.php` | Corregido (tenant filter) |
| `url_ad_interstitials` | `tenant_id,short_link_id,id,title,ad_type,status,impression_count,click_count,media_s3_key,ad_html` | SELECT | `EcosistemaUrlLocatorLinkRepository.php` | Corregido (tenant filter) |
| `url_ad_clicks` | contrato revisado | N/A en módulo auditado | N/A | Sin uso directo |
| `url_ad_impressions` | contrato revisado | N/A en módulo auditado | N/A | Sin uso directo |
| `url_attachment_access_logs` | contrato revisado | N/A en módulo auditado | N/A | Sin uso directo |
| `url_language_redirect_logs` | contrato revisado | N/A en módulo auditado | N/A | Sin uso directo |
| `url_message_access_logs` | contrato revisado | N/A en módulo auditado | N/A | Sin uso directo |
| `url_message_attachments` | contrato revisado | N/A en módulo auditado | N/A | Sin uso directo |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Alta | `app/Core/UrlLocator/EcosistemaUrlLocatorPublicRedirectService.php` | `resolveLanguage` | `url_short_link_languages` | `tenant_id` | Lectura tenant-aware sin filtro por tenant. | Se agregó `tenant_id=:tenant_id` + binding. |
| Alta | `app/Core/UrlLocator/EcosistemaUrlLocatorPublicRedirectService.php` | `resolveTargetUrl` | `url_short_link_languages` | `tenant_id` | Lookup por idioma sin filtro tenant. | Se agregó `tenant_id=:tenant_id` + binding. |
| Media | `app/Core/UrlLocator/EcosistemaUrlLocatorLinkRepository.php` | múltiples queries de detalle | `url_short_link_languages`, `url_smart_link_settings`, `url_message_templates`, `url_ad_interstitials` | `tenant_id` | Filtro tenant indirecto vía join; faltaba guardrail directo en tabla tenant-aware. | Se reforzó cada query con `*.tenant_id=:tenant_id`. |

## 4. Cambios realizados
- [ ] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [ ] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [ ] Views corregidas si exponían campos sensibles
- [x] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `url_short_links` | `tenant_id`,`slug`,`target_url` | Sí | `tenant_id` desde sesión/auth en servicio de escritura; resto validado de formulario | OK |
| `url_clicks` | `tenant_id`,`short_link_id` | Sí | `tenant_id` desde `config[url_locator.public_tenant_id]`; `short_link_id` desde lookup interno por slug | OK |

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
- Warnings aceptados: `composer schema:usage` no está definido en `composer.json` (no aplica).
- Pendientes que pasan al backlog:
  - Revisar el contrato de `url_smart_link_settings` entregado: contiene dos estructuras mezcladas (incluye `rule_id/action_type/config_json`) que parecen corresponder a otra tabla; requiere confirmación de fuente para una auditoría funcional adicional.
- Evidencia principal:
  - `docs/schema-usage/url_locator_pr230_audit.md`
