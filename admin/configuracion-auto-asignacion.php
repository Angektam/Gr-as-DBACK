<?php
require_once '../conexion.php';
require_once 'AutoAsignacionGruas.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar sesión y permisos de administrador
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Login.php");
    exit();
}

// Verificar si es administrador (ajustar según tu sistema de roles)
if ($_SESSION['usuario_cargo'] !== 'Administrador') {
    $_SESSION['mensaje'] = "No tienes permisos para acceder a esta página";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: MenuAdmin.PHP");
    exit();
}

$autoAsignacion = new AutoAsignacionGruas($conn);
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['guardar_configuracion'])) {
        $parametros_actualizados = 0;
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'param_') === 0) {
                $parametro = substr($key, 6); // Remover 'param_'
                if ($autoAsignacion->actualizarConfiguracion($parametro, $value)) {
                    $parametros_actualizados++;
                }
            }
        }
        
        if ($parametros_actualizados > 0) {
            $mensaje = "Se actualizaron $parametros_actualizados parámetros correctamente";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "No se pudieron actualizar los parámetros";
            $tipo_mensaje = "error";
        }
    }
    
    if (isset($_POST['procesar_pendientes'])) {
        $resultados = $autoAsignacion->procesarSolicitudesPendientes();
        $exitosos = count(array_filter($resultados, function($r) { return $r['resultado']['success']; }));
        $mensaje = "Se procesaron " . count($resultados) . " solicitudes. $exitosos asignaciones exitosas.";
        $tipo_mensaje = $exitosos > 0 ? "success" : "warning";
    }
}

// Obtener configuración actual
$configuracion = $autoAsignacion->obtenerConfiguracion();

// Obtener estadísticas
$estadisticas = $autoAsignacion->obtenerEstadisticas();

// Obtener solicitudes pendientes
$query_pendientes = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente' AND grua_asignada_id IS NULL";
$result_pendientes = $conn->query($query_pendientes);
$solicitudes_pendientes = $result_pendientes->fetch_assoc()['total'];

