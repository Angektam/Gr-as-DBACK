# Sistema Mejorado de Auto-Asignación con Gestión de Clima y Notificaciones

## 📋 Descripción General

Este sistema mejora la auto-asignación de grúas con tres funcionalidades principales:

1. **Gestión de Condiciones Climáticas** - Control de servicio según el clima
2. **Manejo de Disponibilidad de Grúas** - Notificaciones cuando no hay grúas disponibles
3. **Sistema de Notificaciones en Tiempo Real** - Alertas automáticas para usuarios

---

## 🚀 Instalación

### Paso 1: Ejecutar el Script SQL

Ejecuta el archivo `configuracion-clima-notificaciones.sql` en tu base de datos:

```bash
mysql -u tu_usuario -p tu_base_de_datos < configuracion-clima-notificaciones.sql
```

Esto creará las siguientes tablas:
- `notificaciones_usuarios` - Almacena notificaciones para usuarios
- `suspension_servicio` - Registra suspensiones del servicio
- `eventos_sistema` - Log de eventos importantes

### Paso 2: Verificar Archivos

Asegúrate de tener los siguientes archivos en tu proyecto:

- `AutoAsignacionGruas.php` (actualizado)
- `gestion-clima-servicio.php`
- `api-notificaciones.php`
- `widget-notificaciones.php`
- `configuracion-clima-notificaciones.sql`

---

## 🎯 Funcionalidades

### 1. Gestión de Clima y Servicio

**Acceso:** `gestion-clima-servicio.php`

**Características:**
- ✅ Suspender servicio manualmente por clima adverso
- ✅ Reactivar servicio cuando mejora el clima
- ✅ Configurar condiciones climáticas que bloquean automáticamente
- ✅ Ver historial de suspensiones
- ✅ Monitorear eventos del sistema

**Condiciones Climáticas Configurables:**
- Lluvia Fuerte
- Vientos Fuertes
- Niebla Densa
- Tormentas Eléctricas

**Cómo usar:**
1. Accede como administrador
2. Ve a "Configuración Auto-Asignación"
3. Haz clic en "Gestión de Clima"
4. Selecciona las condiciones que quieres bloquear
5. O suspende manualmente el servicio con una razón específica

### 2. Sistema de Notificaciones

**API Endpoint:** `api-notificaciones.php`

**Acciones disponibles:**

```javascript
// Obtener notificaciones del usuario
GET api-notificaciones.php?accion=obtener_notificaciones&limite=20

// Marcar notificación como leída
POST api-notificaciones.php
Body: accion=marcar_leida&notificacion_id=123

// Marcar todas como leídas
POST api-notificaciones.php
Body: accion=marcar_todas_leidas

// Obtener estado del servicio
GET api-notificaciones.php?accion=estado_servicio

// Obtener alertas del sistema
GET api-notificaciones.php?accion=obtener_alertas_sistema
```

### 3. Widget de Notificaciones

**Archivo:** `widget-notificaciones.php`

**Integración en tus páginas:**

```php
<?php include 'widget-notificaciones.php'; ?>
```

**Características del Widget:**
- 🔔 Campana de notificaciones con badge de contador
- 📋 Panel desplegable con lista de notificaciones
- 🎨 Iconos y colores según tipo de notificación
- ⚡ Actualización automática cada 30 segundos
- 📊 Barra de estado del servicio
- 🚨 Alertas emergentes para eventos importantes

---

## 💡 Casos de Uso

### Caso 1: Mal Clima - Suspensión del Servicio

**Escenario:** Hay una tormenta eléctrica

**Proceso:**
1. Administrador accede a "Gestión de Clima"
2. Selecciona "Suspender Servicio"
3. Elige tipo: "Condiciones Climáticas"
4. Escribe razón: "Tormenta eléctrica en la zona"
5. El sistema:
   - ❌ Bloquea nuevas asignaciones
   - 📧 Notifica a usuarios con solicitudes pendientes
   - 🚨 Muestra alerta en la barra superior
   - 📝 Registra el evento

### Caso 2: Sin Grúas Disponibles

**Escenario:** Usuario solicita servicio pero no hay grúas

