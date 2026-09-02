# Control Contratista — Documentación técnica

**Sistema de Información de Reportes de Terceros (SIRT)**  
Organización: Colbeef  
Repositorio: `https://github.com/JuaninJuan999/control-contratista.git`

---

## Tabla de contenidos

1. [Resumen ejecutivo](#1-resumen-ejecutivo)
2. [Stack tecnológico](#2-stack-tecnológico)
3. [Arquitectura de la aplicación](#3-arquitectura-de-la-aplicación)
4. [Estructura del proyecto](#4-estructura-del-proyecto)
5. [Modelo de dominio y reglas de negocio](#5-modelo-de-dominio-y-reglas-de-negocio)
6. [Capa de persistencia (base de datos)](#6-capa-de-persistencia-base-de-datos)
7. [Capa de aplicación (backend)](#7-capa-de-aplicación-backend)
8. [Rutas HTTP](#8-rutas-http)
9. [Autenticación, autorización y roles](#9-autenticación-autorización-y-roles)
10. [Capa de presentación (frontend)](#10-capa-de-presentación-frontend)
11. [Alertas automáticas por correo](#11-alertas-automáticas-por-correo)
12. [Búsqueda global](#12-búsqueda-global)
13. [Almacenamiento de archivos](#13-almacenamiento-de-archivos)
14. [Configuración y variables de entorno](#14-configuración-y-variables-de-entorno)
15. [Despliegue y operación](#15-despliegue-y-operación)
16. [Flujo de desarrollo local](#16-flujo-de-desarrollo-local)
17. [Convenciones de código](#17-convenciones-de-código)
18. [Pruebas](#18-pruebas)
19. [Diagramas de referencia](#19-diagramas-de-referencia)
20. [Glosario](#20-glosario)

---

## 1. Resumen ejecutivo

**Control Contratista** es una aplicación web monolítica desarrollada para el equipo SISO/SIRT de Colbeef. Su propósito es centralizar el **control documental y de cumplimiento normativo** de:

- Empresas contratistas (internas y externas)
- Personal contratista externo e interno
- Planillas de seguridad social (SS)
- Inducción y reinducción (I/R)
- Licencias de conducción, manipulador de alimentos y ARL
- Vehículos (SOAT, tecnomecánica, tarjeta de propiedad, inspección sanitaria)

La aplicación expone una **interfaz web** (no REST API pública). Los usuarios acceden mediante sesión autenticada, consultan vencimientos en un dashboard, gestionan registros CRUD y reciben **alertas automáticas por correo** cuando las fechas límite de planilla SS se acercan o vencen.

### Módulos funcionales

| Módulo | Descripción |
|--------|-------------|
| **Dashboard** | Panel de alertas por categoría (SS, I/R, licencias, manipulador, vehículos) |
| **Empresas** | CRUD con clasificación interna/externa, planilla dependiente/independiente, contratistas y vehículos anidados |
| **Planillas SS** | Adjuntos mensuales por empresa (dependiente) o por contratista interno (independiente) |
| **Contratistas externos** | Personal de empresas externas con control I/R y documentos |
| **Contratistas internos** | Personal de empresas internas con SS individual o compartida |
| **Vehículos** | Registro documental por empresa |
| **Usuarios** | Gestión de cuentas y roles |
| **Usabilidad** | Métricas de sesión (solo superadministrador) |
| **Búsqueda global** | Panel en cabecera con sugerencias en tiempo real |
| **WorkColbeef** | Enlace externo al portal institucional |

---

## 2. Stack tecnológico

### 2.1 Backend

| Tecnología | Versión | Rol |
|------------|---------|-----|
| **PHP** | ^8.3 (platform 8.3.30) | Lenguaje del servidor |
| **Laravel** | ^13.8 | Framework MVC, ORM, auth, mail, schedule |
| **Eloquent ORM** | (Laravel) | Mapeo objeto-relacional |
| **SQLite / MySQL** | Configurable | Persistencia (SQLite en dev, MySQL/PostgreSQL posible en prod) |
| **PhpSpreadsheet** | ^5.7 | Importación/exportación Excel de planillas de contratistas |

### 2.2 Frontend

| Tecnología | Versión | Rol |
|------------|---------|-----|
| **HTML5** | — | Estructura de vistas |
| **Blade** | (Laravel) | Motor de plantillas del servidor |
| **JavaScript (ES modules)** | Nativo | Interacciones en cliente (filtros, búsqueda, combobox) |
| **Tailwind CSS** | ^4.0 | Utilidades CSS y diseño responsivo |
| **Vite** | ^8.0 | Bundler y dev server de assets |
| **Instrument Sans** | — | Fuente principal (`--font-sans`) |
| **Playfair Display** | — | Fuente de títulos (`--font-display`) |

### 2.3 Infraestructura y herramientas

| Componente | Uso |
|------------|-----|
| **Composer** | Gestión de dependencias PHP |
| **npm** | Gestión de dependencias frontend |
| **nginx** | Servidor web en producción (Laragon) |
| **Laragon** | Entorno local Windows |
| **Git / GitHub** | Control de versiones |
| **PHPUnit** | Framework de pruebas |
| **Laravel Pint** | Formateo de código PHP |
| **Laravel Pail** | Visualización de logs en desarrollo |

### 2.4 Paquetes Composer relevantes

**Producción (`require`):**
- `laravel/framework` — núcleo del framework
- `laravel/tinker` — consola interactiva REPL
- `phpoffice/phpspreadsheet` — lectura/escritura Excel

**Desarrollo (`require-dev`):**
- `phpunit/phpunit` — pruebas unitarias e integración
- `laravel/pint` — estilo PSR-12
- `fakerphp/faker` — datos de prueba
- `mockery/mockery` — mocks en tests

---

## 3. Arquitectura de la aplicación

### 3.1 Patrón arquitectónico

La aplicación sigue el patrón **MVC en capas** propio de Laravel, con separación adicional en:

```
┌─────────────────────────────────────────────────────────────┐
│                    CAPA DE PRESENTACIÓN                      │
│  Blade + Tailwind + JavaScript (resources/views, css, js)   │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP (web.php)
┌──────────────────────────▼──────────────────────────────────┐
│                    CAPA DE CONTROL                           │
│  Controllers + Form Requests + Middleware                    │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                    CAPA DE DOMINIO                           │
│  Models + Traits + Support (constantes/enums de dominio)     │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                    CAPA DE SERVICIOS                         │
│  Services (lógica transaccional, storage, alertas, Excel)    │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                    CAPA DE PERSISTENCIA                      │
│  Eloquent + Migraciones + SQLite/MySQL                       │
└─────────────────────────────────────────────────────────────┘
```

### 3.2 Principios de diseño aplicados

1. **Fat models, thin controllers (moderado):** La lógica de estado (vigencia SS, I/R, días restantes) vive en modelos y traits; los controladores orquestan requests y respuestas.
2. **Form Requests:** Toda entrada de usuario pasa por clases `Store*Request` / `Update*Request` con reglas de validación explícitas.
3. **Support classes:** Constantes de dominio (`EmpresaTipo`, `PlanillaTipo`, `UserRol`) centralizadas fuera de modelos para evitar strings mágicos.
4. **Concerns reutilizables:** Traits como `ContratistaComun` comparten comportamiento entre externos e internos.
5. **Services para operaciones transversales:** Storage de archivos, alertas por correo e importación Excel no se duplican en controladores.
6. **Sin API REST:** Toda la interacción es server-rendered; JavaScript complementa UX (filtros, combobox, búsqueda).

### 3.3 Flujo de una petición típica

```
Usuario → nginx → public/index.php → Laravel Kernel
    → Middleware (auth, restrict.consulta, TrackUserUsabilidad)
    → Controller@action
    → Form Request (validación)
    → Model / Service (lógica)
    → Blade view (respuesta HTML)
```

---

## 4. Estructura del proyecto

```
control-contratista/
├── app/
│   ├── Console/Commands/          # Comandos Artisan (alertas SS)
│   ├── Http/
│   │   ├── Controllers/           # Controladores web
│   │   │   ├── Auth/              # Login / logout
│   │   │   └── Concerns/          # Traits de controlador reutilizables
│   │   ├── Middleware/            # Auth por rol, usabilidad, consulta
│   │   └── Requests/              # Validación de formularios
│   ├── Mail/                      # Clases Mailable para alertas
│   ├── Models/                    # Modelos Eloquent
│   │   └── Concerns/              # Traits de modelo (ContratistaComun, SS)
│   ├── Providers/                 # Service providers (búsqueda global)
│   ├── Services/                  # Lógica de negocio transversal
│   │   └── PlanillaContratistas/  # Importación Excel
│   └── Support/                   # Constantes y utilidades de dominio
├── bootstrap/app.php              # Registro middleware y rutas (Laravel 13)
├── config/                        # Configuración de la aplicación
├── database/
│   ├── migrations/                # 37 migraciones de esquema
│   ├── seeders/                   # Datos iniciales (usuario TIC)
│   └── factories/                 # Factories para tests
├── deploy/nginx/                  # Config nginx versionada + README crítico
├── lang/es/                       # Traducciones (auth)
├── public/                        # Punto de entrada web + assets compilados
│   ├── build/                     # Salida de Vite (CSS/JS)
│   └── image/                     # Imágenes institucionales
├── resources/
│   ├── css/app.css                # Tailwind 4 + componentes custom
│   ├── js/app.js                  # JavaScript de entrada
│   └── views/                     # Plantillas Blade (~72 archivos)
├── routes/
│   ├── web.php                    # Rutas HTTP
│   └── console.php                # Schedule de tareas
├── storage/                       # Archivos subidos, logs, cache, sessions
├── tests/                         # PHPUnit
├── .env.example                   # Plantilla de variables de entorno
├── composer.json                  # Dependencias PHP
├── package.json                   # Dependencias frontend
├── vite.config.js                 # Configuración Vite
└── DOCUMENTACION-Control-Contratistas.md  # Documentación técnica
```

---

## 5. Modelo de dominio y reglas de negocio

### 5.1 Entidades principales

```
                    ┌─────────────┐
                    │   Empresa   │
                    └──────┬──────┘
           ┌───────────────┼───────────────┐
           │               │               │
    ┌──────▼──────┐ ┌──────▼──────┐ ┌─────▼─────┐
    │ Contratista │ │ Contratista │ │  Vehículo │
    │   Externo   │ │   Interno   │ │           │
    └─────────────┘ └──────┬──────┘ └───────────┘
                           │
                    ┌──────▼──────────────────┐
                    │ PlanillaArchivo (interno)│
                    └─────────────────────────┘

    Empresa ──► EmpresaPlanillaArchivo (planilla dependiente)
    User ──► UserUsabilidadSesion
```

### 5.2 Clasificación de empresas

Definida en `App\Support\EmpresaTipo`:

| Tipo | Constante | Comportamiento |
|------|-----------|----------------|
| **Interna** | `INTERNA` | Empresa de Colbeef; lleva control SS, contratistas internos, fecha límite |
| **Externa** | `EXTERNA` | Empresa contratista externa; solo contratistas externos; **sin** planilla SS a nivel empresa |

### 5.3 Tipos de planilla SS

Definida en `App\Support\PlanillaTipo` (solo aplica a empresas **INTERNA**):

| Tipo | Constante | Planilla SS | Fecha límite |
|------|-----------|-------------|--------------|
| **Dependiente** | `DEPENDIENTE` | Una planilla por **empresa** (`empresas.limite`) | A nivel empresa |
| **Independiente** | `INDEPENDIENTE` | Una planilla por **cada contratista interno** | Por persona (`contratistas_internos.limite`) |

**Reglas consolidadas:**

| Escenario | Planilla SS empresa | Planilla SS por empleado |
|-----------|--------------------|-------------------------|
| Empresa externa | No | No |
| Empresa interna dependiente | Sí | No (usa la de la empresa) |
| Empresa interna independiente | No | Sí (cada interno) |

**Métodos clave en `Empresa`:**
- `llevaPlanillaSs()` → interna + dependiente
- `planillaSsPorEmpleado()` → interna + independiente
- `estadoLimiteParaListado()` → peor estado entre internos si es independiente

### 5.4 Estados de vigencia SS

Calculados dinámicamente (no se almacenan en BD):

| Estado | Condición |
|--------|-----------|
| `VIGENTE` | Más de 10 días hasta la fecha límite |
| `PRÓXIMA A VENCER` | Entre 0 y 10 días inclusive |
| `VENCIDA` | Fecha límite ya pasada |
| `null` / sin fecha | No hay fecha límite configurada |

Implementación en `Empresa::getEstadoLimiteAttribute()` y traits de contratista interno.

### 5.5 Contratistas: externo vs interno

| Aspecto | Externo | Interno |
|---------|---------|---------|
| Tabla | `contratistas_externos` | `contratistas_internos` |
| Empresa | FK obligatoria | FK obligatoria |
| I/R | `fecha_ultima_ir` + `vigencia_dias` → `fecha_vencimiento` | Igual |
| Control mensual | JSON `meses` (ok / rechazado / vacío) | Igual + celda SS en mes vigente |
| Planilla SS | No aplica directamente | Dependiente o independiente |
| Documentos | Cédula, licencia, manipulador, ARL | Igual + planilla SS si independiente |

**Trait `ContratistaComun`:**
- Calcula `fecha_vencimiento` desde última I/R
- `toggleMes()` / `estadoMes()` para control mensual
- Normaliza `numero_documento` (sin puntos ni guiones en BD)

### 5.6 Vehículos

Modelo `Vehiculo` vinculado a `Empresa`. Documentos controlados:

| Documento | Campo / accessor |
|-----------|------------------|
| SOAT | `soat_vencimiento`, `soat_estado` |
| Tecnomecánica | `tecnomecanica_vencimiento`, `tecnomecanica_estado` |
| Tarjeta de propiedad | Archivo adjunto |
| Inspección sanitaria | `inspeccion_sanitaria_vencimiento` |

### 5.7 Inducción / Reinducción (I/R)

- **Entrada:** `fecha_ultima_ir` (nullable) + `vigencia_dias`
- **Calculado:** `fecha_vencimiento = fecha_ultima_ir + vigencia_dias`
- **Estados:** VIGENTE / PRÓXIMA A VENCER / VENCIDA (misma lógica de 10 días)
- **Dashboard:** sección de pendientes de inducción para quienes no tienen fecha registrada

---

## 6. Capa de persistencia (base de datos)

### 6.1 Tablas principales

| Tabla | Descripción |
|-------|-------------|
| `users` | Usuarios del sistema (username, rol, activo) |
| `sessions` | Sesiones Laravel (driver database) |
| `empresas` | Empresas con NIT, correos JSON, limite, planilla, tipo_empresa |
| `contratistas_externos` | Personal externo |
| `contratistas_internos` | Personal interno + tipo_planilla, limite SS |
| `vehiculos` | Vehículos por empresa |
| `empresa_planilla_archivos` | Archivos SS por empresa y periodo |
| `contratista_interno_planilla_archivos` | Archivos SS por contratista independiente |
| `empresa_alerta_planilla_envios` | Log de alertas enviadas (empresa) |
| `contratista_interno_alerta_planilla_envios` | Log de alertas enviadas (interno) |
| `user_usabilidad_sesiones` | Métricas de actividad por sesión |
| `cache`, `jobs`, `failed_jobs` | Infraestructura Laravel |

### 6.2 Relaciones Eloquent

```php
Empresa
  ├── hasMany ContratistaExterno
  ├── hasMany ContratistaInterno
  ├── hasMany Vehiculo
  └── hasMany EmpresaPlanillaArchivo

ContratistaInterno
  ├── belongsTo Empresa
  └── hasMany ContratistaInternoPlanillaArchivo

User
  └── hasMany UserUsabilidadSesion
```

### 6.3 Migraciones

El proyecto contiene **37 migraciones** que documentan la evolución del esquema desde mayo–agosto de 2026. Las migraciones son la fuente de verdad del esquema; no se usan esquemas SQL externos.

Comando para aplicar:
```bash
php artisan migrate
```

---

## 7. Capa de aplicación (backend)

### 7.1 Controladores

| Controlador | Responsabilidad |
|-------------|-----------------|
| `DashboardController` | Panel de vencimientos por categoría |
| `EmpresaController` | CRUD empresas, filtros servidor, gestión anidada |
| `PlanillaController` | Listado planillas SS dependientes, subir/eliminar archivos |
| `PlanillaContratistaController` | Importación masiva Excel → contratistas internos |
| `ContratistaExternoController` | CRUD externos, toggle activo |
| `ContratistaInternoController` | CRUD internos, toggle activo/mes, planilla SS |
| `VehiculoController` | CRUD vehículos con documentos |
| `UserController` | CRUD usuarios, toggle activo |
| `BusquedaGlobalController` | Página y sugerencias de búsqueda |
| `UsabilidadController` | Reporte de sesiones (superadmin) |
| `Auth\LoginController` | Login por username, logout, tracking |

**Concerns de controlador:**
- `GuardaContratistaConDocumentos` — persistencia + storage de archivos de contratista
- `GuardaPlanillaSsInterno` — adjuntos SS por contratista independiente

### 7.2 Modelos

| Modelo | Ubicación | Notas |
|--------|-----------|-------|
| `Empresa` | `app/Models/Empresa.php` | Lógica SS, scopes de búsqueda y filtro |
| `ContratistaExterno` | `app/Models/` | Usa trait `ContratistaComun` |
| `ContratistaInterno` | `app/Models/` | Traits `ContratistaComun` + `ContratistaInternoSeguridadSocial` |
| `Vehiculo` | `app/Models/` | Accessors de estado por documento |
| `User` | `app/Models/` | Métodos de permisos por rol |
| `EmpresaPlanillaArchivo` | `app/Models/` | Metadata de archivos SS empresa |
| `ContratistaInternoPlanillaArchivo` | `app/Models/` | Metadata de archivos SS interno |

### 7.3 Servicios

| Servicio | Función |
|----------|---------|
| `AlertasPlanillaEmpresaService` | Detección de hitos y envío deduplicado de alertas SS |
| `BusquedaGlobalIndice` | Índice en memoria para panel de búsqueda |
| `ContratistaDocumentoStorage` | Almacenamiento de documentos de contratistas |
| `VehiculoDocumentoStorage` | Almacenamiento de documentos de vehículos |
| `PlanillaEmpresaStorage` | Archivos planilla a nivel empresa |
| `PlanillaContratistaInternoStorage` | Archivos planilla por interno |
| `UserUsabilidadTracker` | Registro de actividad e inactividad |
| `SesionUsuarioService` | Cierre de sesión por inactividad |
| `PlanillaContratistas/LectorExcel` | Lectura de filas Excel |
| `PlanillaContratistas/ImportadorPlanillaContratistas` | Importación masiva |
| `PlanillaContratistas/GeneradorPlantillaExcel` | Generación de plantilla descargable |

### 7.4 Form Requests (validación)

Ubicación: `app/Http/Requests/`

Ejemplos:
- `StoreEmpresaRequest` / `UpdateEmpresaRequest`
- `StoreContratistaExternoRequest` / `UpdateContratistaInternoRequest`
- `StoreUserRequest` / `UpdateUserRequest`

Patrón: reglas explícitas, mensajes en español, normalización de documentos vía `App\Support\NumeroDocumento`.

### 7.5 Support (constantes de dominio)

| Clase | Contenido |
|-------|-----------|
| `EmpresaTipo` | `INTERNA`, `EXTERNA` |
| `PlanillaTipo` | `DEPENDIENTE`, `INDEPENDIENTE` |
| `UserRol` | Roles y etiquetas |
| `PeriodoPlanilla` | Formato de periodos mensuales |
| `AlertaPlanillaHito` | Hitos de alerta (proxima_10, proxima_5, vencida_10) |
| `NumeroDocumento` | Normalización de documentos (quita puntos/guiones) |
| `TerminoBusqueda` | Utilidades para búsqueda ILIKE |

### 7.6 Comandos Artisan

| Comando | Signature | Descripción |
|---------|-----------|-------------|
| `EnviarAlertasPlanillaProximaVencer` | `alertas:planilla-proxima-vencer {--dry-run}` | Envía alertas SS según hitos configurados |

Programado en `routes/console.php`:
```php
Schedule::command(EnviarAlertasPlanillaProximaVencer::class)
    ->dailyAt('07:00')
    ->timezone('America/Bogota')
    ->when(fn () => config('alertas_planilla.habilitado'));
```

### 7.7 Clases Mail

| Clase | Destinatario |
|-------|--------------|
| `PlanillaProximaVencerEmpresaMail` | Correos de la empresa (dependiente) |
| `PlanillaProximaVencerInternoMail` | Equipo SISO interno |
| `PlanillaProximaVencerContratistaEmpresaMail` | Empresa (interno independiente) |
| `PlanillaProximaVencerContratistaInternoMail` | Equipo SISO (interno independiente) |

Plantillas Blade en `resources/views/mails/`.

---

## 8. Rutas HTTP

Archivo: `routes/web.php`  
Health check Laravel 13: `GET /up`

### 8.1 Rutas públicas

| Método | URI | Nombre | Acción |
|--------|-----|--------|--------|
| GET | `/` | — | Redirect a dashboard o login |
| GET | `/login` | `login` | Formulario de login |
| POST | `/login` | `login.store` | Autenticación (throttle 6/min) |

### 8.2 Rutas autenticadas

Middleware: `auth`, `restrict.consulta`

| Grupo | Rutas principales |
|-------|-------------------|
| Dashboard | `GET /dashboard` |
| Búsqueda | `GET /buscar`, `GET /buscar/sugerencias` |
| Empresas | `Route::resource empresas` (except show) |
| Planillas | index, store/destroy archivo, update tipo |
| Importación Excel | plantilla, preview, importar (`access.usuarios`) |
| Contratistas externos | resource + PATCH activo |
| Contratistas internos | resource + PATCH activo/mes + descarga planilla |
| Vehículos | resource (except show, destroy) |
| Usuarios | resource + toggle activo (`access.usuarios`) |
| Usabilidad | `GET /usabilidad` (`access.superadmin`) |
| Logout | `POST /logout` |

**No existe `routes/api.php`:** la aplicación no expone API REST.

---

## 9. Autenticación, autorización y roles

### 9.1 Autenticación

- **Guard:** `web` (sesión almacenada en BD)
- **Campo de login:** `username` (case-insensitive), no email
- **Remember me:** soportado
- **Usuarios inactivos:** rechazados en login
- **Lifetime sesión:** 120 minutos (`SESSION_LIFETIME`)

### 9.2 Roles (`App\Support\UserRol`)

| Rol | Constante | Permisos |
|-----|-----------|----------|
| Superadministrador | `superadministrador` | Acceso total + usabilidad + eliminar usuarios |
| Administrador | `administrador` | Edición, usuarios (sin crear superadmin), importar planilla |
| Operativo | `operativo` | Edición operativa de registros |
| Consulta | `consulta` | **Solo lectura** (GET en listados) |

### 9.3 Middleware personalizado

Registrados en `bootstrap/app.php`:

| Alias | Clase | Efecto |
|-------|-------|--------|
| `restrict.consulta` | `RestrictConsultaAccess` | Bloquea POST/PATCH/DELETE y rutas create/edit para rol consulta |
| `access.usuarios` | `EnsureCanAccessUsuariosModule` | Solo superadmin + admin |
| `access.superadmin` | `EnsureSuperadmin` | Solo superadmin |
| *(grupo web)* | `TrackUserUsabilidad` | Tracking de actividad; cierre por inactividad |

### 9.4 Métodos de permiso en `User`

```php
$user->esSuperadmin();
$user->soloConsulta();
$user->puedeEditar();
$user->puedeAccederModuloUsuarios();
$user->puedeImportarPlanilla();
$user->puedeEliminarUsuarios();
$user->puedeEliminarContratistas();
```

---

## 10. Capa de presentación (frontend)

### 10.1 Layouts

| Archivo | Uso |
|---------|-----|
| `layouts/app.blade.php` | Layout principal autenticado (nav, header, footer) |
| `layouts/guest.blade.php` | Login |
| `layouts/_menu_usuario.blade.php` | Menú desplegable de usuario |
| `layouts/_busqueda_global*.blade.php` | Panel de búsqueda en header |
| `layouts/_workbeef_link.blade.php` | Enlace WorkColbeef (borde verde/rojo) |
| `layouts/_sesion_inactividad_script.blade.php` | Cierre automático por inactividad |

### 10.2 Organización de vistas por módulo

```
resources/views/
├── auth/login.blade.php
├── dashboard.blade.php
├── empresas/              # CRUD + secciones anidadas
├── planillas/             # Listado e historial SS
├── contratistas/          # Partials compartidos (form, filtros, combobox)
├── contratistas_externos/
├── contratistas_internos/
├── vehiculos/
├── usuarios/
├── usabilidad/
├── busqueda/
└── mails/                 # Plantillas de correo
```

### 10.3 Estilos (`resources/css/app.css`)

- **Tailwind CSS 4** con directiva `@import 'tailwindcss'`
- **Componentes custom:**
  - `.btn-emerald-glow` — botón «Volver al listado» con resplandor verde
  - `.workcolbeef-link` — borde mitad verde / mitad rojo
  - `.login-text-glow` — títulos con resplandor en login
  - `.dash-stat-card`, `.dash-alert-*` — tarjetas del dashboard

### 10.4 JavaScript

Patrón predominante: **scripts inline en partials Blade** (`@once` + IIFE) para:
- Combobox de búsqueda de empresa con lista flotante
- Filtros de contratistas
- Búsqueda global con sugerencias
- Control mensual (toggle meses)
- Sesión por inactividad

No se usa framework JS (React/Vue/Alpine); JavaScript vanilla modularizado por partial.

### 10.5 Compilación de assets

```bash
# Desarrollo (hot reload)
npm run dev

# Producción
npm run build
```

Salida en `public/build/` referenciada vía `@vite()` en layouts.

---

## 11. Alertas automáticas por correo

### 11.1 Configuración

Archivo: `config/alertas_planilla.php`  
Variables en `.env`:

```env
ALERTAS_PLANILLA_HABILITADAS=true
ALERTAS_PLANILLA_DIAS_PROXIMA=10,5
ALERTAS_PLANILLA_DIAS_VENCIDA=10
ALERTAS_PLANILLA_CORREOS_INTERNOS="siso@colbeef.com|aux.siso@colbeef.com"
ALERTAS_PLANILLA_HORA=07:00
ALERTAS_PLANILLA_ZONA=America/Bogota
```

### 11.2 Hitos de alerta

| Hito | Cuándo se dispara |
|------|-------------------|
| `proxima_10` | Exactamente 10 días antes del vencimiento |
| `proxima_5` | Exactamente 5 días antes |
| `vencida_10` | Exactamente 10 días **después** del vencimiento |

### 11.3 Canales de envío

- **Correo a la empresa:** direcciones en `empresas.correos` (JSON)
- **Correo interno SISO:** lista configurada en `ALERTAS_PLANILLA_CORREOS_INTERNOS`

### 11.4 Deduplicación

Tablas `empresa_alerta_planilla_envios` y `contratista_interno_alerta_planilla_envios` registran cada envío por:
- Entidad (empresa o contratista)
- Hito
- Vigencia (fecha límite)
- Canal

Evita reenvíos duplicados en el mismo hito.

### 11.5 Ejecución manual

```bash
# Simular sin enviar correos
php artisan alertas:planilla-proxima-vencer --dry-run

# Ejecutar envío real
php artisan alertas:planilla-proxima-vencer
```

---

## 12. Búsqueda global

- **Ubicación:** panel en header (`layouts/_busqueda_global`)
- **Servicio:** `BusquedaGlobalIndice` (registrado en `AppServiceProvider`)
- **Rutas:** `GET /buscar`, `GET /buscar/sugerencias`
- **Alcance:** empresas, contratistas (nombre, documento), vehículos (placa), usuarios
- **Implementación:** índice en memoria construido al boot; sugerencias vía AJAX

Búsqueda en listado de empresas (servidor):
- Scope `Empresa::scopeBuscarTexto()` — ILIKE en nombre, NIT, contratistas y placas
- Filtros adicionales: estado SS, tipo empresa, planilla (query params GET)

---

## 13. Almacenamiento de archivos

- **Disco:** `local` (`FILESYSTEM_DISK=local`)
- **Ubicación:** `storage/app/` (privado, no accesible directamente vía URL)
- **Descarga:** rutas controladas en controladores con autorización

Servicios de storage:
- `ContratistaDocumentoStorage` — cédula, licencia, manipulador
- `VehiculoDocumentoStorage` — tarjeta propiedad, documentos vehículo
- `PlanillaEmpresaStorage` — planillas SS empresa
- `PlanillaContratistaInternoStorage` — planillas SS por interno

---

## 14. Configuración y variables de entorno

### 14.1 Archivos de configuración relevantes

| Archivo | Contenido |
|---------|-----------|
| `config/app.php` | Nombre, timezone, `workbeef_url` |
| `config/alertas_planilla.php` | Hitos, correos, hora de envío |
| `config/usabilidad.php` | Segundos de inactividad, cierre de sesión |
| `config/auth.php` | Guard web, model User |
| `config/session.php` | Driver database, lifetime |
| `config/mail.php` | SMTP (Colbeef documentado en `.env.example`) |

### 14.2 Variables `.env` destacadas

```env
APP_NAME="Control Contratista"
APP_LOCALE=es
APP_URL=http://localhost
WORKBEEF_URL=http://192.168.20.205:8000/site.html

DB_CONNECTION=sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120

USABILIDAD_INACTIVIDAD_SEGUNDOS=900
USABILIDAD_CERRAR_SESION=true

QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 14.3 Usuario inicial (seeder)

Desarrollo: usuario `TIC` con contraseña `SIRT123` (rol superadministrador).  
**Cambiar credenciales en producción.**

---

## 15. Despliegue y operación

### 15.1 Entorno de producción actual

| Aspecto | Valor |
|---------|-------|
| Servidor | Windows + Laragon |
| IP / Puerto | `192.168.20.205:8009` |
| Servidor web | nginx |
| Config versionada | `deploy/nginx/control-contratista.conf` |

### 15.2 Configuración nginx crítica

Documentada en `deploy/nginx/README.md`.

**Correcto:**
```nginx
try_files $uri $uri/ /index.php?$query_string;
```

**Incorrecto (rompe query strings):**
```nginx
try_files $uri $uri/ /index.php?$is_args$args;
```

Síntoma: el primer parámetro GET llega con `?` en el nombre (`?buscar` en lugar de `buscar`).

### 15.3 Pasos de despliegue

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

### 15.4 Correo en producción

Descomentar y configurar SMTP Colbeef en `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.colbeef.com.co
MAIL_PORT=465
MAIL_USERNAME=no-responder-sirt@colbeef.com.co
```

### 15.5 Scheduler (alertas)

En producción, el cron de Laravel debe ejecutarse cada minuto:
```
* * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

En Windows/Laragon: Tarea programada equivalente o `php artisan schedule:work`.

---

## 16. Flujo de desarrollo local

### 16.1 Instalación inicial

```bash
composer setup
# Equivalente a: install + .env + key:generate + migrate + npm install + build
```

### 16.2 Desarrollo con hot reload

```bash
composer dev
# Ejecuta en paralelo: serve + queue + pail + vite
```

### 16.3 Comandos útiles

```bash
php artisan migrate          # Aplicar migraciones
php artisan db:seed            # Usuario inicial
php artisan test               # Ejecutar tests
./vendor/bin/pint              # Formatear código PHP
npm run build                  # Compilar assets producción
```

---

## 17. Convenciones de código

### 17.1 PHP / Laravel

- **PSR-4** autoloading bajo namespace `App\`
- **PHP 8.3+** features: typed properties, `#[Fillable]`, `#[Hidden]` attributes en modelos
- **Form Requests** para toda validación de entrada
- **Final classes** en Support (`EmpresaTipo`, `UserRol`) — no instanciables
- **Scopes Eloquent** para filtros reutilizables (`scopeBuscarTexto`, `scopeFiltrarEstadoSsListado`)
- **Accessors** con sintaxis Laravel 11+ (`getEstadoLimiteAttribute`)
- **Nombres en español** para dominio de negocio (métodos, constantes de estado)
- **Comentarios PHPDoc** en métodos con arrays tipados (`@return array{...}`)

### 17.2 Blade

- Partials con prefijo `_` (`_filtros_contratistas_panel.blade.php`)
- `@once` para scripts que no deben duplicarse
- `@class([...])` para clases condicionales
- Componentes inline (no Livewire)

### 17.3 CSS

- Tailwind utilities como base
- Componentes en `@layer components` en `app.css`
- Paleta institucional: emerald (verde), red (rojo), zinc (grises)

### 17.4 Git

- Rama principal: `main`
- Mensajes de commit en español, orientados al **porqué**
- Config nginx y `.env` sensibles versionados con cuidado (`.env` en `.gitignore`)

### 17.5 Seguridad

- Contraseñas hasheadas (bcrypt)
- CSRF en todos los formularios POST/PATCH/DELETE
- Throttle en login (6 intentos/minuto)
- Middleware de rol en rutas sensibles
- Archivos en `storage/` no expuestos públicamente

---

## 18. Pruebas

| Aspecto | Estado |
|---------|--------|
| Framework | PHPUnit ^12.5 |
| Config | `phpunit.xml` — SQLite `:memory:` |
| Tests existentes | `ExampleTest` (Unit + Feature) |
| Cobertura real | **Mínima** — solo verifica redirect `/` → login |

Ejecutar:
```bash
composer test
# o
php artisan test
```

**Recomendación:** ampliar tests en modelos (`Empresa::estadoLimiteParaListado`), scopes y servicios de alertas.

---

## 19. Diagramas de referencia

### 19.1 Flujo planilla SS (empresa dependiente)

```
┌──────────────┐     define      ┌─────────────────┐
│   Empresa    │ ──────────────► │ empresas.limite │
│   INTERNA    │                 └────────┬────────┘
│  DEPENDIENTE │                          │
└──────┬───────┘                          ▼
       │                    ┌──────────────────────────┐
       │ adjunta            │ empresa_planilla_archivos │
       └──────────────────► │ (periodo + vigencia_hasta)│
                            └──────────────────────────┘
                                      │
                                      ▼
                            planillaVigenteAdjunta() → bool
```

### 19.2 Flujo planilla SS (interno independiente)

```
┌──────────────┐     define      ┌──────────────────────────┐
│   Empresa    │                 │ contratistas_internos    │
│   INTERNA    │ ──────────────► │ .limite (por persona)    │
│ INDEPENDIENTE│                 └────────┬─────────────────┘
└──────────────┘                          │
                                          ▼
                            ┌──────────────────────────────────┐
                            │ contratista_interno_planilla_    │
                            │ archivos                         │
                            └──────────────────────────────────┘
```

### 19.3 Matriz de decisión — dónde va la planilla SS

```
                    │ Planilla empresa │ Planilla por empleado │
────────────────────┼──────────────────┼───────────────────────│
Empresa EXTERNA     │       NO         │          NO             │
Empresa INTERNA DEP │       SÍ         │          NO             │
Empresa INTERNA IND │       NO         │          SÍ             │
```

---

## 20. Glosario

| Término | Significado |
|---------|-------------|
| **SIRT** | Sistema de Información de Reportes de Terceros |
| **SISO** | Seguridad e Higiene Ocupacional |
| **SS** | Seguridad Social (planilla mensual) |
| **I/R** | Inducción / Reinducción |
| **ARL** | Aseguradora de Riesgos Laborales |
| **SOAT** | Seguro Obligatorio de Accidentes de Tránsito |
| **NIT** | Número de Identificación Tributaria |
| **Colbeef** | Organización propietaria del sistema |
| **WorkColbeef** | Portal externo institucional |
| **Dependiente** | Planilla SS única por empresa |
| **Independiente** | Planilla SS por cada contratista interno |
| **Hito** | Momento exacto en el que se dispara una alerta (días antes/después del vencimiento) |

---

## Historial del documento

| Versión | Fecha | Descripción |
|---------|-------|-------------|
| 1.0 | Septiembre 2026 | Documentación inicial completa del sistema |

---

*Documento generado para el equipo de desarrollo y operación de Colbeef. Para actualizaciones, mantener sincronizado con la rama `main` del repositorio.*
