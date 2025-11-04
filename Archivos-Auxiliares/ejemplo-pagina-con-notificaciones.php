<?php
/**
 * EJEMPLO DE PÁGINA CON WIDGET DE NOTIFICACIONES
 * Este es un ejemplo de cómo integrar el widget de notificaciones
 * en cualquier página de tu sistema
 */

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';
// La sesión ya se inicia en config.php

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

// Obtener información del usuario
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_cargo = $_SESSION['usuario_cargo'] ?? 'Usuario';

// Inicializar auto-asignación para verificar estado
$autoAsignacion = new AutoAsignacionGruas($conn);
$estado_servicio = $autoAsignacion->obtenerEstadoServicio();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo - Sistema con Notificaciones | DBACK</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .header-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        .status-indicator.active {
            background: #27ae60;
        }
        
        .status-indicator.inactive {
            background: #e74c3c;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list i {
            color: #3498db;
            margin-right: 10px;
        }
        
        code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <!-- WIDGET DE NOTIFICACIONES - Solo necesitas incluir esta línea -->
    <?php include 'widget-notificaciones.php'; ?>
    
    <div class="main-container">
        <div class="header-section">
            <h1>
                <i class="fas fa-bell"></i> 
                Ejemplo de Integración del Sistema de Notificaciones
            </h1>
            <p class="text-muted">
                Esta página demuestra cómo integrar el widget de notificaciones y estado del servicio
            </p>
            
            <div class="mt-3">
                <span class="status-indicator <?php echo $estado_servicio['servicio_activo'] ? 'active' : 'inactive'; ?>"></span>
                <strong>Estado del Servicio:</strong> 
                <?php echo $estado_servicio['servicio_activo'] ? 'Operativo' : 'Suspendido'; ?>
            </div>
        </div>

        <!-- Información del Usuario -->
        <div class="info-card">
            <h4><i class="fas fa-user"></i> Información del Usuario</h4>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario_nombre); ?></p>
            <p><strong>Cargo:</strong> <?php echo htmlspecialchars($usuario_cargo); ?></p>
            <p><strong>ID de Sesión:</strong> <?php echo $_SESSION['usuario_id']; ?></p>
        </div>

        <!-- Estado del Servicio -->
        <div class="info-card">
            <h4><i class="fas fa-cloud-sun"></i> Estado Actual del Servicio</h4>
            
            <ul class="feature-list">
                <li>
                    <i class="fas fa-<?php echo $estado_servicio['servicio_activo'] ? 'check-circle text-success' : 'times-circle text-danger'; ?>"></i>
                    <strong>Servicio:</strong> 
                    <?php echo $estado_servicio['servicio_activo'] ? 'Activo y operativo' : 'Suspendido'; ?>
                </li>
                
                <li>
                    <i class="fas fa-truck"></i>
                    <strong>Grúas Disponibles:</strong> 
                    <?php echo $estado_servicio['gruas_disponibles']; ?>
                </li>
                
                <li>
                    <i class="fas fa-clock"></i>
                    <strong>Solicitudes Pendientes:</strong> 
                    <?php echo $estado_servicio['solicitudes_pendientes']; ?>
                </li>
                
                <li>
                    <i class="fas fa-cloud"></i>
                    <strong>Clima:</strong> 
                    <?php echo $estado_servicio['clima_apto'] ? 'Condiciones favorables' : 'Condiciones adversas'; ?>
                </li>
            </ul>
            
            <?php if (!$estado_servicio['servicio_activo']): ?>
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Motivo:</strong> <?php echo htmlspecialchars($estado_servicio['razon_inactivo']); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Guía de Integración -->
        <div class="info-card">
            <h4><i class="fas fa-code"></i> Cómo Integrar el Widget</h4>
            
            <p>Para agregar el widget de notificaciones a cualquier página, solo necesitas:</p>
            
            <h6 class="mt-4">1. Incluir el archivo PHP</h6>
            <pre>&lt;?php include 'widget-notificaciones.php'; ?&gt;</pre>
            
            <h6 class="mt-4">2. Asegurarte de tener las librerías necesarias</h6>
            <pre>&lt;!-- Bootstrap CSS --&gt;
&lt;link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"&gt;

