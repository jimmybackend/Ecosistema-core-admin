# Ecosistema Core Admin

Aplicación administrativa interna del ecosistema. Este repositorio conserva la base técnico-operativa y documentación de referencia con lenguaje honesto sobre el estado real por módulo.

## Índice público/comercial recomendado

- Estado por módulo: [`docs/estado_modulos.md`](docs/estado_modulos.md)
- Catálogo de módulos: [`docs/modulos.md`](docs/modulos.md)
- Flujo operativo: [`docs/flujo_operativo.md`](docs/flujo_operativo.md)
- Diagramas base: [`docs/diagramas.md`](docs/diagramas.md)
- FAQ: [`docs/faq.md`](docs/faq.md)
- Contacto oficial: [`contacto.md`](contacto.md)
- Política de contacto público: [`docs/politica_contacto_publico.md`](docs/politica_contacto_publico.md)
- Reglas de assets: [`assets/README.md`](assets/README.md)
- Diagrams assets: [`assets/diagrams/README.md`](assets/diagrams/README.md)

## Mensaje oficial de estado

- Hay base operativa estable (auth/core/system/auditoría).
- Módulos extendidos operan por capas: `Read-only`, `Dry-run`, `Controlled` y/o estado `Parcial`.
- **No** se comunica como SaaS completo en esta etapa.
- **No** se afirma que Billing, Integrations, Support o Workers estén terminados.
- La IA se presenta con gobierno humano (no autonomía total).

## Contrato técnico relacionado

- Contrato de descarga Drive (estado contractual, no productivo por defecto):
  - `docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md`

## Activos visuales

- Si no hay imágenes aprobadas, se debe declarar explícitamente que los assets visuales están pendientes.
- No inventar capturas/screenshots ni usar datos reales en material visual.

## Validación rápida

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
composer smoke
```
