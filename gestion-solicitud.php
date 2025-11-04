<?php
require_once 'conexion.php';
// La sesión ya se inicia en config.php que es incluido por conexion.php

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar que se hayan proporcionado los parámetros necesarios
if (!isset($_GET['accion']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje'] = "Parámetros inválidos";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: procesar-solicitud.php");
    exit();
}

$accion = $_GET['accion'];
$solicitud_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener el estado actual de la solicitud
$query = "SELECT estado FROM solicitudes WHERE id = ?";
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
$estado_actual = $solicitud['estado'];

// Determinar el nuevo estado según la acción
switch ($accion) {
    case 'aceptar':
        $nuevo_estado = 'asignada';
        $mensaje_exito = "Solicitud aceptada correctamente";
        break;
    
    case 'completar':
        $nuevo_estado = 'completada';
        $mensaje_exito = "Solicitud marcada como completada";
        break;
    
    case 'cancelar':
        $nuevo_estado = 'cancelada';
        $mensaje_exito = "Solicitud cancelada correctamente";
        break;
    
    default:
        $_SESSION['mensaje'] = "Acción no válida";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: detalle-solicitud.php?id=$solicitud_id");
        exit();
}

// Validar transición de estado permitida
$transiciones_permitidas = [
    'pendiente' => ['asignada', 'cancelada'],
    'asignada' => ['en_proceso', 'completada', 'cancelada'],
    'en_proceso' => ['completada', 'cancelada']
];

if (!in_array($nuevo_estado, $transiciones_permitidas[$estado_actual] ?? [])) {
    $_SESSION['mensaje'] = "No se puede cambiar de $estado_actual a $nuevo_estado";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: detalle-solicitud.php?id=$solicitud_id");
    exit();
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Actualizar el estado de la solicitud
    $query = "UPDATE solicitudes SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $nuevo_estado, $solicitud_id);
    $stmt->execute();
    
    // 2. Registrar en el historial
    if ($conn->query("SHOW TABLES LIKE 'historial_solicitudes'")->num_rows > 0) {
        $query = "INSERT INTO historial_solicitudes 
                 (solicitud_id, estado_anterior, estado_nuevo, usuario_id) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issi", $solicitud_id, $estado_actual, $nuevo_estado, $usuario_id);
        $stmt->execute();
    }
    
    // 3. Si es completada, registrar fecha de completado
    if ($accion == 'completar') {
        $query = "UPDATE solicitudes SET fecha_completado = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
    }
    
    // Confirmar transacción
    $conn->commit();
    
    $_SESSION['mensaje'] = $mensaje_exito;
    $_SESSION['tipo_mensaje'] = "exito";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    
    $_SESSION['mensaje'] = "Error al procesar la solicitud: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
}

// Redirigir de vuelta al detalle de la solicitud
header("Location: detalle-solicitud.php?id=$solicitud_id");
exit();
?>