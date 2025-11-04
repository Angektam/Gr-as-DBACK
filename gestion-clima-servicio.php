<?php
require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';
// La sesión ya se inicia en config.php

// Verificar sesión y permisos de administrador
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

if ($_SESSION['usuario_cargo'] !== 'Administrador') {
    $_SESSION['mensaje'] = "No tienes permisos para acceder a esta página";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: MenuAdmin.php");
    exit();
}

$autoAsignacion = new AutoAsignacionGruas($conn);
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['suspender_servicio'])) {
        $razon = $_POST['razon_suspension'];
        $tipo_suspension = $_POST['tipo_suspension'];
        $usuario_id = $_SESSION['usuario_id'];
        
        $query = "CALL suspender_servicio_clima(?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $razon, $tipo_suspension, $usuario_id);
        
        if ($stmt->execute()) {
            $mensaje = "Servicio suspendido correctamente";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al suspender el servicio";
            $tipo_mensaje = "error";
        }
    }
    
    if (isset($_POST['reactivar_servicio'])) {
        $query = "CALL reactivar_servicio()";
        if ($conn->query($query)) {
            $mensaje = "Servicio reactivado correctamente";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al reactivar el servicio";
            $tipo_mensaje = "error";
        }
    }
    
    if (isset($_POST['actualizar_clima'])) {
        $parametros_clima = [
            'bloquear_lluvia_fuerte',
            'bloquear_vientos_fuertes',
            'bloquear_niebla_densa',
            'bloquear_tormenta'
        ];
        
        foreach ($parametros_clima as $param) {
            $valor = isset($_POST[$param]) ? '1' : '0';
            $autoAsignacion->actualizarConfiguracion($param, $valor);
        }
        
        $mensaje = "Configuración de clima actualizada";
        $tipo_mensaje = "success";
    }
}

// Obtener estado actual del servicio
$estado_servicio = $autoAsignacion->obtenerEstadoServicio();

// Obtener configuración de clima
$configuracion = $autoAsignacion->obtenerConfiguracion();

// Obtener historial de suspensiones
$query_historial = "SELECT ss.*, u.Nombre as suspendido_por_nombre 
                    FROM suspension_servicio ss
                    LEFT JOIN usuarios u ON ss.suspendido_por = u.id
                    ORDER BY ss.fecha_suspension DESC 
                    LIMIT 10";
$historial_suspensiones = $conn->query($query_historial);

// Obtener eventos recientes
$query_eventos = "SELECT * FROM eventos_sistema 
                  WHERE tipo_evento IN ('suspension_servicio', 'reactivacion_servicio', 'clima_adverso', 'sin_gruas')
                  ORDER BY fecha_evento DESC 
                  LIMIT 20";
