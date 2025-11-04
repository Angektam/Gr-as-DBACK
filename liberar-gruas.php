<?php
/**
 * Sistema de Liberación de Grúas
 * Libera grúas completadas y asigna nuevas solicitudes automáticamente
 */

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

echo "<h1>🚛 Sistema de Liberación de Grúas</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar grúas que pueden ser liberadas (solicitudes completadas)
$query_gruas_ocupadas = "SELECT s.id as solicitud_id, s.nombre_completo, s.tipo_servicio, s.estado, s.grua_asignada_id, s.fecha_asignacion,
                                g.ID as grua_id, g.Placa, g.ubicacion_actual, g.coordenadas_actuales
                         FROM solicitudes s
                         JOIN gruas g ON s.grua_asignada_id = g.ID
                         WHERE s.estado IN ('completada', 'cancelada')
                         ORDER BY s.fecha_asignacion DESC";

$result_gruas_ocupadas = $conn->query($query_gruas_ocupadas);

echo "<h2>🔍 Grúas Ocupadas con Solicitudes Completadas</h2>";

if ($result_gruas_ocupadas->num_rows > 0) {
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
    
    $gruas_para_liberar = [];
    while ($row = $result_gruas_ocupadas->fetch_assoc()) {
        $gruas_para_liberar[] = $row;
        
        $estado_color = $row['estado'] == 'completada' ? '#28a745' : '#dc3545';
        $estado_texto = $row['estado'] == 'completada' ? 'Completada' : 'Cancelada';
        
        echo "<tr>";
        echo "<td style='padding:8px;'>{$row['solicitud_id']}</td>";
        echo "<td style='padding:8px;'>{$row['nombre_completo']}</td>";
        echo "<td style='padding:8px;'>{$row['tipo_servicio']}</td>";
        echo "<td style='padding:8px;'><span style='background:$estado_color; color:white; padding:4px 8px; border-radius:4px;'>$estado_texto</span></td>";
        echo "<td style='padding:8px;'>{$row['Placa']} (ID: {$row['grua_id']})</td>";
        echo "<td style='padding:8px;'>{$row['fecha_asignacion']}</td>";
        echo "<td style='padding:8px;'>";
        echo "<button onclick='liberarGrua({$row['grua_id']}, {$row['solicitud_id']})' style='background:#007bff; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;'>Liberar Grúa</button>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background:#fff3cd; padding:15px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>📊 Resumen</h3>";
    echo "<p><strong>Total grúas para liberar:</strong> " . count($gruas_para_liberar) . "</p>";
    echo "</div>";
    
} else {
    echo "<div style='background:#d1ecf1; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>ℹ️ No hay grúas para liberar</h3>";
    echo "<p>No se encontraron grúas con solicitudes completadas o canceladas.</p>";
    echo "</div>";
}

// Verificar solicitudes pendientes que pueden ser asignadas
$query_pendientes = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'";
$result_pendientes = $conn->query($query_pendientes);
$total_pendientes = $result_pendientes->fetch_assoc()['total'];

echo "<h2>📋 Solicitudes Pendientes</h2>";
echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:20px 0;'>";
echo "<p><strong>Total de solicitudes pendientes:</strong> $total_pendientes</p>";
echo "</div>";

// Función para liberar grúa y asignar nueva solicitud
if (isset($_POST['liberar_grua'])) {
    $grua_id = (int)$_POST['grua_id'];
    $solicitud_id = (int)$_POST['solicitud_id'];
    
    echo "<h2>🔄 Procesando Liberación de Grúa</h2>";
    
    try {
        // 1. Liberar la grúa (actualizar estado de la solicitud a 'liberada')
        $query_liberar = "UPDATE solicitudes SET estado = 'liberada', fecha_liberacion = NOW() WHERE id = ?";
        $stmt_liberar = $conn->prepare($query_liberar);
        $stmt_liberar->bind_param("i", $solicitud_id);
        
        if ($stmt_liberar->execute()) {
            echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:10px 0;'>";
            echo "<h4>✅ Grúa liberada exitosamente</h4>";
            echo "<p>La grúa ha sido liberada y está disponible para nuevas asignaciones.</p>";
            echo "</div>";
            
            // 2. Buscar nueva solicitud pendiente para asignar
            $autoAsignacion = new AutoAsignacionGruas($conn);
            
            // Obtener solicitudes pendientes ordenadas por urgencia y fecha
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
                
                echo "<div style='background:#e3f2fd; padding:15px; border-radius:10px; margin:10px 0;'>";
                echo "<h4>🔄 Asignando nueva solicitud...</h4>";
                echo "<p>Buscando nueva solicitud para asignar a la grúa liberada...</p>";
                echo "</div>";
                
                // Asignar nueva solicitud
                $resultado_asignacion = $autoAsignacion->asignarGrua($nueva_solicitud_id);
                
                if ($resultado_asignacion['success']) {
                    echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:10px 0;'>";
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
                } else {
                    echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:10px 0;'>";
                    echo "<h4>⚠️ No se pudo asignar nueva solicitud</h4>";
                    echo "<p><strong>Mensaje:</strong> {$resultado_asignacion['message']}</p>";
                    echo "<p>La grúa está liberada pero no hay solicitudes pendientes compatibles.</p>";
                    echo "</div>";
                }
            } else {
                echo "<div style='background:#fff3cd; padding:15px; border-radius:10px; margin:10px 0;'>";
                echo "<h4>ℹ️ No hay solicitudes pendientes</h4>";
                echo "<p>La grúa ha sido liberada pero no hay solicitudes pendientes para asignar.</p>";
                echo "</div>";
            }
            
        } else {
            echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:10px 0;'>";
            echo "<h4>❌ Error al liberar grúa</h4>";
            echo "<p><strong>Error:</strong> " . $stmt_liberar->error . "</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h4>❌ Error en el proceso</h4>";
        echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

// Verificar estado actual de grúas
echo "<h2>🚛 Estado Actual de Grúas</h2>";
$query_estado_gruas = "SELECT g.ID, g.Placa, g.Estado, 
                              COUNT(CASE WHEN s.estado = 'asignada' THEN 1 END) as solicitudes_activas,
                              COUNT(CASE WHEN s.estado = 'completada' THEN 1 END) as solicitudes_completadas,
                              COUNT(CASE WHEN s.estado = 'liberada' THEN 1 END) as solicitudes_liberadas
                       FROM gruas g
                       LEFT JOIN solicitudes s ON g.ID = s.grua_asignada_id
                       GROUP BY g.ID, g.Placa, g.Estado
                       ORDER BY g.ID";

$result_estado = $conn->query($query_estado_gruas);

echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:8px;'>ID</th>";
echo "<th style='padding:8px;'>Placa</th>";
echo "<th style='padding:8px;'>Estado</th>";
echo "<th style='padding:8px;'>Solicitudes Activas</th>";
echo "<th style='padding:8px;'>Solicitudes Completadas</th>";
echo "<th style='padding:8px;'>Solicitudes Liberadas</th>";
echo "<th style='padding:8px;'>Disponibilidad</th>";
echo "</tr>";

while ($row = $result_estado->fetch_assoc()) {
    $disponible = $row['solicitudes_activas'] == 0 ? 'Disponible' : 'Ocupada';
    $disponible_color = $row['solicitudes_activas'] == 0 ? '#28a745' : '#dc3545';
    
    echo "<tr>";
    echo "<td style='padding:8px;'>{$row['ID']}</td>";
    echo "<td style='padding:8px;'>{$row['Placa']}</td>";
    echo "<td style='padding:8px;'>{$row['Estado']}</td>";
    echo "<td style='padding:8px;'>{$row['solicitudes_activas']}</td>";
    echo "<td style='padding:8px;'>{$row['solicitudes_completadas']}</td>";
    echo "<td style='padding:8px;'>{$row['solicitudes_liberadas']}</td>";
    echo "<td style='padding:8px;'><span style='background:$disponible_color; color:white; padding:4px 8px; border-radius:4px;'>$disponible</span></td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>

<script>
function liberarGrua(gruaId, solicitudId) {
    if (confirm('¿Está seguro de que desea liberar esta grúa y asignar una nueva solicitud?')) {
        // Crear formulario para enviar la solicitud
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const inputGrua = document.createElement('input');
        inputGrua.type = 'hidden';
        inputGrua.name = 'grua_id';
        inputGrua.value = gruaId;
        
        const inputSolicitud = document.createElement('input');
        inputSolicitud.type = 'hidden';
        inputSolicitud.name = 'solicitud_id';
        inputSolicitud.value = solicitudId;
        
        const inputAccion = document.createElement('input');
        inputAccion.type = 'hidden';
        inputAccion.name = 'liberar_grua';
        inputAccion.value = '1';
        
        form.appendChild(inputGrua);
        form.appendChild(inputSolicitud);
        form.appendChild(inputAccion);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
button {
    transition: all 0.3s ease;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

table {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

th {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

tr:hover {
    background-color: #e3f2fd;
    transform: scale(1.01);
    transition: all 0.3s ease;
}
</style>
