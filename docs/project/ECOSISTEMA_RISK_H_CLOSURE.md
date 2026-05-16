# ECOSISTEMA — Cierre de riesgos técnicos H

## Objetivo
Consolidar en un solo checklist el estado real de cierre de riesgos técnicos H para preparar una versión mostrable (demo) sin sobreprometer cobertura productiva.

## Criterios de estado
- **Cerrado**: riesgo atendido con evidencia técnica/documental consistente.
- **Mitigado**: existen controles parciales o de contención; aún no hay cobertura productiva end-to-end.
- **Pendiente**: falta implementación o validación clave para considerar el riesgo bajo control.

## Checklist único de riesgos H

| Riesgo técnico H | Estado | PR que lo cubre | Evidencia | Siguiente paso |
|---|---|---|---|---|
| Fallo runtime en permisos por `tenant_id` faltante | **Cerrado** | #157 | `RolePermissionService` evita `tenant_id` libre desde request y valida rol; `PermissionRepository` alinea `core_role_permissions` con `tenant_id`. Ver también `docs/auth/AUTH_PERMISSIONS_SCHEMA_ALIGNMENT.md` y smoke-check de PR #157. | Mantener este control en futuras rutas de permisos/roles y extender checklist al onboarding de módulos nuevos. |
| Smoke-check sin compatibilidad real contra DB | **Mitigado** | #168 | Existe `scripts/schema-compatibility-check.php` y se invoca desde `scripts/smoke-check.php` como chequeo opcional read-only; permite detectar desalineaciones críticas cuando DB está disponible. | Convertir chequeo opcional en pipeline CI con entorno de integración y umbrales de fallo por severidad. |
| Nombre de base inconsistente | **Cerrado** | #169 | Canonicalización documental en `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md`; README y runbooks alineados; smoke-check bloquea mención peligrosa `USE ecosistema;` en artefactos operativos críticos. | Incluir guardrails adicionales en plantillas de despliegue para evitar regresiones de naming. |
| Flags controlled con defaults productivos | **Cerrado** | #170 | Matriz y defaults seguros documentados en `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md` + sección de seguridad/flags en README con defaults en `false` para integraciones sensibles. | Auditar trimestralmente nuevos flags para mantener la política “secure by default”. |
| Workers/cron no productivos completos | **Mitigado** | #171 | Estado explícito en `docs/ops/WORKERS_CRON_CURRENT_STATE.md`: sin workers productivos activos, sin AWS/S3 real y sin envío masivo; README replica el estado para evitar ambigüedad. | Definir plan por fases (colas, retries, observabilidad, runbooks) y habilitación progresiva por entorno. |
| Analytics/IP/geolocalización/consentimiento | **Mitigado** | #172 | Política y límites en `docs/project/ECOSISTEMA_ANALYTICS_PRIVACY_CONSENT.md`; cobertura orientada a cumplimiento documental y guardrails de operación controlada. | Completar evidencia técnica de enforcement en runtime (consent mode, minimización IP, retención y auditoría). |
| Aliases UI sobre columnas reales | **Cerrado** | #173 | Inventario y mapeo en `docs/project/ECOSISTEMA_UI_SCHEMA_ALIASES.md`, reduciendo ambigüedad entre etiquetas UI y columnas canónicas. | Añadir validación automatizada de aliases en revisiones de vistas/reportes nuevos. |

## Alcance para demo
Con el estado actual:
- Se considera **listo para demo controlada** lo cubierto como **cerrado** y lo **mitigado** con disclaimers explícitos.
- Permanece **fuera de promesa productiva** cualquier flujo que dependa de habilitación real de workers/colas/integraciones externas no activadas por defecto.

## Referencias
- `docs/project/CORE_ADMIN_DEMO_READINESS_CHECKLIST.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`
- `docs/project/CORE_ADMIN_SHOWCASE_DEMO_GUIDE.md`
