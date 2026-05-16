# ECOSISTEMA Module Status Verification (PR #192)

Fecha de verificación: 2026-05-16 (UTC)

## Objetivo
Confirmar y dejar explícito el estado real por módulo entre:
- Core Admin (este repositorio)
- Ecosistema BD (SQL canónico)
- Presentación (matriz comercial)

## Fuentes verificadas en esta ejecución

### Core Admin (verificado localmente)
- `README.md`
- `docs/estado_modulos.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS.md` (referencia de estado canónico)
- `routes/web.php`

### Ecosistema BD / Presentación
- **No verificable en esta ejecución**: en el entorno sólo está clonado `Ecosistema-core-admin`.
- Se deja validación cruzada manual obligatoria para:
  - `jimmybackend/Ecosistema-bd`
  - `jimmybackend/Ecosistema-presentacion`

## Estados permitidos usados en esta matriz
- `implemented`
- `read-only`
- `dry-run`
- `controlled`
- `documented-design`
- `roadmap`
- `not-started`
- `unknown`

## Matriz de estado real por módulo (base Core Admin)

> Regla aplicada: se asigna estado sólo con evidencia en rutas/docs de Core Admin disponibles en este entorno. Para BD/Presentación sin checkout local se marca warning.

| Módulo | Estado verificado | Evidencia Core Admin | Notas de verificación cruzada BD/Presentación |
|---|---|---|---|
| Core | `implemented` | README + rutas base + matriz técnica | Pendiente contraste SQL canónico y matriz comercial externa |
| Auth | `implemented` + `controlled` | Login/logout/sesión + registro con flag | Verificar que presentación no lo venda como “full self-service signup” |
| Drive | `controlled` + `read-only` + `dry-run` | Rutas cloud/drive + flags S3/remote off por defecto | Contrastar con tablas canónicas en Ecosistema-bd |
| URL Locator | `controlled` + `read-only` + `dry-run` | Rutas `/url/locator/*`, `/u/{slug}` + docs de dry-run/readonly | Revisar claims de presentación sobre redirect “terminado” |
| Landing | `controlled` + `read-only` + `dry-run` | Rutas `/landing/*`, `/l/{slug}` y flags render/submit | Revisar si presentación lo marca como productivo completo |
| Browser Analytics | `controlled` + `read-only` + `dry-run` | Rutas `/browser/analytics/*` + collector write flag | Validar narrativa de consent/privacy en presentación |
| CRM | `controlled` + `read-only` + `dry-run` | Rutas `/crm/*` y docs dry-run | Contrastar con SQL canónico de leads/followups |
| Campaigns | `controlled` + `read-only` + `dry-run` | Rutas `/campaigns*` + create dry-run | Verificar que presentación no lo marque “terminado” |
| Mail | `controlled` + `dry-run` | Rutas mail + flags SMTP/send | Confirmar claims comerciales de envío real |
| Notifications | `controlled` + `read-only` + `dry-run` | Rutas `mail-notifications/*` + flags | Confirmar alineación con presentación |
| Workflow | `controlled` + `read-only` + `dry-run` | Rutas `/workflow/*` + execute/dry-run | Contrastar con SQL canónico de reglas/runs |
| Reports | `controlled` + `read-only` + `dry-run` | Rutas reports/export + flags write/PII | Revisar promesa comercial de export productivo |
| Audit | `implemented` + `read-only` | `/system/audit`, `/audit/events` | Verificar mapeo BD de eventos/auditoría |
| Security | `controlled` + `dry-run` + `read-only` | Rutas `rate-limit`/permissions audit | Verificar comunicación externa “enforcement” |
| AI | `controlled` + `dry-run` | Rutas `POST /ai/assist` y summaries dry-run | Confirmar que presentación no diga “automatización completa” |
| Billing | `roadmap` | README y estado modular lo reportan pendiente | Requiere verificación en presentación/BD |
| Integrations | `roadmap` | README lo deja parcial/pendiente | Requiere verificación en presentación/BD |
| Support | `roadmap` | README lo deja pendiente | Requiere verificación en presentación/BD |
| Privacy/Compliance | `documented-design` + `controlled` | docs de privacidad/consent + flags | Confirmar matriz comercial si existe módulo explícito |
| Jobs/Workers | `roadmap` | docs/ops reporta estado limitado actual | Contrastar con cron/jobs reales en otros repos |
| Onboarding | `implemented` | Rutas `/onboarding/*` + servicios/repos onboarding | Validar tablas canónicas en Ecosistema-bd |
| Platform health | `implemented` + `read-only` | Rutas `/platform/health*` | Contrastar representación comercial externa |

## Discrepancias detectadas en Core Admin

1. **README y docs internas están mayormente alineadas**, pero usan taxonomía textual mixta (“estable/parcial/roadmap”) y no exactamente el set normalizado de estados de este PR.
   - Acción: este documento normaliza al set permitido sin cambiar lógica de producto.

2. **No se pudo hacer contraste real con Ecosistema-bd ni Ecosistema-presentacion** por ausencia de esos repos en el entorno.
   - Acción: se deja checklist manual obligatorio.

## Warnings obligatorios para publicación

- No afirmar “terminado” en Presentación para módulos en `read-only`, `dry-run` o `controlled`.
- En módulos con BD diseñada pero UI no operativa completa, usar `documented-design`.
- Mantener README de Core Admin alineado con esta matriz normalizada si se actualiza narrativa comercial.

## Checklist manual pendiente (fuera de este entorno)

1. Clonar/actualizar `jimmybackend/Ecosistema-bd` y validar SQL canónico por módulo.
2. Clonar/actualizar `jimmybackend/Ecosistema-presentacion` y localizar matriz comercial.
3. Para cada módulo en `controlled`/`dry-run`/`read-only`, bloquear wording “terminado”.
4. Registrar discrepancias encontradas y corregir documentación en el repo correspondiente.

## Resultado

Estado real por módulo documentado con evidencia disponible en Core Admin y con pendientes explícitos donde faltó acceso a BD/Presentación.
