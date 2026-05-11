# Ecosistema UI Unificado

## 1) Propósito
Unificar el sistema visual de **Ecosistema Core Admin** en un único CSS principal reusable para módulos administrativos multi-tenant.

## 2) Fuentes de referencia (`Sistema de Modos - Dashboard css v2.1`)
- `dashboard.css`
- `dashboardv1.css`
- `dashboardv2.css`
- `ejemplo.html`
- `ejemplo - Copy.html`
- `script.js`

## 3) Estructura final
- `public/assets/css/ecosistema-ui.css` (sistema principal)
- `public/assets/js/ecosistema-ui-demo.js` (demo no negocio)
- `public/examples/ui-dashboard.html`
- `public/examples/ui-components.html`
- `docs/ui/ECOSISTEMA_UI_UNIFICADO.md`

## 4) Tokens CSS
Incluye categorías: marca, superficies, texto, bordes, sombras, scrollbar, spacing, tipografía, radios, transiciones, z-index y breakpoints.

## 5) Temas disponibles
- `theme-light`
- `theme-dark`
- `theme-high-contrast`
- `theme-reading`
- `theme-protanopia`
- `theme-deuteranopia`
- `theme-astigmatism`
- `theme-cataracts`
- `theme-glaucoma`
- `theme-macular`
- `theme-blur`
- `theme-light-sensitivity`
- `theme-myopia`
- `theme-strabismus`
- `theme-autism`
- `theme-epilepsy`

Todos mapean variables finales: `--bg-primary`, `--bg-secondary`, `--bg-tertiary`, `--text-primary`, `--text-secondary`, `--text-muted`, `--border`, `--shadow`, `--scrollbar`, `--scrollbar-thumb`.

## 6) Componentes
Layout (header/sidebar/main/footer), nav con submenús, botones, forms, checkbox/radio/switch, select, textarea, file, alerts, cards/stat cards, tabs, tables, badges, status, accordion, progress, modal, dropdown, pagination y utilities base.

## 7) Accesibilidad
- `:focus-visible` consistente.
- `prefers-reduced-motion` activo.
- alto contraste, modo lectura, sensibilidad a luz.
- estados no dependen solo de color (íconos/patrones/etiquetas en ejemplos).

## 8) Responsive
Breakpoints: 1200 / 992 / 768 / 576.
- sidebar fija desktop, colapsable móvil.
- tablas con scroll horizontal.
- cards y formularios adaptables.

## 9) Reglas para futuros módulos
- Reutilizar clases `eco-*`.
- Mantener compatibilidad progresiva con `.header`, `.sidebar`, `.main-content`, `.card`, `.btn`, `.form-control`, `.data-table`, `.alert`, `.modal`.
- Evitar CSS paralelo para misma responsabilidad.

## 10) Qué no debe hacerse
No backend/API/workers/DB. No frameworks CSS externos. No declarar producción final. No borrar carpeta de referencia sin autorización.

## 11) Estado final
**Listo para integración visual inicial**. Pendiente: conexión backend, validación de accesibilidad final y adopción en pantallas reales Core/Admin.
