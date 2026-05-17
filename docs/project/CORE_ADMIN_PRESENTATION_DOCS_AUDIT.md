# CORE ADMIN — Auditoría de documentación comercial mal ubicada (PRs #175–#181)

Fecha: 2026-05-17  
Repositorio auditado: `jimmybackend/Ecosistema-core-admin`

## Objetivo

Inventariar documentación de carácter comercial/presentación pública incorporada en `Ecosistema-core-admin` durante la ventana de PRs #175–#181, para definir correcciones en PRs posteriores **sin borrar ni mover archivos en este PR**.

## Alcance revisado

Se auditó el conjunto solicitado:

- `docs/estado_modulos.md`
- `docs/modulos.md`
- `docs/faq.md`
- `docs/flujo_operativo.md`
- `contacto.md`
- `docs/politica_contacto_publico.md`
- `docs/diagramas.md`
- `assets/`
- `plan_trabajo_pr_175_181.md`

También se validó historial reciente para este alcance (commits con prefijo `docs(presentation)` y relacionados).

## Criterios de clasificación

- `mantener_en_core_admin`: documentación técnica/operativa interna necesaria para ejecutar, gobernar o auditar Core Admin.
- `mover_a_presentacion`: contenido principalmente narrativo/comercial o de demo pública que encaja mejor en `Ecosistema-presentacion`.
- `duplicar_en_presentacion_y_reemplazar_por_enlace`: mantener versión mínima interna (si aporta contexto operativo) y alojar la versión principal en presentación.
- `eliminar_si_ya_existe_en_presentacion`: retirar en Core Admin solo cuando exista copia canónica equivalente en presentación.
- `requiere_revision_manual`: hay dependencia de decisiones de producto/compliance o falta confirmar estado canónico en otro repo.

## Inventario y decisión

| Archivo / Ruta | Clasificación | Motivo | Riesgo si se queda en core-admin | Acción recomendada |
|---|---|---|---|---|
| `docs/estado_modulos.md` | `mover_a_presentacion` | Matriz redactada en lenguaje comercial para demo externa (“estado comercial”, “qué mostrar/no prometer”). | Desplaza el foco del repo interno a material de pitch; aumenta drift entre verdad técnica y narrativa comercial. | Migrar al repo de presentación y dejar en Core Admin solo referencia técnica a matrices internas de estado real. |
| `docs/modulos.md` | `mover_a_presentacion` | Tabla “obligatoria para demo/comercial”; orientada a storytelling de venta. | Puede ser tomada erróneamente como contrato técnico estable del sistema. | Mover al repo de presentación; en Core Admin mantener documentación técnica por módulo (rutas, flags, permisos). |
| `docs/faq.md` | `duplicar_en_presentacion_y_reemplazar_por_enlace` | FAQ útil para comunicación externa; parte del contenido sirve como guía de expectativas para soporte interno. | Duplicidad y divergencia de mensajes públicos entre repos. | Definir versión canónica en presentación, y dejar en Core Admin una versión breve interna o enlace al FAQ canónico. |
| `docs/flujo_operativo.md` | `duplicar_en_presentacion_y_reemplazar_por_enlace` | Mezcla visión de demo pública con advertencias operativas reales (flags/permisos). | Si queda solo comercial, puede ocultar límites técnicos; si se duplica sin control, habrá incoherencia. | Partir en: (a) narrativa pública en presentación, (b) anexo operativo técnico en Core Admin; enlazar entre ambos. |
| `contacto.md` | `mover_a_presentacion` | Documento de “contacto oficial” para publicación pública, no operación interna de admin. | Exposición innecesaria de política de contacto en repo técnico; ruido documental. | Mover a presentación y dejar enlace en README de presentación. En Core Admin conservar solo referencia de compliance si aplica. |
| `docs/politica_contacto_publico.md` | `requiere_revision_manual` | Es política de publicación (compliance/comunicación), potencialmente transversal a varios repos. | Si se elimina/mueve sin gobernanza, puede romper controles de privacidad/publicación. | Validar ownership (compliance/comms). Si política es global, ubicar en repo/política corporativa y enlazar desde ambos repos. |
| `docs/diagramas.md` | `duplicar_en_presentacion_y_reemplazar_por_enlace` | Diagramas de alto nivel útiles para presentación; también sirven para onboarding técnico ligero. | Riesgo de obsolescencia si el material visual de presentación evoluciona sin sincronía técnica. | Mantener versión técnica mínima en Core Admin (arquitectura real), y versión narrativa en presentación con enlace cruzado. |
| `assets/README.md` | `mover_a_presentacion` | Guía de assets visuales para material público. | Incentiva mantener recursos de marketing en repo de operación. | Mover al repo de presentación con su pipeline de aprobación visual. |
| `assets/diagrams/README.md` | `duplicar_en_presentacion_y_reemplazar_por_enlace` | Define fuente editable de diagramas (`.mmd`), útil para mantenimiento técnico y también para slides públicas. | Puede generar forks de diagramas sin trazabilidad entre repos. | Mantener fuente técnica en Core Admin si describe operación real; publicar export/copia curada en presentación y documentar flujo de sync. |
| `assets/diagrams/flujo_operativo.mmd` | `duplicar_en_presentacion_y_reemplazar_por_enlace` | Diagrama reutilizable para demo y para discusión técnica de flujo. | Deriva semántica entre versión demo y versión operacional. | Declarar versión canónica (sugerido: técnica en Core Admin) y exportar copia para presentación. |
| `assets/diagrams/modulos_ecosistema.mmd` | `duplicar_en_presentacion_y_reemplazar_por_enlace` | Diagrama conceptual transversal del ecosistema; doble uso técnico/comercial. | Ambigüedad sobre “estado real” vs “visión comercial”. | Separar labels “actual/target” y enlazar versión pública en presentación. |
| `plan_trabajo_pr_175_181.md` | `mantener_en_core_admin` | Plan de ejecución de PRs en este repo; artefacto de trazabilidad interna. | Bajo: corresponde al historial operativo del repo. | Mantener en Core Admin como evidencia de alcance y decisiones. |

## Priorización sugerida para PRs siguientes

1. **PR A (bajo riesgo):** mover documentos claramente comerciales (`docs/estado_modulos.md`, `docs/modulos.md`, `contacto.md`, `assets/README.md`) a `Ecosistema-presentacion`.
2. **PR B (riesgo medio):** resolver duplicados controlados (`docs/faq.md`, `docs/flujo_operativo.md`, `docs/diagramas.md`, `assets/diagrams/*`) con política de canonicidad + enlaces.
3. **PR C (gobernanza):** decidir ubicación final de `docs/politica_contacto_publico.md` con responsables de compliance/comunicación.

## No cambios ejecutados en este PR

- No se borró ningún archivo.
- No se movieron rutas entre repositorios.
- No se modificó lógica productiva.

Este PR deja inventario y recomendación para ejecutar la corrección en PRs posteriores de forma controlada.
