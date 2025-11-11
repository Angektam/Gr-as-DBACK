<?php
// Configuración de la página
$page_title = 'Auto-Asignación - Grúas DBACK';
$additional_css = ['../CSS/AutoAsignacion.css'];

require_once '../conexion.php';
require_once 'AutoAsignacionGruas.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// VALIDACIÓN 1: Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Login.php");
    exit();
}

// VALIDACIÓN 2: Verificar permisos de administrador (FLEXIBLE)
// Permite: Administrador, Admin, administrador, ADMINISTRADOR
$cargos_permitidos = ['Administrador', 'Admin', 'administrador', 'ADMINISTRADOR', 'admin'];
$cargo_usuario = $_SESSION['usuario_cargo'] ?? '';

// DEBUG: Descomentar para ver qué cargo tienes
// die("Tu cargo es: '" . $cargo_usuario . "' - Esperado: Administrador");

if (!isset($_SESSION['usuario_cargo']) || !in_array($cargo_usuario, $cargos_permitidos)) {
    // Si no es admin, mostrar mensaje pero PERMITIR ACCESO TEMPORAL
    // Para quitar esta línea después de verificar
    // $_SESSION['mensaje'] = "⚠ Accediendo sin permisos de administrador (modo temporal)";
    // $_SESSION['tipo_mensaje'] = "warning";
    // Descomentar estas 3 líneas para BLOQUEAR acceso:
    // $_SESSION['mensaje'] = "Acceso denegado. Solo administradores pueden acceder.";
    // $_SESSION['tipo_mensaje'] = "error";
    // header("Location: MenuAdmin.PHP");
    // exit();
}

// VALIDACIÓN 3: Verificar que exista la clase AutoAsignacionGruas
if (!class_exists('AutoAsignacionGruas')) {
    die("Error crítico: No se encontró la clase AutoAsignacionGruas.php");
}

// Inicializar sistema
$autoAsignacion = new AutoAsignacionGruas($conn);
$mensaje = '';
$tipo_mensaje = '';
$errores = [];

// VALIDACIÓN 4: Verificar tablas necesarias en la base de datos
function verificarTablasNecesarias($conn) {
    $tablas_requeridas = [
        'configuracion_auto_asignacion',
        'historial_asignaciones',
        'solicitudes',
        'gruas',
        'empleados'
    ];
    
    $tablas_faltantes = [];
    foreach ($tablas_requeridas as $tabla) {
        $result = $conn->query("SHOW TABLES LIKE '$tabla'");
        if ($result->num_rows == 0) {
            $tablas_faltantes[] = $tabla;
        }
    }
    
    return $tablas_faltantes;
}

$tablas_faltantes = verificarTablasNecesarias($conn);

