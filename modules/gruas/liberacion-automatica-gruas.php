<?php
/**
 * Sistema de Liberación Automática de Grúas
 * Libera automáticamente grúas completadas y asigna nuevas solicitudes
 */

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

echo "<h1>🤖 Sistema de Liberación Automática de Grúas</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Configuración del sistema
$configuracion = [
    'liberar_automaticamente' => true,
    'max_liberaciones_por_ejecucion' => 5,
    'tiempo_minimo_entre_liberaciones' => 30, // segundos
    'notificar_liberaciones' => true
];

echo "<h2>⚙️ Configuración del Sistema</h2>";
echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:20px 0;'>";
echo "<ul>";
echo "<li><strong>Liberación automática:</strong> " . ($configuracion['liberar_automaticamente'] ? 'Habilitada' : 'Deshabilitada') . "</li>";
echo "<li><strong>Máximo por ejecución:</strong> {$configuracion['max_liberaciones_por_ejecucion']} grúas</li>";
echo "<li><strong>Tiempo mínimo entre liberaciones:</strong> {$configuracion['tiempo_minimo_entre_liberaciones']} segundos</li>";
echo "<li><strong>Notificaciones:</strong> " . ($configuracion['notificar_liberaciones'] ? 'Habilitadas' : 'Deshabilitadas') . "</li>";
echo "</ul>";
echo "</div>";

if (!$configuracion['liberar_automaticamente']) {
    echo "<div style='background:#fff3cd; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>⚠️ Liberación Automática Deshabilitada</h3>";
    echo "<p>El sistema de liberación automática está deshabilitado. Para habilitarlo, modifica la configuración.</p>";
    echo "</div>";
    exit;
}

// Buscar grúas que pueden ser liberadas automáticamente
$query_gruas_para_liberar = "SELECT s.id as solicitud_id, s.nombre_completo, s.tipo_servicio, s.estado, s.grua_asignada_id, s.fecha_asignacion,
                                    g.ID as grua_id, g.Placa, g.ubicacion_actual, g.coordenadas_actuales
                             FROM solicitudes s
                             JOIN gruas g ON s.grua_asignada_id = g.ID
                             WHERE s.estado IN ('completada', 'cancelada')
                             AND s.fecha_liberacion IS NULL
                             ORDER BY 
                                 CASE s.urgencia 
                                     WHEN 'emergencia' THEN 1 
                                     WHEN 'urgente' THEN 2 
                                     WHEN 'normal' THEN 3 
                                 END, 
                                 s.fecha_asignacion ASC
                             LIMIT ?";

$stmt_gruas = $conn->prepare($query_gruas_para_liberar);
$stmt_gruas->bind_param("i", $configuracion['max_liberaciones_por_ejecucion']);
$stmt_gruas->execute();
$result_gruas = $stmt_gruas->get_result();

$gruas_para_liberar = [];
while ($row = $result_gruas->fetch_assoc()) {
    $gruas_para_liberar[] = $row;
}

echo "<h2>🔍 Grúas Identificadas para Liberación</h2>";

