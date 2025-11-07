<?php
// Configuración de la página
$page_title = 'Gestión de Gastos - Grúas DBACK';
$additional_css = ['./CSS/Gastos.css', 'https://cdn.jsdelivr.net/npm/chart.js'];

require_once '../conexion.php';
require_once '../utils/validaciones.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Login.php');
    exit;
}

// Generar token CSRF
$csrf_token = generarCSRF();

// Función para obtener iconos según categoría
function obtenerIconoCategoria($categoria) {
    $iconos = [
        'Reparacion' => 'fa-tools',
        'Gasto_Oficina' => 'fa-money-bill-wave',
        'Gasolina' => 'fa-gas-pump'
    ];
    return $iconos[$categoria] ?? 'fa-money-bill-wave';
}

// Conectar a la base de datos
if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión: " . ($conn->connect_error ?? "No se pudo establecer conexión"));
}

$conexion = $conn;

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    $token_recibido = $_POST['csrf_token'] ?? '';
    if (!validarCSRF($token_recibido)) {
        $_SESSION['error'] = "Error de seguridad: token inválido. Por favor, recarga la página.";
    } else {
        $validador = new Validador();

        if (isset($_POST['crear_gasto'])) {
            // Crear nuevo gasto
            $tipo = Validador::sanitizar($_POST['tipo'] ?? '', 'string');
            $idGrua = intval($_POST['id_grua'] ?? 0);
            $descripcion = Validador::sanitizar($_POST['descripcion'] ?? '', 'string');
            $fecha = Validador::sanitizar($_POST['fecha'] ?? '', 'string');
            $hora = Validador::sanitizar($_POST['hora'] ?? '', 'string');
            $costo_raw = $_POST['costo'] ?? '0';
            $costo = floatval(preg_replace('/[^0-9.]/', '', $costo_raw));

            // Validaciones
            $tiposValidos = ['Reparacion','Gasto_Oficina','Gasolina'];
            if (!in_array($tipo, $tiposValidos, true)) {
                $validador->agregarError('tipo', 'Tipo inválido');
            }
            $validador->validarNumero($idGrua, 'id_grua', 1, 999999);
            $validador->requerido($descripcion, 'descripcion', 'La descripción es requerida');
            $validador->longitud($descripcion, 'descripcion', 5, 400);
            $validador->requerido($fecha, 'fecha', 'La fecha es requerida');
            $validador->requerido($hora, 'hora', 'La hora es requerida');
            $validador->validarNumero($costo, 'costo', 0, 999999);

            if (!$validador->tieneErrores()) {
                $stmt = $conexion->prepare("INSERT INTO `reparacion-servicio` (ID_Grua, Tipo, Descripcion, Fecha, Hora, Costo) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issssd", $idGrua, $tipo, $descripcion, $fecha, $hora, $costo);
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Gasto creado correctamente";
                    } else {
                        $_SESSION['error'] = "No se pudo crear el gasto: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $_SESSION['error'] = "Error preparando inserción: " . $conexion->error;
                }
            } else {
                $_SESSION['error'] = $validador->obtenerErroresString(' · ');
            }
        } elseif (isset($_POST['editar_gasto'])) {
            // Editar gasto existente
            $idGasto = intval($_POST['id_gasto'] ?? 0);
            $tipo = Validador::sanitizar($_POST['tipo'] ?? '', 'string');
            $idGrua = intval($_POST['id_grua'] ?? 0);
            $descripcion = Validador::sanitizar($_POST['descripcion'] ?? '', 'string');
            $fecha = Validador::sanitizar($_POST['fecha'] ?? '', 'string');
            $hora = Validador::sanitizar($_POST['hora'] ?? '', 'string');
            $costo_raw = $_POST['costo'] ?? '0';
            $costo = floatval(preg_replace('/[^0-9.]/', '', $costo_raw));

            // Validaciones
            $validador->validarNumero($idGasto, 'id_gasto', 1, 999999);
            $tiposValidos = ['Reparacion','Gasto_Oficina','Gasolina'];
            if (!in_array($tipo, $tiposValidos, true)) {
                $validador->agregarError('tipo', 'Tipo inválido');
            }
            $validador->validarNumero($idGrua, 'id_grua', 1, 999999);
            $validador->requerido($descripcion, 'descripcion', 'La descripción es requerida');
            $validador->longitud($descripcion, 'descripcion', 5, 400);
            $validador->requerido($fecha, 'fecha', 'La fecha es requerida');
            $validador->requerido($hora, 'hora', 'La hora es requerida');
            $validador->validarNumero($costo, 'costo', 0, 999999);

            if (!$validador->tieneErrores()) {
            $stmt = $conexion->prepare("UPDATE `reparacion-servicio` SET ID_Grua=?, Tipo=?, Descripcion=?, Fecha=?, Hora=?, Costo=? WHERE ID_Gasto=?");
            if ($stmt) {
                $stmt->bind_param("issssdi", $idGrua, $tipo, $descripcion, $fecha, $hora, $costo, $idGasto);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Gasto actualizado correctamente";
                } else {
                    $_SESSION['error'] = "No se pudo actualizar el gasto: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = "Error preparando actualización: " . $conexion->error;
            }
            } else {
                $_SESSION['error'] = $validador->obtenerErroresString(' · ');
            }
        } elseif (isset($_POST['eliminar_gasto'])) {
            $idGasto = intval($_POST['id_gasto'] ?? 0);
            if ($idGasto <= 0) {
                $_SESSION['error'] = "ID de gasto inválido";
            } else {
                $stmt = $conexion->prepare("DELETE FROM `reparacion-servicio` WHERE ID_Gasto=?");
                if ($stmt) {
                    $stmt->bind_param("i", $idGasto);
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Gasto eliminado correctamente";
                    } else {
                        $_SESSION['error'] = "No se pudo eliminar el gasto: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $_SESSION['error'] = "Error preparando eliminación: " . $conexion->error;
                }
            }
        }
    }

    header("Location: Gastos.php");
    exit;
}

