# Core Admin — Límites de documentación (guardrails)

## Objetivo

Evitar que prompts o documentos de presentación pública/comercial terminen versionados en `Ecosistema-core-admin`.

## Qué documentación **sí** pertenece a Core Admin

Este repo debe contener documentación:

- técnico-operativa del panel administrativo interno;
- de arquitectura, módulos, contratos, rutas, servicios y tablas reales;
- de seguridad, permisos, flags y hardening;
- de operación (runbooks, smoke checks, QA técnico, incidentes, troubleshooting);
- de estado funcional por módulo (`operativo`, `read-only`, `dry-run`, `controlled`, `roadmap`).

En resumen: documentación que describe cómo funciona y se opera Core Admin por dentro.

## Qué documentación pertenece a Ecosistema-presentacion

Todo material orientado a comunicación externa/comercial debe ir en:

- `jimmybackend/Ecosistema-presentacion`.

Ejemplos de contenido que **no** debe vivir en Core Admin:

- pitch comercial, storytelling de marca, brochure de ventas;
- textos de landing pública, propuesta de valor para público general;
- guiones de demo comercial y material de presentación para clientes;
- copy de marketing y narrativa no técnica.

## Qué hacer si se ejecuta por error un prompt comercial en Core Admin

1. **No mergear** esos cambios.
2. Revertir/eliminar los archivos comerciales del branch actual.
3. Mover o recrear ese contenido en `Ecosistema-presentacion`.
4. Confirmar que en Core Admin sólo queden docs técnico-operativas.
5. Dejar nota breve en el PR indicando que se aplicó este guardrail.

## Archivos a revisar antes de merge (quick check)

- `README.md` (sección de alcance y separación con repo de presentación).
- `docs/project/PRESENTATION_REPOSITORY_POINTERS.md`.
- `docs/project/CORE_ADMIN_PRESENTATION_DOCS_AUDIT.md`.
- `docs/project/CORE_ADMIN_DOCS_BOUNDARIES.md` (este documento).
- `docs/project/CORE_ADMIN_CONTRIBUTING_NOTES.md`.

## Regla práctica de decisión

Si un documento está pensado para vender, presentar o comunicar públicamente, **no va en Core Admin**.
Si está pensado para operar, auditar o mantener técnicamente el sistema interno, **sí va en Core Admin**.
