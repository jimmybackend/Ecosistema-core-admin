# CORE ADMIN — Auditoría de seguridad de rutas públicas/técnicas

> Alcance: auditoría defensiva de rutas públicas/técnicas en `routes/web.php` para Core Admin como app administrativa interna con superficies públicas controladas por flags.

## Rutas auditadas

Según `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`, las rutas públicas/técnicas relevantes son:

- `GET /u/{slug}`
- `GET /l/{slug}`
- `POST /l/{slug}/forms/{id}/submit`
- `POST /browser/analytics/collect`

## Resultado ejecutivo

- **Estado general:** ✅ comportamiento alineado a enfoque `controlled`/safe-defaults.
- **Exposición de errores internos:** ✅ no se exponen stack traces ni SQL en respuesta pública.
- **Escrituras con flags apagadas:** ✅ rutas de write técnico/público retornan bloqueo o resultado no operativo cuando flags están en `false`.
- **Open redirect por default:** ✅ bloqueado por default mediante flags de URL Locator.

## Evidencia por ruta

### 1) `GET /u/{slug}` (URL Locator público)

- Usa `EcosistemaUrlLocatorPublicRedirectService` y sólo ejecuta redirect si `allowed=true`.
- Cuando no está permitido o hay excepción: responde `404` + fallback `pages/url-locator/public-redirect-blocked`.
- No expone mensajes internos en catch.

**Riesgo controlado:** evita apertura pública por default cuando `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS=false`.

### 2) `GET /l/{slug}` (Landing render público)

- Evalúa flag `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED`.
- Si permitido, renderiza `pages/landing/public-page`.
- Si bloqueado o falla técnica: fallback `pages/landing/public-page-blocked`.
- No expone detalle interno de excepción.

**Riesgo controlado:** sin habilitación explícita no hay exposición de landing pública.

### 3) `POST /l/{slug}/forms/{id}/submit` (Landing form submit público)

- Evalúa flags `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED` y `ECOSISTEMA_LANDING_FORM_FILE_UPLOADS`.
- Pasa ambos controles al service para gobernar write y uploads.
- En excepción técnica mantiene respuesta segura vía vista `pages/landing/form-submit-result` con resultado por defecto no operativo (`ok=false`).

**Riesgo controlado:** con flags apagadas, el flujo queda no operativo (sin write real).

### 4) `POST /browser/analytics/collect` (colector técnico)

- Requiere simultáneamente:
  - `ECOSISTEMA_BROWSER_ANALYTICS_ENABLED=true`
  - `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE=true`
- Si falta alguno: `404 not_found`.
- Además valida origin y tenant técnico (`ECOSISTEMA_BROWSER_ANALYTICS_TENANT_ID > 0`).
- Manejo de errores seguro (`invalid_payload`, `collector_failed`) sin revelar internals.

**Riesgo controlado:** no hay ingesta/escritura por default; endpoint técnico permanece cerrado.

## Alineación con defaults seguros

- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md` y `.env.example` mantienen estas flags sensibles en `false` por defecto para despliegue seguro.
- `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md` clasifica estas rutas como `controlled` y explicita riesgos/controles.

## Recomendaciones de hardening (sin rediseño)

1. Mantener respuesta genérica en todas las rutas públicas (sin eco de excepción).
2. Mantener fallback views no verbosas para estados bloqueados.
3. Evitar habilitar simultáneamente write/tracking en entornos sin consentimiento/observabilidad operativa.
4. Tratar `ECOSISTEMA_BROWSER_ANALYTICS_TENANT_ID` como requisito operativo antes de encender collector write.

## Estado final

Auditoría completada. Las rutas públicas/técnicas revisadas conservan defaults seguros y comportamiento defensivo consistente con el modelo actual de Core Admin (`internal admin + controlled public surfaces`).
