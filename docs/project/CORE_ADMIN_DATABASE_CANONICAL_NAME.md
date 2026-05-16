# Core Admin — Nombre canónico de base de datos

## Objetivo

Aclarar qué nombre de base debe usar **Core Admin** en instalación, demo y operación diaria.

## Nombre canónico de base de datos

- **Nombre operativo real/canónico actual:** `adbbmis1_eco`.
- **Nombre conceptual/legacy:** `ecosistema` (solo como referencia histórica/documental).

Regla de oro: para conexión runtime de Core Admin, siempre prevalece `DB_DATABASE` en `.env`.

## Regla documental única para este repositorio

- Core Admin **no hardcodea** el nombre de base de datos en código.
- El nombre efectivo siempre se toma desde `.env` mediante `DB_DATABASE` (ver `config/database.php`).
- Para esta instalación/referencia operativa actual (VM/producción documentada), el valor esperado es `adbbmis1_eco`.

## Valor recomendado en plantillas

Actualmente las plantillas del proyecto ya apuntan a `adbbmis1_eco`:

- `.env.example` → `DB_DATABASE=adbbmis1_eco`
- `.env.vm.example` → `DB_DATABASE=adbbmis1_eco`

Si un entorno requiere otro nombre, debe cambiarse **solo** en su `.env` local/deploy, no en lógica de aplicación.

## Sobre referencias históricas a `ecosistema`

En documentación histórica del ecosistema puede aparecer `ecosistema` como nombre unificado/base conceptual.

Para **Core Admin**, eso no sustituye la configuración real: siempre prevalece el valor de `DB_DATABASE` en `.env` (hoy, normalmente `adbbmis1_eco`).

## Qué hacer / qué evitar

### Hacer
- Configurar `DB_DATABASE` explícitamente por entorno.
- Documentar cualquier diferencia de entorno en docs de deploy.

### Evitar
- Hardcodear nombres de base en código PHP, scripts o consultas.
- Asumir que todas las instalaciones usan literalmente `ecosistema`.

## Referencias

- `config/database.php`
- `.env.example`
- `.env.vm.example`
- `docs/project/ECOSISTEMA_FUENTE_MAESTRA.md`

## Verificación documental (smoke-check)

Para evitar regresiones de naming, este repositorio incluye guardrails automáticos en `scripts/smoke-check.php` y un check manual recomendado:

```bash
rg -n "adbbmis1_eco|USE\\s+ecosistema\\s*;|DB_DATABASE=" \
  README.md .env.example .env.vm.example \
  docs/deploy/CORE_ADMIN_VM_RUNBOOK.md docs/deploy/EC2_PRODUCTION_CHECKLIST.md \
  docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md
```

Interpretación esperada:
- `adbbmis1_eco` debe aparecer como referencia operativa.
- `USE ecosistema;` no debe aparecer en artefactos operativos críticos.
- `ecosistema` solo es aceptable cuando se etiqueta explícitamente como histórico/conceptual.