**Proceso:**
1. Usuario envía solicitud
2. Sistema verifica grúas disponibles
3. No encuentra ninguna disponible
4. Automáticamente:
   - 📩 Envía notificación al usuario
   - ⏰ Coloca solicitud en cola de espera
   - 👨‍💼 Alerta a administradores
   - 📊 Registra evento en el sistema

### Caso 3: Asignación Exitosa

**Escenario:** Hay grúas disponibles y buen clima

**Proceso:**
1. Usuario envía solicitud
2. Sistema verifica:
   - ✅ Servicio activo
   - ✅ Clima favorable
   - ✅ Grúas disponibles
3. Asigna grúa automáticamente
4. Notifica al usuario:
   - "¡Grúa asignada exitosamente!"
   - "Placa: ABC-123"
   - "Distancia aproximada: 5.2 km"

---

## 🔧 Configuración

### Parámetros de Configuración

En la tabla `configuracion_auto_asignacion`:

```sql
-- Clima
servicio_suspendido_clima = 0/1
verificar_clima_automatico = 0/1
bloquear_lluvia_fuerte = 0/1
bloquear_vientos_fuertes = 0/1
bloquear_niebla_densa = 0/1
bloquear_tormenta = 0/1

-- Notificaciones
enviar_sms_notificaciones = 0/1
enviar_email_notificaciones = 0/1
enviar_email_admin = 0/1
```

### Procedimientos Almacenados

**Suspender Servicio:**
```sql
CALL suspender_servicio_clima('Tormenta eléctrica', 'clima', usuario_id);
```

**Reactivar Servicio:**
```sql
CALL reactivar_servicio();
```

**Marcar Notificación Leída:**
```sql
CALL marcar_notificacion_leida(notificacion_id);
```

---

## 📊 Tipos de Notificaciones

| Tipo | Color | Icono | Uso |
|------|-------|-------|-----|
| `info` | Azul | ℹ️ | Información general |
| `success` | Verde | ✅ | Operación exitosa |
| `warning` | Amarillo | ⚠️ | Advertencia |
| `danger` | Rojo | ❌ | Error o suspensión |
| `admin` | Morado | 👨‍💼 | Notificación administrativa |

---

## 🔐 Permisos

### Administrador
- ✅ Suspender/Reactivar servicio
- ✅ Configurar condiciones climáticas
- ✅ Ver historial completo
- ✅ Recibir alertas de sistema

### Usuario Regular
- ✅ Ver sus notificaciones
- ✅ Ver estado del servicio
- ✅ Recibir alertas de sus solicitudes

---

## 📱 Ejemplo de Integración Completa

```php
<?php
session_start();
require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Página</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Incluir widget de notificaciones -->
    <?php include 'widget-notificaciones.php'; ?>
    
    <div class="container">
        <h1>Bienvenido al Sistema</h1>
        
        <!-- Tu contenido aquí -->
        
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## 🐛 Troubleshooting

### Las notificaciones no aparecen

**Solución:**
1. Verifica que ejecutaste el script SQL
2. Asegúrate de que la sesión está iniciada
3. Verifica permisos en `api-notificaciones.php`

### El servicio no se suspende

**Solución:**
1. Verifica que el usuario sea administrador
2. Revisa los logs en la tabla `eventos_sistema`
3. Verifica la conexión a la base de datos

### Las grúas no se asignan automáticamente

**Solución:**
1. Verifica que el servicio esté activo
2. Comprueba que no esté suspendido por clima
3. Verifica que haya grúas disponibles

---

## 📈 Mejoras Futuras

- [ ] Integración con API de clima en tiempo real (OpenWeatherMap)
- [ ] Envío de SMS para notificaciones críticas
- [ ] Notificaciones push en aplicación móvil
- [ ] Dashboard de analíticas de clima
- [ ] Predicción de demanda según clima
- [ ] Sistema de alertas tempranas

---

## 👨‍💻 Soporte

Para soporte técnico o reportar bugs, contacta al equipo de desarrollo.

**Documentación creada:** Octubre 2025
**Versión:** 1.0
**Autor:** Sistema DBACK