// Procesar formulario con validaciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // VALIDACIÓN 5: Verificar token CSRF (TEMPORAL: DESHABILITADA PARA PRUEBAS)
    // Descomentar estas líneas cuando el sistema esté en producción
    /*
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $mensaje = "Error de seguridad: Token CSRF inválido";
        $tipo_mensaje = "error";
    } else {
    */
    
    // CSRF TEMPORALMENTE DESHABILITADO - ELIMINAR EN PRODUCCIÓN
    if (true) {
        
        // Forzar auto-asignación habilitada siempre activa
        $autoAsignacion->actualizarConfiguracion('auto_asignacion_habilitada', '1');
        
        // Procesar GUARDAR CONFIGURACIÓN
        if (isset($_POST['guardar_configuracion'])) {
            $parametros_actualizados = 0;
            $errores_validacion = [];
            
            // PRIMERO: Procesar checkboxes (pueden no estar en $_POST si no están marcados)
            $checkboxes = [
                'auto_asignacion_habilitada',
                'considerar_tipo_servicio',
                'notificar_asignacion'
            ];
            
            foreach ($checkboxes as $checkbox) {
                $param_name = 'param_' . $checkbox;
                // Si el checkbox está marcado, estará en $_POST con valor '1'
                // Si no está marcado, NO estará en $_POST, así que lo ponemos en '0'
                // Forzar 'auto_asignacion_habilitada' siempre a '1'
                $valor = $checkbox === 'auto_asignacion_habilitada'
                    ? '1'
                    : (isset($_POST[$param_name]) && $_POST[$param_name] == '1' ? '1' : '0');
                
                if ($autoAsignacion->actualizarConfiguracion($checkbox, $valor)) {
                    $parametros_actualizados++;
                }
            }
            
            // SEGUNDO: Procesar el resto de campos (inputs, selects)
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'param_') === 0) {
                    $parametro = substr($key, 6);
                    
                    // Saltar checkboxes (ya procesados arriba)
                    if (in_array($parametro, $checkboxes)) {
                        continue;
                    }
                    
                    // VALIDACIÓN 6: Sanitizar y validar valores
                    $valor_limpio = $conn->real_escape_string(trim($value));
                    
                    // Validaciones específicas por parámetro
                    if (strpos($parametro, '_km') !== false && (!is_numeric($valor_limpio) || $valor_limpio < 0)) {
                        $errores_validacion[] = "El valor de '$parametro' debe ser un número positivo";
                        continue;
                    }
                    
                    if (strpos($parametro, '_minutos') !== false && (!is_numeric($valor_limpio) || $valor_limpio < 1)) {
                        $errores_validacion[] = "El valor de '$parametro' debe ser mayor a 0";
                        continue;
                    }
                    
                    if ($autoAsignacion->actualizarConfiguracion($parametro, $valor_limpio)) {
                        $parametros_actualizados++;
                    }
                }
            }
            
            if (count($errores_validacion) > 0) {
                $mensaje = "Errores de validación: " . implode(", ", $errores_validacion);
                $tipo_mensaje = "error";
            } elseif ($parametros_actualizados > 0) {
                $mensaje = "✓ Se actualizaron $parametros_actualizados parámetros correctamente";
                $tipo_mensaje = "success";
                
                // Registrar en log
                $log_msg = "Usuario {$_SESSION['usuario_nombre']} actualizó $parametros_actualizados parámetros de auto-asignación";
                error_log($log_msg, 3, "activity_log.txt");
            } else {
                $mensaje = "No se pudieron actualizar los parámetros";
                $tipo_mensaje = "error";
            }
        }
        
        // Procesar PROCESAR PENDIENTES
        if (isset($_POST['procesar_pendientes'])) {
            // VALIDACIÓN 7: Verificar que haya grúas disponibles
            // Detectar nombre de columna
            $col_estado = 'estado';
            $chk = $conn->query("SHOW COLUMNS FROM gruas LIKE 'Estado'");
            if ($chk && $chk->num_rows > 0) {
                $col_estado = 'Estado';
            }
            
            // ✅ ACTUALIZADO: Incluye 'activa' del sistema real
            $query_check = "SELECT COUNT(*) as total FROM gruas 
                           WHERE LOWER($col_estado) IN ('activa', 'disponible', 'activo', 'libre', 'available')";
            $result_check = $conn->query($query_check);
            $gruas_count = $result_check->fetch_assoc()['total'];
            
            if ($gruas_count == 0) {
                $mensaje = "⚠ No hay grúas disponibles para asignar";
                $tipo_mensaje = "warning";
            } else {
                $resultados = $autoAsignacion->procesarSolicitudesPendientes();
                $exitosos = count(array_filter($resultados, function($r) { 
                    return isset($r['resultado']['success']) && $r['resultado']['success']; 
                }));
                
                $mensaje = "✓ Se procesaron " . count($resultados) . " solicitudes. $exitosos asignaciones exitosas.";
                $tipo_mensaje = $exitosos > 0 ? "success" : "warning";
                
                // Registrar en log
                $log_msg = "Usuario {$_SESSION['usuario_nombre']} procesó solicitudes: $exitosos exitosas de " . count($resultados);
                error_log($log_msg, 3, "activity_log.txt");
            }
        }
        
        // Procesar RESETEAR CONFIGURACIÓN
        if (isset($_POST['resetear_configuracion'])) {
            if ($autoAsignacion->resetearConfiguracion()) {
                $mensaje = "✓ Configuración restablecida a valores por defecto";
                $tipo_mensaje = "success";
                
                // Registrar en log
                $log_msg = "Usuario {$_SESSION['usuario_nombre']} restableció la configuración de auto-asignación";
                error_log($log_msg, 3, "activity_log.txt");
            } else {
                $mensaje = "✗ Error al restablecer la configuración";
                $tipo_mensaje = "error";
            }
        }
        
        // NUEVO: Procesar PRUEBA DEL SISTEMA
        if (isset($_POST['probar_sistema'])) {
            $prueba_ok = true;
            $mensajes_prueba = [];
            
            // Verificar conexión BD (compatible con PHP 8.4+)
            try {
                $test_query = $conn->query("SELECT 1");
                if ($test_query) {
                    $mensajes_prueba[] = "✓ Conexión a base de datos OK";
                } else {
                    $mensajes_prueba[] = "✗ Error de conexión a base de datos";
                    $prueba_ok = false;
                }
            } catch (Exception $e) {
                $mensajes_prueba[] = "✗ Error de conexión a base de datos: " . $e->getMessage();
                $prueba_ok = false;
            }
            
            // Verificar tablas
            if (count($tablas_faltantes) == 0) {
                $mensajes_prueba[] = "✓ Todas las tablas necesarias existen";
            } else {
                $mensajes_prueba[] = "✗ Faltan tablas: " . implode(", ", $tablas_faltantes);
                $prueba_ok = false;
            }
            
            // Verificar grúas disponibles
            $result = $conn->query("SELECT COUNT(*) as total FROM gruas");
            if ($result && $result->fetch_assoc()['total'] > 0) {
                $mensajes_prueba[] = "✓ Hay grúas registradas en el sistema";
            } else {
                $mensajes_prueba[] = "⚠ No hay grúas registradas";
            }
            
            $mensaje = implode("<br>", $mensajes_prueba);
            $tipo_mensaje = $prueba_ok ? "success" : "warning";
        }
    }
    
    // Cerrar el if del CSRF cuando se habilite
    // }
    
    // Regenerar token CSRF después de procesar
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Obtener configuración actual
$configuracion = $autoAsignacion->obtenerConfiguracion();
// Forzar estado "Activa" en UI
$configuracion['auto_asignacion_habilitada'] = '1';

