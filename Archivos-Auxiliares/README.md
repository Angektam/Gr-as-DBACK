# Sistema de Grúas DBACK - Estructura Organizada

## 📁 Estructura del Proyecto

El proyecto ha sido reorganizado para mejorar la mantenibilidad y escalabilidad del código.

### 🏗️ Directorios Principales

```
DBACK-main/
├── admin/                    # Módulo de administración
│   ├── Login.php
│   ├── MenuAdmin.PHP
│   └── cerrar_sesion.php
├── api/                      # APIs del sistema
│   ├── api.php
│   ├── solicitud_api.php
│   └── empleados_api.php
├── assets/                   # Recursos estáticos
│   ├── css/                  # Hojas de estilo
│   ├── js/                   # Scripts JavaScript
│   └── images/               # Imágenes e iconos
├── components/               # Componentes reutilizables
│   ├── header-component.php
│   ├── footer-component.php
│   └── sidebar-component.php
├── config/                   # Configuraciones
│   ├── config.php
│   └── paths.php            # Configuración de rutas
├── core/                     # Archivos centrales
│   └── conexion.php
├── database/                 # Scripts de base de datos
│   ├── DBACKBD.sql
│   └── configuracion-auto-asignacion.sql
├── docs/                     # Documentación
│   └── README-AutoAsignacion.md
├── logs/                     # Archivos de log
│   ├── activity_log.txt
│   ├── error_log.txt
│   └── post_error_log.txt
├── modules/                  # Módulos funcionales
│   ├── solicitudes/          # Gestión de solicitudes
│   ├── gruas/               # Gestión de grúas
│   ├── empleados/           # Gestión de empleados
│   ├── gastos/              # Gestión de gastos
│   ├── reportes/            # Sistema de reportes
│   └── auto-asignacion/     # Auto-asignación de grúas
├── tests/                   # Archivos de prueba
│   └── verificar/           # Archivos de verificación
├── utils/                   # Utilidades
│   └── crear-vistas.php
└── uploads/                 # Archivos subidos por usuarios
```

### 🔧 Módulos Funcionales

#### 📋 Módulo de Solicitudes (`modules/solicitudes/`)
- `solicitud.php` - Gestión principal de solicitudes
- `nueva-solicitud.php` - Crear nuevas solicitudes
- `detalle-solicitud.php` - Ver detalles de solicitudes
- `gestion-solicitud.php` - Administrar solicitudes
- `procesar-solicitud.php` - Procesar solicitudes
- `procesar_servicio.php` - Procesar servicios
- `config-solicitud-critico.php` - Configuración crítica

#### 🚛 Módulo de Grúas (`modules/gruas/`)
- `Gruas.php` - Gestión principal de grúas
- `agregar-grua-plataforma.php` - Agregar grúas de plataforma
- `agregar-coordenadas-gruas.php` - Agregar coordenadas de grúas

#### 👥 Módulo de Empleados (`modules/empleados/`)
- `Empleados.php` - Gestión de empleados
- `Empleados.html` - Vista HTML de empleados

#### 💰 Módulo de Gastos (`modules/gastos/`)
- `Gastos.php` - Gestión de gastos

#### 📊 Módulo de Reportes (`modules/reportes/`)
- `Reportes.php` - Sistema de reportes

#### 🤖 Módulo de Auto-Asignación (`modules/auto-asignacion/`)
- `AutoAsignacionGruas.php` - Clase principal de auto-asignación
- `configuracion-auto-asignacion.php` - Configuración del sistema
- `menu-auto-asignacion.php` - Menú específico
- `procesar-auto-asignacion.php` - Procesar asignaciones automáticas
- `debug-auto-asignacion.php` - Debug del sistema
- `probar-auto-asignacion.php` - Pruebas del sistema

### 🎨 Assets

#### CSS (`assets/css/`)
- `Empleados.css`
- `Gastos.css`
- `Gruas.CSS`
- `Login.CSS`
- `MenuAdmin.CSS`
- `panel-solicitud.css`
- `Solicitud_ARCO.CSS`
- `Styles.CSS`

#### JavaScript (`assets/js/`)
- `Gruas.js`

#### Imágenes (`assets/images/`)
- Iconos SVG del sistema
- Logo de DBACK

### 🔧 Configuración

#### Archivo de Rutas (`config/paths.php`)
Centraliza todas las rutas del sistema para facilitar el mantenimiento:

```php
// Ejemplo de uso
include_core('conexion.php');
include_component('header-component.php');
include_module('solicitudes', 'solicitud.php');
```

### 📝 Archivos de Log (`logs/`)
- `activity_log.txt` - Registro de actividades
- `error_log.txt` - Registro de errores
- `post_error_log.txt` - Errores específicos de POST

### 🧪 Testing (`tests/`)
Contiene todos los archivos de verificación y prueba del sistema.

## 🚀 Beneficios de la Nueva Estructura

1. **Organización Clara**: Cada módulo tiene su propio directorio
2. **Mantenibilidad**: Fácil localización de archivos específicos
3. **Escalabilidad**: Estructura preparada para crecimiento
4. **Reutilización**: Componentes centralizados
5. **Separación de Responsabilidades**: Cada directorio tiene un propósito específico

## 📋 Próximos Pasos

1. Actualizar todas las referencias de rutas en los archivos
2. Implementar el sistema de rutas centralizado
3. Crear documentación específica para cada módulo
4. Implementar tests automatizados
5. Configurar sistema de logs centralizado

## 🔄 Migración

Para migrar archivos existentes a esta estructura:

1. Mover archivos a sus directorios correspondientes
2. Actualizar referencias de rutas
3. Probar funcionalidad
4. Actualizar documentación

---

**Nota**: Esta estructura está diseñada para ser mantenible y escalable. Cada módulo puede desarrollarse independientemente mientras mantiene la cohesión del sistema.
