# PR #188 — Inventario de cierre PR150-PR187

Fecha de corte: 2026-05-16  
Repositorio base auditado: `jimmybackend/Ecosistema-core-admin`  
Repositorios relacionados (revisión documental cruzada): `jimmybackend/Ecosistema-bd`, `jimmybackend/Ecosistema-presentacion`.

## Criterio de lectura
- Este inventario consolida **PR150 a PR187** con foco en estado real: `estable`, `read-only`, `dry-run` o `controlled`.
- No se marca “terminado productivo” cuando el alcance documentado es sólo lectura/simulación/control por flag.
- Cuando aplica, se enlaza a documentación existente en este repositorio para trazabilidad.

## Tabla de cierre PR150-PR187

| PR | Tema | Repositorio afectado | Tipo | Estado esperado | Archivos principales tocados | Riesgo cerrado | Pendiente si aplica |
|---|---|---|---|---|---|---|---|
| #150 | README alineado al estado real de Core Admin | Ecosistema-core-admin | documentación | Documentado | `README.md`, `docs/project/ECOSISTEMA_CORE_ADMIN_ESTADO_ACTUAL.md` | Sobrepromesa funcional por documentación desactualizada | Mantener README sincronizado en cada cierre de release |
| #151 | Base canónica de DB aclarada | Ecosistema-core-admin + referencia Ecosistema-bd | documentación | Documentado | `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md` | Errores por nombres de BD inconsistentes | Validar nomenclatura también en pipelines externos |
| #152 | Mapa rutas/servicios/repos/tablas | Ecosistema-core-admin | documentación / verificación | Documentado | `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`, `docs/project/ECOSISTEMA_CORE_ADMIN_RUTAS.md` | Ambigüedad de trazabilidad técnica | Revisar mapa cuando se agreguen endpoints |
| #153 | Matriz de flags/permisos/datos sensibles | Ecosistema-core-admin | seguridad / documentación | Documentado | `docs/project/ECOSISTEMA_PERMISSIONS_AUDIT.md`, docs de flags | Activación accidental de flujos sensibles | Hardening continuo y revisión periódica de flags |
| #154 | Guía honesta de demo | Ecosistema-core-admin + referencia Ecosistema-presentacion | presentación | Documentado | `docs/project/CORE_ADMIN_SHOWCASE_DEMO_GUIDE.md` | Demo vendida como “productiva” en módulos controlados | Actualizar guion conforme nuevos estados |
| #155 | Checklist de preparación para demo y cierre | Ecosistema-core-admin | verificación / documentación | Verificado por checklist | `docs/project/CORE_ADMIN_DEMO_READINESS_CHECKLIST.md`, `docs/project/CORE_ADMIN_OPERATIONAL_CLOSURE.md` | Presentar sin checklist técnico mínimo | Completar checklist manual con DB real antes de producción |
| #156 | Alineación role-permissions con `tenant_id` | Ecosistema-core-admin | corrección técnica / seguridad | Corregido | capa authz + `docs/auth/AUTH_PERMISSIONS_SCHEMA_ALIGNMENT.md` | Asignaciones rol↔permiso fuera de esquema multi-tenant | Mantener test/smoke de regresión en futuros cambios authz |
| #157 | Smoke con compatibilidad opcional de esquema DB | Ecosistema-core-admin | verificación | Verificado (opcional/read-only) | `scripts/smoke-check.php`, `composer.json` | Falsos “OK” sin confirmar esquema esperado | No verificado si no hay conexión DB real |
| #158 | Roles/permisos alineados a esquema real | Ecosistema-core-admin | corrección técnica | Corregido | servicios/repositorio authz | Inconsistencia entre código y esquema vigente | Extender cobertura de pruebas de autorización |
| #159 | Normalización de feature flags | Ecosistema-core-admin | seguridad / documentación | Corregido + documentado | `.env.example`, docs de flags | Defaults inseguros por drift de configuración | Revisar defaults en cada módulo nuevo |
| #160 | Revisión rutas protegidas, permisos y CSRF | Ecosistema-core-admin | seguridad / corrección técnica | Corregido | `routes/web.php` y handlers asociados | Exposición de rutas POST/acciones críticas sin control | Mantener auditoría de rutas al crecer módulos |
| #161 | Ocultar campos internos Drive/Cloud en vistas | Ecosistema-core-admin | seguridad / corrección técnica | Corregido | vistas Cloud/Drive | Fuga de detalles internos operativos | Revisar exposición de metadata al agregar vistas |
| #162 | Mail/Notifications con defaults seguros | Ecosistema-core-admin | seguridad / corrección técnica | Corregido (controlled) | config/env + docs mail/notifications | Envíos no intencionales | Aún sin worker productivo (intencional) |
| #163 | Alineación CRM/Campaigns/Landing/URL Locator | Ecosistema-core-admin + referencia Ecosistema-presentacion | corrección técnica / documentación | Corregido (estado controlado por módulo) | docs de CRM/campaigns/landing/url | Contradicciones de flujo entre módulos de marketing | Integración E2E real pendiente |
| #164 | IA controlada: privacidad + flags | Ecosistema-core-admin | seguridad / corrección técnica | Corregido (controlled/dry-run según flujo) | docs AI + configuración flags | Riesgo privacidad y sobre-ejecución IA | Habilitación productiva IA pendiente por governance |
| #165 | Cierre de alineación para showcase | Ecosistema-core-admin | presentación / verificación | Verificado para demo controlada | `docs/project/CORE_ADMIN_SHOWCASE_RELEASE_NOTES.md` | Mostrar alcance fuera de estado real | Revalidar antes de cada demo externa |
| #166 | Refuerzo `tenant_id` en role-permissions | Ecosistema-core-admin | seguridad / corrección técnica | Corregido | capa authz + repositorio permisos | Regresión de aislamiento tenant | Mantener como condición obligatoria en PRs authz |
| #167 | Smoke read-only de compatibilidad DB opcional | Ecosistema-core-admin | verificación | Verificado (condicionado a conectividad DB) | `scripts/smoke-check.php` | Falsa sensación de cobertura total sin DB real | Checklist manual cuando no hay DB |
| #168 | Fuente única del nombre canónico de DB | Ecosistema-core-admin + referencia Ecosistema-bd | documentación | Documentado | `docs/project/ECOSISTEMA_FUENTE_MAESTRA.md`, `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md` | Divergencia de nombre de DB entre equipos | Sincronizar con scripts/infra externos |
| #169 | Auditoría de defaults seguros en flags controladas | Ecosistema-core-admin | seguridad / verificación | Verificado | `.env.example`, `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md` | Activación por defecto de flujos sensibles | Control continuo en onboarding técnico |
| #170 | Estado real de cron/workers y smoke | Ecosistema-core-admin | documentación / verificación | Documentado (no productivo) | `docs/project/CORE_ADMIN_OPERATIONAL_CLOSURE.md` | Prometer workers/scheduler productivos inexistentes | Cron/workers productivos siguen pendientes |
| #171 | Privacy analytics reforzada | Ecosistema-core-admin | seguridad / documentación | Corregido + documentado | `docs/project/ECOSISTEMA_ANALYTICS_PRIVACY_CONSENT.md` | Recolección sin controles claros de privacidad | Validación legal/política de datos en producción |
| #172 | Alias UI + smoke-check | Ecosistema-core-admin | documentación / verificación | Verificado | `docs/project/ECOSISTEMA_UI_SCHEMA_ALIASES.md`, `scripts/smoke-check.php` | Confusión entre alias UI y nombres técnicos | Mantener glosario actualizado |
| #173 | Cierre checklist de riesgos técnicos H | Ecosistema-core-admin | verificación / documentación | Cerrado documentalmente | `docs/project/ECOSISTEMA_RISK_H_CLOSURE.md` | Riesgos críticos sin dueño explícito | Revisión periódica de riesgos residuales |
| #174 | Plan de trabajo PR175-181 | Ecosistema-core-admin + referencia Ecosistema-presentacion | documentación | Documentado | plan interno (docs project/presentation) | Ejecución no secuenciada de cierre comercial | Replanificar si cambia el alcance del showcase |
| #175 | Matriz comercial de estado por módulo | Ecosistema-presentacion (documentado en core) | presentación | Documentado | docs de presentación + matriz de estado | Mensaje comercial no alineado al estado técnico | Mantener etiquetado por estado en material público |
| #176 | Separación estable/read-only/dry-run/controlled | Ecosistema-core-admin | documentación | Documentado | `docs/project/CORE_ADMIN_MODULE_STATUS.md`, `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md` | Mezcla de estados y promesas ambiguas | Gobernanza de taxonomía de estados |
| #177 | Flujo operativo por etapas | Ecosistema-presentacion + Ecosistema-core-admin (alineación) | presentación / documentación | Documentado | guiones/flujo de demo + notas operativas | Narrativa de flujo distinta a implementación real | Ajustes continuos por release |
| #178 | Contacto público aprobado | Ecosistema-presentacion (referenciado) | presentación / documentación | Documentado | material de presentación | Exponer datos no aprobados | Mantener control de datos públicos aprobados |
| #179 | Material visual base y guía de assets | Ecosistema-presentacion | presentación | Documentado | assets/guías de presentación | Inconsistencia visual y de mensaje | Control de versión de assets |
| #180 | Guion demo con alcance real | Ecosistema-presentacion + Ecosistema-core-admin | presentación / verificación | Documentado | guía demo y release notes | Demo sin disclaimers de estado | Ensayo con checklist técnico previo |
| #181 | Cierre riesgos comerciales | Ecosistema-presentacion | presentación / verificación | Cerrado documentalmente | checklist riesgos comerciales | Riesgo reputacional por promesas excesivas | Revalidar al cambiar pricing/alcance |
| #182 | Alineación `tenant_id` con esquema tenant | Ecosistema-core-admin | corrección técnica / seguridad | Corregido | authz role-permissions + documentación auth | Falla de consistencia multi-tenant | Mantener pruebas de no-regresión |
| #183 | Nombre canónico de DB + referencias legacy | Ecosistema-core-admin + Ecosistema-bd | documentación | Documentado | `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md` | Operación sobre DB equivocada | Limpiar referencias legacy en scripts externos |
| #184 | README clarifica estado operativo real | Ecosistema-core-admin | documentación | Documentado | `README.md`, docs de estado/cierre | Sobrepromesa en documento principal | Mantener README como fuente ejecutiva viva |
| #185 | Etiquetado honesto de módulos en presentación | Ecosistema-presentacion + referencia core | presentación | Documentado | matriz/diapositivas de módulos | Confundir “visible” con “productivo” | Revisión previa a publicación externa |
| #186 | Flujo operativo alineado a implementación actual | Ecosistema-presentacion + Ecosistema-core-admin | presentación / documentación | Documentado | guiones operativos + release notes | Secuencia demo incompatible con plataforma actual | Ajustes por iteración de producto |
| #187 | Cierre contradicciones G | Ecosistema-core-admin | verificación / documentación | Cerrado documentalmente | `docs/project/ECOSISTEMA_CONTRADICTIONS_G_CLOSURE.md` | Contradicciones abiertas entre docs de cierre | Mantener checklist de contradicciones por release |