// 6. Validar y sanitizar parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) && DateTime::createFromFormat('Y-m-d', $_GET['fecha_inicio']) !== false ? 
    $_GET['fecha_inicio'] : date('Y-m-01');

$fecha_fin = isset($_GET['fecha_fin']) && DateTime::createFromFormat('Y-m-d', $_GET['fecha_fin']) !== false ? 
    $_GET['fecha_fin'] : date('Y-m-d');

$tipo_gasto = isset($_GET['tipo_gasto']) ? $conexion->real_escape_string($_GET['tipo_gasto']) : '';
$grua = isset($_GET['grua']) ? $conexion->real_escape_string($_GET['grua']) : '';
$orden = isset($_GET['orden']) && in_array($_GET['orden'], ['fecha_desc', 'fecha_asc', 'costo_desc', 'costo_asc']) ? 
    $_GET['orden'] : 'fecha_desc';

// 7. Construir consulta SQL con filtros
$sql = "SELECT rs.ID_Gasto, rs.ID_Grua, rs.Tipo, rs.Descripcion, rs.Fecha, rs.Hora, rs.Costo,
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

// 8. Ejecutar consulta principal
$gastos = [];
$stmt = $conexion->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result) {
            $gastos = $result->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        $_SESSION['error'] = "Error al ejecutar la consulta: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Error al preparar la consulta: " . $conexion->error;
}

// 9. Consulta para totales
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

// 10. Consulta para totales por tipo de gasto (anteriormente categoría)
$tipos_totales = [];
$sql_tipos = "SELECT Tipo as nombre, SUM(Costo) AS total
               FROM `reparacion-servicio`
               WHERE Fecha BETWEEN ? AND ?
               GROUP BY Tipo";
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

// 11. Consultas para opciones de filtros
$tipos_filtro = [];
$gruas_filtro = [];

