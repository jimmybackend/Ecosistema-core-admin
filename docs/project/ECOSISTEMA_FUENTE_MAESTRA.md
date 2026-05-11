# ECOSISTEMA — FUENTE MAESTRA DE DESARROLLO (Core Admin)

## 1) Propósito

Este documento establece la **fuente maestra de contexto** para guiar el desarrollo por capas de `Ecosistema-core-admin` como primera aplicación operativa del Ecosistema Esforzados.

Su función es alinear al desarrollador, ChatGPT y Codex para trabajar mediante **PRs pequeños, secuenciales y auditables**, evitando mezclar responsabilidades entre repositorios.

---

## 2) Repositorios oficiales

### Repositorio actual (activo)
- `jimmybackend/Ecosistema-core-admin`

### Repositorio de base de datos (canónico)
- `jimmybackend/Ecosistema-bd`

### Repositorios futuros sugeridos (no crear todavía)
- `jimmybackend/Ecosistema-api`
- `jimmybackend/Ecosistema-workers`
- `jimmybackend/Ecosistema-frontend`

---

## 3) Responsabilidad de cada repositorio

## `Ecosistema-bd` (solo base de datos)
Responsabilidad exclusiva:
- base principal `ecosistema`;
- SQL unificado;
- documentación DBA;
- validaciones;
- health checks;
- seeds DEV/QA;
- módulos canónicos y transversales.

**Regla:** este repositorio es la única fuente de verdad para estructura de datos.

## `Ecosistema-core-admin` (primera app operativa)
Responsabilidad:
- consola administrativa base;
- login;
- tenants;
- usuarios;
- roles;
- permisos;
- módulos;
- health checks;
- logs;
- auditoría;
- Mail mínimo;
- Cloud mínimo;
- configuración inicial.

## `Ecosistema-api` (futuro, no crear todavía)
Responsabilidad futura:
- API formal del Ecosistema;
- integraciones externas;
- clientes externos;
- apps móviles.

## `Ecosistema-workers` (futuro, no crear todavía)
Responsabilidad futura:
- jobs;
- colas;
- procesos en segundo plano;
- indexación;
- reportes programados;
- tareas IA.

## `Ecosistema-frontend` (futuro, no crear todavía)
Responsabilidad futura:
- frontend público;
- landing pages;
- clientes;
- portales externos.

---

## 4) Reglas canónicas de datos

1. `Ecosistema-bd` es **solo base de datos**.
2. `Ecosistema-core-admin` es la **primera aplicación operativa**.
3. No modificar base de datos desde PRs de `Ecosistema-core-admin`.
4. No inventar tablas, columnas, índices, catálogos ni relaciones.
5. Toda implementación funcional debe basarse en SQL real de `Ecosistema-bd` o dump real exportado desde phpMyAdmin.
6. Cualquier divergencia detectada entre código y SQL canónico se resuelve a favor de la fuente de datos real.
7. Si un módulo requiere estructuras no existentes en SQL canónico, se documenta como pendiente y no se implementa de forma inventada.

---

## 5) Módulos canónicos (núcleo funcional)

Los módulos canónicos para el arranque de Core Admin son:
- Auth (login y sesión);
- Tenants;
- Usuarios;
- Roles y permisos;
- Módulos del sistema.

Estos módulos se construyen de forma incremental y solo cuando exista sustento en datos canónicos.

---

## 6) Módulos transversales adicionales

Componentes transversales esperados dentro de Core Admin:
- Health checks;
- Logs del sistema;
- Auditoría;
- Mail mínimo;
- Cloud mínimo;
- Onboarding base.

Su implementación debe respetar el orden por capas y no bloquear el núcleo funcional.

---

## 7) Sistema visual

Core Admin deberá utilizar un **sistema UI unificado** con:
- consistencia de layout;
- componentes reutilizables;
- nomenclatura coherente;
- base para escalabilidad visual.

Alcance actual: consolidar base visual operativa para panel administrativo, sin extender a frontend público.

---

## 8) Sistema base a construir (Core Admin)

El sistema base en este repositorio debe cubrir, de manera progresiva:
- estructura inicial del proyecto;
- configuración y conexión PDO;
- autenticación;
- gestión de tenants;
- gestión de usuarios;
- gestión de roles/permisos;
- gestión de módulos;
- capacidades de observabilidad mínima (health, logs, auditoría);
- servicios mínimos (mail/cloud);
- flujo de onboarding base.

---

## 9) Capas de desarrollo

Desarrollo obligatorio por capas:

- **Capa 0 — Base de datos**
- **Capa 1 — Sistema UI unificado**
- **Capa 2 — Estructura base del proyecto**
- **Capa 3 — Conexión PDO y configuración**
- **Capa 4 — Auth**
- **Capa 5 — Tenants**
- **Capa 6 — Usuarios**
- **Capa 7 — Roles y permisos**
- **Capa 8 — Módulos**
- **Capa 9 — System**
- **Capa 10 — Mail mínimo**
- **Capa 11 — Cloud mínimo**
- **Capa 12 — Onboarding base**

Cada capa debe validarse antes de avanzar a la siguiente.

---

## 10) Orden de trabajo recomendado para Codex

1. Confirmar alcance del PR (una capa o parte acotada de una capa).
2. Verificar que la capa objetivo no invada responsabilidades de otros repositorios.
3. Revisar estructuras reales en `Ecosistema-bd` (SQL/dump) antes de implementar lógica de datos.
4. Implementar cambios mínimos necesarios para la capa.
5. Validar (checks técnicos y revisión funcional básica).
6. Documentar supuestos, límites y pendientes.
7. Abrir PR pequeño con título/alcance preciso.

**Política operativa:** no mezclar varias capas críticas en un solo PR si compromete revisión o trazabilidad.

---

## 11) Qué NO debe hacerse todavía

- No crear backend separado (si el PR es solo documental o de capa no correspondiente).
- No crear API separada.
- No crear workers.
- No construir CRM primero.
- No crear frontend público.
- No tocar `Ecosistema-bd` desde este repositorio.
- No borrar la carpeta `sistema de modos`.
- No declarar producción final.

---

## 12) Criterio para avanzar de capa

Se puede avanzar cuando:
1. la capa actual cumple su objetivo mínimo verificable;
2. no introduce deuda estructural evidente;
3. respeta datos canónicos de `Ecosistema-bd`;
4. tiene documentación breve de decisiones;
5. fue integrada mediante PR pequeño y revisable.

Si falta cualquiera de estos puntos, la capa se considera en progreso y no cerrada.

---

## 13) Estado actual del proyecto

Estado de referencia para esta guía:
- `Ecosistema-core-admin` es la primera aplicación operativa en construcción;
- la evolución será por PRs pequeños, capa por capa;
- no se deben mezclar responsabilidades con `Ecosistema-bd`;
- no corresponde crear aún API/workers/frontend público;
- no se considera estado de producción final.

---

## Nota de continuidad

Este documento es una guía viva de continuidad para **desarrollador + ChatGPT + Codex**. Cualquier ajuste de alcance debe respetar la separación de responsabilidades entre repositorios y el avance secuencial por capas.
