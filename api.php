<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "5211";  // Cambia esto por tu contraseña real
$dbname = "DBACK";

// Manejar solicitudes OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => "Error de conexión: " . $conn->connect_error]));
}

// Función para obtener todas las grúas (con prepared statements)
function getGruas($conn, $filtroEstado = 'all', $filtroTipo = 'all', $busqueda = '') {
    $sql = "SELECT * FROM gruas WHERE 1=1";
    $params = [];
    $types = '';
    
    if ($filtroEstado != 'all') {
        $sql .= " AND Estado = ?";
        $params[] = $filtroEstado;
        $types .= 's';
    }
    
    if ($filtroTipo != 'all') {
        $sql .= " AND Tipo = ?";
        $params[] = $filtroTipo;
        $types .= 's';
    }
    
    if (!empty($busqueda)) {
        $busqueda_like = '%' . $busqueda . '%';
        $sql .= " AND (Placa LIKE ? OR Modelo LIKE ?)";
        $params[] = $busqueda_like;
        $params[] = $busqueda_like;
        $types .= 'ss';
    }
    
    $sql .= " ORDER BY ID DESC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt && !empty($params)) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else if ($stmt) {
        $result = $stmt->execute() ? $stmt->get_result() : null;
    } else {
        $result = $conn->query($sql);
    }
    
    $gruas = array();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $gruas[] = $row;
        }
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
    
    return $gruas;
}