// Obtener Tipos de gasto directamente de la enum en la tabla (o si tuvieras una tabla de tipos)
// Por simplicidad, asumimos los tipos directamente de la definición ENUM o de datos ya existentes
$result = $conexion->query("SELECT DISTINCT Tipo FROM `reparacion-servicio` ORDER BY Tipo");
if ($result) $tipos_filtro = $result->fetch_all(MYSQLI_ASSOC);
    
$result = $conexion->query("SELECT ID, Placa, CONCAT(Placa, ' - ', Marca, ' ', Modelo) AS descripcion FROM gruas ORDER BY Placa");
if ($result) $gruas_filtro = $result->fetch_all(MYSQLI_ASSOC);
    
// Quitar proveedores, ya no aplica

// 12. Cerrar conexión
$conexion->close();

// 13. Funciones para generar reportes
function generarPDF($gastos, $filtros) {
    // Verificar si hay datos para el reporte
    if (empty($gastos)) {
        $_SESSION['error'] = "No hay datos para generar el reporte PDF";
        return false;
    }

    // Ruta a TCPDF (ajusta según tu estructura de directorios)
    $tcpdfPath = 'tcpdf/tcpdf.php';
    if (!file_exists($tcpdfPath)) {
        $_SESSION['error'] = "Error: No se encontró la librería TCPDF en $tcpdfPath";
        return false;
    }

    require_once($tcpdfPath);
    
    try {
        // Evitar constantes PDF_* y el uso directo del tipo para no romper el linter si TCPDF no está instalado
        $tcpdfClass = class_exists('TCPDF') ? 'TCPDF' : null;
        if ($tcpdfClass === null) {
            $_SESSION['error'] = "TCPDF no disponible. Instale la librería para exportar a PDF.";
            return false;
        }
        $pdf = new $tcpdfClass('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Grúas DBACK');
        $pdf->SetAuthor('Sistema de Gastos');
        $pdf->SetTitle('Reporte de Gastos');
        $pdf->AddPage();
        
        // Cabecera
        $html = '<h1 style="text-align:center;">Reporte de Gastos</h1>';
        $html .= '<p><strong>Período:</strong> '.date('d/m/Y', strtotime($filtros['fecha_inicio'])).' al '.date('d/m/Y', strtotime($filtros['fecha_fin'])).'</p>';
        
        if (!empty($filtros['tipo_gasto'])) {
            $html .= '<p><strong>Tipo de Gasto:</strong> '.htmlspecialchars($filtros['tipo_gasto']).'</p>';
        }
        if (!empty($filtros['grua'])) {
            $html .= '<p><strong>Grúa:</strong> '.htmlspecialchars($filtros['grua']).'</p>';
        }
        
        // Tabla de gastos
        $html .= '<table border="1" cellpadding="5">
                    <tr style="background-color:#f2f2f2;">
                        <th width="15%">Fecha</th>
                        <th width="35%">Concepto</th>
                        <th width="15%">Monto</th>
                        <th width="20%">Categoría</th>
                        <th width="15%">Vehículo</th>
                    </tr>';
        
        $total = 0;
        foreach ($gastos as $gasto) {
            $html .= '<tr>
                        <td>'.date('d/m/Y', strtotime($gasto['Fecha'])).'</td>
                        <td>'.htmlspecialchars($gasto['Descripcion']).'</td>
                        <td>$'.number_format($gasto['Costo'], 2).'</td>
                        <td>'.htmlspecialchars($gasto['Tipo']).'</td>
                        <td>'.htmlspecialchars($gasto['grua_placa']).'</td>
                    </tr>';
            $total += $gasto['Costo'];
        }
        
        $html .= '<tr style="background-color:#f2f2f2;">
                    <td colspan="2"><strong>Total</strong></td>
                    <td colspan="3"><strong>$'.number_format($total, 2).'</strong></td>
                  </tr></table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('reporte_gastos.pdf', 'D');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Error al generar PDF: " . $e->getMessage();
        return false;
    }
}

