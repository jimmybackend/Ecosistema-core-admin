# Punteros canónicos de documentación pública

## Repositorio canónico de presentación

La documentación pública/comercial del proyecto vive en:

- `jimmybackend/Ecosistema-presentacion`

Este repositorio (`Ecosistema-core-admin`) **no es** la fuente principal de narrativa comercial, pitch ni material público.

## Qué se conserva en Core Admin

En Core Admin se conserva únicamente documentación orientada a operación real:

- estado técnico-operativo por módulo,
- seguridad y compliance técnico,
- rutas, permisos, flags y modos de ejecución,
- runbooks y checklists de operación,
- trazabilidad de decisiones técnicas.

## Regla de canonicidad

Cuando exista contenido de doble uso (presentación + operación), aplicar:

1. Versión pública/corporativa en `Ecosistema-presentacion`.
2. Versión técnica mínima y verificable en `Ecosistema-core-admin`.
3. Enlace cruzado entre ambos repos para evitar drift documental.

## Nota de transición

Esta limpieza asume el prerequisito indicado en el plan: que el contenido comercial ya fue migrado o existe en `Ecosistema-presentacion` (PR #201 o equivalente).

Si alguna pieza no estuviera aún publicada allí, marcarla como `requiere_revision_manual` antes de eliminar contenido técnico útil en Core Admin.
