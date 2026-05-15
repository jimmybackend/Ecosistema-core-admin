# CORE ADMIN — Showcase Demo Guide (honesta y presentable)

Guía práctica para mostrar el Core Admin de forma profesional, clara y **sin vender humo**.

## 1) Mensaje corto de producto

> Core Admin es el panel operativo del ecosistema: hoy resuelve administración interna (acceso, gobierno, observabilidad y control), mientras varios módulos de crecimiento/automatización están visibles pero operan en modo **read-only**, **dry-run** o **controlled por flags**.

Versión corta para abrir demo:
- “Lo que van a ver aquí está separado entre operación real disponible hoy y capacidades preparadas/condicionadas para activación controlada.”

---

## 2) Qué se puede mostrar hoy (con confianza)

Basado en la matriz de estado y rutas reales:

- **Autenticación y sesión**: login/logout y control de acceso administrativo.
- **Dashboard**: entrada operativa del panel.
- **Core Admin estable**:
  - Tenants
  - Users
  - Roles
  - Permissions
  - Modules
- **System/Observabilidad**:
  - Health checks
  - Logs
  - Audit
- **Operación administrativa segura por defecto**:
  - Integraciones externas no productivas por defecto
  - Uso de flags y permisos para habilitar escrituras

---

## 3) Qué NO se debe prometer todavía

No comunicar como “listo para producción por defecto”:

- **AWS/S3 real activo por defecto** (no lo está).
- **SMTP/envío masivo real por defecto** (no lo está).
- **IA autónoma** (no existe: la IA está controlada y/o en dry-run, no actúa sola).
- **Escrituras abiertas** en módulos sensibles (están condicionadas por flags/permisos).
- **Cobertura completa e2e con DB real** (aún faltante).

Frase recomendada:
- “Aquí hay capacidades preparadas y navegables, pero la ejecución real depende de activación explícita, controles y hardening.”

---

## 4) Secuencia sugerida de demo (paso a paso)

### 4.1 Login
- Entrar por `/login`.
- Mensaje: “Partimos desde autenticación y sesión del panel interno.”

### 4.2 Dashboard
- Ir a `/dashboard`.
- Mensaje: “Este dashboard concentra estado operativo y acceso a módulos.”

### 4.3 Tenants / Users / Roles / Permissions
- Recorrer `/tenants`, `/users`, `/roles`, `/permissions`, `/modules`.
- Enfatizar RBAC y control administrativo.
- Mensaje: “La parte más madura hoy es gobierno de acceso y operación interna.”

### 4.4 System Health / Logs / Audit
- Mostrar `/system/health`, `/system/logs`, `/system/audit`, `/audit/events`.
- Mensaje: “Priorizamos trazabilidad y diagnóstico antes de ampliar automatizaciones.”

### 4.5 Drive (read-only / controlled)
- Mostrar `/cloud`, `/cloud/drive` y vistas relacionadas.
- Mensaje: “Drive existe con navegación y contratos, pero la integración remota/AWS está desactivada por defecto.”

### 4.6 URL / Landing / Analytics (read-only / dry-run)
- Mostrar `/url/locator`, `/landing`, `/browser/analytics`.
- Si aplica, enseñar rutas dry-run asociadas.
- Mensaje: “Aquí mostramos capacidades y simulaciones; no estamos afirmando operación pública productiva por defecto.”

### 4.7 CRM / Campaigns / Reports
- Recorrer `/crm`, `/campaigns`, `/reports/*`.
- Distinguir lectura, dry-run y control de escrituras.
- Mensaje: “Estos módulos están presentes y estructurados, con activación gradual y controlada.”

### 4.8 Workflow
- Mostrar `/workflow`, `/workflow/runs`, `/workflow/dry-run`.
- Mensaje: “Workflow está diseñado para ejecutar reglas, pero por defecto usamos simulación/control para evitar efectos no deseados.”

### 4.9 AI controlada
- Mostrar capacidades AI disponibles (assist y dry-runs).
- Mensaje: “IA asistida y controlada: no toma acciones autónomas sin autorización/flags.”

### 4.10 Seguridad / flags
- Cerrar mostrando enfoque de seguridad operativa: permisos + flags.
- Mensaje: “La regla es safe-by-default: sin SMTP real, sin S3 real y sin proveedor IA externo activo por defecto.”

---

## 5) Frases recomendadas para decir durante la demo

- “Preferimos claridad operativa sobre promesas.”
- “Si algo está en dry-run, lo decimos explícitamente.”
- “Read-only significa visibilidad; no implica ejecución productiva.”
- “Controlled por flags significa que podemos habilitar por etapas con rollback.”
- “No activamos integraciones externas por defecto; primero seguridad, luego escala.”
- “La IA sugiere/asiste; no ejecuta sola cambios críticos.”

---

## 6) Preguntas frecuentes técnicas (FAQ)

### ¿Está conectado a AWS/S3 real?
No por defecto. La integración remota está desactivada en configuración base y requiere habilitación explícita de flags + credenciales + controles.

### ¿Se pueden enviar campañas o correos masivos hoy?
No por defecto. El envío real SMTP/mail está apagado y existen flujos controlados/dry-run según módulo.

### ¿La IA puede actuar automáticamente sobre datos o campañas?
No por defecto. IA está pensada como asistencia controlada; la ejecución autónoma no está habilitada por diseño base.

### ¿Qué evita escrituras accidentales?
Combinación de permisos (RBAC), validaciones HTTP/CSRF en rutas administrativas y flags de escritura por módulo.

### ¿El sistema ya tiene pruebas e2e completas con DB real?
Aún no. Existe smoke técnico útil para consistencia base, pero no reemplaza suite e2e full con base real.

---

## 7) Riesgos honestos a declarar

- Riesgo de sobreinterpretar “pantallas visibles” como “módulos productivos completos”.
- Riesgo de demos sin aclarar diferencias entre read-only, dry-run y controlled.
- Riesgo de expectativas comerciales si no se explican límites de integraciones externas.
- Riesgo técnico actual: falta de e2e integral con DB real para validar punta-a-punta todos los flujos sensibles.

Mitigación narrativa:
- Repetir en demo: “visible no significa activo en producción.”

---

## 8) Checklist antes de mostrar

### Narrativa
- [ ] Tengo preparado el mensaje corto de producto.
- [ ] Tengo 2-3 frases para explicar read-only, dry-run y controlled.
- [ ] Tengo disclaimer explícito de límites actuales.

### Entorno/config
- [ ] Validé que flags sensibles siguen en modo seguro por defecto.
- [ ] Confirmé que no hay credenciales reales expuestas.
- [ ] Confirmé que no se hará envío real SMTP ni escritura remota S3 durante la demo.

### Recorrido
- [ ] Orden de demo definido (login → dashboard → core admin → system → módulos condicionados → seguridad).
- [ ] Rutas clave accesibles y datos demo cargados.
- [ ] Plan B preparado si un módulo condicionado no responde (mostrar dry-run/read-only).

### Cierre
- [ ] Reiterar qué está operativo hoy.
- [ ] Reiterar qué está controlado/roadmap.
- [ ] Acordar próximos pasos: hardening + pruebas e2e con DB real + activación gradual por flags.

---

## 9) Mensaje de cierre sugerido

> “Hoy Core Admin ya entrega valor real en administración, seguridad y observabilidad. Los módulos de crecimiento y automatización están presentes, pero su activación productiva es intencionalmente gradual, controlada por permisos y flags, para evitar riesgos operativos.”
