<?php
require 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

function sanitize($str) {
    return htmlspecialchars(strip_tags(trim($str)));
}

// --- Sanitización y validación ---
$nombre = sanitize($data['nombre'] ?? '');
$telefono = isset($data['telefono']) && is_numeric($data['telefono']) ? (int)$data['telefono'] : null;
$email = trim($data['email'] ?? '') ?: null;
$ubicacion_origen = sanitize($data['ubicacion_origen'] ?? '');
$ubicacion_destino = sanitize($data['ubicacion_destino'] ?? '');
$tipo_vehiculo = $data['tipo_vehiculo'] ?? 'Baica';
$marca = sanitize($data['marca'] ?? '');
$modelo = sanitize($data['modelo'] ?? '');
$placa = sanitize($data['placa'] ?? '');
$tipo_servicio = sanitize($data['tipo_servicio'] ?? '');
$descripcion = sanitize($data['descripcion'] ?? '');
$distancia = trim($data['distancia'] ?? '') ?: null;
$costo = isset($data['costo']) && is_numeric($data['costo']) ? $data['costo'] : null;
$metodo_pago = $data['metodo_pago'] ?? 'Efectivo';
$consentimiento = isset($data['consentimiento']) && $data['consentimiento'] == true ? 1 : 0;

// --- Validaciones básicas ---
if (
    empty($nombre) || $telefono === null || empty($ubicacion_origen) || 
    empty($ubicacion_destino) || empty($tipo_servicio) || empty($descripcion)
) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
    exit;
}

if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Formato de email inválido.']);
    exit;
}

$vehiculos_validos = ['Baica', 'Automóvil', 'Camioneta', 'Motocicleta', 'Autobus', 'Submarino'];
if (!in_array($tipo_vehiculo, $vehiculos_validos)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de vehículo no válido']);
    exit;
}

$metodos_validos = ['Efectivo', 'PayPal'];
if (!in_array($metodo_pago, $metodos_validos)) {
    echo json_encode(['success' => false, 'message' => 'Método de pago no válido']);
    exit;
}

// --- Validaciones de longitud opcionales ---
if (strlen($nombre) > 100 || strlen($marca) > 100 || strlen($modelo) > 100) {
    echo json_encode(['success' => false, 'message' => 'Uno de los campos de texto es demasiado largo']);
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

// --- Insertar en base de datos ---
try {
    $stmt = $pdo->prepare("
        INSERT INTO solicitudes_servicio 
        (nombre, telefono, email, ubicacion_origen, ubicacion_destino, tipo_vehiculo, marca, modelo, placa, 
         foto_vehiculo, tipo_servicio, descripcion, distancia, costo, metodo_pago, consentimiento)
        VALUES 
        (:nombre, :telefono, :email, :ubicacion_origen, :ubicacion_destino, :tipo_vehiculo, :marca, :modelo, :placa,
         :foto_vehiculo, :tipo_servicio, :descripcion, :distancia, :costo, :metodo_pago, :consentimiento)
    ");

    $stmt->execute([
        ':nombre' => $nombre,
        ':telefono' => $telefono,
        ':email' => $email ?: null,
        ':ubicacion_origen' => $ubicacion_origen,
        ':ubicacion_destino' => $ubicacion_destino,
        ':tipo_vehiculo' => $tipo_vehiculo,
        ':marca' => $marca,
        ':modelo' => $modelo,
        ':placa' => $placa,
        ':foto_vehiculo' => $foto_vehiculo,
        ':tipo_servicio' => $tipo_servicio,
        ':descripcion' => $descripcion,
        ':distancia' => $distancia ?: null,
        ':costo' => $costo ?: null,
        ':metodo_pago' => $metodo_pago,
        ':consentimiento' => $consentimiento,
    ]);

    $folio = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Solicitud guardada con éxito',
        'folio' => $folio
    ]);

} catch (PDOException $e) {
    error_log("Error al guardar solicitud: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar la solicitud en la base de datos']);
}