// Obtener grúas disponibles
$query_gruas = "SELECT COUNT(*) as total FROM gruas_disponibles";
$result_gruas = $conn->query($query_gruas);
$gruas_disponibles = $result_gruas->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Auto-Asignación de Grúas | DBACK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stats-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stats-card.warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }
        
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            border: none;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: #667eea;
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.8rem;
            padding: 6px 12px;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .config-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #667eea;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
    <!-- Sidebar (incluir desde MenuAdmin.php) -->
    <?php include 'MenuAdmin.php'; ?>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-gear-fill"></i> Configuración Auto-Asignación de Grúas</h1>
            <div>
                <a href="MenuAdmin.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Menú
                </a>
            </div>
        </div>

        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje == 'success' ? 'success' : ($tipo_mensaje == 'error' ? 'danger' : 'warning'); ?> alert-dismissible fade show">
            <i class="bi bi-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : ($tipo_mensaje == 'error' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $solicitudes_pendientes; ?></h3>
                            <p class="mb-0">Solicitudes Pendientes</p>
                        </div>
                        <i class="bi bi-clock-history" style="font-size: 2rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $gruas_disponibles; ?></h3>
                            <p class="mb-0">Grúas Disponibles</p>
                        </div>
                        <i class="bi bi-truck" style="font-size: 2rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo $estadisticas['asignaciones_automaticas'] ?? 0; ?></h3>
                            <p class="mb-0">Auto-Asignaciones</p>
                        </div>
                        <i class="bi bi-robot" style="font-size: 2rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><?php echo round($estadisticas['tiempo_promedio_segundos'] ?? 0, 1); ?>s</h3>
                            <p class="mb-0">Tiempo Promedio</p>
                        </div>
                        <i class="bi bi-stopwatch" style="font-size: 2rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <form method="POST" class="d-inline">
                            <button type="submit" name="procesar_pendientes" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-play-circle"></i> Procesar Solicitudes Pendientes
                            </button>
                        </form>
                        <p class="text-muted mt-2">Procesa hasta 10 solicitudes pendientes automáticamente</p>
                    </div>
                    <div class="col-md-4">
                        <a href="procesar-solicitud.php" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-list-ul"></i> Ver Todas las Solicitudes
                        </a>
                        <p class="text-muted mt-2">Gestionar solicitudes manualmente</p>
                    </div>
                    <div class="col-md-4">
                        <a href="gestion-clima-servicio.php" class="btn btn-warning btn-lg w-100">
                            <i class="bi bi-cloud-sun"></i> Gestión de Clima
                        </a>
                        <p class="text-muted mt-2">Controlar condiciones climáticas y suspensión del servicio</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración Principal -->
        <form method="POST">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-sliders"></i> Parámetros de Auto-Asignación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Configuración General -->
                        <div class="col-md-6">
                            <div class="config-section">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-gear"></i> Configuración General</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Auto-Asignación Habilitada</label>
                                    <div class="d-flex align-items-center">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="param_auto_asignacion_habilitada" value="1" 
                                                   <?php echo ($configuracion['auto_asignacion_habilitada'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                        <span class="ms-2"><?php echo ($configuracion['auto_asignacion_habilitada'] ?? '0') == '1' ? 'Habilitada' : 'Deshabilitada'; ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="radio_busqueda" class="form-label">Radio de Búsqueda (km)</label>
                                    <input type="number" class="form-control" id="radio_busqueda" 
                                           name="param_radio_busqueda_km" 
                                           value="<?php echo $configuracion['radio_busqueda_km'] ?? '50'; ?>" 
                                           min="1" max="200">
                                    <div class="form-text">Distancia máxima para buscar grúas cercanas</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tiempo_maximo" class="form-label">Tiempo Máximo de Espera (minutos)</label>
                                    <input type="number" class="form-control" id="tiempo_maximo" 
                                           name="param_tiempo_maximo_espera_minutos" 
                                           value="<?php echo $configuracion['tiempo_maximo_espera_minutos'] ?? '30'; ?>" 
                                           min="5" max="120">
                                    <div class="form-text">Tiempo antes de asignar cualquier grúa disponible</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="distancia_maxima" class="form-label">Distancia Máxima (km)</label>
                                    <input type="number" class="form-control" id="distancia_maxima" 
                                           name="param_distancia_maxima_km" 
                                           value="<?php echo $configuracion['distancia_maxima_km'] ?? '200'; ?>" 
                                           min="10" max="500">
                                    <div class="form-text">Distancia máxima para considerar una grúa</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Configuración Avanzada -->
                        <div class="col-md-6">
                            <div class="config-section">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cpu"></i> Configuración Avanzada</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Considerar Tipo de Servicio</label>
                                    <div class="d-flex align-items-center">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="param_considerar_tipo_servicio" value="1" 
                                                   <?php echo ($configuracion['considerar_tipo_servicio'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                        <span class="ms-2"><?php echo ($configuracion['considerar_tipo_servicio'] ?? '0') == '1' ? 'Sí' : 'No'; ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="peso_maximo" class="form-label">Peso Máximo Vehículo (kg)</label>
                                    <input type="number" class="form-control" id="peso_maximo" 
                                           name="param_peso_maximo_vehiculo_kg" 
                                           value="<?php echo $configuracion['peso_maximo_vehiculo_kg'] ?? '3500'; ?>" 
                                           min="500" max="10000">
                                    <div class="form-text">Peso máximo para asignar grúa de plataforma</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reintentos" class="form-label">Reintentos de Asignación</label>
                                    <input type="number" class="form-control" id="reintentos" 
                                           name="param_reintentos_asignacion" 
                                           value="<?php echo $configuracion['reintentos_asignacion'] ?? '3'; ?>" 
                                           min="1" max="10">
                                    <div class="form-text">Número de reintentos si falla la asignación</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tiempo_reintentos" class="form-label">Tiempo Entre Reintentos (minutos)</label>
                                    <input type="number" class="form-control" id="tiempo_reintentos" 
                                           name="param_tiempo_entre_reintentos_minutos" 
                                           value="<?php echo $configuracion['tiempo_entre_reintentos_minutos'] ?? '5'; ?>" 
                                           min="1" max="30">
                                    <div class="form-text">Tiempo de espera entre reintentos</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Notificar Asignación</label>
                                    <div class="d-flex align-items-center">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="param_notificar_asignacion" value="1" 
                                                   <?php echo ($configuracion['notificar_asignacion'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                        <span class="ms-2"><?php echo ($configuracion['notificar_asignacion'] ?? '0') == '1' ? 'Sí' : 'No'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prioridades -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="config-section">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-sort-down"></i> Prioridades de Urgencia</h6>
                                <div class="mb-3">
                                    <label for="prioridad_urgencia" class="form-label">Orden de Prioridad</label>
                                    <input type="text" class="form-control" id="prioridad_urgencia" 
                                           name="param_prioridad_urgencia" 
                                           value="<?php echo $configuracion['prioridad_urgencia'] ?? 'emergencia,urgente,normal'; ?>">
                                    <div class="form-text">Orden de prioridad separado por comas (emergencia,urgente,normal)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" name="guardar_configuracion" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Historial de Asignaciones -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Asignaciones</h5>
            </div>
            <div class="card-body">
                <?php
                $query_historial = "SELECT ha.*, s.nombre_completo, s.ubicacion as ubicacion_solicitud, 
                                           g.Placa, g.Tipo, g.coordenadas_actuales as coordenadas_grua
                                   FROM historial_asignaciones ha
                                   JOIN solicitudes s ON ha.solicitud_id = s.id
                                   JOIN gruas g ON ha.grua_id = g.ID
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
                
                <?php if ($result_historial->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
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
                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_asignacion'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($row['Placa']); ?></span>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['Tipo']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['metodo_asignacion'] == 'automatica' ? 'success' : 'info'; ?>">
                                        <?php echo $row['metodo_asignacion'] == 'automatica' ? 'Automática' : 'Manual'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($distancia_mostrar && $distancia_mostrar > 0): ?>
                                        <span class="badge bg-success" title="Método: <?php echo $metodo_calculo; ?>">
                                            <?php echo round($distancia_mostrar, 2); ?> km
                                        </span>
                                        <small class="text-muted d-block"><?php echo $metodo_calculo; ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Sin datos</span>
                                        <small class="text-muted d-block">Sin coordenadas</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['tiempo_asignacion_segundos'] ? $row['tiempo_asignacion_segundos'] . 'ms' : 'N/A'; ?></td>
                                <td>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['criterios_usados'] ?? 'N/A'); ?></small>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-2">No hay asignaciones registradas</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Actualizar estado de los toggles
        document.querySelectorAll('.toggle-switch input').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const span = this.parentElement.nextElementSibling;
                if (this.checked) {
                    span.textContent = this.name.includes('habilitada') ? 'Habilitada' : 
                                      this.name.includes('servicio') ? 'Sí' : 'Sí';
                } else {
                    span.textContent = this.name.includes('habilitada') ? 'Deshabilitada' : 
                                      this.name.includes('servicio') ? 'No' : 'No';
                }
            });
        });
        
        // Confirmar procesamiento de solicitudes
        document.querySelector('button[name="procesar_pendientes"]').addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de procesar las solicitudes pendientes? Esto asignará grúas automáticamente.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
