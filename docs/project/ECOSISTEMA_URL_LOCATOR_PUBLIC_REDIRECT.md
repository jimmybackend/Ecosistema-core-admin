# Ecosistema URL Locator — Public Redirect Controlado

## Flags requeridas
- `ECOSISTEMA_URL_LOCATOR_ENABLED=true`
- `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS=true`

## Defaults seguros
En `.env.example` todo queda apagado por defecto para redirect y tracking.

## Ruta pública
- `GET /u/{slug}`
- No requiere login.
- Nunca acepta `target_url`, `redirect_url` ni `tenant_id` desde request.

## Flujo seguro
1. Validación de flags.
2. Validación de slug.
3. Resolución por base canónica (`url_short_links`) con tenant fijo de configuración (`ECOSISTEMA_URL_LOCATOR_PUBLIC_TENANT_ID`).
4. Validación de estado, expiración y límite de clicks.
5. Resolución de destino desde DB y validación de esquema (`http/https`).
6. Bloqueo de destinos privados internos cuando la config lo exige.

## Tracking y click_count
Solo si `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED=true`:
- Inserta en `url_clicks`.
- Incrementa `url_short_links.click_count`.
- Usa transacción PDO y prepared statements.

## Privacidad IP/User-Agent
- IP solo si `ECOSISTEMA_URL_LOCATOR_COLLECT_IP=true`.
- User-Agent según `ECOSISTEMA_URL_LOCATOR_COLLECT_USER_AGENT`.

## No implementado en este PR
- Interstitials reales.
- Geolocalización externa.
- CRM leads, emails, AWS/S3 y adjuntos.

## Rollback
Apagar:
- `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS=false`
- `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED=false`

## Pruebas manuales
- Flags apagadas: bloquea sin escribir DB.
- Redirect activo + tracking off: redirige sin writes.
- Redirect activo + tracking on: redirige y escribe click + `click_count`.