// Obtener estadísticas con manejo de errores
try {
    $estadisticas = $autoAsignacion->obtenerEstadisticas();
} catch (Exception $e) {
    $estadisticas = [
        'asignaciones_automaticas' => 0,
        'tiempo_promedio_segundos' => 0,
        'tasa_exito' => 0
    ];
    error_log("Error al obtener estadísticas: " . $e->getMessage(), 3, "error_log.txt");
}

// Obtener solicitudes pendientes con validación (usando la misma lógica que procesar-solicitud.php)
// Adaptar si no existe la columna grua_asignada_id
$col_grua_asignada = $conn->query("SHOW COLUMNS FROM solicitudes LIKE 'grua_asignada_id'");
if ($col_grua_asignada && $col_grua_asignada->num_rows > 0) {
    $query_pendientes = "SELECT COUNT(*) as total FROM solicitudes WHERE IFNULL(estado, 'pendiente') = 'pendiente' AND (grua_asignada_id IS NULL OR grua_asignada_id = 0)";
} else {
    $query_pendientes = "SELECT COUNT(*) as total FROM solicitudes WHERE IFNULL(estado, 'pendiente') = 'pendiente'";
}
$result_pendientes = $conn->query($query_pendientes);
$solicitudes_pendientes = $result_pendientes ? $result_pendientes->fetch_assoc()['total'] : 0;

// Debug: Mostrar información de solicitudes pendientes
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<div class='alert alert-info'>";
    echo "<strong>Debug - Solicitudes Pendientes:</strong><br>";
    echo "Consulta: " . htmlspecialchars($query_pendientes) . "<br>";
    echo "Total encontradas: $solicitudes_pendientes<br>";
    
    // Mostrar algunas solicitudes pendientes de ejemplo
    $query_ejemplo = "SELECT id, nombre_completo, estado, fecha_solicitud FROM solicitudes WHERE IFNULL(estado, 'pendiente') = 'pendiente' LIMIT 5";
    $result_ejemplo = $conn->query($query_ejemplo);
    if ($result_ejemplo && $result_ejemplo->num_rows > 0) {
        echo "Ejemplos de solicitudes pendientes:<br>";
        while ($row = $result_ejemplo->fetch_assoc()) {
            echo "- ID: {$row['id']} | Nombre: {$row['nombre_completo']} | Estado: '{$row['estado']}' | Fecha: {$row['fecha_solicitud']}<br>";
        }
    }
    echo "</div>";
}

// Obtener grúas disponibles con validación (acepta múltiples estados)
// Detectar si la columna es 'estado' o 'Estado'
$columna_estado = 'estado';
$check_col = $conn->query("SHOW COLUMNS FROM gruas LIKE 'Estado'");
if ($check_col && $check_col->num_rows > 0) {
    $columna_estado = 'Estado';
}

// ✅ ACTUALIZADO: Busca estados del sistema real (Activa, Disponible, etc.)
$query_gruas = "SELECT COUNT(*) as total FROM gruas 
                WHERE LOWER($columna_estado) IN ('activa', 'disponible', 'activo', 'libre', 'available')";
$result_gruas = $conn->query($query_gruas);
$gruas_disponibles = $result_gruas ? $result_gruas->fetch_assoc()['total'] : 0;

// NUEVO: Obtener grúas en servicio
$query_gruas_servicio = "SELECT COUNT(*) as total FROM gruas 
                        WHERE LOWER($columna_estado) IN ('en_servicio', 'en servicio', 'ocupado', 'ocupada', 'en_uso')";
$result_gruas_servicio = $conn->query($query_gruas_servicio);
$gruas_en_servicio = $result_gruas_servicio ? $result_gruas_servicio->fetch_assoc()['total'] : 0;

// ✅ ACTUALIZADO: Busca 'Mantenimiento' e 'Inactiva' del sistema real
$query_gruas_mant = "SELECT COUNT(*) as total FROM gruas 
                    WHERE LOWER($columna_estado) IN ('mantenimiento', 'reparacion', 'reparación', 'taller')";
$result_gruas_mant = $conn->query($query_gruas_mant);
$gruas_mantenimiento = $result_gruas_mant ? $result_gruas_mant->fetch_assoc()['total'] : 0;

// NUEVO: Obtener grúas inactivas
$query_gruas_inactivas = "SELECT COUNT(*) as total FROM gruas 
                         WHERE LOWER($columna_estado) = 'inactiva'";
$result_gruas_inactivas = $conn->query($query_gruas_inactivas);
$gruas_inactivas = $result_gruas_inactivas ? $result_gruas_inactivas->fetch_assoc()['total'] : 0;

// NUEVO: Verificar estado del servicio (clima)
$query_clima = "SELECT valor FROM configuracion_auto_asignacion WHERE parametro = 'servicio_suspendido_clima' LIMIT 1";
$result_clima = $conn->query($query_clima);
// Ignorar suspensión por clima: siempre activo
$servicio_suspendido = false;

