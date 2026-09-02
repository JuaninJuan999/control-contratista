# Control Contratista — Diagrama de flujo de trabajo

Documento complementario a [`DOCUMENTACION-Control-Contratistas.md`](DOCUMENTACION-Control-Contratistas.md).  
Describe los flujos operativos del sistema SIRT de Colbeef.

---

## 1. Flujo general del sistema

```mermaid
flowchart TB
    subgraph ENTRADA["Acceso al sistema"]
        A([Usuario abre la URL]) --> B{¿Sesión activa?}
        B -->|No| C[Pantalla de login]
        C --> D{¿Credenciales válidas<br/>y usuario activo?}
        D -->|No| C
        D -->|Sí| E[Dashboard]
        B -->|Sí| E
    end

    subgraph MODULOS["Módulos principales"]
        E --> F[Dashboard<br/>alertas y vencimientos]
        E --> G[Empresas]
        E --> H[Planillas SS]
        E --> I[Contratistas externos]
        E --> J[Contratistas internos]
        E --> K[Vehículos]
        E --> L[Usuarios]
        E --> M[Usabilidad]
        E --> N[Búsqueda global]
        E --> O[WorkColbeef<br/>enlace externo]
    end

    subgraph SALIDA["Cierre"]
        E --> P[Logout]
        P --> C
    end

    L -.->|solo admin / superadmin| L
    M -.->|solo superadmin| M
```

---

## 2. Flujo de autenticación y permisos por rol

```mermaid
flowchart TD
    A([POST /login]) --> B[Validar username + password]
    B --> C{¿Usuario activo?}
    C -->|No| D[Error: acceso denegado]
    C -->|Sí| E[Crear sesión en BD]
    E --> F[Registrar sesión de usabilidad]
    F --> G{¿Rol del usuario?}

    G -->|Superadministrador| H[Acceso total<br/>+ Usabilidad<br/>+ Eliminar usuarios]
    G -->|Administrador| I[Edición completa<br/>+ Usuarios<br/>+ Importar Excel]
    G -->|Operativo| J[Edición operativa<br/>CRUD sin módulo usuarios]
    G -->|Consulta| K[Solo lectura<br/>GET en listados]

    H --> L([Navegar módulos])
    I --> L
    J --> L
    K --> L

    L --> M{¿Intenta POST/PATCH/DELETE<br/>o create/edit?}
    M -->|Sí, rol consulta| N[403 Forbidden]
    M -->|No o rol con edición| O[Operación permitida]

    L --> P{¿Inactividad > 15 min?}
    P -->|Sí| Q[Cierre automático de sesión]
    Q --> A
```

---

## 3. Flujo de alta y clasificación de empresa

```mermaid
flowchart TD
    A([Nueva empresa]) --> B[Formulario: datos básicos<br/>nombre, NIT, teléfono, correos]
    B --> C{¿Tipo de empresa?}

    C -->|EXTERNA| D[Empresa externa]
    D --> D1[Registrar contratistas externos]
    D --> D2[Registrar vehículos]
    D --> D3[❌ Sin planilla SS<br/>❌ Sin fecha límite SS]

    C -->|INTERNA| E[Empresa interna]
    E --> F{¿Tipo de planilla SS?}

    F -->|DEPENDIENTE| G[Planilla por empresa]
    G --> G1[Definir fecha límite SS<br/>empresas.limite]
    G --> G2[Registrar contratistas internos]
    G --> G3[Adjuntar planilla SS<br/>módulo Planillas]
    G --> G4[Control mensual I/R<br/>por contratista]

    F -->|INDEPENDIENTE| H[Planilla por empleado]
    H --> H1[❌ Sin planilla a nivel empresa]
    H --> H2[Registrar contratistas internos]
    H --> H3[Cada interno tiene<br/>su propia fecha límite SS]
    H --> H4[Adjuntar planilla SS<br/>por contratista interno]
    H --> H5[Control mensual I/R + SS<br/>por contratista]

    D1 --> I([Empresa operativa])
    D2 --> I
    G4 --> I
    H5 --> I
```

---

## 4. Flujo de planilla de Seguridad Social (SS)

### 4.1 Planilla dependiente (por empresa)

```mermaid
flowchart LR
    A([Empresa INTERNA<br/>DEPENDIENTE]) --> B[Admin define<br/>fecha límite SS]
    B --> C{¿Estado de vigencia?}

    C -->|VIGENTE| D[> 10 días al límite]
    C -->|PRÓXIMA A VENCER| E[0–10 días al límite]
    C -->|VENCIDA| F[Fecha límite pasada]

    D --> G{¿Hay archivo adjunto<br/>para el periodo vigente?}
    E --> G
    F --> H[⚠ Requiere planilla urgente]

    G -->|Sí| I[✅ Planilla al día]
    G -->|No| J[⚠ Falta adjuntar planilla]

    J --> K[Usuario sube archivo<br/>módulo Planillas]
    K --> L[PlanillaEmpresaStorage<br/>guarda en storage/]
    L --> M[Registro en<br/>empresa_planilla_archivos]
    M --> I

    E --> N[Alerta automática<br/>10 y 5 días antes]
    F --> O[Alerta automática<br/>10 días después]
```

