# ECOSISTEMA Landing Forms — Read-only (PR #91)

Implementa consulta administrativa **read-only** para `landing_forms` y `landing_form_fields` usando `tenant_id` desde sesión.

## Tablas canónicas usadas
- `landing_forms`
- `landing_form_fields`
- contexto: `landing_pages`, `crm_marketing_campaigns`

## Qué muestra
- Listado de formularios por tenant y por landing page.
- Detalle de formulario con tabla de campos.
- Resúmenes (`total/active/inactive`).

## Qué se oculta
- `redirect_url` completo (solo `present/preview`).
- `success_message` completo (solo preview).
- `options_json` y `validation_json` crudos.
- `default_value` completo (solo bandera de presencia).

## Seguridad y alcance
- Solo `SELECT` con PDO prepared statements.
- Sin POST de formularios públicos.
- Sin `landing_form_submissions`.
- Sin creación de leads CRM.
- Sin escrituras DB.