## Cobertura explícita de puntos críticos solicitados

- `core_role_permissions.tenant_id`: cubierto explícitamente en PR #156, #166 y #182; referencia técnica en `docs/auth/AUTH_PERMISSIONS_SCHEMA_ALIGNMENT.md`.
- Nombre canónico de DB: cubierto en PR #151, #168 y #183; ver `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md` y `docs/project/ECOSISTEMA_FUENTE_MAESTRA.md`.
- README Core Admin ordenado por estado real: PR #150 y #184.
- Presentación con módulos etiquetados por estado: PR #175, #176, #185.
- Flujo operativo ajustado a avance real: PR #163, #177, #180, #186.
- Workers/cron sin prometer workers productivos: PR #170 y documentación de cierre operativo.
- Privacy/analytics/IP/user-agent/geolocalización: PR #171 y documentación de analytics/privacy en docs project.
- Flags seguras por defecto: PR #159 y #169 con respaldo en `.env.example` y `ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`.

## Qué puede mostrarse hoy

- Core Admin administrativo estable (auth, sesiones, usuarios, roles, permisos, módulos, system/audit).
- Cockpit y módulos extendidos **cuando se etiquetan explícitamente por estado** (read-only/dry-run/controlled).
- Evidencia documental de seguridad base (CSRF, permisos, defaults seguros, privacidad analytics).
- Checklist de smoke y documentación de demo honesta.

## Qué debe mostrarse como beta/controlado

- Flujos de marketing/CRM/campaigns/landing/url locator que dependan de ejecución controlada.
- Integraciones de notificaciones, workflow, exports, cloud/s3, IA y automatizaciones cuando estén en dry-run o controlled.
- Cualquier función condicionada por flags o por entorno sin validación E2E productiva.

## Qué sigue en roadmap

- Verificación E2E completa con DB real y datos de prueba controlados.
- Endurecimiento de observabilidad/alertas/runbooks y operación de cron/workers productivos (si se decide activarlos).
- Cierre de hardening para integraciones externas (SMTP/S3/IA) previo a activación.
- Gobernanza continua de documentación cruzada entre `Ecosistema-core-admin`, `Ecosistema-bd` y `Ecosistema-presentacion` para evitar contradicciones.

## Validación de integridad del documento

- No incluye secretos ni datos reales.
- No marca como “terminado productivo” lo que está definido como read-only/dry-run/controlled.
- Mantiene trazabilidad a documentación existente de Core Admin.
