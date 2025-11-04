<?php
/**
 * Diagnóstico de Procesamiento de Solicitudes
 * Investigar por qué no se están asignando las solicitudes
 */

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

echo "<h1>🔍 Diagnóstico de Procesamiento de Solicitudes</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar solicitudes pendientes
$query_pendientes = "SELECT id, nombre_completo, tipo_servicio, estado, fecha_solicitud FROM solicitudes WHERE estado = 'pendiente' ORDER BY fecha_solicitud DESC";
$result_pendientes = $conn->query($query_pendientes);

echo "<h2>📋 Solicitudes Pendientes</h2>";
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:8px;'>ID</th>";
echo "<th style='padding:8px;'>Nombre</th>";
echo "<th style='padding:8px;'>Tipo Servicio</th>";
echo "<th style='padding:8px;'>Estado</th>";
echo "<th style='padding:8px;'>Fecha</th>";
echo "</tr>";

$solicitudes_pendientes = [];
while ($row = $result_pendientes->fetch_assoc()) {
    $solicitudes_pendientes[] = $row;
    echo "<tr>";
    echo "<td style='padding:8px;'>{$row['id']}</td>";
    echo "<td style='padding:8px;'>{$row['nombre_completo']}</td>";
    echo "<td style='padding:8px;'>{$row['tipo_servicio']}</td>";
    echo "<td style='padding:8px;'>{$row['estado']}</td>";
    echo "<td style='padding:8px;'>{$row['fecha_solicitud']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>Total de solicitudes pendientes:</strong> " . count($solicitudes_pendientes) . "</p>";

if (count($solicitudes_pendientes) > 0) {
    echo "<h2>🧪 Procesando Solicitudes Individualmente</h2>";
    
    $autoAsignacion = new AutoAsignacionGruas($conn);
    $asignaciones_exitosas = 0;
    $errores = [];
    
    foreach ($solicitudes_pendientes as $solicitud) {
        echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:10px 0; border-left: 5px solid #007bff;'>";
        echo "<h3>Solicitud ID: {$solicitud['id']} - {$solicitud['nombre_completo']}</h3>";
        echo "<p><strong>Tipo de servicio:</strong> {$solicitud['tipo_servicio']}</p>";
        
        // Verificar si requiere equipo de ayuda
        $servicios_equipo_ayuda = ['gasolina', 'pila', 'bateria'];
        $requiere_equipo_ayuda = in_array(strtolower($solicitud['tipo_servicio']), $servicios_equipo_ayuda);
        
        echo "<p><strong>Requiere:</strong> " . ($requiere_equipo_ayuda ? "Equipo de Ayuda" : "Grúa") . "</p>";
        
        // Intentar asignar
        echo "<h4>Ejecutando auto-asignación...</h4>";
        
        try {
            $resultado = $autoAsignacion->asignarGrua($solicitud['id']);
            
            if ($resultado['success']) {
                echo "<div style='background:#d4edda; padding:10px; border-radius:5px; margin:10px 0;'>";
                echo "<h4>✅ Asignación Exitosa</h4>";
                echo "<p><strong>Mensaje:</strong> {$resultado['message']}</p>";
                
                if (isset($resultado['grua'])) {
                    echo "<p><strong>Grúa asignada:</strong> {$resultado['grua']['Placa']} (ID: {$resultado['grua']['ID']})</p>";
                    if (isset($resultado['grua']['distancia'])) {
                        echo "<p><strong>Distancia:</strong> " . round($resultado['grua']['distancia'], 2) . " km</p>";
                    }
                }
                
                if (isset($resultado['equipo'])) {
                    echo "<p><strong>Equipo asignado:</strong> {$resultado['equipo']['Nombre']} (ID: {$resultado['equipo']['ID']})</p>";
                    if (isset($resultado['equipo']['distancia'])) {
                        echo "<p><strong>Distancia:</strong> " . round($resultado['equipo']['distancia'], 2) . " km</p>";
                    }
                }
                
                if (isset($resultado['tiempo_asignacion_ms'])) {
                    echo "<p><strong>Tiempo de asignación:</strong> {$resultado['tiempo_asignacion_ms']} ms</p>";
                }
                
                echo "</div>";
                $asignaciones_exitosas++;
            } else {
                echo "<div style='background:#f8d7da; padding:10px; border-radius:5px; margin:10px 0;'>";
                echo "<h4>❌ Asignación Fallida</h4>";
                echo "<p><strong>Mensaje:</strong> {$resultado['message']}</p>";
                
                if (isset($resultado['notificacion'])) {
                    echo "<p><strong>Notificación:</strong> {$resultado['notificacion']}</p>";
                }
                
                if (isset($resultado['accion_sugerida'])) {
                    echo "<p><strong>Acción sugerida:</strong> {$resultado['accion_sugerida']}</p>";
                }
                
                echo "</div>";
                $errores[] = "Solicitud ID {$solicitud['id']}: {$resultado['message']}";
            }
        } catch (Exception $e) {
            echo "<div style='background:#f8d7da; padding:10px; border-radius:5px; margin:10px 0;'>";
            echo "<h4>❌ Error en Asignación</h4>";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
            echo "</div>";
            $errores[] = "Solicitud ID {$solicitud['id']}: " . $e->getMessage();
        }
        
        echo "</div>";
    }
    
    echo "<h2>📊 Resumen del Procesamiento</h2>";
    echo "<div style='background:" . ($asignaciones_exitosas > 0 ? "#d4edda" : "#f8d7da") . "; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid " . ($asignaciones_exitosas > 0 ? "#28a745" : "#dc3545") . ";'>";
    echo "<h3>" . ($asignaciones_exitosas > 0 ? "✅ Procesamiento Exitoso" : "❌ Procesamiento Fallido") . "</h3>";
    echo "<ul>";
    echo "<li><strong>Total procesadas:</strong> " . count($solicitudes_pendientes) . "</li>";
    echo "<li><strong>Asignaciones exitosas:</strong> $asignaciones_exitosas</li>";
    echo "<li><strong>Errores:</strong> " . count($errores) . "</li>";
    echo "</ul>";
    
    if (count($errores) > 0) {
        echo "<h4>Errores encontrados:</h4>";
        echo "<ul>";
        foreach ($errores as $error) {
            echo "<li style='color:red;'>$error</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    // Verificar estado actual de las solicitudes después del procesamiento
    echo "<h2>🔍 Estado Actual de las Solicitudes</h2>";
    $query_actual = "SELECT id, nombre_completo, tipo_servicio, estado, grua_asignada_id, fecha_asignacion FROM solicitudes WHERE id IN (" . implode(',', array_column($solicitudes_pendientes, 'id')) . ") ORDER BY id";
    $result_actual = $conn->query($query_actual);
    
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:8px;'>ID</th>";
    echo "<th style='padding:8px;'>Nombre</th>";
    echo "<th style='padding:8px;'>Tipo Servicio</th>";
    echo "<th style='padding:8px;'>Estado</th>";
    echo "<th style='padding:8px;'>Grúa Asignada</th>";
    echo "<th style='padding:8px;'>Fecha Asignación</th>";
    echo "</tr>";
    
    while ($row = $result_actual->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding:8px;'>{$row['id']}</td>";
        echo "<td style='padding:8px;'>{$row['nombre_completo']}</td>";
        echo "<td style='padding:8px;'>{$row['tipo_servicio']}</td>";
        echo "<td style='padding:8px;'>{$row['estado']}</td>";
        echo "<td style='padding:8px;'>" . ($row['grua_asignada_id'] ?: 'N/A') . "</td>";
        echo "<td style='padding:8px;'>" . ($row['fecha_asignacion'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "<div style='background:#f8d7da; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>⚠️ No hay solicitudes pendientes</h3>";
    echo "<p>No se pueden procesar solicitudes porque no hay solicitudes pendientes en el sistema.</p>";
    echo "</div>";
}

// Verificar configuración del sistema
echo "<h2>⚙️ Verificación de Configuración</h2>";

$query_config = "SELECT parametro, valor FROM configuracion_auto_asignacion WHERE activo = 1";
$result_config = $conn->query($query_config);

echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:20px 0;'>";
echo "<h3>Configuración Actual</h3>";
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:8px;'>Parámetro</th>";
echo "<th style='padding:8px;'>Valor</th>";
echo "</tr>";

while ($row = $result_config->fetch_assoc()) {
    echo "<tr>";
    echo "<td style='padding:8px;'>{$row['parametro']}</td>";
    echo "<td style='padding:8px;'>{$row['valor']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Verificar grúas disponibles
$query_gruas = "SELECT COUNT(*) as total FROM gruas WHERE disponible_desde IS NOT NULL";
$result_gruas = $conn->query($query_gruas);
$total_gruas = $result_gruas->fetch_assoc()['total'];

echo "<div style='background:#d1ecf1; padding:15px; border-radius:10px; margin:20px 0;'>";
echo "<h3>🚛 Grúas Disponibles</h3>";
echo "<p><strong>Total:</strong> $total_gruas</p>";
echo "</div>";

$conn->close();
?>
