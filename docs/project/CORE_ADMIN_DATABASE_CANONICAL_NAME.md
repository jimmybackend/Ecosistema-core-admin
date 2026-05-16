# Core Admin — Nombre canónico de base de datos

## Objetivo

Aclarar qué nombre de base debe usar **Core Admin** en instalación, demo y operación diaria.

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
