# CORE ADMIN — Demo Readiness Checklist (controlado)

Checklist operativo para validar si Core Admin está listo para demo controlada de fin de semana (sábado/domingo), sin riesgos de exposición ni ejecuciones no deseadas.

> Regla general: **si no se puede demostrar seguridad operativa y control de alcance, es NO-GO**.

---

## 1) Ambiente local / VM

- [ ] Entorno objetivo definido (local o VM) y estable por al menos una corrida completa de demo.
- [ ] Dependencias instaladas (`composer install`) sin errores críticos.
- [ ] Servidor local/VM levanta correctamente (`php -S 127.0.0.1:8000 -t public` o equivalente).
- [ ] Hora/zona horaria del host verificadas para evitar confusión en logs/auditoría.
- [ ] Capacidad de rollback rápido (reinicio de app, limpieza de cache, restauración de estado demo).

## 2) `.env` seguro

- [ ] Archivo `.env` usado solo en entorno local/VM; no commit de secretos.
- [ ] `.env.example` se mantiene como plantilla limpia (sin secretos reales).
- [ ] Flags sensibles en modo seguro por defecto (sin activaciones accidentales).
- [ ] No hay tokens/API keys reales visibles en pantalla durante demo.
- [ ] Logs de aplicación no imprimen secretos (tokens, passwords, secrets de proveedores).

## 3) DB configurada

- [ ] Variables `DB_*` apuntan a base de datos de demo (no productiva).
- [ ] Conexión de base validada (`/health/db` o verificación equivalente).
- [ ] Migraciones/estructura consistentes con el estado actual del proyecto.
- [ ] Dataset de demo cargado con datos sintéticos/no sensibles.
- [ ] Plan de reset de DB preparado antes de cada demo.

## 4) Usuario inicial

- [ ] Existe usuario administrador de demo funcional.
- [ ] Credenciales de demo almacenadas de forma segura y no compartidas públicamente.
- [ ] Validado login y logout con ese usuario.
- [ ] Sesión expira/cierra correctamente al hacer logout.

## 5) Roles/permisos mínimos

- [ ] Perfil admin de demo con permisos mínimos necesarios para el recorrido acordado.
- [ ] Validado que usuarios sin permiso no acceden a rutas restringidas.
- [ ] No hay permisos “super-admin total” habilitados innecesariamente para la demo.
- [ ] Flujo de autorización (RBAC) probado en al menos un caso permitido y uno denegado.

## 6) Flags peligrosas apagadas

- [ ] SMTP/envío real desactivado.
- [ ] Integraciones AWS/S3 remotas desactivadas.
- [ ] Escrituras sensibles por módulo desactivadas cuando no sean necesarias.
- [ ] Ejecución real de workflow desactivada (usar dry-run/controlado).
- [ ] IA externa/escritura autónoma desactivada por defecto.

## 7) Rutas públicas verificadas

- [ ] Rutas públicas necesarias para la demo responden correctamente.
- [ ] No existen rutas públicas inesperadas expuestas para administración interna.
- [ ] Validado comportamiento esperado en rutas de login/dashboard/core.
- [ ] Rutas condicionadas (read-only/dry-run) distinguidas claramente en narrativa.

## 8) No exposición de secretos

- [ ] No mostrar `.env`, llaves privadas, credenciales o tokens en pantalla compartida.
- [ ] No usar cuentas reales de proveedores externos durante demo.
- [ ] Capturas/pantallas a compartir revisadas para evitar leaks.
- [ ] Consola/terminal limpia de variables sensibles antes de presentar.

## 9) No exposición de PII

- [ ] Datos usados en demo son ficticios o anonimizados.
- [ ] No se muestran correos/teléfonos/documentos reales de personas.
- [ ] Logs/auditoría revisados para evitar PII en pantallas.
- [ ] Exportaciones/reportes mostrados en demo no contienen PII real.

## 10) Smoke-check

- [ ] `composer smoke` ejecuta sin fallos bloqueantes.
- [ ] Si smoke reporta warning, documentado y aceptado explícitamente antes de demo.
- [ ] Resultado de smoke guardado (nota o captura interna) para trazabilidad.

## 11) Prueba manual de login/dashboard

- [ ] Login exitoso en `/login` con usuario de demo.
- [ ] Dashboard carga completo en `/dashboard`.
- [ ] Logout funciona y redirige adecuadamente.

## 12) Prueba manual de permisos

- [ ] Caso permitido: usuario con permiso accede a módulo core esperado.
- [ ] Caso denegado: usuario sin permiso recibe bloqueo/control correcto.
- [ ] Rutas críticas de administración respetan middleware/autorización.

## 13) Prueba manual de módulos read-only

- [ ] Navegación de módulos read-only funciona sin errores de UI.
- [ ] No hay operaciones de escritura involuntarias en esos módulos.
- [ ] Mensajería/narrativa de “solo lectura” es clara durante demo.

## 14) Prueba manual de dry-run

- [ ] Flujos dry-run disponibles responden correctamente.
- [ ] Resultado dry-run muestra simulación, no ejecución real.
- [ ] Se comunica explícitamente que dry-run no implica impacto productivo.

## 15) Prueba manual de controlled bloqueado por default

- [ ] Flujos “controlled” permanecen bloqueados por defecto sin flag/permiso explícito.
- [ ] Intento de ejecución sin habilitación devuelve respuesta controlada.
- [ ] Solo se habilita temporalmente lo estrictamente necesario (si aplica), con rollback definido.

---

## Go / No-Go

### ✅ GO (apto para demo)
Marcar GO solo si **todas** las condiciones se cumplen:
- [ ] 100% de checks críticos completados (seguridad, permisos, flags, smoke, login/dashboard).
- [ ] Sin exposición de secretos ni PII.
- [ ] Sin integraciones externas reales activadas accidentalmente.
- [ ] Recorrido de demo ensayado de punta a punta (mínimo 1 vez).

### ❌ NO-GO (no mostrar todavía)
Declarar NO-GO si ocurre cualquiera de estos casos:
- [ ] Fallo en autenticación, dashboard o smoke-check crítico.
- [ ] Duda sobre exposición de secretos/PII.
- [ ] Flags peligrosas activadas sin necesidad clara.
- [ ] Permisos inconsistentes o bypass observable.
- [ ] Rutas críticas con errores no mitigados.

---

## Pendientes técnicos antes de producción

> Esta checklist habilita demo controlada, **no** certifica producción.

- Completar suite E2E integral con DB real representativa (incluyendo casos de permisos y errores).
- Endurecer observabilidad/alertas para incidentes de seguridad y acceso.
- Formalizar proceso de gestión/rotación de secretos y auditoría periódica de configuración.
- Cerrar matriz de activación por flags con criterios de rollout/rollback por módulo.
- Validar hardening final de integraciones externas (SMTP, AWS/S3, IA proveedor externo).
- Ejecutar pruebas de carga básica y resiliencia en rutas críticas.
- Definir runbook operativo de incidentes para operación productiva.
- Completar revisión legal/compliance sobre datos sensibles y retención.

---

## Uso sugerido (sábado/domingo)

1. Ejecutar checks de las secciones 1–10.
2. Ensayar recorrido manual con secciones 11–15.
3. Marcar Go/No-Go con evidencia breve (notas/capturas internas).
4. Si NO-GO: corregir, repetir smoke y re-ejecutar checklist.
