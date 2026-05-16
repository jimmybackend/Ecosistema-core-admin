# ECOSISTEMA Privacy & Security Exposure Audit (Admin Views)

Fecha: 2026-05-16  
Repositorio: `jimmybackend/Ecosistema-core-admin`

## Objetivo
Verificar que vistas/respuestas administrativas no expongan secretos, hashes sensibles, claves/rutas internas de storage, JSON crudo ni PII completa fuera de los casos estrictamente necesarios.

## Alcance revisado
Se revisaron vistas en:

- `resources/views/pages/cloud`
- `resources/views/pages/mail`
- `resources/views/pages/url-locator`
- `resources/views/pages/landing`
- `resources/views/pages/browser-analytics`
- `resources/views/pages/crm`
- `resources/views/pages/campaigns`
- `resources/views/pages/workflow`
- `resources/views/pages/reports`
- `resources/views/pages/security`
- `resources/views/pages/audit`
- `resources/views/pages/ai`

Además, se revisó y actualizó `scripts/smoke-check.php` para cubrir un patrón sensible faltante.

---

## Resultado ejecutivo
Estado general: **OK con hardening incremental aplicado**.

- No se observaron exposiciones directas de `password_hash`, `session_token_hash`, `refresh_token_hash`, `AWS_SECRET`, `DB_PASSWORD`, `MAIL_PASSWORD` en vistas administrativas del alcance.
- En módulos con datos sensibles (Cloud, Landing, Browser Analytics, URL Locator, Workflow, Audit), el patrón predominante es de **preview/present/hidden** en lugar de valor crudo.
- Se reforzó smoke-check para detectar referencias directas de campos sensibles que deberían presentarse como preview/ocultos.

---

## Hallazgos por categoría

### 1) Hashes, secretos y credenciales
- Sin hallazgos de exposición directa en vistas auditadas.
- Se mantiene validación de placeholders seguros en `.env.vm.example` desde smoke-check.

### 2) Claves/rutas internas de storage
- En Cloud/Drive aparecen textos UI y estados de validación (`s3_key`, `present/exposed=false`) sin imprimir valor crudo.
- Se mantiene enfoque de “presence flag + hidden value”.

### 3) JSON crudo
- En vistas de analytics/workflow/audit se observan indicadores tipo `metadata_json_present` y no rendering de payload crudo.
- No se identificó impresión directa de `metadata_json`/`raw_data_json` como contenido.

### 4) PII (email, phone, IP, user-agent)
- En Landing/CRM/Analytics prevalece `*_preview` y banderas de presencia para campos sensibles.
- En Security (rate-limit dry-run/result) el campo IP es parte de input operativo del formulario, no listado masivo de PII.
- No se detectó rendering crudo masivo de email/phone/IP en el alcance indicado.

### 5) Analytics: IP, user-agent, geolocalización, consentimiento, privacidad
- Se observan controles y narrativa de privacidad/consentimiento en vistas y documentación del módulo Browser Analytics.
- La exposición en UI se mantiene en modo protegido/preview y sin payloads crudos.

---

## Cambio aplicado (hardening)

Se actualizó `scripts/smoke-check.php` para incluir `MAIL_PASSWORD` en patrones sensibles generales de exposición, reforzando el control estático existente.

---

## Checklist de cierre
- [x] Revisión de vistas del alcance solicitadas.
- [x] Revisión de exposición de secretos/hashes/storage/json/PII.
- [x] Revisión de analytics (IP, UA, geolocalización, consentimiento, privacidad).
- [x] Extensión de smoke-check con patrones críticos faltantes.
- [x] Sin cambios funcionales fuera de hardening/documentación.

## Pendientes técnicos explícitos
- Si se habilitan modos productivos de tracking/write analytics, validar legalmente consentimiento, base legal y retención antes de desplegar en producción.
- Validación end-to-end con DB real: **no verificado por falta de conexión DB en este entorno**.
