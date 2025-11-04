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

// Función para obtener todas las grúas
function getGruas($conn, $filtroEstado = 'all', $filtroTipo = 'all', $busqueda = '') {
    $sql = "SELECT * FROM gruas WHERE 1=1";
    
    if ($filtroEstado != 'all') {
        $sql .= " AND Estado = '" . $conn->real_escape_string($filtroEstado) . "'";
    }
    
    if ($filtroTipo != 'all') {
        $sql .= " AND Tipo = '" . $conn->real_escape_string($filtroTipo) . "'";
    }
    
    if (!empty($busqueda)) {
        $sql .= " AND (Placa LIKE '%" . $conn->real_escape_string($busqueda) . "%' OR Modelo LIKE '%" . $conn->real_escape_string($busqueda) . "%')";
    }
    
    $sql .= " ORDER BY ID DESC";
    
    $result = $conn->query($sql);
    $gruas = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $gruas[] = $row;
        }
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
                $sql = "SELECT * FROM gruas WHERE ID = $id";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    $response['grua'] = $result->fetch_assoc();
                } else {
                    throw new Exception("Grúa no encontrada");
                }
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
                
                $id = isset($_POST['id']) ? intval($_POST['id']) : null;
                $placa = $conn->real_escape_string($_POST['placa']);
                $marca = $conn->real_escape_string($_POST['marca']);
                $modelo = $conn->real_escape_string($_POST['modelo']);
                $tipo = $conn->real_escape_string($_POST['tipo']);
                $estado = $conn->real_escape_string($_POST['estado']);
                
                if ($id) {
                    // Actualizar grúa existente
                    $sql = "UPDATE gruas SET 
                            Placa = '$placa',
                            Marca = '$marca',
                            Modelo = '$modelo',
                            Tipo = '$tipo',
                            Estado = '$estado',
                            FechaActualizacion = CURRENT_TIMESTAMP
                            WHERE ID = $id";
                } else {
                    // Insertar nueva grúa
                    $sql = "INSERT INTO gruas (Placa, Marca, Modelo, Tipo, Estado) VALUES (
                            '$placa',
                            '$marca',
                            '$modelo',
                            '$tipo',
                            '$estado')";
                }
                
                if ($conn->query($sql)) {
                    $response['success'] = true;
                    if (!$id) {
                        $response['id'] = $conn->insert_id;
                    }
                } else {
                    throw new Exception("Error al guardar: " . $conn->error);
                }
                break;
                
            case 'deleteGrua':
                if (!isset($_GET['id'])) {
                    throw new Exception("ID de grúa no proporcionado");
                }
                
                $id = intval($_GET['id']);
                $sql = "DELETE FROM gruas WHERE ID = $id";
                
                if ($conn->query($sql)) {
                    $response['success'] = true;
                } else {
                    throw new Exception("Error al eliminar: " . $conn->error);
                }
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