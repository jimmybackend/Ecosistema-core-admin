# URL Locator create/edit controlled

Este PR habilita creación/edición administrativa controlada sobre `url_short_links`.

- Respeta tenant desde sesión.
- Requiere flags `ECOSISTEMA_URL_LOCATOR_ENABLED=true` y `ECOSISTEMA_URL_LOCATOR_ADMIN_WRITE_ENABLED=true`.
- Mantiene `public_redirects` y `tracking` en `false`.
- No escribe `url_clicks`, `access_token_hash` ni `click_count`.
