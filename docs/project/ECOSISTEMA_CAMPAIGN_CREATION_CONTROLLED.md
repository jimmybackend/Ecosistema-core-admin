# ECOSISTEMA Campaign Creation Controlled (PR #133)

## Alcance
- Ruta: `POST /campaigns`.
- Inserta campaña mínima en `crm_marketing_campaigns` usando tenant y usuario desde sesión autenticada.
- No acepta `tenant_id` desde request.
- No crea/edita migraciones, seeds, tablas ni columnas.

## Flags (default seguro)
- `ECOSISTEMA_CAMPAIGN_CREATION_WRITE=false`: bloquea escrituras de campaña cuando está apagado.
- `ECOSISTEMA_CAMPAIGN_CREATE_LANDING_DRAFT=false`: reservado, sin escritura en este PR.
- `ECOSISTEMA_CAMPAIGN_CREATE_SHORT_LINK=false`: reservado, sin escritura en este PR.

## Seguridad
- Validaciones de entrada para nombre, código, tipo, objetivo, moneda, fechas y presupuesto.
- Errores controlados sin stack trace ni SQL crudo.
- Resultado resumido; no imprime PII sensible.

## Diferencias canónicas
- Fuente canónica: `adbbmis1_eco`.
- Este PR sólo escribe `crm_marketing_campaigns` con columnas reales confirmadas.
- `landing_pages` y `url_short_links` quedan bloqueadas (sólo flags/documentación).