### 4.2 Planilla independiente (por contratista interno)

```mermaid
flowchart LR
    A([Empresa INTERNA<br/>INDEPENDIENTE]) --> B[Alta contratista interno]
    B --> C[Definir fecha límite SS<br/>por persona]
    C --> D{¿Estado SS del interno?}

    D -->|VIGENTE / PRÓXIMA / VENCIDA| E[Evaluar por contratista]
    E --> F{¿Archivo SS adjunto?}
    F -->|No| G[Subir planilla desde<br/>ficha del contratista interno]
    F -->|Sí| H[✅ Al día]

    G --> I[PlanillaContratistaInternoStorage]
    I --> J[contratista_interno_planilla_archivos]

    E --> K[Listado empresa muestra<br/>peor estado entre internos]

    D --> L[Alertas por hito<br/>empresa + equipo SISO]
```

---

## 5. Flujo de gestión de contratista

```mermaid
flowchart TD
    A([Alta de contratista]) --> B{¿Tipo?}

    B -->|Externo| C[Vincular a empresa EXTERNA]
    B -->|Interno| D[Vincular a empresa INTERNA]

    C --> E[Datos personales<br/>documento, ARL, licencia]
    D --> E

    E --> F[Adjuntar documentos<br/>cédula, licencia, manipulador]
    F --> G{¿Tiene fecha<br/>última I/R?}

    G -->|No| H[⚠ Pendiente de inducción<br/>aparece en Dashboard]
    G -->|Sí| I[Calcular fecha_vencimiento<br/>fecha_ultima_ir + vigencia_dias]

    I --> J{¿Estado I/R?}
    J -->|VIGENTE| K[> 10 días]
    J -->|PRÓXIMA A VENCER| L[0–10 días]
    J -->|VENCIDA| M[< 0 días]

    K --> N[Control mensual<br/>marcar meses ok/rechazado]
    L --> N
    M --> N

    N --> O{¿Contratista interno<br/>planilla independiente?}
    O -->|Sí| P[Gestionar SS individual]
    O -->|No| Q[SS hereda de empresa<br/>si es dependiente]

    N --> R{¿Activo?}
    R -->|Toggle inactivo| S[Excluido de alertas<br/>y listados activos]
    R -->|Activo| T([Contratista en operación])
```

---

## 6. Flujo de control mensual (I/R y SS)

```mermaid
flowchart TD
    A([Listado de contratistas]) --> B[Usuario abre fila expandible]
    B --> C[Grilla 12 meses<br/>Ene – Dic]

    C --> D{Click en celda de mes}
    D --> E[Toggle estado del mes]

    E --> F{Estado actual}
    F -->|vacío| G[ok ✓]
    F -->|ok| H[rechazado ✗]
    F -->|rechazado| I[vacío]

    G --> J[PATCH /mes<br/>guarda JSON meses]
    H --> J
    I --> J

    C --> K{¿Interno + mes vigente<br/>+ planilla independiente?}
    K -->|Sí| L[Celda especial SS<br/>muestra estado planilla]
    K -->|No| C

    J --> M([Estado persistido en BD])
    L --> M
```

---

## 7. Flujo de alertas automáticas SS

```mermaid
flowchart TD
    A([Cron diario 07:00<br/>America/Bogota]) --> B{¿ALERTAS_PLANILLA<br/>HABILITADAS?}
    B -->|No| Z([Fin])
    B -->|Sí| C[alertas:planilla-proxima-vencer]

    C --> D[AlertasPlanillaEmpresaService]
    D --> E[Buscar empresas DEPENDIENTES<br/>con hito de hoy]
    D --> F[Buscar internos INDEPENDIENTES<br/>con hito de hoy]

    E --> G{¿Hito coincide?}
    F --> G

    G -->|proxima_10| H[10 días antes del vencimiento]
    G -->|proxima_5| I[5 días antes]
    G -->|vencida_10| J[10 días después del vencimiento]
    G -->|Ninguno| Z

    H --> K{¿Ya se envió<br/>este hito + vigencia?}
    I --> K
    J --> K

    K -->|Sí| Z
    K -->|No| L[Enviar correos]

    L --> M[Correo a empresa<br/>empresas.correos]
    L --> N[Correo interno SISO<br/>siso@colbeef.com, etc.]

    M --> O[Registrar en<br/>*_alerta_planilla_envios]
    N --> O
    O --> Z
```

---

## 8. Flujo operativo diario del equipo SISO

