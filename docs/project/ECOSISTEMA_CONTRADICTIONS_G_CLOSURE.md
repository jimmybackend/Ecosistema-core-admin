# ECOSISTEMA_CONTRADICTIONS_G_CLOSURE

> Checklist final de cierre de contradicciones G (base de numeración posterior al PR #181).
> Fecha de verificación documental: 2026-05-16.

## Alcance
Este checklist cierra (o deja explícitamente mitigado/pendiente) los 4 puntos de contradicción G con evidencia verificable en Core Admin, sin declarar pruebas en producción ni inventar esquema/rutas.

## Resultado ejecutivo
- 1) `core_role_permissions` vs Core Admin: **CERRADO**.
- 2) Nombre de DB inconsistente: **CERRADO**.
- 3) Presentación describe visión completa como operación completa: **MITIGADO**.
- 4) README mezcla base estable con capacidades nuevas: **CERRADO**.

---

## 1) `core_role_permissions` no alinea con Core Admin
- **Estado**: **CERRADO**.
- **Archivo revisado/modificado (evidencia)**:
  - `docs/auth/AUTH_PERMISSIONS_SCHEMA_ALIGNMENT.md`
  - `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- **Evidencia concreta**:
  - Se documenta explícitamente que `core_role_permissions.tenant_id` se inserta siempre y que lecturas/borrados filtran por `role_id + tenant_id`.
  - El mapa ruta-servicio-tabla de Core Admin describe el mismo comportamiento de asignación rol↔permiso con `tenant_id` derivado del rol, no libre por request.
- **Prueba ejecutada**:
  - `rg -n "core_role_permissions|tenant_id" docs/auth/AUTH_PERMISSIONS_SCHEMA_ALIGNMENT.md docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- **Limitación remanente**:
  - La evidencia en este cierre es documental/técnica; no incluye corrida E2E integral en ambiente de producción.
- **Referencia interna**:
  - Ver también `docs/project/ECOSISTEMA_RISK_H_CLOSURE.md` (fila de cierre de riesgo por `tenant_id` faltante).

## 2) Nombre de DB inconsistente
- **Estado**: **CERRADO**.
- **Archivo revisado/modificado (evidencia)**:
  - `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md`
  - `docs/deploy/EC2_PRODUCTION_CHECKLIST.md`
- **Evidencia concreta**:
  - Se fija la regla canónica: Core Admin toma el nombre efectivo de DB desde `DB_DATABASE` en `.env`.
  - Se mantiene referencia operativa esperada `adbbmis1_eco` en documentos de operación/deploy, sin hardcodearlo como obligación de código.
- **Prueba ejecutada**:
  - `rg -n "DB_DATABASE|adbbmis1_eco|config/database.php" docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md docs/deploy/EC2_PRODUCTION_CHECKLIST.md`
- **Limitación remanente**:
  - No se validó conexión contra producción en este PR (intencionalmente fuera de alcance).
- **Referencia interna**:
  - `README.md` y `docs/project/CORE_ADMIN_OPERATIONAL_CLOSURE.md` mantienen el mismo criterio.

## 3) Presentación describe visión completa como operación completa
- **Estado**: **MITIGADO**.
- **Archivo revisado/modificado (evidencia)**:
  - `docs/checklist_presentacion_publica.md`
  - `docs/project/CORE_ADMIN_SHOWCASE_DEMO_GUIDE.md`
  - `docs/estado_modulos.md`
- **Evidencia concreta**:
  - La documentación de presentación y estado separa explícitamente módulos `Disponible/Parcial/Read-only/Controlled/Roadmap`.
  - El guion de showcase incluye mensajes anti-“humo” y evita afirmar operación pública productiva por defecto.
- **Prueba ejecutada**:
  - `rg -n "Roadmap|Parcial|Read-only|no estamos afirmando operación pública productiva|sin exagerar capacidad operativa" docs/checklist_presentacion_publica.md docs/project/CORE_ADMIN_SHOWCASE_DEMO_GUIDE.md docs/estado_modulos.md`
- **Limitación remanente**:
  - Sigue siendo posible desalineación humana en demos futuras si no se usa el guion/checklists oficiales.
- **Siguiente PR recomendado**:
  - Forzar plantilla única de “estado operativo” en material de presentación para reducir variación narrativa.

## 4) README Core Admin mezcla base estable con capacidades nuevas
- **Estado**: **CERRADO** (con trazabilidad reforzada en este PR).
- **Archivo revisado/modificado (evidencia)**:
  - `README.md` (sección de referencias operativas complementarias actualizada para apuntar a este checklist final).
- **Evidencia concreta**:
  - README ya separa “Base estable” vs “Parcial/controlado” vs “No productivo completo/pendiente”.
  - Ahora incluye enlace directo al checklist final de contradicciones G para auditoría rápida.
- **Prueba ejecutada**:
  - `rg -n "Base estable|Parcial/controlado|No productivo completo|ECOSISTEMA_CONTRADICTIONS_G_CLOSURE" README.md`
- **Limitación remanente**:
  - El README sigue siendo resumen de alto nivel; el detalle operativo depende de documentos de estado por módulo.
- **Referencia interna**:
  - `docs/project/CORE_ADMIN_MODULE_STATUS.md`
  - `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`

---

## Evidencia de comandos del cierre
Comandos usados para consolidar evidencia en este PR:

1. `rg -n "core_role_permissions|tenant_id" docs/auth/AUTH_PERMISSIONS_SCHEMA_ALIGNMENT.md docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
2. `rg -n "DB_DATABASE|adbbmis1_eco|config/database.php" docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md docs/deploy/EC2_PRODUCTION_CHECKLIST.md`
3. `rg -n "Roadmap|Parcial|Read-only|no estamos afirmando operación pública productiva|sin exagerar capacidad operativa" docs/checklist_presentacion_publica.md docs/project/CORE_ADMIN_SHOWCASE_DEMO_GUIDE.md docs/estado_modulos.md`
4. `rg -n "Base estable|Parcial/controlado|No productivo completo|ECOSISTEMA_CONTRADICTIONS_G_CLOSURE" README.md`
5. `composer smoke` (si aplica en el entorno local con dependencias presentes).

## Cierre
Este documento deja trazabilidad verificable de contradicciones G para demo/showcase y mantenimiento, y reduce riesgo de reabrir las mismas contradicciones en PRs futuros.
