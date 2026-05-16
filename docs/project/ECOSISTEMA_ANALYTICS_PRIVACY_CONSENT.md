# ECOSISTEMA Analytics — Privacy & Consent Baseline

## Objetivo
Definir reglas explícitas de privacidad/consentimiento para cualquier uso productivo de Browser Analytics, visitas de Landing, clicks/URL tracking y attribution rollups en Core Admin.

## Alcance
Aplica a superficies:
- Browser Analytics (`/browser/analytics`, collector dry-run/controlled).
- Landing visits y landing form submissions (`/landing/*`).
- URL Locator clicks/tracking (`/url/locator/*`).
- Attribution URL/Landing y rollups (`/attribution/*`).

Este documento **no habilita** tracking productivo. Solo fija baseline de consentimiento y defaults seguros.

## Principio rector
Sin consentimiento explícito y verificable, no debe activarse recolección sensible ni escrituras de tracking.

## Datos potencialmente capturables y condición mínima

### 1) IP (completa o enmascarada)
- Estado por defecto: **apagado** (`*_COLLECT_IP=false`).
- Sin consentimiento explícito: solo se permite no capturar IP o usar previews/enmascarado en vistas administrativas read-only.
- Con consentimiento explícito + base legal/documentada: puede capturarse para casos definidos (fraude/seguridad/atribución), con retención mínima.

### 2) User-Agent
- Estado por defecto: **apagado** (`*_COLLECT_USER_AGENT=false` en analytics/url locator según módulo).
- Sin consentimiento explícito: no capturar crudo para tracking productivo.
- Con consentimiento explícito: permitido para clasificación técnica (device/browser) con minimización.

### 3) Referer
- Debe tratarse como dato potencialmente sensible (puede incluir parámetros identificables).
- Sin consentimiento explícito: evitar persistencia cruda en tracking write; usar truncado/preview cuando aplique en modo read-only.
- Con consentimiento explícito: permitido bajo minimización y política de retención.

### 4) UTM (source/medium/campaign/term/content)
- Puede persistirse para atribución/campañas solo cuando tracking write esté habilitado y consentido.
- Sin consentimiento explícito: no activar pipeline de escritura de analytics.

### 5) Geolocalización aproximada (país/región/ciudad/coordenadas aproximadas)
- Estado por defecto: no activa por proveedor.
- No se debe activar geolocalización por IP ni proveedor externo sin consentimiento explícito y documentación de proveedor.
- Si se habilita en futuro, preferir granularidad aproximada (país/región/ciudad) y minimizar coordenadas.

### 6) Eventos de navegación/clicks/forms
- Incluye pageviews, eventos UI, clicks, visitas landing y formularios.
- Estado por defecto: write desactivado (`*_TRACKING_ENABLED=false`, `*_COLLECTOR_WRITE=false`, `*_ROLLUP_WRITE=false`).
- Solo con consentimiento explícito + flags habilitadas se permite persistencia productiva.

## Defaults seguros exigidos (baseline)
- `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED=false`
- `ECOSISTEMA_URL_LOCATOR_COLLECT_IP=false`
- `ECOSISTEMA_URL_LOCATOR_COLLECT_USER_AGENT=false`
- `ECOSISTEMA_BROWSER_ANALYTICS_ENABLED=false`
- `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE=false`
- `ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_IP=false`
- `ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_USER_AGENT=false`
- `ECOSISTEMA_ATTRIBUTION_ENABLED=false`
- `ECOSISTEMA_ATTRIBUTION_WRITE=false`
- `ECOSISTEMA_ATTRIBUTION_ROLLUP_WRITE=false`

## Condiciones previas para cualquier activación en producción
1. Consentimiento explícito implementado en la interfaz/capa pública aplicable.
2. Documento legal/política de privacidad actualizado con finalidades, bases legales y retención.
3. Validación de minimización (campos estrictamente necesarios).
4. Revisión de seguridad y auditoría de flags/configuración por tenant/entorno.
5. Evidencia de pruebas en entorno no productivo antes de habilitar write.

## Fuera de alcance de este PR
- Activar tracking en producción.
- Integrar proveedor de geolocalización.
- Introducir nueva recolección de IP.
