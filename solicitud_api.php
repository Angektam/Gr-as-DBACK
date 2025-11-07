<?php
require_once 'conexion.php';
require_once 'utils/validaciones.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar solicitudes OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Leer y validar JSON
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

// Crear instancia del validador
$validador = new Validador();

// Sanitizar y validar datos
$nombre = Validador::sanitizar($data['nombre'] ?? '', 'string');
$telefono = Validador::sanitizar($data['telefono'] ?? '', 'string');
$email = Validador::sanitizar($data['email'] ?? '', 'email');
$ubicacion_origen = Validador::sanitizar($data['ubicacion_origen'] ?? '', 'string');
$ubicacion_destino = Validador::sanitizar($data['ubicacion_destino'] ?? '', 'string');
$tipo_vehiculo = Validador::sanitizar($data['tipo_vehiculo'] ?? 'Baica', 'string');
$marca = Validador::sanitizar($data['marca'] ?? '', 'string');
$modelo = Validador::sanitizar($data['modelo'] ?? '', 'string');
$placa = Validador::sanitizar($data['placa'] ?? '', 'string');
$tipo_servicio = Validador::sanitizar($data['tipo_servicio'] ?? '', 'string');
$descripcion = Validador::sanitizar($data['descripcion'] ?? '', 'string');
$distancia = Validador::sanitizar($data['distancia'] ?? '0', 'string');
$costo_raw = $data['costo'] ?? '0';
$costo_clean = preg_replace('/[^0-9.]/', '', $costo_raw);
$costo = floatval($costo_clean);
$metodo_pago = Validador::sanitizar($data['metodo_pago'] ?? 'Efectivo', 'string');
$consentimiento = isset($data['consentimiento']) && $data['consentimiento'] == true ? 1 : 0;

// Aplicar validaciones
$validador->validarNombre($nombre, 'nombre', 2, 50, true);
$validador->validarTelefono($telefono, 'telefono', true);
if (!empty($email)) {
    $validador->validarEmail($email, 'email', false);
}
$validador->requerido($ubicacion_origen, 'ubicacion_origen', 'La ubicación de origen es requerida');
$validador->longitud($ubicacion_origen, 'ubicacion_origen', 5, 200);
$validador->requerido($ubicacion_destino, 'ubicacion_destino', 'La ubicación de destino es requerida');
$validador->longitud($ubicacion_destino, 'ubicacion_destino', 5, 200);
$validador->longitud($marca, 'marca', 1, 50);
$validador->longitud($modelo, 'modelo', 1, 50);
$validador->longitud($placa, 'placa', 0, 20);
$validador->requerido($tipo_servicio, 'tipo_servicio', 'El tipo de servicio es requerido');
$validador->longitud($descripcion, 'descripcion', 10, 500);
$validador->validarNumero($costo, 'costo', 0, 999999);
$validador->validarNumero(floatval(preg_replace('/[^0-9.]/', '', $distancia)), 'distancia', 0, 9999);

// Validar valores permitidos
$vehiculos_validos = ['Baica', 'Automóvil', 'Camioneta', 'Motocicleta', 'Autobus', 'Otro'];
if (!in_array($tipo_vehiculo, $vehiculos_validos)) {
    $validador->agregarError('tipo_vehiculo', 'Tipo de vehículo no válido');
}

$tipos_servicio_validos = ['remolque', 'bateria', 'gasolina', 'llanta', 'arranque', 'mecanica'];
if (!in_array($tipo_servicio, $tipos_servicio_validos)) {
    $validador->agregarError('tipo_servicio', 'Tipo de servicio no válido');
}

$metodos_validos = ['Efectivo', 'PayPal', 'Tarjeta', 'OXXO'];
if (!in_array($metodo_pago, $metodos_validos)) {
    $validador->agregarError('metodo_pago', 'Método de pago no válido');
}

if ($consentimiento != 1) {
    $validador->agregarError('consentimiento', 'Debes aceptar el consentimiento de datos');
}

// Si hay errores, retornarlos
if ($validador->tieneErrores()) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Errores de validación',
        'errors' => $validador->obtenerErrores()
    ]);
    exit;
}

// --- Guardar imagen si existe ---
$foto_vehiculo = null;

if (!empty($data['foto_vehiculo'])) {
    $base64 = $data['foto_vehiculo'];

    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
        $base64 = substr($base64, strpos($base64, ',') + 1);
        $type = strtolower($type[1]);

        if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo json_encode(['success' => false, 'message' => 'Tipo de imagen no soportado']);
            exit;
        }

        $base64 = str_replace(' ', '+', $base64);
        $imageData = base64_decode($base64);

        if ($imageData === false) {
            echo json_encode(['success' => false, 'message' => 'Error al decodificar la imagen']);
            exit;
        }

        if (strlen($imageData) > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Imagen demasiado grande (máx 2MB)']);
            exit;
        }

        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid('vehiculo_') . '.' . $type;
        $filePath = $uploadDir . $fileName;

        if (file_put_contents($filePath, $imageData) === false) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
            exit;
        }

        $foto_vehiculo = 'uploads/' . $fileName;
    } else {
        echo json_encode(['success' => false, 'message' => 'Formato de imagen inválido']);
        exit;
    }
}

// --- Insertar en base de datos usando prepared statements ---
$distancia_km = floatval(preg_replace('/[^0-9.]/', '', $distancia));
$ip_cliente = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

try {
    $stmt = $conn->prepare("INSERT INTO solicitudes (
        nombre_completo, telefono, email, ubicacion, ubicacion_destino, tipo_vehiculo, marca_vehiculo, modelo_vehiculo, 
        foto_vehiculo, tipo_servicio, descripcion_problema, urgencia, distancia_km, costo_estimado, 
        consentimiento_datos, ip_cliente, user_agent
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $urgencia = 'normal'; // Valor por defecto
    $foto_nombre = $foto_vehiculo ? basename($foto_vehiculo) : '';
    
    $stmt->bind_param("ssssssssssssddisss",
        $nombre, $telefono, $email, $ubicacion_origen, $ubicacion_destino,
        $tipo_vehiculo, $marca, $modelo, $foto_nombre,
        $tipo_servicio, $descripcion, $urgencia, $distancia_km, $costo,
        $consentimiento, $ip_cliente, $user_agent
    );

    if ($stmt->execute()) {
        $folio = $conn->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Solicitud guardada con éxito',
            'folio' => $folio
        ]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

} catch (Exception $e) {
    error_log("Error al guardar solicitud: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error al guardar la solicitud en la base de datos',
        'error' => (defined('APP_ENV') && APP_ENV === 'development') ? $e->getMessage() : 'Error interno del servidor'
    ]);
    
    if (isset($stmt)) {
        $stmt->close();
    }
}