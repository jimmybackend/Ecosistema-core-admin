# PR #230 — Auditoría URL Locator vs `adbbmis1_eco`

Fuente de verdad: `adbbmis1_eco.sql` (contrato entregado en el requerimiento del PR).

## Evidencia de revisión técnica

| Archivo | Función | Query/operación | Tabla(s) | Validación | Acción |
|---|---|---|---|---|---|
| `app/Core/UrlLocator/EcosistemaUrlLocatorPublicRedirectService.php` | `resolveLanguage` | SELECT idiomas por short link | `url_short_link_languages` | La tabla es tenant-aware y la lectura no filtraba por `tenant_id`. | Se añadió filtro `tenant_id=:tenant_id` y binding seguro desde contexto (`public_tenant_id`). |
| `app/Core/UrlLocator/EcosistemaUrlLocatorPublicRedirectService.php` | `resolveTargetUrl` | SELECT target por idioma | `url_short_link_languages` | Faltaba restricción de tenant en lookup puntual. | Se añadió `tenant_id=:tenant_id` y binding seguro. |
| `app/Core/UrlLocator/EcosistemaUrlLocatorLinkRepository.php` | `listLinkLanguages` | SELECT + join | `url_short_link_languages`, `url_short_links`, `url_languages` | Consulta filtraba por tenant sólo vía join a short link; se reforzó aislamiento directo de tabla tenant-aware. | Se añadió `sll.tenant_id=:tenant_id`. |
| `app/Core/UrlLocator/EcosistemaUrlLocatorLinkRepository.php` | `findSmartSettings` | SELECT + join | `url_smart_link_settings`, `url_short_links` | Tabla tenant-aware sin filtro directo de tenant. | Se añadió `s.tenant_id=:tenant_id`. |
| `app/Core/UrlLocator/EcosistemaUrlLocatorLinkRepository.php` | `listLinkMessageTemplates` | SELECT + join | `url_message_templates`, `url_short_links` | Tabla tenant-aware sin filtro directo de tenant. | Se añadió `mt.tenant_id=:tenant_id`. |
| `app/Core/UrlLocator/EcosistemaUrlLocatorLinkRepository.php` | `listLinkAdInterstitials` | SELECT + join | `url_ad_interstitials`, `url_short_links` | Tabla tenant-aware sin filtro directo de tenant. | Se añadió `ai.tenant_id=:tenant_id`. |

## Resultado resumido

- No se detectaron columnas inexistentes en queries SQL de URL Locator para las tablas objetivo revisadas.
- No se detectó exposición completa de `access_token_hash`, `media_s3_key`, `ad_html`, `body_html`, `custom_css`, `custom_js` en vistas del módulo; se mantienen banderas `*_present`/`*_exposed=false`.
- Se reforzó cumplimiento de regla tenant en lecturas administrativas y en resolución pública de idioma.
