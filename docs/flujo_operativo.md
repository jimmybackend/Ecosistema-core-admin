# Flujo operativo (anexo técnico de Core Admin)

La narrativa de demo/presentación del flujo end-to-end es canónica en:

- `jimmybackend/Ecosistema-presentacion`

Este documento conserva únicamente el alcance operativo interno.

## Alcance operativo en Core Admin

- El flujo funcional está segmentado por modo (`read-only`, `dry-run`, `controlled`, productivo por entorno).
- Las acciones sensibles requieren permisos, flags y trazabilidad.
- La habilitación productiva se realiza por etapas y con controles de riesgo.

## Referencias técnicas

- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- `docs/project/ECOSISTEMA_FEATURE_FLAGS_AUDIT.md`
- `docs/project/ECOSISTEMA_ROUTE_SERVICE_VIEW_MATRIX.md`
- `docs/project/PRESENTATION_REPOSITORY_POINTERS.md`
