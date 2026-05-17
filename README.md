# Ecosistema Core Admin

Aplicación administrativa interna del ecosistema, demostrable públicamente con lenguaje honesto sobre su estado real.

## Documentos clave para demo pública

- `docs/demo.md`
- `docs/estado_real.md`
- `docs/modulos.md`
- `docs/flujo_operativo.md`
- `contacto.md`
- `assets/README.md`

## Mensaje oficial de estado

- Hay base operativa estable (auth/core/system/auditoría).
- Módulos extendidos operan por capas: `read-only`, `dry-run`, `controlled`.
- **No** se comunica como SaaS completo en esta etapa.
- **No** se afirma que Billing, Integrations, Support o Workers estén terminados.
- La IA se presenta con gobierno humano (no autonomía total).

- Contrato de descarga Drive (estado contractual, no productivo por defecto): `docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md`

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
