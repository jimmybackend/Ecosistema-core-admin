# ECOSISTEMA Landing Submissions — Read-only (PR #92)

Consulta administrativa **read-only** para `landing_form_submissions` y `landing_form_submission_values` filtrado por `tenant_id` desde sesión.

## Tablas canónicas usadas
- `landing_form_submissions`
- `landing_form_submission_values`
- contexto: `landing_forms`, `landing_pages`, `landing_visits`, `crm_marketing_campaigns`

## Protección PII / campos ocultos
- No se expone `raw_data_json` (solo bandera `raw_data_json_present`).
- No se expone `value_json` (solo bandera `value_json_present`).
- `value_text` se muestra solo como preview truncado.
- `file_path` y `s3_key` no se exponen (solo banderas `*_present`).
- `ip_address` y `user_agent` se muestran en preview enmascarado/truncado.
- Coordenadas (`latitude/longitude`) no se exponen.

## Alcance funcional
- No procesa formularios públicos.
- No crea submissions.
- No crea ni actualiza leads CRM.
- No descarga adjuntos.
- Solo operaciones `SELECT` con PDO prepared statements.
