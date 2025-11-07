<?php
/**
 * Script para procesar solicitudes pendientes automáticamente
 * Este script puede ser ejecutado por cron job cada X minutos
 * 
 * Uso: php procesar-auto-asignacion.php
 * Cron: */5 * * * * php /ruta/al/proyecto/procesar-auto-asignacion.php
 */

require_once '../conexion.php';
require_once 'AutoAsignacionGruas.php';

// Configurar logging
ini_set('log_errors', 1);
ini_set('error_log', 'auto_asignacion.log');

function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message");
}

try {
    logMessage("Iniciando procesamiento de auto-asignación");
    
    $autoAsignacion = new AutoAsignacionGruas($conn);
    
    if (!$autoAsignacion->estaHabilitada()) {
        logMessage("Auto-asignación deshabilitada, saliendo");
        exit(0);
    }
    
    // Obtener solicitudes pendientes
    $query_pendientes = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente' AND grua_asignada_id IS NULL";
    $result_pendientes = $conn->query($query_pendientes);
    $total_pendientes = $result_pendientes->fetch_assoc()['total'];
    
    logMessage("Solicitudes pendientes: $total_pendientes");
    
    if ($total_pendientes == 0) {
        logMessage("No hay solicitudes pendientes, saliendo");
        exit(0);
    }
    
    // Obtener grúas disponibles
    $query_gruas = "SELECT COUNT(*) as total FROM gruas_disponibles";
    $result_gruas = $conn->query($query_gruas);
    $total_gruas = $result_gruas->fetch_assoc()['total'];
    
    logMessage("Grúas disponibles: $total_gruas");
    
    if ($total_gruas == 0) {
        logMessage("No hay grúas disponibles, saliendo");
        exit(0);
    }
    
    // Procesar solicitudes
    $resultados = $autoAsignacion->procesarSolicitudesPendientes();
    
    $exitosos = 0;
    $fallidos = 0;
    
    foreach ($resultados as $resultado) {
        if ($resultado['resultado']['success']) {
            $exitosos++;
            logMessage("Asignación exitosa - Solicitud: {$resultado['solicitud_id']}, Grúa: {$resultado['resultado']['grua']['Placa']}");
        } else {
            $fallidos++;
            logMessage("Asignación fallida - Solicitud: {$resultado['solicitud_id']}, Error: {$resultado['resultado']['message']}");
        }
    }
    
    logMessage("Procesamiento completado - Exitosos: $exitosos, Fallidos: $fallidos");
    
    // Enviar notificación si hay muchas solicitudes pendientes
    if ($total_pendientes > 10) {
        logMessage("ALERTA: Hay $total_pendientes solicitudes pendientes");
        // Aquí podrías agregar lógica para enviar email o notificación
    }
    
} catch (Exception $e) {
    logMessage("Error en procesamiento: " . $e->getMessage());
    exit(1);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

logMessage("Script finalizado");
?>