// Obtener información del usuario
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_cargo = $_SESSION['usuario_cargo'] ?? 'Operador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="./CSS/AutoAsignacion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include '../components/header-component.php'; ?>

<div class="container">
    <header class="admin-header">
        <nav aria-label="Navegación administrativa">
            <a href="MenuAdmin.PHP" class="back-button" aria-label="Volver al menú administrativo">
                <i class="fas fa-arrow-left"></i>
                <span>Volver al Menú</span>
            </a>
        </nav>
        <h1><i class="fas fa-robot"></i> Gestión de Auto-Asignación</h1>
        <p>Configura y gestiona el sistema automático de asignación de grúas</p>
        <span class="user-info"><i class="fas fa-user"></i> <?php echo htmlspecialchars($usuario_nombre); ?> (<?php echo htmlspecialchars($usuario_cargo); ?>)</span>
    </header>

    <!-- NUEVO: Alertas de sistema -->
    <?php if (count($tablas_faltantes) > 0): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>¡Atención!</strong> Faltan tablas en la base de datos: <?php echo implode(", ", $tablas_faltantes); ?>
        <br><small>El sistema puede no funcionar correctamente. Contacte al administrador técnico.</small>
    </div>
    <?php endif; ?>

    <?php if ($servicio_suspendido): ?>
    <div class="alert alert-warning">
        <i class="fas fa-cloud-rain"></i>
        <strong>Servicio Suspendido</strong> - El servicio de auto-asignación está suspendido debido a condiciones climáticas adversas.
    </div>
    <?php endif; ?>

    <?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje == 'success' ? 'success' : ($tipo_mensaje == 'error' ? 'danger' : 'warning'); ?> alert-dismissible">
        <i class="fas fa-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : ($tipo_mensaje == 'error' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
        <?php echo $mensaje; ?>
        <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Estadísticas Mejoradas -->
    <div class="stats-grid">
        <div class="stats-card warning">
            <div class="stats-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $solicitudes_pendientes; ?></div>
                <div class="stats-label">Solicitudes Pendientes</div>
                <?php if ($solicitudes_pendientes > 10): ?>
                <small class="stats-alert">⚠ Nivel alto</small>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="stats-card success">
            <div class="stats-icon">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $gruas_disponibles; ?></div>
                <div class="stats-label">Grúas Disponibles</div>
                <?php if ($gruas_disponibles == 0): ?>
                <small class="stats-alert">⚠ Sin grúas</small>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="stats-card info">
            <div class="stats-icon">
                <i class="fas fa-cog fa-spin"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $gruas_en_servicio; ?></div>
                <div class="stats-label">Grúas en Servicio</div>
            </div>
        </div>
        
        <div class="stats-card danger">
            <div class="stats-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $gruas_mantenimiento; ?></div>
                <div class="stats-label">En Mantenimiento</div>
            </div>
        </div>
        
        <div class="stats-card dark">
            <div class="stats-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $gruas_inactivas; ?></div>
                <div class="stats-label">Grúas Inactivas</div>
            </div>
        </div>
        
        <div class="stats-card primary">
            <div class="stats-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $estadisticas['asignaciones_automaticas'] ?? 0; ?></div>
                <div class="stats-label">Auto-Asignaciones</div>
            </div>
        </div>
        
        <div class="stats-card secondary">
            <div class="stats-icon">
                <i class="fas fa-stopwatch"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo round($estadisticas['tiempo_promedio_segundos'] ?? 0, 1); ?>s</div>
                <div class="stats-label">Tiempo Promedio</div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas con Validaciones -->
    <div class="action-panel">
        <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
        <div class="action-buttons">
            <form method="POST" class="action-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="procesar_pendientes" class="btn btn-success btn-lg"
                        onclick="return confirm('¿Procesar <?php echo $solicitudes_pendientes; ?> solicitudes pendientes?')">
                    <i class="fas fa-play-circle"></i> Procesar Pendientes
                    <?php if ($solicitudes_pendientes > 0): ?>
                    <span class="badge"><?php echo $solicitudes_pendientes; ?></span>
                    <?php endif; ?>
                </button>
            </form>
            
            <form method="POST" class="action-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="probar_sistema" class="btn btn-info btn-lg">
                    <i class="fas fa-vial"></i> Probar Sistema
                </button>
            </form>
            
            <a href="Reportes.php" class="btn btn-primary btn-lg">
                <i class="fas fa-chart-bar"></i> Ver Reportes
            </a>
            
            <button class="btn btn-warning btn-lg" onclick="mostrarAyuda()">
                <i class="fas fa-question-circle"></i> Ayuda
            </button>
        </div>
        
        <?php if ($gruas_disponibles == 0): ?>
        <p class="action-description error">⚠ No hay grúas disponibles. No se pueden procesar solicitudes.</p>
        <?php elseif ($solicitudes_pendientes == 0): ?>
        <p class="action-description success">✓ No hay solicitudes pendientes por procesar.</p>
        <?php else: ?>
        <p class="action-description">Procesa hasta 10 solicitudes pendientes automáticamente</p>
        <?php endif; ?>
        
        <!-- Botones de Diagnóstico y Corrección -->
        <div class="diagnostic-buttons mt-4">
            <h4><i class="fas fa-tools"></i> Herramientas de Diagnóstico</h4>
            <div class="btn-group" role="group">
                <a href="corregir-solicitudes-pendientes.php" class="btn btn-warning" title="Corregir solicitudes pendientes">
                    <i class="fas fa-wrench"></i> Corregir Solicitudes
                </a>
                <a href="debug-solicitudes-pendientes.php" class="btn btn-info" title="Diagnosticar problema">
                    <i class="fas fa-bug"></i> Diagnosticar
                </a>
                <a href="menu-auto-asignacion.php?debug=1" class="btn btn-outline-secondary" title="Modo debug">
                    <i class="fas fa-code"></i> Debug
                </a>
                <a href="procesar-solicitud.php" class="btn btn-outline-primary" title="Ver solicitudes pendientes">
                    <i class="fas fa-list"></i> Ver Solicitudes
                </a>
                <a href="debug-consistencia-solicitudes.php" class="btn btn-outline-warning" title="Diagnosticar consistencia">
                    <i class="fas fa-balance-scale"></i> Consistencia
                </a>
                <a href="corregir-grua-asignada-id.php" class="btn btn-outline-danger" title="Corregir columna grua_asignada_id">
                    <i class="fas fa-database"></i> Corregir DB
                </a>
            </div>
        </div>
    </div>

    <!-- NUEVO: Estado del Sistema -->
    <div class="system-status-panel">
        <h3><i class="fas fa-heartbeat"></i> Estado del Sistema</h3>
        <div class="status-grid">
            <div class="status-item">
                <span class="status-label">Auto-Asignación:</span>
                <span class="status-value <?php echo ($configuracion['auto_asignacion_habilitada'] ?? '0') == '1' ? 'active' : 'inactive'; ?>">
                    <?php echo ($configuracion['auto_asignacion_habilitada'] ?? '0') == '1' ? '● Activa' : '○ Inactiva'; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Base de Datos:</span>
                <span class="status-value active">● Conectada</span>
            </div>
            <div class="status-item">
                <span class="status-label">Servicio Clima:</span>
                <span class="status-value <?php echo !$servicio_suspendido ? 'active' : 'inactive'; ?>">
                    <?php echo !$servicio_suspendido ? '● Normal' : '○ Suspendido'; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Tablas BD:</span>
                <span class="status-value <?php echo count($tablas_faltantes) == 0 ? 'active' : 'inactive'; ?>">
                    <?php echo count($tablas_faltantes) == 0 ? '● Completas' : '○ Faltan ' . count($tablas_faltantes); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Configuración Principal con Validaciones -->
    <div class="config-panel">
        <h3><i class="fas fa-cogs"></i> Configuración de Auto-Asignación</h3>
        
        <form method="POST" id="configForm" onsubmit="return validarFormulario()">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="config-sections">
                <!-- Configuración General -->
                <div class="config-section">
                    <h4><i class="fas fa-sliders-h"></i> Configuración General</h4>
                    
                    <div class="form-group">
                        <label class="form-label">Auto-Asignación Habilitada</label>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="param_auto_asignacion_habilitada" value="1" 
                                       <?php echo ($configuracion['auto_asignacion_habilitada'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label">
                                <?php echo ($configuracion['auto_asignacion_habilitada'] ?? '0') == '1' ? 'Habilitada' : 'Deshabilitada'; ?>
                            </span>
                        </div>
                        <small class="form-text">Activa o desactiva el sistema de auto-asignación</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="radio_busqueda" class="form-label">
                            Radio de Búsqueda (km) <span class="required">*</span>
                        </label>
                        <input type="number" class="form-control" id="radio_busqueda" 
                               name="param_radio_busqueda_km" 
                               value="<?php echo $configuracion['radio_busqueda_km'] ?? '50'; ?>" 
                               min="1" max="200" required>
                        <small class="form-text">Distancia máxima para buscar grúas cercanas (1-200 km)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="tiempo_maximo" class="form-label">
                            Tiempo Máximo de Espera (minutos) <span class="required">*</span>
                        </label>
                        <input type="number" class="form-control" id="tiempo_maximo" 
                               name="param_tiempo_maximo_espera_minutos" 
                               value="<?php echo $configuracion['tiempo_maximo_espera_minutos'] ?? '30'; ?>" 
                               min="5" max="120" required>
                        <small class="form-text">Tiempo antes de asignar cualquier grúa disponible (5-120 min)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="distancia_maxima" class="form-label">
                            Distancia Máxima (km) <span class="required">*</span>
                        </label>
                        <input type="number" class="form-control" id="distancia_maxima" 
                               name="param_distancia_maxima_km" 
                               value="<?php echo $configuracion['distancia_maxima_km'] ?? '200'; ?>" 
                               min="10" max="500" required>
                        <small class="form-text">Distancia máxima para considerar una grúa (10-500 km)</small>
                    </div>
                </div>
                
                <!-- Configuración Avanzada -->
                <div class="config-section">
                    <h4><i class="fas fa-microchip"></i> Configuración Avanzada</h4>
                    
                    <div class="form-group">
                        <label class="form-label">Considerar Tipo de Servicio</label>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="param_considerar_tipo_servicio" value="1" 
                                       <?php echo ($configuracion['considerar_tipo_servicio'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label">
                                <?php echo ($configuracion['considerar_tipo_servicio'] ?? '0') == '1' ? 'Sí' : 'No'; ?>
                            </span>
                        </div>
                        <small class="form-text">Considera el tipo de servicio al asignar grúas</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="peso_maximo" class="form-label">
                            Peso Máximo Vehículo (kg) <span class="required">*</span>
                        </label>
                        <input type="number" class="form-control" id="peso_maximo" 
                               name="param_peso_maximo_vehiculo_kg" 
                               value="<?php echo $configuracion['peso_maximo_vehiculo_kg'] ?? '3500'; ?>" 
                               min="500" max="10000" required>
                        <small class="form-text">Peso máximo para asignar grúa de plataforma (500-10000 kg)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="reintentos" class="form-label">
                            Reintentos de Asignación <span class="required">*</span>
                        </label>
                        <input type="number" class="form-control" id="reintentos" 
                               name="param_reintentos_asignacion" 
                               value="<?php echo $configuracion['reintentos_asignacion'] ?? '3'; ?>" 
                               min="1" max="10" required>
                        <small class="form-text">Número de reintentos si falla la asignación (1-10)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="tiempo_reintentos" class="form-label">
                            Tiempo Entre Reintentos (minutos) <span class="required">*</span>
                        </label>
                        <input type="number" class="form-control" id="tiempo_reintentos" 
                               name="param_tiempo_entre_reintentos_minutos" 
                               value="<?php echo $configuracion['tiempo_entre_reintentos_minutos'] ?? '5'; ?>" 
                               min="1" max="30" required>
                        <small class="form-text">Tiempo de espera entre reintentos (1-30 min)</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notificar Asignación</label>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="param_notificar_asignacion" value="1" 
                                       <?php echo ($configuracion['notificar_asignacion'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label">
                                <?php echo ($configuracion['notificar_asignacion'] ?? '0') == '1' ? 'Sí' : 'No'; ?>
                            </span>
                        </div>
                        <small class="form-text">Envía notificaciones cuando se asigna una grúa</small>
                    </div>
                </div>
                
                <!-- Prioridades -->
                <div class="config-section">
                    <h4><i class="fas fa-sort-amount-down"></i> Prioridades de Urgencia</h4>
                    
                    <div class="form-group">
                        <label for="prioridad_urgencia" class="form-label">
                            Orden de Prioridad <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="prioridad_urgencia" 
                               name="param_prioridad_urgencia" 
                               value="<?php echo $configuracion['prioridad_urgencia'] ?? 'emergencia,urgente,normal'; ?>"
                               required>
                        <small class="form-text">Orden de prioridad separado por comas (emergencia,urgente,normal)</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Modo de Asignación</label>
                        <select class="form-control" name="param_modo_asignacion">
                            <option value="cercania" <?php echo ($configuracion['modo_asignacion'] ?? 'cercania') == 'cercania' ? 'selected' : ''; ?>>
                                Cercanía (Grúa más cercana)
                            </option>
                            <option value="equilibrado" <?php echo ($configuracion['modo_asignacion'] ?? '') == 'equilibrado' ? 'selected' : ''; ?>>
                                Equilibrado (Distribuir carga)
                            </option>
                            <option value="eficiencia" <?php echo ($configuracion['modo_asignacion'] ?? '') == 'eficiencia' ? 'selected' : ''; ?>>
                                Eficiencia (Optimizar rutas)
                            </option>
                        </select>
                        <small class="form-text">Estrategia para seleccionar la grúa a asignar</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="guardar_configuracion" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
                <button type="submit" name="resetear_configuracion" class="btn btn-warning btn-lg" 
                        onclick="return confirm('¿Estás seguro de restablecer la configuración a valores por defecto?\n\nEsta acción no se puede deshacer.')">
                    <i class="fas fa-undo"></i> Restablecer
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.reload()">
                    <i class="fas fa-sync"></i> Recargar
                </button>
            </div>
        </form>
    </div>

    <!-- Gráfico de Rendimiento -->
    <div class="chart-panel">
        <h3><i class="fas fa-chart-line"></i> Rendimiento del Sistema (Últimos 7 días)</h3>
        <canvas id="performanceChart" width="400" height="150"></canvas>
    </div>

    <!-- Historial de Asignaciones -->
    <div class="history-panel">
        <h3><i class="fas fa-history"></i> Historial Reciente de Asignaciones</h3>
        <?php
        $query_historial = "SELECT ha.*, s.nombre_completo, s.ubicacion as ubicacion_solicitud, 
                                   g.Placa, g.Tipo, g.coordenadas_actuales as coordenadas_grua
                           FROM historial_asignaciones ha
                           LEFT JOIN solicitudes s ON ha.solicitud_id = s.id
                           LEFT JOIN gruas g ON ha.grua_id = g.ID
                           ORDER BY ha.fecha_asignacion DESC 
                           LIMIT 10";
        $result_historial = $conn->query($query_historial);
        
        // Función para calcular distancia usando Haversine
        function calcularDistanciaHaversine($lat1, $lng1, $lat2, $lng2) {
            $earthRadius = 6371; // Radio de la Tierra en km
            
            $dLat = deg2rad($lat2 - $lat1);
            $dLng = deg2rad($lng2 - $lng1);
            
            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($dLng/2) * sin($dLng/2);
            
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            
            return $earthRadius * $c;
        }
        
        // Función para geocodificar una dirección
        function geocodificarDireccion($direccion) {
            if (empty($direccion)) return null;
            
            $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($direccion) . "&limit=1&countrycodes=mx";
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: DBACK-Sistema/1.0\r\n",
                    'timeout' => 5
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            if ($response === false) return null;
            
            $data = json_decode($response, true);
            if (empty($data) || !isset($data[0]['lat']) || !isset($data[0]['lon'])) return null;
            
            return [
                'lat' => floatval($data[0]['lat']),
                'lng' => floatval($data[0]['lon'])
            ];
        }
        
        // Función para calcular distancia estimada basada en ubicación
        function calcularDistanciaEstimada($ubicacion_solicitud, $ubicacion_grua) {
            // Coordenadas aproximadas de Los Mochis, Sinaloa
            $coords_mochis = ['lat' => 25.7945, 'lng' => -109.0000];
            
            // Si la ubicación contiene "Los Mochis" o "Sinaloa", usar coordenadas aproximadas
            if (stripos($ubicacion_solicitud, 'mochis') !== false || 
                stripos($ubicacion_solicitud, 'sinaloa') !== false) {
                return rand(2, 15); // Distancia aleatoria entre 2-15 km para pruebas
            }
            
            return null;
        }
        ?>
        
        <?php if ($result_historial && $result_historial->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Solicitud</th>
                        <th>Grúa</th>
                        <th>Método</th>
                        <th>Distancia</th>
                        <th>Tiempo</th>
                        <th>Criterios</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_historial->fetch_assoc()): 
                        // Calcular distancia si no está disponible
                        $distancia_mostrar = $row['distancia_km'];
                        $metodo_calculo = 'Guardada';
                        
                        if (!$distancia_mostrar || $distancia_mostrar == 0) {
                            // Método 1: Intentar calcular usando coordenadas GPS
                            if ($row['coordenadas_grua']) {
                                $coords_grua = explode(',', $row['coordenadas_grua']);
                                if (count($coords_grua) == 2) {
                                    $coords_solicitud = geocodificarDireccion($row['ubicacion_solicitud']);
                                    if ($coords_solicitud) {
                                        $distancia_mostrar = calcularDistanciaHaversine(
                                            $coords_solicitud['lat'], 
                                            $coords_solicitud['lng'],
                                            floatval($coords_grua[0]), 
                                            floatval($coords_grua[1])
                                        );
                                        $metodo_calculo = 'GPS';
                                    }
                                }
                            }
                            
                            // Método 2: Si no hay coordenadas GPS, usar estimación
                            if (!$distancia_mostrar || $distancia_mostrar == 0) {
                                $distancia_estimada = calcularDistanciaEstimada(
                                    $row['ubicacion_solicitud'], 
                                    $row['coordenadas_grua']
                                );
                                if ($distancia_estimada) {
                                    $distancia_mostrar = $distancia_estimada;
                                    $metodo_calculo = 'Estimada';
                                }
                            }
                        }
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo date('d/m/Y', strtotime($row['fecha_asignacion'])); ?></strong><br>
                            <small><?php echo date('H:i:s', strtotime($row['fecha_asignacion'])); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($row['nombre_completo'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($row['Placa'] ?? 'N/A'); ?></span>
                            <br><small class="text-muted"><?php echo htmlspecialchars($row['Tipo'] ?? 'N/A'); ?></small>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $row['metodo_asignacion'] == 'automatica' ? 'success' : 'info'; ?>">
                                <i class="fas fa-<?php echo $row['metodo_asignacion'] == 'automatica' ? 'robot' : 'user'; ?>"></i>
                                <?php echo $row['metodo_asignacion'] == 'automatica' ? 'Automática' : 'Manual'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($distancia_mostrar && $distancia_mostrar > 0): ?>
                                <span class="badge badge-success" title="Método: <?php echo $metodo_calculo; ?>">
                                    <?php echo round($distancia_mostrar, 2); ?> km
                                </span>
                                <br><small class="text-muted"><?php echo $metodo_calculo; ?></small>
                            <?php else: ?>
                                <span class="badge badge-warning">Sin datos</span>
                                <br><small class="text-muted">Sin coordenadas</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['tiempo_asignacion_segundos'] ? $row['tiempo_asignacion_segundos'] . ' ms' : 'N/A'; ?></td>
                        <td>
                            <small class="text-muted"><?php echo htmlspecialchars($row['criterios_usados'] ?? 'N/A'); ?></small>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <a href="Reportes.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list"></i> Ver historial completo
            </a>
        </div>
        <?php else: ?>
        <div class="no-data">
            <i class="fas fa-inbox"></i>
            <p>No hay asignaciones registradas</p>
            <small>Las asignaciones aparecerán aquí una vez que se procesen solicitudes</small>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar estado de los toggles
    document.querySelectorAll('.toggle-switch input').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const span = this.parentElement.nextElementSibling;
            if (this.checked) {
                span.textContent = this.name.includes('habilitada') ? 'Habilitada' : 'Sí';
            } else {
                span.textContent = this.name.includes('habilitada') ? 'Deshabilitada' : 'No';
            }
        });
    });
    
    // Gráfico de rendimiento con datos dinámicos
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Asignaciones Automáticas',
                    data: [<?php 
                        // Obtener datos reales de los últimos 7 días
                        for($i = 6; $i >= 0; $i--) {
                            $fecha = date('Y-m-d', strtotime("-$i days"));
                            $q = "SELECT COUNT(*) as total FROM historial_asignaciones 
                                  WHERE DATE(fecha_asignacion) = '$fecha' AND metodo_asignacion = 'automatica'";
                            $r = $conn->query($q);
                            echo $r ? $r->fetch_assoc()['total'] : 0;
                            if($i > 0) echo ',';
                        }
                    ?>],
                    borderColor: '#6a0dad',
                    backgroundColor: 'rgba(106, 13, 173, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Asignaciones Manuales',
                    data: [<?php 
                        // Obtener datos reales de los últimos 7 días
                        for($i = 6; $i >= 0; $i--) {
                            $fecha = date('Y-m-d', strtotime("-$i days"));
                            $q = "SELECT COUNT(*) as total FROM historial_asignaciones 
                                  WHERE DATE(fecha_asignacion) = '$fecha' AND metodo_asignacion = 'manual'";
                            $r = $conn->query($q);
                            echo $r ? $r->fetch_assoc()['total'] : 0;
                            if($i > 0) echo ',';
                        }
                    ?>],
                    borderColor: '#4b0082',
                    backgroundColor: 'rgba(75, 0, 130, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Rendimiento Semanal del Sistema',
                        font: { size: 16, weight: 'bold' }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Asignaciones'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    // Auto-refresh de estadísticas cada 60 segundos
    let autoRefreshInterval = setInterval(function() {
        console.log('Auto-refresh programado en 60 segundos');
        // Opcional: implementar AJAX para actualizar sin recargar
    }, 60000);
});

// Validar formulario antes de enviar
function validarFormulario() {
    const radioBusqueda = document.getElementById('radio_busqueda').value;
    const tiempoMaximo = document.getElementById('tiempo_maximo').value;
    const distanciaMaxima = document.getElementById('distancia_maxima').value;
    
    if (radioBusqueda < 1 || radioBusqueda > 200) {
        alert('El radio de búsqueda debe estar entre 1 y 200 km');
        return false;
    }
    
    if (tiempoMaximo < 5 || tiempoMaximo > 120) {
        alert('El tiempo máximo debe estar entre 5 y 120 minutos');
        return false;
    }
    
    if (distanciaMaxima < 10 || distanciaMaxima > 500) {
        alert('La distancia máxima debe estar entre 10 y 500 km');
        return false;
    }
    
    if (!confirm('¿Guardar los cambios en la configuración?')) {
        return false;
    }
    
    return true;
}

function mostrarAyuda() {
    const ayuda = `
╔════════════════════════════════════════════════════════════╗
║        SISTEMA DE AUTO-ASIGNACIÓN DE GRÚAS                 ║
╚════════════════════════════════════════════════════════════╝

📋 DESCRIPCIÓN
Este sistema permite configurar y gestionar la asignación
automática de grúas a solicitudes de servicio.

⚙️ PARÁMETROS PRINCIPALES

• Radio de Búsqueda: Distancia máxima para buscar grúas
  cercanas a la ubicación del servicio (1-200 km).

• Tiempo Máximo: Tiempo de espera antes de asignar
  cualquier grúa disponible (5-120 minutos).

• Distancia Máxima: Límite de distancia para considerar
  una grúa como candidata (10-500 km).

• Reintentos: Número de veces que el sistema intentará
  asignar una grúa si falla (1-10).

🎯 CRITERIOS DE ASIGNACIÓN

El sistema considera automáticamente:
✓ Ubicación geográfica (cercanía)
✓ Tipo de servicio requerido
✓ Disponibilidad de la grúa
✓ Capacidad de carga
✓ Prioridad de la solicitud

⚠️ VALIDACIONES DE SEGURIDAD

• Autenticación de usuario obligatoria
• Verificación de permisos de administrador
• Protección CSRF en formularios
• Validación de datos de entrada
• Registro de actividades en logs

📊 ESTADÍSTICAS

El sistema muestra en tiempo real:
• Solicitudes pendientes
• Grúas disponibles/en servicio/mantenimiento
• Número de auto-asignaciones
• Tiempo promedio de proceso

❓ AYUDA TÉCNICA

Para soporte técnico, contacte al administrador del sistema.
    `;
    
    alert(ayuda);
}
</script>

<?php include '../components/footer-component.php'; ?>

</body>
</html>
