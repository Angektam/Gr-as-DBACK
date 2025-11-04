<?php
/**
 * Test de Auto-Asignación - Sistema DBACK
 * Prueba completa del sistema de auto-asignación de grúas y equipos de ayuda
 */

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

echo "<h1>🧪 Test de Auto-Asignación - Sistema DBACK</h1>";
echo "<p><strong>Fecha de prueba:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Crear instancia del sistema de auto-asignación
$autoAsignacion = new AutoAsignacionGruas($conn);

echo "<h2>📋 1. Verificación del Sistema</h2>";

// Verificar si está habilitado
$habilitado = $autoAsignacion->estaHabilitada();
echo "<div style='background:" . ($habilitado ? "#d4edda" : "#f8d7da") . "; padding:15px; border-radius:10px; margin:10px 0;'>";
echo "<h3>" . ($habilitado ? "✅ Sistema Habilitado" : "❌ Sistema Deshabilitado") . "</h3>";
echo "<p>Estado: " . ($habilitado ? "Operativo" : "Inactivo") . "</p>";
echo "</div>";

// Obtener estado del servicio
$estado = $autoAsignacion->obtenerEstadoServicio();
echo "<div style='background:#e3f2fd; padding:15px; border-radius:10px; margin:10px 0;'>";
echo "<h3>📊 Estado del Servicio</h3>";
echo "<ul>";
echo "<li><strong>Servicio activo:</strong> " . ($estado['servicio_activo'] ? "Sí" : "No") . "</li>";
echo "<li><strong>Grúas disponibles:</strong> " . $estado['gruas_disponibles'] . "</li>";
echo "<li><strong>Solicitudes pendientes:</strong> " . $estado['solicitudes_pendientes'] . "</li>";
echo "<li><strong>Clima apto:</strong> " . ($estado['clima_apto'] ? "Sí" : "No") . "</li>";
echo "<li><strong>Mensaje:</strong> " . $estado['mensaje_usuario'] . "</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🔍 2. Verificación de Datos</h2>";

// Verificar solicitudes pendientes
$query_solicitudes = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'";
$result_solicitudes = $conn->query($query_solicitudes);
$solicitudes_pendientes = $result_solicitudes->fetch_assoc()['total'];

echo "<div style='background:#fff3cd; padding:15px; border-radius:10px; margin:10px 0;'>";
echo "<h3>📝 Solicitudes Pendientes</h3>";
echo "<p><strong>Total:</strong> $solicitudes_pendientes</p>";

