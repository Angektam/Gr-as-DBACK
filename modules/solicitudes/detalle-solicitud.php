<?php
/**
 * Detalle y Edición de Solicitudes - Sistema DBACK
 * Versión mejorada con mejor UX y funcionalidades adicionales
 */

require_once '../../conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../Login.php");
    exit();
}

// Verificar ID de solicitud
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje'] = "ID de solicitud no válido";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: procesar-solicitud.php");
    exit();
}

$solicitud_id = (int)$_GET['id'];
$modo_edicion = isset($_GET['editar']) && $_GET['editar'] == '1';
$accion_rapida = isset($_GET['accion']) ? $_GET['accion'] : '';

// Procesar acciones rápidas (cambio de estado directo)
if ($accion_rapida && in_array($accion_rapida, ['aceptar', 'completar', 'cancelar', 'asignar'])) {
    $nuevos_estados = [
        'aceptar' => 'asignada',
        'completar' => 'completada', 
        'cancelar' => 'cancelada',
        'asignar' => 'en_proceso'
    ];
    
    if (isset($nuevos_estados[$accion_rapida])) {
        $nuevo_estado = $nuevos_estados[$accion_rapida];
        $query = "UPDATE solicitudes SET estado = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $nuevo_estado, $solicitud_id);
        
        if ($stmt->execute()) {
            $mensajes_accion = [
                'aceptar' => 'Solicitud aceptada correctamente',
                'completar' => 'Solicitud marcada como completada',
                'cancelar' => 'Solicitud cancelada',
                'asignar' => 'Solicitud asignada para procesar'
            ];
            
            $_SESSION['mensaje'] = $mensajes_accion[$accion_rapida];
            $_SESSION['tipo_mensaje'] = "exito";
            header("Location: detalle-solicitud.php?id=$solicitud_id");
            exit();
        } else {
            $_SESSION['mensaje'] = "Error al actualizar el estado";
            $_SESSION['tipo_mensaje'] = "error";
        }
    }
}

// Procesar formulario de edición si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_cambios'])) {
    // Validar datos requeridos
    $errores = [];
    
    if (empty($_POST['nombre'])) $errores[] = "El nombre es requerido";
    if (empty($_POST['telefono'])) $errores[] = "El teléfono es requerido";
    if (empty($_POST['tipo_servicio'])) $errores[] = "El tipo de servicio es requerido";
    if (empty($_POST['tipo_vehiculo'])) $errores[] = "El tipo de vehículo es requerido";
    if (empty($_POST['marca_vehiculo'])) $errores[] = "La marca del vehículo es requerida";
    if (empty($_POST['modelo_vehiculo'])) $errores[] = "El modelo del vehículo es requerido";
    if (empty($_POST['ubicacion_origen'])) $errores[] = "La ubicación origen es requerida";
    if (empty($_POST['estado'])) $errores[] = "El estado es requerido";
    
    // Validar formato de email si se proporciona
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido";
    }
    
    // Validar teléfono (solo números, espacios, guiones y paréntesis)
    if (!empty($_POST['telefono']) && !preg_match('/^[\d\s\-\(\)\+]+$/', $_POST['telefono'])) {
        $errores[] = "El formato del teléfono no es válido";
    }
    
    if (!empty($errores)) {
        $_SESSION['mensaje'] = "Errores de validación: " . implode(", ", $errores);
        $_SESSION['tipo_mensaje'] = "error";
    } else {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $tipo_servicio = $conn->real_escape_string($_POST['tipo_servicio']);
        $tipo_vehiculo = $conn->real_escape_string($_POST['tipo_vehiculo'] ?? '');
        $marca_vehiculo = $conn->real_escape_string($_POST['marca_vehiculo'] ?? '');
        $modelo_vehiculo = $conn->real_escape_string($_POST['modelo_vehiculo'] ?? '');
    $ubicacion_origen = $conn->real_escape_string($_POST['ubicacion_origen']);
    $ubicacion_destino = $conn->real_escape_string($_POST['ubicacion_destino'] ?? '');
    $descripcion = $conn->real_escape_string($_POST['descripcion'] ?? '');
    $costo = (float)$_POST['costo'];
    $estado = $conn->real_escape_string($_POST['estado']);

    $query = "UPDATE solicitudes SET 
              nombre_completo = ?, 
              telefono = ?, 
              email = ?, 
              tipo_servicio = ?, 
              tipo_vehiculo = ?, 
              marca_vehiculo = ?, 
              modelo_vehiculo = ?, 
              ubicacion = ?, 
              descripcion_problema = ?, 
              costo_estimado = ?, 
              estado = ? 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssdsi", 
        $nombre, $telefono, $email, $tipo_servicio, 
        $tipo_vehiculo, $marca_vehiculo, $modelo_vehiculo, $ubicacion_origen, 
        $descripcion, $costo, $estado, 
        $solicitud_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Solicitud actualizada correctamente";
        $_SESSION['tipo_mensaje'] = "exito";
        header("Location: detalle-solicitud.php?id=$solicitud_id");
        exit();
    } else {
        $_SESSION['mensaje'] = "Error al actualizar la solicitud: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "error";
        }
    }
}

