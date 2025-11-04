<?php
/**
 * Sistema de Gestión de Gastos Mejorado - DBACK
 * Versión mejorada con diseño moderno y funcionalidades avanzadas
 */

session_start();

// Verificación de sesión
if (!isset($_SESSION['usuario_nombre'])) {
    header('Location: Login.php');
    exit;
}

// Configuración de la base de datos
require_once 'conexion.php';

// Configuración de la página
$page_title = 'Gestión de Gastos - Grúas DBACK';
$additional_css = [
    'https://cdn.jsdelivr.net/npm/chart.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

// Función para obtener iconos según categoría
function obtenerIconoCategoria($categoria) {
    $iconos = [
        'Reparacion' => 'fa-tools',
        'Gasto_Oficina' => 'fa-building',
        'Gasolina' => 'fa-gas-pump',
        'Mantenimiento' => 'fa-wrench',
        'Seguro' => 'fa-shield-alt',
        'Limpieza' => 'fa-spray-can',
        'Otros' => 'fa-receipt'
    ];
    return $iconos[$categoria] ?? 'fa-receipt';
}

// Función para obtener colores según categoría
function obtenerColorCategoria($categoria) {
    $colores = [
        'Reparacion' => '#e74c3c',
        'Gasto_Oficina' => '#3498db',
        'Gasolina' => '#f39c12',
        'Mantenimiento' => '#2ecc71',
        'Seguro' => '#9b59b6',
        'Limpieza' => '#1abc9c',
        'Otros' => '#95a5a6'
    ];
    return $colores[$categoria] ?? '#95a5a6';
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = [];

    if (isset($_POST['crear_gasto'])) {
        // Crear nuevo gasto
        $tipo = $_POST['tipo'] ?? '';
        $idGrua = $_POST['id_grua'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $costo = $_POST['costo'] ?? '';
        $proveedor = trim($_POST['proveedor'] ?? '');
        $factura = trim($_POST['factura'] ?? '');

        // Validaciones
        $tiposValidos = ['Reparacion', 'Gasto_Oficina', 'Gasolina', 'Mantenimiento', 'Seguro', 'Limpieza', 'Otros'];
        if (!in_array($tipo, $tiposValidos)) $errores[] = 'Tipo inválido';
        if (!ctype_digit((string)$idGrua)) $errores[] = 'Grúa inválida';
        if ($descripcion === '' || mb_strlen($descripcion) > 400) $errores[] = 'Descripción requerida (máx 400)';
        if (!DateTime::createFromFormat('Y-m-d', $fecha)) $errores[] = 'Fecha inválida';
        if (!DateTime::createFromFormat('H:i', $hora)) $errores[] = 'Hora inválida';
        if (!is_numeric($costo) || $costo < 0) $errores[] = 'Costo inválido';

        if (empty($errores)) {
            $stmt = $conexion->prepare("INSERT INTO `reparacion-servicio` (ID_Grua, Tipo, Descripcion, Fecha, Hora, Costo, Proveedor, Factura) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("issssdss", $idGrua, $tipo, $descripcion, $fecha, $hora, $costo, $proveedor, $factura);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Gasto creado correctamente";
                } else {
                    $_SESSION['error'] = "Error al crear gasto: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $_SESSION['error'] = implode(' · ', $errores);
        }
    } elseif (isset($_POST['editar_gasto'])) {
        // Editar gasto existente
        $idGasto = $_POST['id_gasto'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $idGrua = $_POST['id_grua'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $costo = $_POST['costo'] ?? '';
        $proveedor = trim($_POST['proveedor'] ?? '');
        $factura = trim($_POST['factura'] ?? '');

        // Validaciones
        $tiposValidos = ['Reparacion', 'Gasto_Oficina', 'Gasolina', 'Mantenimiento', 'Seguro', 'Limpieza', 'Otros'];
        if (!ctype_digit((string)$idGasto)) $errores[] = 'ID de gasto inválido';
        if (!in_array($tipo, $tiposValidos)) $errores[] = 'Tipo inválido';
        if (!ctype_digit((string)$idGrua)) $errores[] = 'Grúa inválida';
        if ($descripcion === '' || mb_strlen($descripcion) > 400) $errores[] = 'Descripción requerida (máx 400)';
        if (!DateTime::createFromFormat('Y-m-d', $fecha)) $errores[] = 'Fecha inválida';
        if (!DateTime::createFromFormat('H:i', $hora)) $errores[] = 'Hora inválida';
        if (!is_numeric($costo) || $costo < 0) $errores[] = 'Costo inválido';

        if (empty($errores)) {
            $stmt = $conexion->prepare("UPDATE `reparacion-servicio` SET ID_Grua=?, Tipo=?, Descripcion=?, Fecha=?, Hora=?, Costo=?, Proveedor=?, Factura=? WHERE ID_Gasto=?");
            if ($stmt) {
                $stmt->bind_param("issssdssi", $idGrua, $tipo, $descripcion, $fecha, $hora, $costo, $proveedor, $factura, $idGasto);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Gasto actualizado correctamente";
                } else {
                    $_SESSION['error'] = "Error al actualizar gasto: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $_SESSION['error'] = implode(' · ', $errores);
        }
    } elseif (isset($_POST['eliminar_gasto'])) {
        // Eliminar gasto
        $idGasto = $_POST['id_gasto'] ?? '';
        
        if (ctype_digit((string)$idGasto)) {
            $stmt = $conexion->prepare("DELETE FROM `reparacion-servicio` WHERE ID_Gasto=?");
            if ($stmt) {
                $stmt->bind_param("i", $idGasto);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Gasto eliminado correctamente";
                } else {
                    $_SESSION['error'] = "Error al eliminar gasto: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $_SESSION['error'] = "ID de gasto inválido";
        }
    }

    header("Location: Gastos-mejorado.php");
    exit;
}

// Obtener parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) && DateTime::createFromFormat('Y-m-d', $_GET['fecha_inicio']) !== false ? 
    $_GET['fecha_inicio'] : date('Y-m-01');

$fecha_fin = isset($_GET['fecha_fin']) && DateTime::createFromFormat('Y-m-d', $_GET['fecha_fin']) !== false ? 
    $_GET['fecha_fin'] : date('Y-m-d');

$tipo_gasto = isset($_GET['tipo_gasto']) ? $conexion->real_escape_string($_GET['tipo_gasto']) : '';
$grua = isset($_GET['grua']) ? $conexion->real_escape_string($_GET['grua']) : '';
$orden = isset($_GET['orden']) && in_array($_GET['orden'], ['fecha_desc', 'fecha_asc', 'costo_desc', 'costo_asc']) ? 
    $_GET['orden'] : 'fecha_desc';

// Construir consulta SQL con filtros
$sql = "SELECT rs.ID_Gasto, rs.ID_Grua, rs.Tipo, rs.Descripcion, rs.Fecha, rs.Hora, rs.Costo, rs.Proveedor, rs.Factura,
               g.Placa AS grua_placa, g.Marca AS grua_marca, g.Modelo AS grua_modelo
        FROM `reparacion-servicio` rs
        JOIN gruas g ON rs.ID_Grua = g.ID
        WHERE rs.Fecha BETWEEN ? AND ?";

$params = [$fecha_inicio, $fecha_fin];
$types = "ss";

if (!empty($tipo_gasto)) {
    $sql .= " AND rs.Tipo = ?";
    $params[] = $tipo_gasto;
    $types .= "s";
}

if (!empty($grua)) {
    $sql .= " AND g.Placa = ?";
    $params[] = $grua;
    $types .= "s";
}

// Ordenación
switch ($orden) {
    case 'fecha_asc': $sql .= " ORDER BY rs.Fecha ASC"; break;
    case 'costo_desc': $sql .= " ORDER BY rs.Costo DESC"; break;
    case 'costo_asc': $sql .= " ORDER BY rs.Costo ASC"; break;
    default: $sql .= " ORDER BY rs.Fecha DESC";
}

// Ejecutar consulta principal
$gastos = [];
$stmt = $conexion->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result) {
            $gastos = $result->fetch_all(MYSQLI_ASSOC);
        }
    }
    $stmt->close();
}

// Consulta para totales
$totales = ['total_gastado' => 0, 'total_registros' => 0];
$sql_totales = "SELECT SUM(rs.Costo) AS total_gastado, COUNT(*) AS total_registros
                FROM `reparacion-servicio` rs
                WHERE rs.Fecha BETWEEN ? AND ?";
$stmt = $conexion->prepare($sql_totales);
if ($stmt) {
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result) {
            $totales = $result->fetch_assoc() ?? $totales;
        }
    }
    $stmt->close();
}

// Consulta para totales por tipo de gasto
$tipos_totales = [];
$sql_tipos = "SELECT Tipo as nombre, SUM(Costo) AS total, COUNT(*) as cantidad
               FROM `reparacion-servicio`
               WHERE Fecha BETWEEN ? AND ?
               GROUP BY Tipo
               ORDER BY total DESC";
$stmt = $conexion->prepare($sql_tipos);
if ($stmt) {
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result) {
            $tipos_totales = $result->fetch_all(MYSQLI_ASSOC);
        }
    }
    $stmt->close();
}

