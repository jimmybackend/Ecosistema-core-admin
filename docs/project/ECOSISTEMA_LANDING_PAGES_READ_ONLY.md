# ECOSISTEMA Landing Pages — Admin Read-only (PR #89)

Este PR implementa solamente lectura administrativa para Landing Pages.

## Alcance
- Rutas protegidas por sesión y permiso existente `modules.view` (fallback administrativo al no crear permisos nuevos).
- Tenant isolation usando `auth_tenant_id` de sesión.
- Operaciones SQL de solo lectura (`SELECT`) en:
  - `landing_pages`
  - `landing_templates` (metadata)
  - `landing_page_versions` (resumen)
  - `landing_page_blocks` (resumen)
  - `crm_marketing_campaigns` (nombre)
  - `core_users` (display/email)
- DTOs seguros sin exponer HTML/JSON crudo (`custom_head_html`, `custom_body_html`, `template_json`, `layout_json`, `settings_json`, `content_json`).

## No incluido
- Crear/editar/publicar landing pages.
- Render público `/l/{slug}`.
- Tracking real de visitas.
- Formularios públicos o submissions.
- Escrituras DB (`INSERT/UPDATE/DELETE`).

## Componentes
- `App\Core\Landing\EcosistemaLandingAdapter`
- `App\Core\Landing\EcosistemaLandingPageRepository`
- `App\Core\Landing\EcosistemaLandingPageService`
- Vistas:
  - `resources/views/pages/landing/index.php`
  - `resources/views/pages/landing/pages.php`
  - `resources/views/pages/landing/page-detail.php`

## Rutas
- `GET /landing`
- `GET /landing/pages`
- `GET /landing/pages/{id}`

Todas requieren login y permiso administrativo existente.