&lt;!-- Font Awesome --&gt;
&lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"&gt;</pre>
            
            <h6 class="mt-4">3. El widget incluye automáticamente:</h6>
            <ul class="feature-list">
                <li>
                    <i class="fas fa-bell"></i>
                    Campana de notificaciones con contador de no leídas
                </li>
                <li>
                    <i class="fas fa-list"></i>
                    Panel desplegable con lista de notificaciones
                </li>
                <li>
                    <i class="fas fa-info-circle"></i>
                    Barra de estado del servicio
                </li>
                <li>
                    <i class="fas fa-exclamation-triangle"></i>
                    Alertas emergentes para eventos importantes
                </li>
                <li>
                    <i class="fas fa-sync"></i>
                    Actualización automática cada 30 segundos
                </li>
            </ul>
        </div>

        <!-- Funciones JavaScript Disponibles -->
        <div class="info-card">
            <h4><i class="fas fa-code"></i> Funciones JavaScript Disponibles</h4>
            
            <p>El widget proporciona las siguientes funciones que puedes usar:</p>
            
            <ul class="feature-list">
                <li>
                    <code>toggleNotifications()</code> - Abrir/cerrar panel de notificaciones
                </li>
                <li>
                    <code>cargarNotificaciones()</code> - Recargar lista de notificaciones
                </li>
                <li>
                    <code>marcarLeida(notifId)</code> - Marcar una notificación como leída
                </li>
                <li>
                    <code>marcarTodasLeidas()</code> - Marcar todas las notificaciones como leídas
                </li>
                <li>
                    <code>verificarEstadoServicio()</code> - Verificar estado actual del servicio
                </li>
            </ul>
            
            <h6 class="mt-4">Ejemplo de uso:</h6>
            <pre>// Forzar recarga de notificaciones
cargarNotificaciones();

// Verificar estado del servicio
verificarEstadoServicio();</pre>
        </div>

        <!-- Botones de Prueba -->
        <div class="info-card">
            <h4><i class="fas fa-vial"></i> Prueba las Funciones</h4>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i> Abrir Notificaciones
                    </button>
                </div>
                
                <div class="col-md-4">
                    <button class="btn btn-success w-100" onclick="cargarNotificaciones()">
                        <i class="fas fa-sync"></i> Recargar Notificaciones
                    </button>
                </div>
                
                <div class="col-md-4">
                    <button class="btn btn-warning w-100" onclick="verificarEstadoServicio()">
                        <i class="fas fa-cloud-sun"></i> Verificar Estado
                    </button>
                </div>
            </div>
        </div>

        <!-- Enlaces Útiles -->
        <div class="info-card">
            <h4><i class="fas fa-link"></i> Enlaces Útiles</h4>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="configuracion-auto-asignacion.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-cog"></i> Configuración Auto-Asignación
                    </a>
                </div>
                
                <div class="col-md-6">
                    <a href="gestion-clima-servicio.php" class="btn btn-outline-warning w-100">
                        <i class="fas fa-cloud-sun"></i> Gestión de Clima
                    </a>
                </div>
                
                <div class="col-md-6">
                    <a href="INSTRUCCIONES-SISTEMA-CLIMA-NOTIFICACIONES.md" class="btn btn-outline-info w-100" target="_blank">
                        <i class="fas fa-book"></i> Documentación Completa
                    </a>
                </div>
                
                <div class="col-md-6">
                    <a href="MenuAdmin.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-home"></i> Volver al Menú Principal
                    </a>
                </div>
            </div>
        </div>

        <!-- Nota de Desarrollo -->
        <div class="alert alert-info mt-4">
            <i class="fas fa-lightbulb"></i>
            <strong>Nota:</strong> Este es un ejemplo de integración. En producción, 
            puedes personalizar el estilo y comportamiento del widget según tus necesidades.
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script personalizado -->
    <script>
        // Ejemplo: Mostrar alerta cuando se carga la página
        console.log('Widget de notificaciones cargado correctamente');
        
        // Ejemplo: Función personalizada para mostrar mensaje
        function mostrarMensajePersonalizado() {
            alert('El sistema de notificaciones está funcionando correctamente!');
        }
    </script>
</body>
</html>

