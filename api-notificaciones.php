<?php
/**
 * API para notificaciones y estado del servicio
 * Este endpoint proporciona información en tiempo real sobre:
 * - Notificaciones del usuario
 * - Estado del servicio de auto-asignación
 * - Disponibilidad de grúas
 * - Condiciones climáticas
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';
// La sesión ya se inicia en config.php

$response = [
    'success' => false,
    'data' => null,
    'message' => ''
];

try {
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['usuario_id'])) {
        $response['message'] = 'Usuario no autenticado';
        echo json_encode($response);
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'];
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

    $autoAsignacion = new AutoAsignacionGruas($conn);

    switch ($accion) {
        case 'obtener_notificaciones':
            $limite = $_GET['limite'] ?? 10;
            
            $query = "SELECT 
                        id,
                        solicitud_id,
                        tipo,
                        mensaje,
                        fecha_creacion,
                        leido
                      FROM notificaciones_usuarios
                      WHERE usuario_id = ?
                      ORDER BY fecha_creacion DESC
                      LIMIT ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $usuario_id, $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notificaciones = [];
            while ($row = $result->fetch_assoc()) {
                $row['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($row['fecha_creacion']));
                $notificaciones[] = $row;
            }
            
            // Contar notificaciones no leídas
            $query_count = "SELECT COUNT(*) as total FROM notificaciones_usuarios 
                           WHERE usuario_id = ? AND leido = 0";
            $stmt_count = $conn->prepare($query_count);
            $stmt_count->bind_param("i", $usuario_id);
            $stmt_count->execute();
            $count_result = $stmt_count->get_result();
            $no_leidas = $count_result->fetch_assoc()['total'];
            
            $response['success'] = true;
            $response['data'] = [
                'notificaciones' => $notificaciones,
                'no_leidas' => $no_leidas
            ];
            break;

        case 'marcar_leida':
            $notificacion_id = $_POST['notificacion_id'] ?? 0;
            
            $query = "UPDATE notificaciones_usuarios 
                     SET leido = 1, fecha_lectura = NOW() 
                     WHERE id = ? AND usuario_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $notificacion_id, $usuario_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Notificación marcada como leída';
            } else {
                $response['message'] = 'Error al marcar notificación';
            }
            break;

        case 'marcar_todas_leidas':
            $query = "UPDATE notificaciones_usuarios 
                     SET leido = 1, fecha_lectura = NOW() 
                     WHERE usuario_id = ? AND leido = 0";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $usuario_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Todas las notificaciones marcadas como leídas';
                $response['data'] = ['actualizadas' => $stmt->affected_rows];
            } else {
                $response['message'] = 'Error al marcar notificaciones';
            }
            break;

        case 'estado_servicio':
            $estado = $autoAsignacion->obtenerEstadoServicio();
            
            $response['success'] = true;
            $response['data'] = $estado;
            break;

        case 'verificar_clima':
            $clima = $autoAsignacion->verificarCondicionesClimaticas();
            
            $response['success'] = true;
            $response['data'] = $clima;
            break;

        case 'estadisticas_usuario':
            // Obtener estadísticas personalizadas del usuario
            $query = "SELECT 
                        COUNT(*) as total_solicitudes,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'asignada' THEN 1 ELSE 0 END) as asignadas,
                        SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas
                      FROM solicitudes
                      WHERE usuario_id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $estadisticas = $result->fetch_assoc();
            
            $response['success'] = true;
            $response['data'] = $estadisticas;
            break;

        case 'obtener_alertas_sistema':
            // Obtener alertas globales del sistema (clima, falta de grúas, etc.)
            $alertas = [];
            
            // Verificar estado del servicio
            $estado = $autoAsignacion->obtenerEstadoServicio();
            
            if (!$estado['servicio_activo']) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'icono' => 'exclamation-triangle',
                    'titulo' => 'Servicio Suspendido',
                    'mensaje' => $estado['mensaje_usuario']
                ];
            } elseif ($estado['gruas_disponibles'] == 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icono' => 'truck',
                    'titulo' => 'Sin Grúas Disponibles',
                    'mensaje' => 'Actualmente no hay grúas disponibles. Su solicitud será procesada cuando haya una grúa libre.'
                ];
            } elseif (!$estado['clima_apto']) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icono' => 'cloud-rain',
                    'titulo' => 'Condiciones Climáticas Adversas',
                    'mensaje' => $estado['razon_inactivo']
                ];
            }
            
            // Verificar si hay muchas solicitudes pendientes
            if ($estado['solicitudes_pendientes'] > 10) {
                $alertas[] = [
                    'tipo' => 'info',
                    'icono' => 'clock',
                    'titulo' => 'Alta Demanda',
                    'mensaje' => 'Hay ' . $estado['solicitudes_pendientes'] . ' solicitudes en espera. El tiempo de atención puede ser mayor.'
                ];
            }
            
            $response['success'] = true;
            $response['data'] = [
                'alertas' => $alertas,
                'estado_servicio' => $estado
            ];
            break;

        default:
            $response['message'] = 'Acción no válida';
            break;
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("Error en API notificaciones: " . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>