```mermaid
flowchart TD
    A([Inicio jornada]) --> B[Ingresar al sistema]
    B --> C[Revisar Dashboard]

    C --> D{¿Hay vencidas?}
    D -->|Sí| E[Atender categorías rojas<br/>SS, I/R, licencias, vehículos]
    D -->|No| F{¿Hay próximas a vencer?}

    F -->|Sí| G[Planificar gestión<br/>próximos 10 días]
    F -->|No| H[Revisar pendientes<br/>de inducción]

    E --> I[Ir al módulo correspondiente]
    G --> I
    H --> I

    I --> J{¿Acción requerida?}
    J -->|Subir planilla SS| K[Módulo Planillas<br/>o ficha interno]
    J -->|Actualizar I/R| L[Editar contratista<br/>fecha_ultima_ir]
    J -->|Documento vehículo| M[Editar vehículo]
    J -->|Nuevo registro| N[Crear empresa /<br/>contratista / vehículo]

    K --> O[Archivo guardado<br/>estado actualizado]
    L --> O
    M --> O
    N --> O

    O --> P[Dashboard refleja<br/>nuevo estado]
    P --> Q{¿Más pendientes?}
    Q -->|Sí| C
    Q -->|No| R([Fin jornada / logout])
```

---

## 9. Flujo de importación masiva (Excel → internos)

```mermaid
flowchart TD
    A([Admin / Superadmin]) --> B[Empresa → Importar planilla]
    B --> C[Descargar plantilla Excel<br/>opcional]
    C --> D[Completar filas<br/>contratistas internos]
    D --> E[Subir archivo]
    E --> F[Vista previa<br/>POST preview]
    F --> G{¿Filas válidas?}

    G -->|Errores| H[Mostrar errores<br/>por fila]
    H --> D

    G -->|OK| I[Confirmar importación<br/>POST importar]
    I --> J[ImportadorPlanillaContratistas]
    J --> K[Crear/actualizar<br/>contratistas_internos]
    K --> L([Listado actualizado])
```

---

## 10. Flujo técnico de una petición HTTP

```mermaid
sequenceDiagram
    actor U as Usuario
    participant N as nginx
    participant L as Laravel
    participant M as Middleware
    participant C as Controller
    participant S as Service/Model
    participant V as Vista Blade

    U->>N: GET /empresas?buscar=ACME
    N->>L: index.php?$query_string
    L->>M: auth + restrict.consulta
    M->>M: TrackUserUsabilidad
    M->>C: EmpresaController@index
    C->>S: Empresa::scopeBuscarTexto()
    S->>S: Eloquent + filtros SS
    S-->>C: Collection paginada
    C->>V: empresas.index
    V-->>U: HTML + Tailwind CSS
```

---

## 11. Mapa de decisión — ¿Dónde gestiono la planilla SS?

```mermaid
flowchart TD
    A([¿Dónde adjunto planilla SS?]) --> B{¿Tipo empresa?}

    B -->|EXTERNA| C[No aplica<br/>sin control SS]
    B -->|INTERNA| D{¿Planilla?}

    D -->|DEPENDIENTE| E[Módulo Planillas<br/>una planilla por empresa]
    D -->|INDEPENDIENTE| F[Ficha de cada<br/>contratista interno]

    E --> G[Usar empresas.limite<br/>como referencia de vigencia]
    F --> H[Usar contratistas_internos.limite<br/>por persona]
```

---

## Leyenda de símbolos

| Símbolo | Significado |
|---------|-------------|
| `([ ])` | Inicio / fin de proceso |
| `{ }` | Decisión |
| `[ ]` | Acción / paso |
| `-.->` | Acceso restringido por rol |
| `❌` | No aplica en este flujo |
| `⚠` | Requiere atención |
| `✅` | Estado correcto / cumplido |

---

## Cómo visualizar estos diagramas

Los diagramas usan **Mermaid** en este archivo. También están exportados en **PNG** en:

```
docs/diagramas/png/
├── 01-flujo-general.png              ← flujo principal del sistema
├── 02-auth-permisos.png
├── 03-alta-empresa.png
├── 04-planilla-ss-dependiente.png
├── 05-planilla-ss-independiente.png
├── 06-gestion-contratista.png
├── 07-control-mensual.png
├── 08-alertas-automaticas.png
├── 09-operacion-diaria-siso.png
├── 10-importacion-excel.png
├── 11-peticion-http.png
└── 12-mapa-planilla-ss.png
```

Para regenerar los PNG tras editar este archivo:

```bash
node scripts/export-diagramas-png.mjs
```

También se renderizan en GitHub, VS Code / Cursor con extensión Mermaid, o en [mermaid.live](https://mermaid.live).

---

*Complemento de [`DOCUMENTACION-Control-Contratistas.md`](DOCUMENTACION-Control-Contratistas.md) — Control Contratista / Colbeef / SIRT*