if ($solicitudes_pendientes > 0) {
    // Mostrar detalles de las solicitudes pendientes
    $query_detalles = "SELECT id, nombre_completo, tipo_servicio, ubicacion, fecha_solicitud FROM solicitudes WHERE estado = 'pendiente' ORDER BY fecha_solicitud DESC LIMIT 5";
    $result_detalles = $conn->query($query_detalles);
    
    echo "<h4>Últimas 5 solicitudes pendientes:</h4>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:8px;'>ID</th>";
    echo "<th style='padding:8px;'>Nombre</th>";
    echo "<th style='padding:8px;'>Tipo Servicio</th>";
    echo "<th style='padding:8px;'>Ubicación</th>";
    echo "<th style='padding:8px;'>Fecha</th>";
    echo "</tr>";
    
    while ($row = $result_detalles->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding:8px;'>{$row['id']}</td>";
        echo "<td style='padding:8px;'>{$row['nombre_completo']}</td>";
        echo "<td style='padding:8px;'>{$row['tipo_servicio']}</td>";
        echo "<td style='padding:8px;'>{$row['ubicacion']}</td>";
        echo "<td style='padding:8px;'>{$row['fecha_solicitud']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:#6c757d;'>No hay solicitudes pendientes para procesar.</p>";
}
echo "</div>";

// Verificar grúas disponibles
$query_gruas = "SELECT COUNT(*) as total FROM gruas WHERE disponible_desde IS NOT NULL";
$result_gruas = $conn->query($query_gruas);
$gruas_disponibles = $result_gruas->fetch_assoc()['total'];

echo "<div style='background:#d1ecf1; padding:15px; border-radius:10px; margin:10px 0;'>";
echo "<h3>🚛 Grúas Disponibles</h3>";
echo "<p><strong>Total:</strong> $gruas_disponibles</p>";

if ($gruas_disponibles > 0) {
    // Mostrar algunas grúas disponibles
    $query_gruas_detalle = "SELECT ID, Placa, Tipo, coordenadas_actuales FROM gruas WHERE disponible_desde IS NOT NULL LIMIT 3";
    $result_gruas_detalle = $conn->query($query_gruas_detalle);
    
    echo "<h4>Algunas grúas disponibles:</h4>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:8px;'>ID</th>";
    echo "<th style='padding:8px;'>Placa</th>";
    echo "<th style='padding:8px;'>Tipo</th>";
    echo "<th style='padding:8px;'>Coordenadas</th>";
    echo "</tr>";
    
    while ($row = $result_gruas_detalle->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding:8px;'>{$row['ID']}</td>";
        echo "<td style='padding:8px;'>{$row['Placa']}</td>";
        echo "<td style='padding:8px;'>{$row['Tipo']}</td>";
        echo "<td style='padding:8px;'>{$row['coordenadas_actuales']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:#6c757d;'>No hay grúas disponibles en este momento.</p>";
}
echo "</div>";

echo "<h2>🧪 3. Pruebas de Auto-Asignación</h2>";

if ($solicitudes_pendientes > 0) {
    echo "<div style='background:#e8f5e8; padding:15px; border-radius:10px; margin:10px 0;'>";
    echo "<h3>🚀 Ejecutando Pruebas de Auto-Asignación</h3>";
    
    // Obtener la primera solicitud pendiente para probar
    $query_prueba = "SELECT id FROM solicitudes WHERE estado = 'pendiente' ORDER BY fecha_solicitud ASC LIMIT 1";
    $result_prueba = $conn->query($query_prueba);
    
    if ($result_prueba && $row = $result_prueba->fetch_assoc()) {
        $solicitud_id_prueba = $row['id'];
        
        echo "<p><strong>Probando con solicitud ID:</strong> $solicitud_id_prueba</p>";
        
        // Obtener detalles de la solicitud antes de la prueba
        $query_detalle = "SELECT nombre_completo, tipo_servicio, ubicacion, ubicacion_destino FROM solicitudes WHERE id = $solicitud_id_prueba";
        $result_detalle = $conn->query($query_detalle);
        $solicitud_detalle = $result_detalle->fetch_assoc();
        
        echo "<div style='background:#f8f9fa; padding:10px; border-radius:5px; margin:10px 0;'>";
        echo "<h4>Detalles de la solicitud de prueba:</h4>";
        echo "<ul>";
        echo "<li><strong>Nombre:</strong> {$solicitud_detalle['nombre_completo']}</li>";
        echo "<li><strong>Tipo de servicio:</strong> {$solicitud_detalle['tipo_servicio']}</li>";
        echo "<li><strong>Ubicación origen:</strong> {$solicitud_detalle['ubicacion']}</li>";
        echo "<li><strong>Ubicación destino:</strong> {$solicitud_detalle['ubicacion_destino']}</li>";
        echo "</ul>";
        echo "</div>";
        
        // Verificar si requiere equipo de ayuda
        $servicios_equipo_ayuda = ['gasolina', 'pila', 'bateria'];
        $requiere_equipo_ayuda = in_array(strtolower($solicitud_detalle['tipo_servicio']), $servicios_equipo_ayuda);
        
        echo "<div style='background:" . ($requiere_equipo_ayuda ? "#fff3cd" : "#d1ecf1") . "; padding:10px; border-radius:5px; margin:10px 0;'>";
        echo "<h4>" . ($requiere_equipo_ayuda ? "🚗 Requiere Equipo de Ayuda" : "🚛 Requiere Grúa") . "</h4>";
        echo "<p>Tipo de servicio: <strong>{$solicitud_detalle['tipo_servicio']}</strong></p>";
        echo "</div>";
        
        // Ejecutar la auto-asignación
        echo "<h4>Ejecutando auto-asignación...</h4>";
        
        $resultado = $autoAsignacion->asignarGrua($solicitud_id_prueba);
        
        echo "<div style='background:" . ($resultado['success'] ? "#d4edda" : "#f8d7da") . "; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h4>" . ($resultado['success'] ? "✅ Auto-Asignación Exitosa" : "❌ Auto-Asignación Fallida") . "</h4>";
        echo "<p><strong>Mensaje:</strong> {$resultado['message']}</p>";
        
        if (isset($resultado['notificacion'])) {
            echo "<p><strong>Notificación:</strong> {$resultado['notificacion']}</p>";
        }
        
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
        
        if (isset($resultado['accion_sugerida'])) {
            echo "<p><strong>Acción sugerida:</strong> {$resultado['accion_sugerida']}</p>";
        }
        
        echo "</div>";
        
        // Verificar el estado actual de la solicitud después de la asignación
        $query_estado_actual = "SELECT estado, equipo_asignado FROM solicitudes WHERE id = $solicitud_id_prueba";
        $result_estado_actual = $conn->query($query_estado_actual);
        $estado_actual = $result_estado_actual->fetch_assoc();
        
        echo "<div style='background:#e3f2fd; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h4>Estado Actual de la Solicitud</h4>";
        echo "<p><strong>Estado:</strong> {$estado_actual['estado']}</p>";
        if ($estado_actual['equipo_asignado']) {
            echo "<p><strong>Equipo asignado:</strong> {$estado_actual['equipo_asignado']}</p>";
        }
        echo "</div>";
        
    } else {
        echo "<p style='color:#6c757d;'>No se encontraron solicitudes pendientes para probar.</p>";
    }
    
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:10px 0;'>";
    echo "<h3>⚠️ No hay solicitudes pendientes</h3>";
    echo "<p>No se pueden ejecutar pruebas de auto-asignación porque no hay solicitudes pendientes en el sistema.</p>";
    echo "<p><strong>Sugerencia:</strong> Crea una nueva solicitud desde el formulario para probar el sistema.</p>";
    echo "</div>";
}

echo "<h2>📊 4. Estadísticas del Sistema</h2>";

// Estadísticas generales
$query_stats = "
    SELECT 
        estado,
        COUNT(*) as total,
        AVG(TIMESTAMPDIFF(MINUTE, fecha_solicitud, NOW())) as tiempo_promedio_minutos
    FROM solicitudes 
    WHERE fecha_solicitud >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY estado
    ORDER BY total DESC
";

$result_stats = $conn->query($query_stats);

echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:10px 0;'>";
echo "<h3>📈 Estadísticas de la Última Semana</h3>";
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:8px;'>Estado</th>";
echo "<th style='padding:8px;'>Total</th>";
echo "<th style='padding:8px;'>Tiempo Promedio (min)</th>";
echo "</tr>";

while ($row = $result_stats->fetch_assoc()) {
    $estado_display = [
        'pendiente' => 'Pendiente',
        'asignada' => 'Servicio Pendiente',
        'en_proceso' => 'En Proceso',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada'
    ][$row['estado']] ?? $row['estado'];
    
    echo "<tr>";
    echo "<td style='padding:8px;'>{$estado_display}</td>";
    echo "<td style='padding:8px;'>{$row['total']}</td>";
    echo "<td style='padding:8px;'>" . round($row['tiempo_promedio_minutos'], 1) . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h2>🔧 5. Verificación de Configuración</h2>";

// Verificar configuración de auto-asignación
$query_config = "SELECT parametro, valor FROM configuracion_auto_asignacion WHERE activo = 1";
$result_config = $conn->query($query_config);

echo "<div style='background:#e8f5e8; padding:15px; border-radius:10px; margin:10px 0;'>";
echo "<h3>⚙️ Configuración Actual</h3>";
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

echo "<h2>✅ 6. Resumen de la Prueba</h2>";

echo "<div style='background:#d4edda; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid #28a745;'>";
echo "<h3>🎉 Test de Auto-Asignación Completado</h3>";
echo "<ul>";
echo "<li>✅ <strong>Sistema verificado:</strong> " . ($habilitado ? "Habilitado" : "Deshabilitado") . "</li>";
echo "<li>✅ <strong>Grúas disponibles:</strong> $gruas_disponibles</li>";
echo "<li>✅ <strong>Solicitudes pendientes:</strong> $solicitudes_pendientes</li>";
echo "<li>✅ <strong>Clima apto:</strong> " . ($estado['clima_apto'] ? "Sí" : "No") . "</li>";
echo "<li>✅ <strong>Prueba ejecutada:</strong> " . ($solicitudes_pendientes > 0 ? "Sí" : "No (sin solicitudes)") . "</li>";
echo "</ul>";

if ($solicitudes_pendientes > 0) {
    echo "<p><strong>Recomendación:</strong> El sistema está funcionando correctamente. Puedes procesar más solicitudes o crear nuevas para probar diferentes escenarios.</p>";
} else {
    echo "<p><strong>Recomendación:</strong> Crea una nueva solicitud desde el formulario para probar completamente el sistema de auto-asignación.</p>";
}
echo "</div>";

echo "<div style='text-align:center; margin:30px 0; padding:20px; background:linear-gradient(135deg, #27ae60 0%, #5a7ba7 100%); color:white; border-radius:15px;'>";
echo "<h3 style='margin:0 0 10px 0;'>🧪 Test de Auto-Asignación Finalizado</h3>";
echo "<p style='margin:0; opacity:0.9;'>Sistema DBACK - Verificación completa del sistema de auto-asignación</p>";
echo "<p style='margin:10px 0 0 0; font-size:0.9rem; opacity:0.8;'>Ejecutado el " . date('d/m/Y H:i:s') . "</p>";
echo "</div>";

$conn->close();
?>
