# ECOSISTEMA CRM CAMPAIGNS READ-ONLY

Implementación de lectura para `crm_marketing_campaigns` usando `adbbmis1_eco` como fuente canónica.

## Alcance
- Rutas: `/crm`, `/crm/campaigns`, `/crm/campaigns/{id}`.
- Adapter en modo `read-only` y `db_writes=false`.
- Repository con consultas `SELECT` únicamente.
- Service con DTO seguro que enmascara `budget` y sanitiza `landing_url` (sin query completa).

## Notas
- No se crean migraciones, seeds ni cambios de esquema.
- Si faltan tablas/campos en `adbbmis1_eco`, el módulo falla cerrado mostrando datos vacíos/404 segura.