// Procesar acciones
if (isset($_GET['action'])) {
    $response = array();
    
    try {
        switch ($_GET['action']) {
            case 'getGruas':
                $filtroEstado = isset($_GET['estado']) ? $_GET['estado'] : 'all';
                $filtroTipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'all';
                $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
                $response['gruas'] = getGruas($conn, $filtroEstado, $filtroTipo, $busqueda);
                break;
                
            case 'getGrua':
                if (!isset($_GET['id'])) {
                    throw new Exception("ID de grúa no proporcionado");
                }
                
                $id = intval($_GET['id']);
                if ($id <= 0) {
                    throw new Exception("ID de grúa inválido");
                }
                
                $stmt = $conn->prepare("SELECT * FROM gruas WHERE ID = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $response['grua'] = $result->fetch_assoc();
                } else {
                    throw new Exception("Grúa no encontrada");
                }
                $stmt->close();
                break;
                
            case 'saveGrua':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    throw new Exception("Método no permitido");
                }
                
                // Leer el input JSON si es una solicitud POST con JSON
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $_POST = $input;
                }
                
                $required = ['placa', 'marca', 'modelo', 'tipo', 'estado'];
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("El campo $field es obligatorio");
                    }
                }
                
                // Validar placa (7 caracteres)
                if (strlen($_POST['placa']) != 7) {
                    throw new Exception("La placa debe tener exactamente 7 caracteres");
                }
                
                require_once 'utils/validaciones.php';
                $validador = new Validador();
                
                $id = isset($_POST['id']) ? intval($_POST['id']) : null;
                $placa = Validador::sanitizar($_POST['placa'] ?? '', 'string');
                $marca = Validador::sanitizar($_POST['marca'] ?? '', 'string');
                $modelo = Validador::sanitizar($_POST['modelo'] ?? '', 'string');
                $tipo = Validador::sanitizar($_POST['tipo'] ?? '', 'string');
                $estado = Validador::sanitizar($_POST['estado'] ?? '', 'string');
                
                // Validaciones
                $validador->requerido($placa, 'placa', 'La placa es requerida');
                $validador->longitud($placa, 'placa', 5, 10);
                $validador->requerido($marca, 'marca', 'La marca es requerida');
                $validador->longitud($marca, 'marca', 1, 50);
                $validador->requerido($modelo, 'modelo', 'El modelo es requerido');
                $validador->longitud($modelo, 'modelo', 1, 50);
                $validador->requerido($tipo, 'tipo', 'El tipo es requerido');
                $validador->requerido($estado, 'estado', 'El estado es requerido');
                
                $estados_validos = ['disponible', 'en_uso', 'mantenimiento', 'inactiva'];
                if (!in_array($estado, $estados_validos)) {
                    $validador->agregarError('estado', 'Estado no válido');
                }
                
                if ($validador->tieneErrores()) {
                    throw new Exception("Errores de validación: " . $validador->obtenerErroresString(', '));
                }
                
                if ($id) {
                    // Actualizar grúa existente
                    $stmt = $conn->prepare("UPDATE gruas SET 
                            Placa = ?, Marca = ?, Modelo = ?, Tipo = ?, Estado = ?,
                            FechaActualizacion = CURRENT_TIMESTAMP
                            WHERE ID = ?");
                    $stmt->bind_param("sssssi", $placa, $marca, $modelo, $tipo, $estado, $id);
                } else {
                    // Insertar nueva grúa
                    $stmt = $conn->prepare("INSERT INTO gruas (Placa, Marca, Modelo, Tipo, Estado) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $placa, $marca, $modelo, $tipo, $estado);
                }
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    if (!$id) {
                        $response['id'] = $conn->insert_id;
                    }
                } else {
                    throw new Exception("Error al guardar: " . $stmt->error);
                }
                $stmt->close();
                break;
                
            case 'deleteGrua':
                if (!isset($_GET['id'])) {
                    throw new Exception("ID de grúa no proporcionado");
                }
                
                $id = intval($_GET['id']);
                if ($id <= 0) {
                    throw new Exception("ID de grúa inválido");
                }
                
                $stmt = $conn->prepare("DELETE FROM gruas WHERE ID = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                } else {
                    throw new Exception("Error al eliminar: " . $stmt->error);
                }
                $stmt->close();
                break;
                
            case 'getMantenimientos':
                if (!isset($_GET['gruaId'])) {
                    throw new Exception("ID de grúa no proporcionado");
                }
                
                $gruaId = intval($_GET['gruaId']);
                $sql = "SELECT * FROM mantenimientos WHERE GruaID = $gruaId ORDER BY Fecha DESC";
                $result = $conn->query($sql);
                $mantenimientos = array();
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $mantenimientos[] = $row;
                    }
                }
                
                $response['mantenimientos'] = $mantenimientos;
                break;
                
            case 'saveMantenimiento':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    throw new Exception("Método no permitido");
                }
                
                // Leer el input JSON si es una solicitud POST con JSON
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $_POST = $input;
                }
                
                $required = ['gruaId', 'tipo', 'fecha', 'tecnico', 'detalles'];
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("El campo $field es obligatorio");
                    }
                }
                
                $gruaId = intval($_POST['gruaId']);
                $tipo = $conn->real_escape_string($_POST['tipo']);
                $fecha = $conn->real_escape_string($_POST['fecha']);
                $tecnico = $conn->real_escape_string($_POST['tecnico']);
                $costo = isset($_POST['costo']) ? floatval($_POST['costo']) : 0;
                $detalles = $conn->real_escape_string($_POST['detalles']);
                
                $sql = "INSERT INTO mantenimientos (GruaID, Tipo, Fecha, Tecnico, Costo, Detalles) VALUES (
                        $gruaId,
                        '$tipo',
                        '$fecha',
                        '$tecnico',
                        $costo,
                        '$detalles')";
                
                if ($conn->query($sql)) {
                    // Actualizar estado de la grúa si es mantenimiento correctivo
                    if ($tipo == 'correctivo') {
                        $updateSql = "UPDATE gruas SET Estado = 'Mantenimiento' WHERE ID = $gruaId";
                        $conn->query($updateSql);
                    }
                    
                    $response['success'] = true;
                    $response['id'] = $conn->insert_id;
                } else {
                    throw new Exception("Error al guardar: " . $conn->error);
                }
                break;
                
            case 'getStats':
                $stats = array(
                    'total' => 0,
                    'activas' => 0,
                    'mantenimiento' => 0,
                    'inactivas' => 0
                );
                
                $sql = "SELECT Estado, COUNT(*) as cantidad FROM gruas GROUP BY Estado";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $stats['total'] += $row['cantidad'];
                        
                        if ($row['Estado'] == 'Activa') {
                            $stats['activas'] = $row['cantidad'];
                        } elseif ($row['Estado'] == 'Mantenimiento') {
                            $stats['mantenimiento'] = $row['cantidad'];
                        } elseif ($row['Estado'] == 'Inactiva') {
                            $stats['inactivas'] = $row['cantidad'];
                        }
                    }
                }
                
                $response = $stats;
                break;
                
            default:
                throw new Exception("Acción no válida");
        }
    } catch (Exception $e) {
        http_response_code(400);
        $response = ['error' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit();
}

// Si no se especificó ninguna acción válida
http_response_code(404);
echo json_encode(['error' => 'Acción no especificada']);
?>