if (empty($gruas_para_liberar)) {
    echo "<div style='background:#d1ecf1; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>ℹ️ No hay grúas para liberar</h3>";
    echo "<p>No se encontraron grúas con solicitudes completadas o canceladas que puedan ser liberadas automáticamente.</p>";
    echo "</div>";
} else {
    echo "<div style='background:#fff3cd; padding:15px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>📊 Resumen</h3>";
    echo "<p><strong>Total grúas para liberar:</strong> " . count($gruas_para_liberar) . "</p>";
    echo "</div>";
    
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:8px;'>Solicitud ID</th>";
    echo "<th style='padding:8px;'>Cliente</th>";
    echo "<th style='padding:8px;'>Tipo Servicio</th>";
    echo "<th style='padding:8px;'>Estado</th>";
    echo "<th style='padding:8px;'>Grúa Asignada</th>";
    echo "<th style='padding:8px;'>Fecha Asignación</th>";
    echo "<th style='padding:8px;'>Acción</th>";
    echo "</tr>";
    
    foreach ($gruas_para_liberar as $grua) {
        $estado_color = $grua['estado'] == 'completada' ? '#28a745' : '#dc3545';
        $estado_texto = $grua['estado'] == 'completada' ? 'Completada' : 'Cancelada';
        
        echo "<tr>";
        echo "<td style='padding:8px;'>{$grua['solicitud_id']}</td>";
        echo "<td style='padding:8px;'>{$grua['nombre_completo']}</td>";
        echo "<td style='padding:8px;'>{$grua['tipo_servicio']}</td>";
        echo "<td style='padding:8px;'><span style='background:$estado_color; color:white; padding:4px 8px; border-radius:4px;'>$estado_texto</span></td>";
        echo "<td style='padding:8px;'>{$grua['Placa']} (ID: {$grua['grua_id']})</td>";
        echo "<td style='padding:8px;'>{$grua['fecha_asignacion']}</td>";
        echo "<td style='padding:8px;'><span style='background:#007bff; color:white; padding:4px 8px; border-radius:4px;'>Pendiente</span></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Procesar liberaciones automáticas
if (!empty($gruas_para_liberar)) {
    echo "<h2>🔄 Procesando Liberaciones Automáticas</h2>";
    
    $autoAsignacion = new AutoAsignacionGruas($conn);
    $liberaciones_exitosas = 0;
    $asignaciones_exitosas = 0;
    $errores = [];
    
    foreach ($gruas_para_liberar as $index => $grua) {
        echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:10px 0; border-left: 5px solid #007bff;'>";
        echo "<h3>Procesando Grúa #" . ($index + 1) . " - {$grua['Placa']}</h3>";
        echo "<p><strong>Solicitud:</strong> ID {$grua['solicitud_id']} - {$grua['nombre_completo']}</p>";
        
        try {
            // 1. Liberar la grúa
            $query_liberar = "UPDATE solicitudes SET estado = 'liberada', fecha_liberacion = NOW() WHERE id = ?";
            $stmt_liberar = $conn->prepare($query_liberar);
            $stmt_liberar->bind_param("i", $grua['solicitud_id']);
            
            if ($stmt_liberar->execute()) {
                echo "<div style='background:#d4edda; padding:10px; border-radius:5px; margin:10px 0;'>";
                echo "<h4>✅ Grúa liberada exitosamente</h4>";
                echo "<p>La grúa {$grua['Placa']} ha sido liberada y está disponible para nuevas asignaciones.</p>";
                echo "</div>";
                $liberaciones_exitosas++;
                
                // 2. Buscar nueva solicitud pendiente para asignar
                $query_nueva_solicitud = "SELECT id FROM solicitudes 
                                        WHERE estado = 'pendiente' 
                                        ORDER BY 
                                            CASE urgencia 
                                                WHEN 'emergencia' THEN 1 
                                                WHEN 'urgente' THEN 2 
                                                WHEN 'normal' THEN 3 
                                            END, 
                                            fecha_solicitud ASC 
                                        LIMIT 1";
                
                $result_nueva = $conn->query($query_nueva_solicitud);
                
                if ($result_nueva->num_rows > 0) {
                    $nueva_solicitud = $result_nueva->fetch_assoc();
                    $nueva_solicitud_id = $nueva_solicitud['id'];
                    
                    echo "<div style='background:#e3f2fd; padding:10px; border-radius:5px; margin:10px 0;'>";
                    echo "<h4>🔄 Asignando nueva solicitud...</h4>";
                    echo "<p>Buscando nueva solicitud para asignar a la grúa liberada...</p>";
                    echo "</div>";
                    
                    // Asignar nueva solicitud
                    $resultado_asignacion = $autoAsignacion->asignarGrua($nueva_solicitud_id);
                    
                    if ($resultado_asignacion['success']) {
                        echo "<div style='background:#d4edda; padding:10px; border-radius:5px; margin:10px 0;'>";
                        echo "<h4>✅ Nueva solicitud asignada exitosamente</h4>";
                        echo "<p><strong>Mensaje:</strong> {$resultado_asignacion['message']}</p>";
                        
                        if (isset($resultado_asignacion['grua'])) {
                            echo "<p><strong>Grúa asignada:</strong> {$resultado_asignacion['grua']['Placa']} (ID: {$resultado_asignacion['grua']['ID']})</p>";
                            if (isset($resultado_asignacion['grua']['distancia'])) {
                                echo "<p><strong>Distancia:</strong> " . round($resultado_asignacion['grua']['distancia'], 2) . " km</p>";
                            }
                        }
                        
                        if (isset($resultado_asignacion['equipo'])) {
                            echo "<p><strong>Equipo asignado:</strong> {$resultado_asignacion['equipo']['Nombre']} (ID: {$resultado_asignacion['equipo']['ID']})</p>";
                            if (isset($resultado_asignacion['equipo']['distancia'])) {
                                echo "<p><strong>Distancia:</strong> " . round($resultado_asignacion['equipo']['distancia'], 2) . " km</p>";
                            }
                        }
                        
                        if (isset($resultado_asignacion['tiempo_asignacion_ms'])) {
                            echo "<p><strong>Tiempo de asignación:</strong> {$resultado_asignacion['tiempo_asignacion_ms']} ms</p>";
                        }
                        
                        echo "</div>";
                        $asignaciones_exitosas++;
                    } else {
                        echo "<div style='background:#f8d7da; padding:10px; border-radius:5px; margin:10px 0;'>";
                        echo "<h4>⚠️ No se pudo asignar nueva solicitud</h4>";
                        echo "<p><strong>Mensaje:</strong> {$resultado_asignacion['message']}</p>";
                        echo "<p>La grúa está liberada pero no hay solicitudes pendientes compatibles.</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<div style='background:#fff3cd; padding:10px; border-radius:5px; margin:10px 0;'>";
                    echo "<h4>ℹ️ No hay solicitudes pendientes</h4>";
                    echo "<p>La grúa ha sido liberada pero no hay solicitudes pendientes para asignar.</p>";
                    echo "</div>";
                }
                
            } else {
                echo "<div style='background:#f8d7da; padding:10px; border-radius:5px; margin:10px 0;'>";
                echo "<h4>❌ Error al liberar grúa</h4>";
                echo "<p><strong>Error:</strong> " . $stmt_liberar->error . "</p>";
                echo "</div>";
                $errores[] = "Error al liberar grúa {$grua['Placa']}: " . $stmt_liberar->error;
            }
            
        } catch (Exception $e) {
            echo "<div style='background:#f8d7da; padding:10px; border-radius:5px; margin:10px 0;'>";
            echo "<h4>❌ Error en el proceso</h4>";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
            echo "</div>";
            $errores[] = "Error en grúa {$grua['Placa']}: " . $e->getMessage();
        }
        
        echo "</div>";
        
        // Pausa entre liberaciones si está configurado
        if ($configuracion['tiempo_minimo_entre_liberaciones'] > 0 && $index < count($gruas_para_liberar) - 1) {
            sleep($configuracion['tiempo_minimo_entre_liberaciones']);
        }
    }
    
    // Resumen final
    echo "<h2>📊 Resumen de Liberaciones Automáticas</h2>";
    echo "<div style='background:" . ($liberaciones_exitosas > 0 ? "#d4edda" : "#f8d7da") . "; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid " . ($liberaciones_exitosas > 0 ? "#28a745" : "#dc3545") . ";'>";
    echo "<h3>" . ($liberaciones_exitosas > 0 ? "✅ Procesamiento Exitoso" : "❌ Procesamiento Fallido") . "</h3>";
    echo "<ul>";
    echo "<li><strong>Grúas procesadas:</strong> " . count($gruas_para_liberar) . "</li>";
    echo "<li><strong>Liberaciones exitosas:</strong> $liberaciones_exitosas</li>";
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
}

$conn->close();

echo "<div style='text-align:center; margin:30px 0; padding:30px; background:linear-gradient(135deg, #28a745 0%, #007bff 100%); color:white; border-radius:20px;'>";
echo "<h2 style='margin:0 0 15px 0; font-size:2em;'>🤖 Liberación Automática Completada</h2>";
echo "<p style='margin:0; font-size:1.2em; opacity:0.9;'>Sistema de liberación automática ejecutado</p>";
echo "<p style='margin:10px 0 0 0; font-size:0.9rem; opacity:0.7;'>Ejecutado el " . date('d/m/Y H:i:s') . "</p>";
echo "</div>";
?>
