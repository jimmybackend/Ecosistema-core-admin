# Diagramas base para presentación

Este documento reúne material visual mínimo para explicar el producto de forma comercial y técnica **sin publicar datos reales**, sin capturas de producción y sin generar PDF por ahora.

## 1) Flujo operativo ideal

```mermaid
flowchart LR
    A[Captación/Entrada<br/>Landing + Formularios] --> B[Normalización<br/>Core Admin]
    B --> C[Enriquecimiento<br/>Reglas + Workflow]
    C --> D[Gestión comercial<br/>CRM/Campaigns]
    D --> E[Comunicación<br/>Mail/Notificaciones]
    E --> F[Medición<br/>Analytics + Reportes]
    F --> G[Optimización continua]
    G --> B

    H[Auditoría y Seguridad] -. supervisa .-> B
    H -. supervisa .-> C
    H -. supervisa .-> D
    H -. supervisa .-> E
```

## 2) Capas del ecosistema

```mermaid
graph TD
    U[Capa de experiencia<br/>UI/Admin + vistas operativas]
    S[Capa de servicios<br/>Workflow, CRM, URL Locator, Reports, AI, Drive]
    C[Capa canónica<br/>Base de datos + contratos de datos]
    G[Capa de gobierno<br/>Auth, permisos, auditoría, flags]

    U --> S --> C
    G --> U
    G --> S
    G --> C
```

## 3) Estados de módulo

```mermaid
stateDiagram-v2
    [*] --> ReadOnly
    ReadOnly --> DryRun: validación de reglas
    DryRun --> Controlled: habilitación por flags/permisos
    Controlled --> Productivo: evidencia operativa y monitoreo

    Productivo --> Controlled: rollback controlado
    Controlled --> DryRun: fallback seguro
    DryRun --> ReadOnly: desactivación temporal
```

## 4) Relación Presentación, Core Admin y Base canónica

```mermaid
flowchart TB
    P[Repo Presentación<br/>material comercial y narrativa] --> A[Core Admin<br/>superficie operativa y módulos]
    A --> B[Base canónica<br/>fuente de verdad de datos]

    P -. no escribe datos reales .-> B
    A -. lectura/escritura según estado del módulo .-> B
```

## Reglas de uso de estos diagramas

- Mantener contenido genérico y anonimizado.
- No incluir capturas de producción.
- No incluir PII, secretos, URLs privadas ni credenciales.
- No usar logos de terceros sin autorización explícita.
- Este material es base para futuras exportaciones (PDF/GitHub Pages), no entrega final.
