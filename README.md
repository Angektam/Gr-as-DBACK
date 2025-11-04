# Sistema de Gestión de Grúas DBACK

Sistema completo de gestión y administración de servicios de grúas, desarrollado en PHP con MySQL.

## 🚛 Características Principales

- **Gestión de Solicitudes**: Creación, seguimiento y gestión de solicitudes de servicio
- **Administración de Grúas**: Control de flota de grúas con ubicación GPS
- **Asignación Automática**: Sistema inteligente de asignación automática de grúas
- **Gestión de Empleados**: Administración de personal y conductores
- **Control de Gastos**: Sistema de registro y seguimiento de gastos operativos
- **Panel de Administración**: Interfaz completa para administradores
- **Notificaciones**: Sistema de notificaciones en tiempo real
- **Reportes**: Generación de reportes y estadísticas

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: mysqli, json, session

## 🔧 Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/tu-usuario/DBACK-main.git
cd DBACK-main
```

2. Configurar la base de datos:
   - Crear una base de datos MySQL llamada `dback`
   - Importar el esquema de base de datos (si existe)

3. Configurar las credenciales:
   - Copiar `config-production.php` a `config.php`
   - Editar `config.php` con tus credenciales de base de datos:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   define('DB_NAME', 'dback');
   ```

4. Configurar permisos:
   - Asegurar que el directorio `uploads/` tenga permisos de escritura

## 📁 Estructura del Proyecto

```
DBACK-main/
├── CSS/                    # Estilos CSS
├── Elementos/              # Imágenes y recursos
├── Archivos-Auxiliares/    # Archivos auxiliares y documentación
├── api.php                 # API principal
├── conexion.php            # Conexión a base de datos
├── config.php              # Configuración (NO incluir en git)
├── index.html              # Página principal
├── Login.php               # Sistema de autenticación
├── MenuAdmin.PHP           # Panel de administración
├── solicitud.php           # Creación de solicitudes
├── Gruas.php               # Gestión de grúas
├── Empleados.php           # Gestión de empleados
├── Gastos.php              # Gestión de gastos
└── ...
```

## 🚀 Uso

1. Acceder al sistema desde el navegador
2. Usar la página de inicio para solicitar servicios
3. Los administradores pueden acceder al panel de administración mediante `Login.php`

## 🔐 Seguridad

- **IMPORTANTE**: No subir `config.php` al repositorio (está en .gitignore)
- Usar `config-production.php` como plantilla para producción
- Cambiar todas las contraseñas por defecto
- Configurar HTTPS en producción

## 👥 Contribución

Este es un proyecto privado. Para contribuciones, contactar al administrador del repositorio.

## 📝 Licencia

Proyecto privado - Todos los derechos reservados

## 📞 Contacto

Para más información sobre el sistema, contactar al equipo de desarrollo.

---

**Versión**: 2.0.0  
**Última actualización**: 2025