$eventos_recientes = $conn->query($query_eventos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clima y Servicio | DBACK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .header-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .header-section h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .estado-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 15px;
        }
        
        .estado-activo {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
        }
        
        .estado-inactivo {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #ecf0f1;
            padding: 12px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .weather-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .weather-option {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .form-check-input {
            width: 50px;
            height: 25px;
            cursor: pointer;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            padding: 20px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="configuracion-auto-asignacion.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Volver a Configuración
        </a>
        
        <div class="header-section">
            <h1><i class="fas fa-cloud-sun"></i> Gestión de Clima y Estado del Servicio</h1>
            <p class="text-muted">Controla las condiciones climáticas y la disponibilidad del servicio de grúas</p>
            
            <div class="estado-badge <?php echo $estado_servicio['servicio_activo'] ? 'estado-activo' : 'estado-inactivo'; ?>">
                <i class="fas fa-<?php echo $estado_servicio['servicio_activo'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $estado_servicio['servicio_activo'] ? 'SERVICIO OPERATIVO' : 'SERVICIO SUSPENDIDO'; ?>
            </div>
            
            <?php if (!$estado_servicio['servicio_activo']): ?>
            <div class="alert alert-warning mt-3">
                <strong>Motivo:</strong> <?php echo htmlspecialchars($estado_servicio['razon_inactivo']); ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: #3498db;">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-number" style="color: #3498db;">
                    <?php echo $estado_servicio['gruas_disponibles']; ?>
                </div>
                <div class="stat-label">Grúas Disponibles</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #f39c12;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number" style="color: #f39c12;">
                    <?php echo $estado_servicio['solicitudes_pendientes']; ?>
                </div>
                <div class="stat-label">Solicitudes Pendientes</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: <?php echo $estado_servicio['clima_apto'] ? '#27ae60' : '#e74c3c'; ?>;">
                    <i class="fas fa-cloud-sun"></i>
                </div>
                <div class="stat-number" style="color: <?php echo $estado_servicio['clima_apto'] ? '#27ae60' : '#e74c3c'; ?>;">
                    <?php echo $estado_servicio['clima_apto'] ? 'APTO' : 'NO APTO'; ?>
                </div>
                <div class="stat-label">Estado Climático</div>
            </div>
        </div>

        <div class="row">
            <!-- Control de Servicio -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-power-off"></i> Control del Servicio
                    </div>
                    <div class="card-body">
                        <?php if ($estado_servicio['servicio_activo']): ?>
                        <form method="POST" onsubmit="return confirm('¿Estás seguro de suspender el servicio? Esto afectará a todas las solicitudes pendientes.')">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Razón de la Suspensión</label>
                                <textarea name="razon_suspension" class="form-control" rows="3" required 
                                          placeholder="Ej: Tormenta eléctrica en la zona"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de Suspensión</label>
                                <select name="tipo_suspension" class="form-select" required>
                                    <option value="clima">Condiciones Climáticas</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                    <option value="emergencia">Emergencia</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="suspender_servicio" class="btn btn-danger w-100">
                                <i class="fas fa-ban"></i> Suspender Servicio
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="text-center mb-3">
                            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #e74c3c;"></i>
                            <p class="mt-3"><strong>El servicio está actualmente suspendido</strong></p>
                        </div>
                        <form method="POST" onsubmit="return confirm('¿Estás seguro de reactivar el servicio?')">
                            <button type="submit" name="reactivar_servicio" class="btn btn-success w-100">
                                <i class="fas fa-check-circle"></i> Reactivar Servicio
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Configuración de Clima -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-cloud-showers-heavy"></i> Configuración Climática
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <p class="text-muted mb-3">Selecciona las condiciones climáticas que bloquearán el servicio automáticamente:</p>
                            
                            <div class="weather-options">
                                <div class="weather-option">
                                    <i class="fas fa-cloud-rain" style="font-size: 2rem; color: #3498db;"></i>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="bloquear_lluvia_fuerte" 
                                               <?php echo ($configuracion['bloquear_lluvia_fuerte'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Lluvia Fuerte</label>
                                    </div>
                                </div>
                                
                                <div class="weather-option">
                                    <i class="fas fa-wind" style="font-size: 2rem; color: #95a5a6;"></i>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="bloquear_vientos_fuertes"
                                               <?php echo ($configuracion['bloquear_vientos_fuertes'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Vientos Fuertes</label>
                                    </div>
                                </div>
                                
                                <div class="weather-option">
                                    <i class="fas fa-smog" style="font-size: 2rem; color: #7f8c8d;"></i>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="bloquear_niebla_densa"
                                               <?php echo ($configuracion['bloquear_niebla_densa'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Niebla Densa</label>
                                    </div>
                                </div>
                                
                                <div class="weather-option">
                                    <i class="fas fa-bolt" style="font-size: 2rem; color: #f39c12;"></i>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="bloquear_tormenta"
                                               <?php echo ($configuracion['bloquear_tormenta'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Tormentas</label>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="actualizar_clima" class="btn btn-primary w-100 mt-3">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Suspensiones -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Historial de Suspensiones
            </div>
            <div class="card-body">
                <?php if ($historial_suspensiones->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha Suspensión</th>
                                <th>Tipo</th>
                                <th>Razón</th>
                                <th>Suspendido Por</th>
                                <th>Fecha Reactivación</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $historial_suspensiones->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_suspension'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $row['tipo_suspension'] == 'clima' ? 'primary' : 
                                            ($row['tipo_suspension'] == 'emergencia' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($row['tipo_suspension']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['razon']); ?></td>
                                <td><?php echo htmlspecialchars($row['suspendido_por_nombre'] ?? 'Sistema'); ?></td>
                                <td><?php echo $row['fecha_reactivacion'] ? date('d/m/Y H:i', strtotime($row['fecha_reactivacion'])) : '-'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['activo'] ? 'danger' : 'success'; ?>">
                                        <?php echo $row['activo'] ? 'Activo' : 'Finalizado'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-2">No hay historial de suspensiones</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Eventos Recientes -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bell"></i> Eventos Recientes del Sistema
            </div>
            <div class="card-body">
                <?php if ($eventos_recientes->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo de Evento</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $eventos_recientes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($row['fecha_evento'])); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo str_replace('_', ' ', ucfirst($row['tipo_evento'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['descripcion'] ?? ''); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-2">No hay eventos registrados</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

