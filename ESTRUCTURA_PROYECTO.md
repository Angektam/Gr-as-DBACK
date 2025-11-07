# Estructura del Proyecto DBACK

## Organización de Archivos

```
DBACK-main/
│
├── admin/                      # Panel de administración
│   ├── Login.php              # Página de inicio de sesión
│   ├── MenuAdmin.PHP          # Menú principal de administración
│   ├── Gruas.php              # Gestión de grúas
│   ├── Empleados.php          # Gestión de empleados
│   ├── Gastos.php             # Gestión de gastos
│   ├── Reportes.php           # Reportes del sistema
│   ├── AutoAsignacionGruas.php # Auto-asignación de grúas
│   ├── configuracion-auto-asignacion.php
│   ├── menu-auto-asignacion.php
│   └── procesar-auto-asignacion.php
│
├── api/                        # Endpoints de API
│   ├── api.php                # API principal
│   ├── solicitud_api.php      # API de solicitudes
│   ├── empleados_api.php      # API de empleados
│   └── api-notificaciones.php # API de notificaciones
│
├── modules/                    # Módulos del sistema
│   ├── solicitudes/           # Módulo de solicitudes
│   │   ├── solicitud.php
│   │   ├── procesar-solicitud.php
│   │   ├── detalle-solicitud.php
│   │   ├── gestion-solicitud.php
│   │   ├── nueva-solicitud.php
│   │   ├── procesar_servicio.php
│   │   └── gestion-clima-servicio.php
│   │
│   ├── gruas/                 # Módulo de grúas
│   │   ├── estado-gruas.php
│   │   ├── ver-gruas.php
│   │   ├── liberar-gruas.php
│   │   ├── liberacion-automatica-gruas.php
│   │   ├── mejorar-api-nominatim.php
│   │   └── geocodificacion-fallback.php
│   │
│   ├── empleados/             # Módulo de empleados (reservado)
│   └── gastos/                # Módulo de gastos (reservado)
│
├── components/                 # Componentes reutilizables
│   ├── header-component.php
│   ├── footer-component.php
│   ├── sidebar-component.php
│   └── widget-notificaciones.php
│
├── assets/                     # Recursos estáticos
│   ├── css/                   # Hojas de estilo
│   │   ├── variables.css      # Variables CSS globales
│   │   ├── Common.css
│   │   ├── Login.CSS
│   │   ├── MenuAdmin.CSS
│   │   ├── Gruas.css
│   │   ├── Empleados.css
│   │   ├── Gastos.css
│   │   ├── AutoAsignacion.css
│   │   ├── panel-solicitud.css
│   │   ├── Solicitud_ARCO.CSS
│   │   ├── Styles.CSS
│   │   └── styles.css
│   │
│   ├── js/                    # Scripts JavaScript
│   │   ├── validaciones.js
│   │   └── validaciones.css
│   │
│   └── images/                # Imágenes y recursos gráficos
│       ├── LogoDBACK.png
│       └── [otros archivos de imagen]
│
├── public/                     # Archivos públicos
│   ├── index.html             # Página principal
│   └── index-styles.css       # Estilos de la página principal
│
├── config/                     # Configuración
│   ├── config.php             # Configuración principal
│   ├── config-production.php  # Configuración de producción
│   └── paths.php              # Sistema de rutas centralizado
│
├── utils/                      # Utilidades
│   └── validaciones.php       # Sistema de validaciones
│
├── database/                   # Base de datos
│   └── DBACKBD.sql            # Script de base de datos
│
├── uploads/                    # Archivos subidos por usuarios
│
├── conexion.php               # Archivo de conexión a BD (raíz)
├── cerrar_sesion.php          # Cerrar sesión
│
└── [archivos de documentación y configuración]
    ├── README.md
    ├── GUIA_AWS.md
    ├── GUIA_HOSTING.md
    ├── GUIA_INFINITYFREE.md
    ├── RESUMEN_VALIDACIONES.md
    ├── vercel.json
    └── package.json
```

## Sistema de Rutas

El proyecto utiliza un sistema de rutas centralizado en `config/paths.php` que proporciona:

### Constantes de Rutas de Sistema (File System)
- `ROOT_PATH` - Ruta raíz del proyecto
- `ADMIN_PATH` - Ruta de administración
- `API_PATH` - Ruta de APIs
- `MODULES_PATH` - Ruta de módulos
- `ASSETS_PATH` - Ruta de recursos
- `CSS_PATH` - Ruta de CSS
- `JS_PATH` - Ruta de JavaScript
- `IMAGES_PATH` - Ruta de imágenes
- `UPLOADS_PATH` - Ruta de uploads

### Constantes de Rutas Web (URLs)
- `BASE_URL` - URL base
- `ADMIN_URL` - URL de administración
- `API_URL` - URL de APIs
- `CSS_URL` - URL de CSS
- `JS_URL` - URL de JavaScript
- `IMAGES_URL` - URL de imágenes

### Funciones Helper
- `css_url($path)` - Obtener URL de archivo CSS
- `js_url($path)` - Obtener URL de archivo JS
- `image_url($path)` - Obtener URL de imagen
- `admin_url($path)` - Obtener URL de admin
- `api_url($path)` - Obtener URL de API
- `module_url($module, $path)` - Obtener URL de módulo
- `require_component($file)` - Incluir componente

## Uso de Rutas

### En archivos PHP:
```php
<?php
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/config.php';

// Usar rutas
require_once UTILS_PATH . '/validaciones.php';
require_component('header-component.php');
?>
```

### En HTML/PHP:
```html
<link rel="stylesheet" href="<?php echo css_url('styles.css'); ?>">
<script src="<?php echo js_url('script.js'); ?>"></script>
<img src="<?php echo image_url('logo.png'); ?>" alt="Logo">
<a href="<?php echo admin_url('Login.php'); ?>">Login</a>
```

## Beneficios de esta Estructura

1. **Organización Clara**: Cada tipo de archivo tiene su lugar
2. **Mantenibilidad**: Fácil de encontrar y modificar archivos
3. **Escalabilidad**: Fácil agregar nuevos módulos
4. **Rutas Centralizadas**: Cambios de rutas en un solo lugar
5. **Seguridad**: Separación clara entre archivos públicos y privados
6. **Reutilización**: Componentes compartidos en una carpeta común

## Notas Importantes

- Todos los archivos PHP en `admin/`, `api/`, y `modules/` deben incluir `config/paths.php` al inicio
- Los componentes se incluyen usando `require_component()` en lugar de `include`
- Las rutas de assets se generan usando las funciones helper para mantener consistencia
- El archivo `index.html` está en `public/` y debe usar rutas relativas `../` para acceder a otros directorios