// Obtener datos de la solicitud
$query = "SELECT * FROM solicitudes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $solicitud_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['mensaje'] = "Solicitud no encontrada";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: procesar-solicitud.php");
    exit();
}

$solicitud = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modo_edicion ? 'Editar' : 'Detalle de'; ?> Solicitud #<?php echo $solicitud['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <style>
        /* Estilos generales */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #343a40;
            line-height: 1.6;
        }

        .container-fluid {
            padding: 0 2rem;
        }

        /* Mejoras en las cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Estilos para los badges de estado mejorados */
        .estado-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .estado-pendiente { 
            background-color: #FFF3CD; 
            color: #856404;
            border: 1px solid #FFEEBA;
        }

        .estado-asignada { 
            background-color: #D1ECF1; 
            color: #0C5460;
            border: 1px solid #BEE5EB;
        }

        .estado-en_proceso { 
            background-color: #CCE5FF; 
            color: #004085;
            border: 1px solid #B8DAFF;
        }

        .estado-completada { 
            background-color: #D4EDDA; 
            color: #155724;
            border: 1px solid #C3E6CB;
        }

        .estado-cancelada { 
            background-color: #E2E3E5; 
            color: #383D41;
            border: 1px solid #D6D8DB;
        }

        /* Mejoras en los formularios */
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        /* Botones mejorados */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-lg {
            padding: 12px 24px;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .btn-success {
            background-color: #198754;
            border-color: #198754;
        }

        .btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #565e64;
        }

        /* Estilos mejorados para botones outline */
        .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
            background-color: transparent;
        }

        .btn-outline-primary:hover {
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
            background-color: transparent;
        }

        .btn-outline-secondary:hover {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-outline-info {
            color: #0dcaf0;
            border-color: #0dcaf0;
            background-color: transparent;
        }

        .btn-outline-info:hover {
            color: #000;
            background-color: #0dcaf0;
            border-color: #0dcaf0;
        }

        .btn-outline-success {
            color: #198754;
            border-color: #198754;
            background-color: transparent;
        }

        .btn-outline-success:hover {
            color: #fff;
            background-color: #198754;
            border-color: #198754;
        }

        /* Asegurar que los botones sean visibles */
        .btn-group-vertical .btn {
            border: 2px solid;
            margin-bottom: 0.5rem;
            min-height: 40px;
            font-weight: 600;
        }

        .btn-group-vertical .btn:last-child {
            margin-bottom: 0;
        }

        /* Estilo específico para el botón de imprimir */
        .btn-outline-info {
            border-width: 2px;
            font-weight: 600;
        }

        .btn-outline-info i {
            font-size: 1.1em;
        }

        /* Alertas mejoradas */
        .alert {
            border-radius: 8px;
            padding: 1rem 1.5rem;
            border: none;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        /* Mejoras en la tipografía */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            color: #212529;
        }

        /* Espaciados mejorados */
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .mb-5 {
            margin-bottom: 2rem !important;
        }

        /* Diseño responsive */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        /* Efectos para los campos del formulario */
        .form-group {
            margin-bottom: 1.25rem;
            transition: all 0.3s ease;
        }

        .form-group:focus-within {
            transform: translateY(-2px);
        }

        /* Estilos para los textareas */
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Mejoras en la visualización de datos */
        p strong {
            color: #495057;
            min-width: 150px;
            display: inline-block;
        }

        /* Efecto hover para las cards interactivas */
        .card-interactive {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .card-interactive:hover {
            background-color: #f8f9fa;
        }

        /* Estilo para el título principal */
        .page-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .page-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background: #0d6efd;
            border-radius: 2px;
        }

        /* Mejoras en los tooltips */
        .tooltip-inner {
            border-radius: 6px;
            padding: 6px 12px;
        }

        /* Estilos para panel de acciones rápidas */
        .action-buttons-panel {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .action-buttons-panel h5 {
            color: #495057;
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
        }

        .btn-group-vertical .btn {
            border-radius: 8px !important;
            margin-bottom: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-group-vertical .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Mejoras para la información de estado */
        .estado-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #2196f3;
        }

        .estado-info h6 {
            margin: 0;
            color: #1565c0;
            font-weight: 600;
        }

        /* Mejoras para las cards de información */
        .info-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .info-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        /* Mejoras para los badges de estado */
        .estado-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Animaciones suaves */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Mejoras para responsive */
        @media (max-width: 768px) {
            .action-buttons-panel .row {
                margin: 0;
            }
            
            .action-buttons-panel .col-md-6 {
                margin-bottom: 1rem;
            }
            
            .btn-group-vertical .btn {
                font-size: 0.9rem;
                padding: 0.75rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <main class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                    <h1 class="page-title"><?php echo $modo_edicion ? 'Editar' : 'Detalle de'; ?> Solicitud #<?php echo $solicitud['id']; ?></h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle"></i> 
                            <?php if ($modo_edicion): ?>
                                Modo de edición - Modifica los detalles de la solicitud
                            <?php else: ?>
                                Vista de detalles - Información completa de la solicitud
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-info" onclick="mostrarAtajos()" title="Ver atajos de teclado">
                            <i class="bi bi-keyboard"></i>
                        </button>
                        <?php if ($modo_edicion): ?>
                            <a href="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        <?php else: ?>
                            <a href="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>&editar=1" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <a href="procesar-solicitud.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] == 'exito' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                    <?php echo $_SESSION['mensaje']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
                endif; ?>

                <?php if ($modo_edicion): ?>
                <!-- Formulario de Edición -->
                <form method="POST" action="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Información del Cliente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="nombre" class="form-label">Nombre:</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control" 
                                               value="<?php echo htmlspecialchars($solicitud['nombre_completo']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="telefono" class="form-label">Teléfono:</label>
                                        <input type="text" id="telefono" name="telefono" class="form-control" 
                                               value="<?php echo htmlspecialchars($solicitud['telefono']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email:</label>
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($solicitud['email'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Detalles del Servicio</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="tipo_servicio" class="form-label">Tipo de Servicio:</label>
                                        <select id="tipo_servicio" name="tipo_servicio" class="form-select" required>
                                            <option value="remolque" <?php echo $solicitud['tipo_servicio'] == 'remolque' ? 'selected' : ''; ?>>Remolque</option>
                                            <option value="bateria" <?php echo $solicitud['tipo_servicio'] == 'bateria' ? 'selected' : ''; ?>>Cambio de batería</option>
                                            <option value="gasolina" <?php echo $solicitud['tipo_servicio'] == 'gasolina' ? 'selected' : ''; ?>>Suministro de gasolina</option>
                                            <option value="llanta" <?php echo $solicitud['tipo_servicio'] == 'llanta' ? 'selected' : ''; ?>>Cambio de llanta</option>
                                            <option value="arranque" <?php echo $solicitud['tipo_servicio'] == 'arranque' ? 'selected' : ''; ?>>Servicio de arranque</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tipo_vehiculo" class="form-label">Tipo de Vehículo:</label>
                                        <select id="tipo_vehiculo" name="tipo_vehiculo" class="form-select" required>
                                            <option value="automovil" <?php echo ($solicitud['tipo_vehiculo'] ?? '') == 'automovil' ? 'selected' : ''; ?>>Automóvil</option>
                                            <option value="camioneta" <?php echo ($solicitud['tipo_vehiculo'] ?? '') == 'camioneta' ? 'selected' : ''; ?>>Camioneta</option>
                                            <option value="motocicleta" <?php echo ($solicitud['tipo_vehiculo'] ?? '') == 'motocicleta' ? 'selected' : ''; ?>>Motocicleta</option>
                                            <option value="camion" <?php echo ($solicitud['tipo_vehiculo'] ?? '') == 'camion' ? 'selected' : ''; ?>>Camión</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="marca_vehiculo" class="form-label">Marca:</label>
                                                <input type="text" id="marca_vehiculo" name="marca_vehiculo" class="form-control" 
                                                       value="<?php echo htmlspecialchars($solicitud['marca_vehiculo'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="modelo_vehiculo" class="form-label">Modelo:</label>
                                                <input type="text" id="modelo_vehiculo" name="modelo_vehiculo" class="form-control" 
                                                       value="<?php echo htmlspecialchars($solicitud['modelo_vehiculo'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Ubicaciones</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="ubicacion_origen" class="form-label">Ubicación Origen:</label>
                                        <textarea id="ubicacion_origen" name="ubicacion_origen" class="form-control" required><?php echo htmlspecialchars($solicitud['ubicacion']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="ubicacion_destino" class="form-label">Ubicación Destino:</label>
                                        <textarea id="ubicacion_destino" name="ubicacion_destino" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Otros Detalles</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="descripcion" class="form-label">Descripción:</label>
                                        <textarea id="descripcion" name="descripcion" class="form-control"><?php echo htmlspecialchars($solicitud['descripcion_problema'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="costo" class="form-label">Costo (MXN):</label>
                                        <input type="number" step="0.01" id="costo" name="costo" class="form-control" 
                                               value="<?php echo number_format($solicitud['costo_estimado'] ?? 0, 2, '.', ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="estado" class="form-label">Estado:</label>
                                        <select id="estado" name="estado" class="form-select" required>
                                            <option value="pendiente" <?php echo $solicitud['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="asignada" <?php echo $solicitud['estado'] == 'asignada' ? 'selected' : ''; ?>>Servicio Pendiente</option>
                                            <option value="en_proceso" <?php echo $solicitud['estado'] == 'en_proceso' ? 'selected' : ''; ?>>En proceso</option>
                                            <option value="completada" <?php echo $solicitud['estado'] == 'completada' ? 'selected' : ''; ?>>Completada</option>
                                            <option value="cancelada" <?php echo $solicitud['estado'] == 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <button type="submit" name="guardar_cambios" class="btn btn-success btn-lg">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                        <a href="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>

                <?php else: ?>
                <!-- Vista de Detalles (sin edición) -->
                
                <!-- Información de Estado Mejorada -->
                <div class="estado-info fade-in-up">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6><i class="bi bi-info-circle"></i> Estado Actual de la Solicitud</h6>
                            <p class="mb-0">
                                    <span class="estado-badge estado-<?php echo str_replace('_', '-', strtolower($solicitud['estado'])); ?>">
                                        <?php 
                                        $estados = [
                                            'pendiente' => 'Pendiente',
                                            'asignada' => 'Servicio Pendiente',
                                            'en_proceso' => 'En Proceso',
                                            'completada' => 'Completada',
                                            'cancelada' => 'Cancelada'
                                        ];
                                        echo $estados[strtolower($solicitud['estado'])] ?? 'Pendiente';
                                        ?>
                                    </span>
                                - Solicitud #<?php echo $solicitud['id']; ?> creada el <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> 
                                <?php 
                                $tiempo_transcurrido = time() - strtotime($solicitud['fecha_solicitud']);
                                $horas = floor($tiempo_transcurrido / 3600);
                                $minutos = floor(($tiempo_transcurrido % 3600) / 60);
                                echo "Hace {$horas}h {$minutos}m";
                                ?>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card info-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person"></i> Información del Cliente</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($solicitud['nombre_completo']); ?></p>
                                <p><strong>Teléfono:</strong> 
                                    <a href="tel:<?php echo htmlspecialchars($solicitud['telefono']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($solicitud['telefono']); ?>
                                    </a>
                                </p>
                                <p><strong>Email:</strong> 
                                    <?php if (!empty($solicitud['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($solicitud['email']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($solicitud['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Fecha de solicitud:</strong> <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card info-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-tools"></i> Detalles del Servicio</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Tipo de servicio:</strong> 
                                    <span class="badge bg-info">
                                        <?php 
                                        $tipos_servicio = [
                                            'remolque' => 'Remolque',
                                            'bateria' => 'Cambio de batería',
                                            'gasolina' => 'Suministro de gasolina',
                                            'llanta' => 'Cambio de llanta',
                                            'arranque' => 'Servicio de arranque',
                                            'otro' => 'Otro servicio'
                                        ];
                                        echo $tipos_servicio[strtolower($solicitud['tipo_servicio'])] ?? ucfirst($solicitud['tipo_servicio']);
                                        ?>
                                    </span>
                                </p>
                                <p><strong>Vehículo:</strong> 
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($solicitud['tipo_vehiculo'] ?? 'No especificado'); ?>
                                    </span>
                                    <?php if (!empty($solicitud['marca_vehiculo']) || !empty($solicitud['modelo_vehiculo'])): ?>
                                        - <?php echo htmlspecialchars($solicitud['marca_vehiculo'] ?? ''); ?> <?php echo htmlspecialchars($solicitud['modelo_vehiculo'] ?? ''); ?>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Ubicación:</strong> 
                                    <i class="bi bi-geo-alt"></i> 
                                    <?php echo htmlspecialchars($solicitud['ubicacion']); ?>
                                </p>
                                <p><strong>Costo estimado:</strong> 
                                    <span class="badge bg-success fs-6">
                                        $<?php echo number_format($solicitud['costo_estimado'] ?? 0, 2); ?> MXN
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa GPS y Ruta -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card info-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Mapa GPS y Ruta del Servicio</h5>
                                <button class="btn btn-sm btn-outline-info" onclick="debugMapa()" title="Debug del mapa">
                                    <i class="bi bi-bug"></i> Debug
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="mapa-servicio" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                                <div class="mt-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="badge bg-primary fs-6 mb-2">Distancia Total</div>
                                                <div class="h5" id="distancia-total">Calculando...</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="badge bg-success fs-6 mb-2">Tiempo Estimado</div>
                                                <div class="h5" id="tiempo-estimado">Calculando...</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="badge bg-warning fs-6 mb-2">Costo por KM</div>
                                                <div class="h5">$80 MXN</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="alert alert-info" id="info-destino" style="display: none;">
                                                <i class="bi bi-info-circle"></i>
                                                <strong>Información:</strong> Esta solicitud no tiene un destino específico definido. 
                                                El servicio se realizará en la ubicación de origen.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card info-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-truck"></i> Grúa Asignada</h5>
                            </div>
                            <div class="card-body">
                                <div id="grua-info">
                                    <div class="text-center text-muted py-3">
                                        <i class="bi bi-truck" style="font-size: 3rem; color: #6c757d;"></i>
                                        <p class="mt-2 mb-0">No hay grúa asignada</p>
                                        <button class="btn btn-primary btn-sm mt-2" onclick="asignarGrua()">
                                            <i class="bi bi-plus-circle"></i> Asignar Grúa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card info-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-chat-text"></i> Descripción del Problema</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($solicitud['descripcion_problema'])): ?>
                                    <div class="descripcion-problema">
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($solicitud['descripcion_problema'])); ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="bi bi-chat-square-text" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No se proporcionó descripción del problema</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción Rápida -->
                <div class="action-buttons-panel mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="bi bi-lightning-charge"></i> Acciones Rápidas</h5>
                            <div class="btn-group-vertical w-100" role="group">
                                <?php if ($solicitud['estado'] == 'pendiente'): ?>
                                    <a href="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>&accion=aceptar" 
                                       class="btn btn-success mb-2" 
                                       onclick="return confirm('¿Aceptar esta solicitud?')">
                                        <i class="bi bi-check-circle"></i> Aceptar Solicitud
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (in_array($solicitud['estado'], ['asignada', 'en_proceso'])): ?>
                                    <a href="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>&accion=completar" 
                                       class="btn btn-primary mb-2"
                                       onclick="return confirm('¿Marcar como completada?')">
                                        <i class="bi bi-check2-all"></i> Marcar Completada
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!in_array($solicitud['estado'], ['completada', 'cancelada'])): ?>
                                    <a href="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>&accion=cancelar" 
                                       class="btn btn-danger mb-2"
                                       onclick="return confirm('¿Cancelar esta solicitud?')">
                                        <i class="bi bi-x-circle"></i> Cancelar Solicitud
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($solicitud['estado'] == 'asignada'): ?>
                                    <a href="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>&accion=asignar" 
                                       class="btn btn-warning mb-2"
                                       onclick="return confirm('¿Asignar para procesar?')">
                                        <i class="bi bi-person-workspace"></i> Asignar para Procesar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="bi bi-tools"></i> Herramientas</h5>
                            <div class="btn-group-vertical w-100" role="group">
                                <a href="detalle-solicitud.php?id=<?php echo $solicitud_id; ?>&editar=1" 
                                   class="btn btn-outline-primary mb-2">
                                    <i class="bi bi-pencil"></i> Editar Detalles
                                </a>
                                
                                <a href="procesar-solicitud.php" 
                                   class="btn btn-outline-secondary mb-2">
                                    <i class="bi bi-arrow-left"></i> Volver a Lista
                                </a>
                                
                                <button class="btn btn-outline-info mb-2" onclick="imprimirSolicitud()">
                                    <i class="bi bi-printer"></i> Imprimir
                                </button>
                                
                                <button class="btn btn-outline-success mb-2" onclick="copiarEnlace()">
                                    <i class="bi bi-link-45deg"></i> Copiar Enlace
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activar tooltips de Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Validación de formulario
            if (document.querySelector('form')) {
                document.querySelector('form').addEventListener('submit', function(e) {
                    let valid = true;
                    
                    // Validar campos requeridos
                    this.querySelectorAll('[required]').forEach(function(field) {
                        if (!field.value.trim()) {
                            valid = false;
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                        alert('Por favor complete todos los campos requeridos');
                    }
                });
            }

            // Agregar animaciones a los elementos
            document.querySelectorAll('.card, .action-buttons-panel').forEach(function(element) {
                element.classList.add('fade-in-up');
            });

            // Auto-ocultar alertas después de 5 segundos
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    if (alert.classList.contains('alert-dismissible')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });

        // Función para imprimir la solicitud
        function imprimirSolicitud() {
            const contenido = document.querySelector('.container-fluid').innerHTML;
            const ventanaImpresion = window.open('', '_blank');
            
            ventanaImpresion.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Solicitud #<?php echo $solicitud['id']; ?> - Grúas DBACK</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            .btn, .action-buttons-panel { display: none !important; }
                            .card { border: 1px solid #000 !important; }
                            body { font-size: 12px; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container-fluid">
                        <h1 class="text-center mb-4">Solicitud de Servicio #<?php echo $solicitud['id']; ?></h1>
                        ${contenido}
                    </div>
                </body>
                </html>
            `);
            
            ventanaImpresion.document.close();
            ventanaImpresion.focus();
            ventanaImpresion.print();
        }

        // Función para copiar enlace
        function copiarEnlace() {
            const enlace = window.location.href;
            navigator.clipboard.writeText(enlace).then(function() {
                // Mostrar notificación temporal
                const notificacion = document.createElement('div');
                notificacion.className = 'alert alert-success position-fixed';
                notificacion.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
                notificacion.innerHTML = '<i class="bi bi-check-circle"></i> Enlace copiado al portapapeles';
                document.body.appendChild(notificacion);
                
                setTimeout(function() {
                    notificacion.remove();
                }, 3000);
            }).catch(function(err) {
                alert('Error al copiar el enlace: ' + err);
            });
        }

        // Función para confirmar acciones rápidas
        function confirmarAccion(accion, mensaje) {
            return confirm(mensaje || `¿Está seguro de ${accion} esta solicitud?`);
        }

        // Mejorar la experiencia de usuario con atajos de teclado
        document.addEventListener('keydown', function(e) {
            // Ctrl + E para editar
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                const editBtn = document.querySelector('a[href*="editar=1"]');
                if (editBtn) editBtn.click();
            }
            
            // Ctrl + P para imprimir
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                imprimirSolicitud();
            }
            
            // Escape para volver
            if (e.key === 'Escape') {
                window.location.href = 'procesar-solicitud.php';
            }
        });

        // Mostrar información de atajos de teclado
        function mostrarAtajos() {
            const atajos = `
                Atajos de Teclado Disponibles:
                
                Ctrl + E    - Editar solicitud
                Ctrl + P    - Imprimir solicitud
                Escape      - Volver a la lista
                
                También puedes usar los botones de acción rápida
                para cambiar el estado de la solicitud fácilmente.
            `;
            alert(atajos);
        }

        // Variables globales para el mapa
        let mapaServicio;
        let routingControl;
        let marcadorOrigen;
        let marcadorDestino;

        // Inicializar mapa GPS
        function inicializarMapa() {
            // Coordenadas por defecto (Los Mochis, Sinaloa)
            const coordenadasDefault = [25.814960975032974, -108.97984572706956];
            
            // Crear mapa
            mapaServicio = L.map('mapa-servicio').setView(coordenadasDefault, 13);
            
            // Agregar capa de tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(mapaServicio);

            // Obtener coordenadas de la solicitud
            const ubicacionOrigen = '<?php echo addslashes($solicitud['ubicacion']); ?>';
            const ubicacionDestino = '<?php echo addslashes($solicitud['ubicacion_destino'] ?? ''); ?>';
            
            console.log('Datos de la solicitud:', {
                origen: ubicacionOrigen,
                destino: ubicacionDestino,
                tieneDestino: ubicacionDestino && ubicacionDestino.trim() !== ''
            });
            
            // Geocodificar ubicaciones y mostrar ruta
            if (ubicacionOrigen) {
                geocodificarYMostrarRuta(ubicacionOrigen, ubicacionDestino);
            }
        }

        // Geocodificar ubicaciones y mostrar ruta
        function geocodificarYMostrarRuta(origen, destino) {
            console.log('Iniciando geocodificación:', { origen, destino });
            
            // Mostrar estado de carga
            document.getElementById('distancia-total').textContent = 'Cargando ubicaciones...';
            document.getElementById('tiempo-estimado').textContent = 'Calculando...';
            
            // Geocodificar origen
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(origen)}&limit=1&countrycodes=mx`)
                .then(response => response.json())
                .then(data => {
                    console.log('Datos de origen:', data);
                    if (data && data.length > 0) {
                        const latOrigen = parseFloat(data[0].lat);
                        const lngOrigen = parseFloat(data[0].lon);
                        
                        console.log('Coordenadas de origen:', latOrigen, lngOrigen);
                        
                        // Crear marcador de origen
                        marcadorOrigen = L.marker([latOrigen, lngOrigen], {
                            icon: L.divIcon({
                                className: 'marcador-origen',
                                html: '<div style="background-color: #28a745; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold;">O</div>',
                                iconSize: [30, 30],
                                iconAnchor: [15, 15]
                            })
                        }).addTo(mapaServicio);
                        
                        marcadorOrigen.bindPopup(`<strong>Origen:</strong><br>${origen}`).openPopup();
                        
                        // Si hay destino, geocodificarlo también
                        if (destino && destino.trim() !== '') {
                            console.log('Geocodificando destino:', destino);
                            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(destino)}&limit=1&countrycodes=mx`)
                                .then(response => response.json())
                                .then(dataDestino => {
                                    console.log('Datos de destino:', dataDestino);
                                    if (dataDestino && dataDestino.length > 0) {
                                        const latDestino = parseFloat(dataDestino[0].lat);
                                        const lngDestino = parseFloat(dataDestino[0].lon);
                                        
                                        console.log('Coordenadas de destino:', latDestino, lngDestino);
                                        
                                        // Crear marcador de destino
                                        marcadorDestino = L.marker([latDestino, lngDestino], {
                                            icon: L.divIcon({
                                                className: 'marcador-destino',
                                                html: '<div style="background-color: #dc3545; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold;">D</div>',
                                                iconSize: [30, 30],
                                                iconAnchor: [15, 15]
                                            })
                                        }).addTo(mapaServicio);
                                        
                                        marcadorDestino.bindPopup(`<strong>Destino:</strong><br>${destino}`);
                                        
                                        // Ajustar vista para mostrar ambos puntos
                                        const grupo = new L.featureGroup([marcadorOrigen, marcadorDestino]);
                                        mapaServicio.fitBounds(grupo.getBounds().pad(0.1));
                                        
                                        // Mostrar ruta
                                        mostrarRuta([latOrigen, lngOrigen], [latDestino, lngDestino]);
                                    } else {
                                        console.log('No se encontró destino, mostrando solo origen');
                                        // Solo mostrar origen
                                        mapaServicio.setView([latOrigen, lngOrigen], 15);
                                        calcularDistanciaDirecta([latOrigen, lngOrigen], [latOrigen, lngOrigen]);
                                        document.getElementById('distancia-total').textContent = 'Solo origen disponible';
                                        document.getElementById('tiempo-estimado').textContent = 'N/A';
                                        
                                        // Mostrar alerta informativa
                                        document.getElementById('info-destino').style.display = 'block';
                                    }
                                })
                                .catch(error => {
                                    console.error('Error al geocodificar destino:', error);
                                    // Solo mostrar origen
                                    mapaServicio.setView([latOrigen, lngOrigen], 15);
                                    calcularDistanciaDirecta([latOrigen, lngOrigen], [latOrigen, lngOrigen]);
                                    document.getElementById('distancia-total').textContent = 'Error en destino';
                                    document.getElementById('tiempo-estimado').textContent = 'N/A';
                                });
                        } else {
                            console.log('No hay destino definido, mostrando solo origen');
                            // Solo mostrar origen
                            mapaServicio.setView([latOrigen, lngOrigen], 15);
                            calcularDistanciaDirecta([latOrigen, lngOrigen], [latOrigen, lngOrigen]);
                            document.getElementById('distancia-total').textContent = 'Solo origen disponible';
                            document.getElementById('tiempo-estimado').textContent = 'N/A';
                            
                            // Mostrar alerta informativa
                            document.getElementById('info-destino').style.display = 'block';
                        }
                    } else {
                        console.log('No se encontró origen');
                        document.getElementById('distancia-total').textContent = 'Ubicación no encontrada';
                        document.getElementById('tiempo-estimado').textContent = 'N/A';
                    }
                })
                .catch(error => {
                    console.error('Error al geocodificar origen:', error);
                    document.getElementById('distancia-total').textContent = 'Error al cargar ubicación';
                    document.getElementById('tiempo-estimado').textContent = 'N/A';
                });
        }

        // Mostrar ruta entre dos puntos
        function mostrarRuta(origen, destino) {
            console.log('Mostrando ruta entre:', origen, destino);
            
            // Limpiar control de ruteo anterior si existe
            if (routingControl) {
                mapaServicio.removeControl(routingControl);
            }
            
            // Configurar control de ruteo
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(origen[0], origen[1]),
                    L.latLng(destino[0], destino[1])
                ],
                routeWhileDragging: false,
                addWaypoints: false,
                createMarker: function() { return null; }, // No crear marcadores automáticos
                lineOptions: {
                    styles: [{ color: '#007bff', weight: 6, opacity: 0.8 }]
                },
                show: false, // No mostrar panel de instrucciones
                collapsible: false
            }).addTo(mapaServicio);

            // Escuchar eventos de ruteo
            routingControl.on('routesfound', function(e) {
                console.log('Ruta encontrada:', e.routes);
                const routes = e.routes;
                const summary = routes[0].summary;
                
                if (summary) {
                    // Actualizar información de distancia y tiempo
                    const distanciaKm = (summary.totalDistance / 1000).toFixed(2);
                    const tiempoMinutos = Math.round(summary.totalTime / 60);
                    
                    document.getElementById('distancia-total').textContent = `${distanciaKm} km`;
                    document.getElementById('tiempo-estimado').textContent = `${tiempoMinutos} min`;
                    
                    console.log('Distancia calculada:', distanciaKm, 'km');
                    console.log('Tiempo estimado:', tiempoMinutos, 'min');
                    
                    // Ajustar vista del mapa para mostrar toda la ruta
                    const group = new L.featureGroup([marcadorOrigen, marcadorDestino]);
                    mapaServicio.fitBounds(group.getBounds().pad(0.1));
                } else {
                    console.log('No se pudo obtener información de la ruta');
                    calcularDistanciaDirecta(origen, destino);
                }
            });

            routingControl.on('routingerror', function(e) {
                console.error('Error en el ruteo:', e);
                // Calcular distancia directa como fallback
                calcularDistanciaDirecta(origen, destino);
            });
            
            // Timeout para evitar que se quede cargando indefinidamente
            setTimeout(() => {
                if (document.getElementById('distancia-total').textContent === 'Cargando ubicaciones...') {
                    console.log('Timeout en ruteo, usando cálculo directo');
                    calcularDistanciaDirecta(origen, destino);
                }
            }, 10000); // 10 segundos de timeout
        }

        // Calcular distancia directa (Haversine)
        function calcularDistanciaDirecta(origen, destino) {
            const R = 6371; // Radio de la Tierra en km
            const dLat = (destino[0] - origen[0]) * Math.PI / 180;
            const dLon = (destino[1] - origen[1]) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(origen[0] * Math.PI / 180) * Math.cos(destino[0] * Math.PI / 180) *
                    Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distancia = R * c;
            
            // Tiempo estimado (velocidad promedio 40 km/h)
            const tiempoMinutos = Math.round((distancia / 40) * 60);
            
            document.getElementById('distancia-total').textContent = `${distancia.toFixed(2)} km`;
            document.getElementById('tiempo-estimado').textContent = `${tiempoMinutos} min`;
        }

        // Función para asignar grúa
        function asignarGrua() {
            // Simular asignación de grúa
            const gruasDisponibles = [
                { id: 1, nombre: 'Grúa DBACK-001', conductor: 'Juan Pérez', telefono: '668-123-4567', estado: 'Disponible' },
                { id: 2, nombre: 'Grúa DBACK-002', conductor: 'María García', telefono: '668-234-5678', estado: 'Disponible' },
                { id: 3, nombre: 'Grúa DBACK-003', conductor: 'Carlos López', telefono: '668-345-6789', estado: 'En servicio' }
            ];
            
            const gruaDisponible = gruasDisponibles.find(g => g.estado === 'Disponible');
            
            if (gruaDisponible) {
                document.getElementById('grua-info').innerHTML = `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-truck" style="font-size: 3rem; color: #28a745;"></i>
                        </div>
                        <h6 class="text-success">${gruaDisponible.nombre}</h6>
                        <p class="mb-1"><strong>Conductor:</strong> ${gruaDisponible.conductor}</p>
                        <p class="mb-1"><strong>Teléfono:</strong> 
                            <a href="tel:${gruaDisponible.telefono}" class="text-decoration-none">${gruaDisponible.telefono}</a>
                        </p>
                        <p class="mb-1"><strong>Estado:</strong> 
                            <span class="badge bg-success">${gruaDisponible.estado}</span>
                        </p>
                        <div class="mt-3">
                            <button class="btn btn-success btn-sm me-2" onclick="contactarConductor('${gruaDisponible.telefono}')">
                                <i class="bi bi-telephone"></i> Llamar
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="cambiarGrua()">
                                <i class="bi bi-arrow-repeat"></i> Cambiar
                            </button>
                        </div>
                    </div>
                `;
                
                // Mostrar notificación de éxito
                mostrarNotificacion('Grúa asignada correctamente', 'success');
            } else {
                mostrarNotificacion('No hay grúas disponibles en este momento', 'warning');
            }
        }

        // Función para contactar al conductor
        function contactarConductor(telefono) {
            window.open(`tel:${telefono}`, '_self');
        }

        // Función para cambiar grúa
        function cambiarGrua() {
            document.getElementById('grua-info').innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="bi bi-truck" style="font-size: 3rem; color: #6c757d;"></i>
                    <p class="mt-2 mb-0">No hay grúa asignada</p>
                    <button class="btn btn-primary btn-sm mt-2" onclick="asignarGrua()">
                        <i class="bi bi-plus-circle"></i> Asignar Grúa
                    </button>
                </div>
            `;
        }

        // Función para mostrar notificaciones
        function mostrarNotificacion(mensaje, tipo) {
            const notificacion = document.createElement('div');
            notificacion.className = `alert alert-${tipo} position-fixed`;
            notificacion.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
            notificacion.innerHTML = `<i class="bi bi-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${mensaje}`;
            document.body.appendChild(notificacion);
            
            setTimeout(() => {
                notificacion.remove();
            }, 3000);
        }

        // Función de debug para el mapa
        function debugMapa() {
            const ubicacionOrigen = '<?php echo addslashes($solicitud['ubicacion']); ?>';
            const ubicacionDestino = '<?php echo addslashes($solicitud['ubicacion_destino'] ?? ''); ?>';
            
            console.log('=== DEBUG DEL MAPA ===');
            console.log('Ubicación origen:', ubicacionOrigen);
            console.log('Ubicación destino:', ubicacionDestino);
            console.log('Mapa inicializado:', typeof mapaServicio !== 'undefined');
            console.log('Marcador origen:', marcadorOrigen);
            console.log('Marcador destino:', marcadorDestino);
            console.log('Control de ruteo:', routingControl);
            
            // Mostrar información en la interfaz
            const debugInfo = `
                <strong>Debug del Mapa:</strong><br>
                Origen: ${ubicacionOrigen}<br>
                Destino: ${ubicacionDestino || 'No definido'}<br>
                Mapa: ${typeof mapaServicio !== 'undefined' ? 'Inicializado' : 'No inicializado'}<br>
                Marcador O: ${marcadorOrigen ? 'Creado' : 'No creado'}<br>
                Marcador D: ${marcadorDestino ? 'Creado' : 'No creado'}<br>
                Ruteo: ${routingControl ? 'Activo' : 'No activo'}
            `;
            
            // Crear popup de debug
            if (marcadorOrigen) {
                marcadorOrigen.bindPopup(debugInfo).openPopup();
            } else {
                alert(debugInfo);
            }
        }

        // Inicializar mapa cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            // Solo inicializar el mapa si no estamos en modo edición
            <?php if (!$modo_edicion): ?>
            setTimeout(() => {
                inicializarMapa();
            }, 1000);
            <?php endif; ?>
        });
    </script>
</body>
</html>