function generarExcel($gastos, $filtros) {
    if (empty($gastos)) {
        $_SESSION['error'] = "No hay datos para generar el reporte Excel";
        return false;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="reporte_gastos.xls"');
    header('Cache-Control: max-age=0');
    
    echo '<table border="1">
            <tr><th colspan="5" style="background-color:#cccccc;">Reporte de Gastos</th></tr>
            <tr><th colspan="5">Período: '.date('d/m/Y', strtotime($filtros['fecha_inicio'])).' al '.date('d/m/Y', strtotime($filtros['fecha_fin'])).'</th></tr>';
    
    if (!empty($filtros['tipo_gasto'])) {
        echo '<tr><th colspan="5">Tipo de Gasto: '.htmlspecialchars($filtros['tipo_gasto']).'</th></tr>';
    }
    if (!empty($filtros['grua'])) {
        echo '<tr><th colspan="5">Grúa: '.htmlspecialchars($filtros['grua']).'</th></tr>';
    }
    
    echo '<tr style="background-color:#f2f2f2;">
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Monto</th>
            <th>Categoría</th>
            <th>Vehículo</th>
          </tr>';
    
    $total = 0;
    foreach ($gastos as $gasto) {
        echo '<tr>
                <td>'.date('d/m/Y', strtotime($gasto['Fecha'])).'</td>
                <td>'.htmlspecialchars($gasto['Descripcion']).'</td>
                <td>$'.number_format($gasto['Costo'], 2).'</td>
                <td>'.htmlspecialchars($gasto['Tipo']).'</td>
                <td>'.htmlspecialchars($gasto['grua_placa']).'</td>
              </tr>';
        $total += $gasto['Costo'];
    }
    
    echo '<tr style="background-color:#f2f2f2;">
            <td colspan="2"><strong>Total</strong></td>
            <td colspan="3"><strong>$'.number_format($total, 2).'</strong></td>
          </tr></table>';
    exit;
}

// 14. Procesar exportación de reportes
if (isset($_GET['export'])) {
    $filtros = [
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'tipo_gasto' => $tipo_gasto,
        'grua' => $grua
    ];
    
    if ($_GET['export'] == 'pdf') {
        generarPDF($gastos, $filtros);
    } elseif ($_GET['export'] == 'excel') {
        generarExcel($gastos, $filtros);
    }
    
    // Si llegamos aquí es que hubo un error
    header('Location: Gastos.php');
    exit;
}
?>

<?php include '../components/header-component.php'; ?>
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

        .main-content {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin: 20px;
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin: 1rem 0;
            border: none;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: currentColor;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .admin-header {
            background: var(--gradient-primary);
            color: white;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .back-button:hover, .back-button:focus {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
            text-decoration: none;
            color: white;
        }
        
        .back-button svg {
            transition: transform 0.3s ease;
        }
        
        .back-button:hover svg {
            transform: translateX(-3px);
        }

        .container {
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: var(--gradient-primary);
            color: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header-actions {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .filters-panel {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            animation: slideInRight 0.8s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .filters-title {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            border-bottom: 3px solid var(--secondary-color);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-row {
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
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border-left: 5px solid var(--secondary-color);
            animation: fadeInUp 0.6s ease-out;
        }

        .summary-card:nth-child(1) { animation-delay: 0.1s; }
        .summary-card:nth-child(2) { animation-delay: 0.2s; }
        .summary-card:nth-child(3) { animation-delay: 0.3s; }
        .summary-card:nth-child(4) { animation-delay: 0.4s; }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .summary-card h3 {
            color: var(--dark-color);
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .summary-card .description {
            color: #6c757d;
            font-size: 0.85rem;
            margin: 0.5rem 0 0 0;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            animation: slideInRight 0.8s ease-out;
        }

        .chart-title {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 1.3rem;
            font-weight: 600;
            border-bottom: 3px solid var(--success-color);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--shadow);
            animation: fadeInUp 0.8s ease-out;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .badge:hover::before {
            left: 100%;
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
            text-decoration: none;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
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
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s ease, height 0.3s ease;
        }

        .action-btn:hover::before {
            width: 100%;
            height: 100%;
        }

        .action-btn:hover {
            transform: scale(1.15);
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
            -webkit-backdrop-filter: blur(5px);
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
            animation: fadeInUp 0.5s ease-out;
        }

        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .close:hover {
            color: var(--danger-color);
            transform: scale(1.1);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e9ecef;
            background: white;
            color: var(--dark-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .pagination-btn:hover {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            text-decoration: none;
        }

        .pagination-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 1.5rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .action-btn {
                width: 30px;
                height: 30px;
            }
            
            .header-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 1rem;
            }
            
            .filters-panel,
            .chart-container,
            .table-container {
                padding: 1rem;
            }
            
            .summary-card {
                padding: 1rem;
            }
            
            .summary-card .value {
                font-size: 1.5rem;
            }
        }

        /* Efectos de scroll suave */
        html {
            scroll-behavior: smooth;
        }

        /* Mejoras en el scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Efectos de transición global */
        * {
            transition: all 0.3s ease;
        }

        /* Mejoras en los iconos */
        .fas, .far, .fab {
            transition: transform 0.3s ease;
        }

        .btn:hover .fas,
        .btn:hover .far,
        .btn:hover .fab {
            transform: scale(1.1);
        }

        /* Efectos de sombra dinámicos */
        .summary-card,
        .chart-container,
        .table-container,
        .filters-panel {
            transition: box-shadow 0.3s ease;
        }

        .summary-card:hover,
        .chart-container:hover,
        .table-container:hover,
        .filters-panel:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        /* Mejoras en el formulario */
        .form-group {
            position: relative;
        }

        .form-group label {
            transition: color 0.3s ease;
        }

        .form-group input:focus + label,
        .form-group select:focus + label,
        .form-group textarea:focus + label {
            color: var(--secondary-color);
        }

        /* Efectos de validación */
        .form-group input:invalid:not(:placeholder-shown) {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-group input:valid:not(:placeholder-shown) {
            border-color: var(--success-color);
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
        }

        /* Efectos de hover en la tabla */
        tbody tr {
            position: relative;
        }

        tbody tr::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 0;
            height: 100%;
            background: var(--secondary-color);
            transition: width 0.3s ease;
        }

        tbody tr:hover::before {
            width: 4px;
        }

        /* Mejoras en la accesibilidad */
        .btn:focus,
        .action-btn:focus,
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: 2px solid var(--secondary-color);
            outline-offset: 2px;
        }
    </style>
</head>
    <main class="main-content">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert" style="background:#d4edda; color:#155724; border-color:#c3e6cb;">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
       <header class="admin-header">
    <nav aria-label="Navegación administrativa">
        <a href="MenuAdmin.PHP" class="back-button" aria-label="Volver al menú administrativo">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            <span>Volver al Menú</span>
        </a>
    </nav>
</header>

<style>
    .admin-header {
        background-color: #f8f9fa;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .back-button:hover, .back-button:focus {
        background-color: #e9ecef;
        color: #0056b3;
    }
    
    .back-button svg {
        transition: transform 0.2s ease;
    }
    
    .back-button:hover svg {
        transform: translateX(-2px);
    }
</style>
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-file-invoice-dollar"></i> Reportes de Gastos</h1>
                <div class="header-actions">
                    <a href="?export=pdf&<?= http_build_query($_GET) ?>" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <a href="?export=excel&<?= http_build_query($_GET) ?>" class="btn btn-secondary">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </a>
                </div>
            </div>

            <div class="filters-panel" style="margin-bottom: 18px;">
                <h2 class="filters-title"><i class="fas fa-plus-circle"></i> Registrar Gasto</h2>
                <form method="post" id="gastoForm" style="display:grid; gap:12px;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="crear_gasto" value="1">
                    <input type="hidden" name="id_gasto" id="id_gasto_edit" value="">
                    <div class="filter-row">
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Seleccione...</option>
                                <option value="Reparacion">Reparación</option>
                                <option value="Gasto_Oficina">Gasto de Oficina</option>
                                <option value="Gasolina">Gasolina</option>
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
                        <div class="form-group" style="flex:1">
                            <label for="descripcion">Descripción</label>
                            <input type="text" id="descripcion" name="descripcion" maxlength="400" required placeholder="Ej. Cambio de aceite, compra de gasolina...">
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars(date('Y-m-d')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="hora">Hora</label>
                            <input type="time" id="hora" name="hora" value="<?= htmlspecialchars(date('H:i')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="costo">Costo</label>
                            <input type="number" id="costo" name="costo" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="form-group" style="align-self:end">
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> <span id="btnText">Guardar</span>
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnCancelar" style="display:none;" onclick="cancelarEdicion()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="filters-panel">
                <h2 class="filters-title"><i class="fas fa-filter"></i> Filtros del Reporte</h2>
                <form id="reportForm" method="get">
                    <div class="filter-row">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de Inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
                        </div>
                        <div class="form-group">
                            <label for="fecha_fin">Fecha de Fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
                        </div>
                        <div class="form-group">
                            <label for="categoria">Tipo de Gasto</label>
                            <select id="tipo_gasto" name="tipo_gasto">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($tipos_filtro as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo['Tipo'] ?? $tipo['nombre']) ?>" <?= ((isset($tipo['Tipo']) && $tipo['Tipo'] == ($tipo_gasto ?? '')) || (isset($tipo['nombre']) && $tipo['nombre'] == ($tipo_gasto ?? ''))) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo['Tipo'] ?? $tipo['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-row">
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
                    <div class="filter-row">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Generar Reporte
                        </button>
                        <a href="Gastos.php" class="btn btn-secondary">
                            <i class="fas fa-broom"></i> Limpiar Filtros
                        </a>
                    </div>
                </form>
            </div>

            <div class="summary-cards">
                <div class="summary-card">
                    <h3><i class="fas fa-dollar-sign"></i> Total Gastado</h3>
                    <div class="value">$<?= number_format($totales['total_gastado'] ?? 0, 2) ?></div>
                    <div class="description">En el período seleccionado</div>
                </div>
                <div class="summary-card">
                    <h3><i class="fas fa-list-ol"></i> Registros</h3>
                    <div class="value"><?= $totales['total_registros'] ?? 0 ?></div>
                    <div class="description">Transacciones encontradas</div>
                </div>
                <?php foreach (array_slice($tipos_totales, 0, 4) as $tipo): ?>
                <div class="summary-card">
                    <h3><i class="fas <?= obtenerIconoCategoria($tipo['nombre']) ?>"></i> <?= htmlspecialchars($tipo['nombre']) ?></h3>
                    <div class="value">$<?= number_format($tipo['total'], 2) ?></div>
                    <div class="description">
                        <?= round(($tipo['total'] / ($totales['total_gastado'] ?: 1)) * 100, 1) ?>% del total
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="chart-container">
                <h2 class="chart-title"><i class="fas fa-chart-bar"></i> Distribución de Gastos</h2>
                <canvas id="gastosChart" height="100"></canvas>
            </div>

            <div class="chart-container">
                <h2 class="chart-title"><i class="fas fa-chart-line"></i> Evolución Mensual</h2>
                <canvas id="evolucionChart" height="100"></canvas>
            </div>

            <div class="table-container">
                <h2><i class="fas fa-table"></i> Detalle de Gastos</h2>
                <?php if (!empty($gastos)): ?>
                <table>
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
                            <td>$<?= number_format($gasto['Costo'], 2) ?></td>
                            <td><span class="badge" style="background-color: #6c757d;"><?= $gasto['Tipo'] ?></span></td>
                            <td><?= htmlspecialchars($gasto['grua_placa']) ?></td>
                            <td>N/A</td>
                            <td>N/A</td>
                            <td class="actions-cell">
                                <button class="action-btn" title="Ver detalle" onclick="verDetalle(<?= $gasto['ID_Gasto'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit" title="Editar" onclick="editarGasto(<?= $gasto['ID_Gasto'] ?>, '<?= htmlspecialchars($gasto['Tipo']) ?>', <?= $gasto['ID_Grua'] ?>, '<?= htmlspecialchars($gasto['Descripcion']) ?>', '<?= $gasto['Fecha'] ?>', '<?= $gasto['Hora'] ?>', <?= $gasto['Costo'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" title="Eliminar" onclick="eliminarGasto(<?= $gasto['ID_Gasto'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <button class="pagination-btn"><i class="fas fa-angle-double-left"></i></button>
                    <button class="pagination-btn">1</button>
                    <button class="pagination-btn active">2</button>
                    <button class="pagination-btn">3</button>
                    <button class="pagination-btn"><i class="fas fa-angle-double-right"></i></button>
                </div>
                <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-info-circle"></i> No se encontraron gastos con los filtros seleccionados
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

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
                            backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'right' },
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

            // Gráfico de evolución mensual (datos de ejemplo)
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
                                tension: 0.3
                            },
                            {
                                label: 'Mantenimiento',
                                data: mantenimiento,
                                borderColor: '#2ecc71',
                                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                                fill: true,
                                tension: 0.3
                            },
                            {
                                label: 'Total',
                                data: total,
                                borderColor: '#e74c3c',
                                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                                fill: true,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Evolución de Gastos Mensuales <?= date("Y") ?>'
                            },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Monto ($)' }
                            }
                        },
                        interaction: { mode: 'nearest', axis: 'x', intersect: false }
                    }
                });
            }

            // Funciones para acciones
            window.verDetalle = function(id) {
                // Crear modal de detalle
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <span class="close" onclick="this.parentElement.parentElement.remove()">&times;</span>
                        <h2>Detalle del Gasto</h2>
                        <div id="detalle-content">
                            <p>Cargando detalles del gasto ID: ${id}...</p>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                modal.style.display = 'block';
            };

            window.editarGasto = function(id, tipo, idGrua, descripcion, fecha, hora, costo) {
                // Llenar el formulario con los datos del gasto
                document.getElementById('id_gasto_edit').value = id;
                document.getElementById('tipo').value = tipo;
                document.getElementById('id_grua').value = idGrua;
                document.getElementById('descripcion').value = descripcion;
                document.getElementById('fecha').value = fecha;
                document.getElementById('hora').value = hora;
                document.getElementById('costo').value = costo;
                
                // Cambiar el formulario a modo edición
                document.querySelector('input[name="crear_gasto"]').value = '';
                document.querySelector('input[name="editar_gasto"]').value = '1';
                document.getElementById('btnText').textContent = 'Actualizar';
                document.getElementById('btnCancelar').style.display = 'inline-block';
                
                // Scroll al formulario
                document.querySelector('.filters-panel').scrollIntoView({ behavior: 'smooth' });
            };

            window.eliminarGasto = function(id) {
                if (confirm('¿Está seguro que desea eliminar este gasto? Esta acción no se puede deshacer.')) {
                    // Crear formulario para eliminar
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
                // Limpiar el formulario
                document.getElementById('gastoForm').reset();
                document.getElementById('id_gasto_edit').value = '';
                document.querySelector('input[name="crear_gasto"]').value = '1';
                document.querySelector('input[name="editar_gasto"]').value = '';
                document.getElementById('btnText').textContent = 'Guardar';
                document.getElementById('btnCancelar').style.display = 'none';
                
                // Restaurar valores por defecto
                document.getElementById('fecha').value = '<?= date('Y-m-d') ?>';
                document.getElementById('hora').value = '<?= date('H:i') ?>';
            };

            // Auto-submit del formulario de filtros
            document.getElementById('reportForm').addEventListener('change', function() {
                this.submit();
            });

            // Validación en tiempo real del formulario
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

            // Cerrar modales al hacer clic fuera
            window.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.remove();
                }
            });
        });
    </script>
<?php include '../components/footer-component.php'; ?>