// Consultas para opciones de filtros
$tipos_filtro = [];
$gruas_filtro = [];

$result = $conexion->query("SELECT DISTINCT Tipo FROM `reparacion-servicio` ORDER BY Tipo");
if ($result) $tipos_filtro = $result->fetch_all(MYSQLI_ASSOC);
    
$result = $conexion->query("SELECT ID, Placa, CONCAT(Placa, ' - ', Marca, ' ', Modelo) AS descripcion FROM gruas ORDER BY Placa");
if ($result) $gruas_filtro = $result->fetch_all(MYSQLI_ASSOC);

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin: 20px;
            overflow: hidden;
        }

        .header-section {
            background: var(--gradient-primary);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header-section p {
            font-size: 1.1rem;
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
            background: var(--light-color);
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid var(--secondary-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card h3 {
            color: var(--dark-color);
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .stat-card .description {
            color: #6c757d;
            font-size: 0.85rem;
            margin: 0.5rem 0 0 0;
        }

        .form-section {
            padding: 2rem;
            background: white;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 1.5rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-info {
            background: var(--info-color);
            color: white;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1.5rem 0;
            box-shadow: var(--shadow);
        }

        .chart-title {
            color: var(--primary-color);
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0 0 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1.5rem 0;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .action-btn.view {
            background: var(--info-color);
            color: white;
        }

        .action-btn.edit {
            background: var(--warning-color);
            color: white;
        }

        .action-btn.delete {
            background: var(--danger-color);
            color: white;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin: 1rem 0;
            border: none;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .filters-panel {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1.5rem 0;
            box-shadow: var(--shadow);
        }

        .export-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            position: relative;
            box-shadow: var(--shadow-lg);
        }

        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: #6c757d;
        }

        .close:hover {
            color: var(--danger-color);
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 1.5rem;
            }
            
            .header-section h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .export-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1><i class="fas fa-chart-line"></i> Gestión de Gastos</h1>
            <p>Sistema de control y análisis de gastos operativos</p>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><i class="fas fa-dollar-sign"></i> Total Gastado</h3>
                <div class="value">$<?= number_format($totales['total_gastado'] ?? 0, 2) ?></div>
                <div class="description">En el período seleccionado</div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fas fa-list-ol"></i> Registros</h3>
                <div class="value"><?= $totales['total_registros'] ?? 0 ?></div>
                <div class="description">Transacciones encontradas</div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fas fa-calendar-alt"></i> Período</h3>
                <div class="value"><?= date('d/m', strtotime($fecha_inicio)) ?> - <?= date('d/m', strtotime($fecha_fin)) ?></div>
                <div class="description">Rango de fechas</div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fas fa-chart-pie"></i> Categorías</h3>
                <div class="value"><?= count($tipos_totales) ?></div>
                <div class="description">Tipos de gasto activos</div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <h2 class="section-title">
                <i class="fas fa-plus-circle"></i> Registrar Nuevo Gasto
            </h2>
            
            <form method="post" id="gastoForm">
                <input type="hidden" name="crear_gasto" value="1">
                <input type="hidden" name="id_gasto" id="id_gasto_edit" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo de Gasto</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Seleccione...</option>
                            <option value="Reparacion">Reparación</option>
                            <option value="Gasto_Oficina">Gasto de Oficina</option>
                            <option value="Gasolina">Gasolina</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Seguro">Seguro</option>
                            <option value="Limpieza">Limpieza</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_grua">Grúa</label>
                        <select id="id_grua" name="id_grua" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($gruas_filtro as $gru): ?>
                                <option value="<?= htmlspecialchars($gru['ID']) ?>">
                                    <?= htmlspecialchars($gru['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="costo">Costo ($)</label>
                        <input type="number" id="costo" name="costo" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" maxlength="400" required placeholder="Descripción detallada del gasto..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="proveedor">Proveedor</label>
                        <input type="text" id="proveedor" name="proveedor" placeholder="Nombre del proveedor">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora">Hora</label>
                        <input type="time" id="hora" name="hora" value="<?= date('H:i') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="factura">Número de Factura</label>
                        <input type="text" id="factura" name="factura" placeholder="Número de factura">
                    </div>
                </div>

                <div class="form-row">
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fas fa-save"></i> <span id="btnText">Guardar Gasto</span>
                    </button>
                    <button type="button" class="btn btn-secondary" id="btnCancelar" style="display:none;" onclick="cancelarEdicion()">
                        <i class="fas fa-times"></i> Cancelar Edición
                    </button>
                </div>
            </form>
        </div>

        <!-- Filters Section -->
        <div class="filters-panel">
            <h2 class="section-title">
                <i class="fas fa-filter"></i> Filtros del Reporte
            </h2>
            
            <form id="reportForm" method="get">
                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_fin">Fecha de Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_gasto">Tipo de Gasto</label>
                        <select id="tipo_gasto" name="tipo_gasto">
                            <option value="">Todos los tipos</option>
                            <?php foreach ($tipos_filtro as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo['Tipo']) ?>" <?= ($tipo['Tipo'] == $tipo_gasto) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo['Tipo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="grua">Grúa</label>
                        <select id="grua" name="grua">
                            <option value="">Todas las grúas</option>
                            <?php foreach ($gruas_filtro as $gru): ?>
                                <option value="<?= htmlspecialchars($gru['Placa']) ?>" <?= ($gru['Placa'] == $grua) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($gru['descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="orden">Ordenar por</label>
                        <select id="orden" name="orden">
                            <option value="fecha_desc" <?= ($orden == 'fecha_desc') ? 'selected' : '' ?>>Fecha (más reciente)</option>
                            <option value="fecha_asc" <?= ($orden == 'fecha_asc') ? 'selected' : '' ?>>Fecha (más antigua)</option>
                            <option value="costo_desc" <?= ($orden == 'costo_desc') ? 'selected' : '' ?>>Monto (mayor a menor)</option>
                            <option value="costo_asc" <?= ($orden == 'costo_asc') ? 'selected' : '' ?>>Monto (menor a mayor)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generar Reporte
                    </button>
                    <a href="Gastos-mejorado.php" class="btn btn-secondary">
                        <i class="fas fa-broom"></i> Limpiar Filtros
                    </a>
                </div>
            </form>
        </div>

        <!-- Charts Section -->
        <div class="chart-container">
            <h2 class="chart-title">
                <i class="fas fa-chart-pie"></i> Distribución de Gastos por Categoría
            </h2>
            <canvas id="gastosChart" height="100"></canvas>
        </div>

        <div class="chart-container">
            <h2 class="chart-title">
                <i class="fas fa-chart-line"></i> Evolución de Gastos
            </h2>
            <canvas id="evolucionChart" height="100"></canvas>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title" style="margin: 0;">
                    <i class="fas fa-table"></i> Detalle de Gastos
                </h2>
                <div class="export-buttons">
                    <a href="?export=pdf&<?= http_build_query($_GET) ?>" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a href="?export=excel&<?= http_build_query($_GET) ?>" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                </div>
            </div>
            
            <?php if (!empty($gastos)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Categoría</th>
                                <th>Vehículo</th>
                                <th>Proveedor</th>
                                <th>Factura</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gastos as $gasto): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($gasto['Fecha'])) ?></td>
                                    <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                    <td><strong>$<?= number_format($gasto['Costo'], 2) ?></strong></td>
                                    <td>
                                        <span class="badge" style="background-color: <?= obtenerColorCategoria($gasto['Tipo']) ?>; color: white;">
                                            <i class="fas <?= obtenerIconoCategoria($gasto['Tipo']) ?>"></i>
                                            <?= $gasto['Tipo'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($gasto['grua_placa']) ?></td>
                                    <td><?= htmlspecialchars($gasto['Proveedor'] ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars($gasto['Factura'] ?: 'N/A') ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn view" title="Ver detalle" onclick="verDetalle(<?= $gasto['ID_Gasto'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="action-btn edit" title="Editar" onclick="editarGasto(<?= $gasto['ID_Gasto'] ?>, '<?= htmlspecialchars($gasto['Tipo']) ?>', <?= $gasto['ID_Grua'] ?>, '<?= htmlspecialchars($gasto['Descripcion']) ?>', '<?= $gasto['Fecha'] ?>', '<?= $gasto['Hora'] ?>', <?= $gasto['Costo'] ?>, '<?= htmlspecialchars($gasto['Proveedor']) ?>', '<?= htmlspecialchars($gasto['Factura']) ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="action-btn delete" title="Eliminar" onclick="eliminarGasto(<?= $gasto['ID_Gasto'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-info-circle"></i>
                    <h3>No se encontraron gastos</h3>
                    <p>No hay gastos que coincidan con los filtros seleccionados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de distribución de gastos
            const gastosCtx = document.getElementById('gastosChart');
            if (gastosCtx) {
                new Chart(gastosCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?= json_encode(array_column($tipos_totales, 'nombre')) ?>,
                        datasets: [{
                            data: <?= json_encode(array_column($tipos_totales, 'total')) ?>,
                            backgroundColor: [
                                '#e74c3c', '#3498db', '#f39c12', '#2ecc71', 
                                '#9b59b6', '#1abc9c', '#95a5a6'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                position: 'right',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de evolución mensual
            const evolucionCtx = document.getElementById('evolucionChart');
            if (evolucionCtx) {
                const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                const mesActual = new Date().getMonth();
                const combustible = Array(12).fill(0);
                const mantenimiento = Array(12).fill(0);
                const total = Array(12).fill(0);
                
                // Simular datos para el mes actual
                if (mesActual >= 0) {
                    combustible[mesActual] = <?= $totales['total_gastado'] ?? 0 ?> * 0.6;
                    mantenimiento[mesActual] = <?= $totales['total_gastado'] ?? 0 ?> * 0.3;
                    total[mesActual] = <?= $totales['total_gastado'] ?? 0 ?>;
                }
                
                new Chart(evolucionCtx, {
                    type: 'line',
                    data: {
                        labels: meses,
                        datasets: [
                            {
                                label: 'Combustible',
                                data: combustible,
                                borderColor: '#3498db',
                                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Mantenimiento',
                                data: mantenimiento,
                                borderColor: '#2ecc71',
                                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Total',
                                data: total,
                                borderColor: '#e74c3c',
                                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Evolución de Gastos Mensuales <?= date("Y") ?>',
                                font: { size: 16, weight: 'bold' }
                            },
                            tooltip: { 
                                mode: 'index', 
                                intersect: false,
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleColor: 'white',
                                bodyColor: 'white'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { 
                                    display: true, 
                                    text: 'Monto ($)',
                                    font: { weight: 'bold' }
                                },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            },
                            x: {
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            }
                        },
                        interaction: { mode: 'nearest', axis: 'x', intersect: false }
                    }
                });
            }

            // Funciones para acciones
            window.verDetalle = function(id) {
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <span class="close" onclick="this.parentElement.parentElement.remove()">&times;</span>
                        <h2><i class="fas fa-info-circle"></i> Detalle del Gasto</h2>
                        <div id="detalle-content">
                            <p><i class="fas fa-spinner fa-spin"></i> Cargando detalles del gasto ID: ${id}...</p>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                modal.style.display = 'block';
            };

            window.editarGasto = function(id, tipo, idGrua, descripcion, fecha, hora, costo, proveedor, factura) {
                document.getElementById('id_gasto_edit').value = id;
                document.getElementById('tipo').value = tipo;
                document.getElementById('id_grua').value = idGrua;
                document.getElementById('descripcion').value = descripcion;
                document.getElementById('fecha').value = fecha;
                document.getElementById('hora').value = hora;
                document.getElementById('costo').value = costo;
                document.getElementById('proveedor').value = proveedor;
                document.getElementById('factura').value = factura;
                
                document.querySelector('input[name="crear_gasto"]').value = '';
                document.querySelector('input[name="editar_gasto"]').value = '1';
                document.getElementById('btnText').textContent = 'Actualizar Gasto';
                document.getElementById('btnCancelar').style.display = 'inline-block';
                
                document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
            };

            window.eliminarGasto = function(id) {
                if (confirm('¿Está seguro que desea eliminar este gasto? Esta acción no se puede deshacer.')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="eliminar_gasto" value="1">
                        <input type="hidden" name="id_gasto" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            };

            window.cancelarEdicion = function() {
                document.getElementById('gastoForm').reset();
                document.getElementById('id_gasto_edit').value = '';
                document.querySelector('input[name="crear_gasto"]').value = '1';
                document.querySelector('input[name="editar_gasto"]').value = '';
                document.getElementById('btnText').textContent = 'Guardar Gasto';
                document.getElementById('btnCancelar').style.display = 'none';
                
                document.getElementById('fecha').value = '<?= date('Y-m-d') ?>';
                document.getElementById('hora').value = '<?= date('H:i') ?>';
            };

            // Auto-submit del formulario de filtros
            document.getElementById('reportForm').addEventListener('change', function() {
                this.submit();
            });

            // Validación del formulario
            document.getElementById('gastoForm').addEventListener('submit', function(e) {
                const costo = parseFloat(document.getElementById('costo').value);
                if (costo < 0) {
                    e.preventDefault();
                    alert('El costo no puede ser negativo');
                    return false;
                }
                
                const descripcion = document.getElementById('descripcion').value.trim();
                if (descripcion.length === 0) {
                    e.preventDefault();
                    alert('La descripción es requerida');
                    return false;
                }
            });

            // Cerrar modales
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.remove();
                }
            });
        });
    </script>
</body>
</html>
