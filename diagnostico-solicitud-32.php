<?php
/**
 * Diagnóstico de Solicitud ID 32 - DIANA CECILIA CAMACHO FLORES
 * Investigar por qué no se auto-asignó
 */

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

echo "<h1>🔍 Diagnóstico de Solicitud ID 32</h1>";
echo "<p><strong>Cliente:</strong> DIANA CECILIA CAMACHO FLORES</p>";
echo "<p><strong>Servicio:</strong> Cambio de batería</p>";
echo "<p><strong>Fecha de diagnóstico:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. Obtener detalles completos de la solicitud
$query_solicitud = "SELECT * FROM solicitudes WHERE id = 32";
$result_solicitud = $conn->query($query_solicitud);

if ($result_solicitud->num_rows > 0) {
    $solicitud = $result_solicitud->fetch_assoc();
    
    echo "<h2>📋 Detalles de la Solicitud</h2>";
    echo "<div style='background:#f8f9fa; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr><th style='padding:10px; background:#007bff; color:white;'>Campo</th><th style='padding:10px; background:#007bff; color:white;'>Valor</th></tr>";
    
    foreach ($solicitud as $campo => $valor) {
        $color = '';
        if ($campo == 'estado' && $valor == 'pendiente') $color = 'background:#fff3cd;';
        if ($campo == 'tipo_servicio' && $valor == 'bateria') $color = 'background:#d4edda;';
        if ($campo == 'grua_asignada_id' && $valor == null) $color = 'background:#f8d7da;';
        
        echo "<tr style='$color'>";
        echo "<td style='padding:10px; font-weight:bold;'>$campo</td>";
        echo "<td style='padding:10px;'>" . ($valor ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 2. Verificar si es un servicio que requiere equipo de ayuda
    echo "<h2>🔧 Análisis de Tipo de Servicio</h2>";
    $tipo_servicio = $solicitud['tipo_servicio'];
    $requiere_equipo_ayuda = in_array($tipo_servicio, ['gasolina', 'bateria', 'llanta']);
    
    echo "<div style='background:#e3f2fd; padding:15px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>Información del Servicio</h3>";
    echo "<p><strong>Tipo de servicio:</strong> $tipo_servicio</p>";
    echo "<p><strong>Requiere equipo de ayuda:</strong> " . ($requiere_equipo_ayuda ? 'SÍ' : 'NO') . "</p>";
    echo "<p><strong>Razón:</strong> " . ($requiere_equipo_ayuda ? 'Los servicios de gasolina, batería y llanta requieren equipos de ayuda especializados' : 'Este servicio requiere grúa para remolque') . "</p>";
    echo "</div>";
    
    // 3. Verificar equipos de ayuda disponibles
    if ($requiere_equipo_ayuda) {
        echo "<h2>🚗 Equipos de Ayuda Disponibles</h2>";
        $query_equipos = "SELECT * FROM equipos_ayuda WHERE Tipo_Servicio = ? AND Disponible = 1";
        $stmt_equipos = $conn->prepare($query_equipos);
        $stmt_equipos->bind_param("s", $tipo_servicio);
        $stmt_equipos->execute();
        $result_equipos = $stmt_equipos->get_result();
        
        if ($result_equipos->num_rows > 0) {
            echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:20px 0;'>";
            echo "<h3>✅ Equipos Disponibles para $tipo_servicio</h3>";
            echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
            echo "<tr style='background:#f0f0f0;'>";
            echo "<th style='padding:10px;'>ID</th>";
            echo "<th style='padding:10px;'>Nombre</th>";
            echo "<th style='padding:10px;'>Tipo Servicio</th>";
            echo "<th style='padding:10px;'>Ubicación</th>";
            echo "<th style='padding:10px;'>Coordenadas</th>";
            echo "<th style='padding:10px;'>Teléfono</th>";
            echo "</tr>";
            
            while ($equipo = $result_equipos->fetch_assoc()) {
                echo "<tr>";
                echo "<td style='padding:10px;'>{$equipo['ID']}</td>";
                echo "<td style='padding:10px; font-weight:bold;'>{$equipo['Nombre']}</td>";
                echo "<td style='padding:10px;'>{$equipo['Tipo_Servicio']}</td>";
                echo "<td style='padding:10px;'>{$equipo['Ubicacion']}</td>";
                echo "<td style='padding:10px;'>{$equipo['Coordenadas']}</td>";
                echo "<td style='padding:10px;'>{$equipo['Telefono']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        } else {
            echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:20px 0;'>";
            echo "<h3>❌ No hay equipos de ayuda disponibles</h3>";
            echo "<p>No se encontraron equipos de ayuda disponibles para el servicio: $tipo_servicio</p>";
            echo "</div>";
        }
        $stmt_equipos->close();
    }
    
    // 4. Verificar grúas disponibles
    echo "<h2>🚛 Grúas Disponibles</h2>";
    $query_gruas = "SELECT ID, Placa, Marca, Modelo, Tipo, Estado, ubicacion_actual, coordenadas_actuales 
                    FROM gruas 
                    WHERE Estado = 'Activa' 
                    AND ID NOT IN (SELECT grua_asignada_id FROM solicitudes WHERE estado = 'asignada' AND grua_asignada_id IS NOT NULL)
                    ORDER BY ID";
    $result_gruas = $conn->query($query_gruas);
    
    if ($result_gruas->num_rows > 0) {
        echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:20px 0;'>";
        echo "<h3>✅ Grúas Disponibles</h3>";
        echo "<p><strong>Total:</strong> {$result_gruas->num_rows} grúas disponibles</p>";
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr style='background:#f0f0f0;'>";
        echo "<th style='padding:10px;'>ID</th>";
        echo "<th style='padding:10px;'>Placa</th>";
        echo "<th style='padding:10px;'>Marca/Modelo</th>";
        echo "<th style='padding:10px;'>Tipo</th>";
        echo "<th style='padding:10px;'>Estado</th>";
        echo "<th style='padding:10px;'>Ubicación</th>";
        echo "</tr>";
        
        while ($grua = $result_gruas->fetch_assoc()) {
            echo "<tr>";
            echo "<td style='padding:10px;'>{$grua['ID']}</td>";
            echo "<td style='padding:10px; font-weight:bold;'>{$grua['Placa']}</td>";
            echo "<td style='padding:10px;'>{$grua['Marca']} {$grua['Modelo']}</td>";
            echo "<td style='padding:10px;'>{$grua['Tipo']}</td>";
            echo "<td style='padding:10px;'><span style='background:#28a745; color:white; padding:4px 8px; border-radius:4px;'>{$grua['Estado']}</span></td>";
            echo "<td style='padding:10px;'>{$grua['ubicacion_actual']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:20px 0;'>";
        echo "<h3>❌ No hay grúas disponibles</h3>";
        echo "<p>Todas las grúas están ocupadas o inactivas.</p>";
        echo "</div>";
    }
    
    // 5. Intentar auto-asignación manual
    echo "<h2>🔄 Intentando Auto-Asignación Manual</h2>";
    echo "<div style='background:#fff3cd; padding:15px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>Ejecutando proceso de auto-asignación...</h3>";
    echo "</div>";
    
    $autoAsignador = new AutoAsignacionGruas($conn);
    $resultado = $autoAsignador->asignarGrua(32);
    
    if ($resultado['success']) {
        echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:20px 0;'>";
        echo "<h3>✅ Auto-asignación exitosa</h3>";
        echo "<p><strong>Mensaje:</strong> " . $resultado['message'] . "</p>";
        if (isset($resultado['grua_asignada'])) {
            echo "<p><strong>Grúa asignada:</strong> " . $resultado['grua_asignada'] . " (ID: " . $resultado['grua_id'] . ")</p>";
            echo "<p><strong>Distancia:</strong> " . round($resultado['distancia'], 2) . " km</p>";
        } elseif (isset($resultado['equipo_asignado'])) {
            echo "<p><strong>Equipo asignado:</strong> " . $resultado['equipo_asignado'] . " (ID: " . $resultado['equipo_id'] . ")</p>";
            echo "<p><strong>Distancia:</strong> " . round($resultado['distancia'], 2) . " km</p>";
        }
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:20px 0;'>";
        echo "<h3>❌ Error en auto-asignación</h3>";
        echo "<p><strong>Mensaje de error:</strong> " . $resultado['message'] . "</p>";
        echo "</div>";
    }
    
    // 6. Verificar estado actual después del intento
    echo "<h2>📊 Estado Actual Después del Intento</h2>";
    $query_actual = "SELECT id, estado, grua_asignada_id, equipo_asignado_id, fecha_asignacion, metodo_asignacion FROM solicitudes WHERE id = 32";
    $result_actual = $conn->query($query_actual);
    $solicitud_actual = $result_actual->fetch_assoc();
    
    echo "<div style='background:#e8f5e8; padding:15px; border-radius:10px; margin:20px 0;'>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr><th style='padding:10px; background:#007bff; color:white;'>Campo</th><th style='padding:10px; background:#007bff; color:white;'>Valor</th></tr>";
    echo "<tr><td style='padding:10px; font-weight:bold;'>ID</td><td style='padding:10px;'>{$solicitud_actual['id']}</td></tr>";
    echo "<tr><td style='padding:10px; font-weight:bold;'>Estado</td><td style='padding:10px;'><span style='background:" . ($solicitud_actual['estado'] == 'asignada' ? '#28a745' : '#ffc107') . "; color:white; padding:4px 8px; border-radius:4px;'>{$solicitud_actual['estado']}</span></td></tr>";
    echo "<tr><td style='padding:10px; font-weight:bold;'>Grúa Asignada ID</td><td style='padding:10px;'>" . ($solicitud_actual['grua_asignada_id'] ?: 'NULL') . "</td></tr>";
    echo "<tr><td style='padding:10px; font-weight:bold;'>Equipo Asignado ID</td><td style='padding:10px;'>" . ($solicitud_actual['equipo_asignado_id'] ?: 'NULL') . "</td></tr>";
    echo "<tr><td style='padding:10px; font-weight:bold;'>Fecha Asignación</td><td style='padding:10px;'>" . ($solicitud_actual['fecha_asignacion'] ?: 'NULL') . "</td></tr>";
    echo "<tr><td style='padding:10px; font-weight:bold;'>Método Asignación</td><td style='padding:10px;'>" . ($solicitud_actual['metodo_asignacion'] ?: 'NULL') . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
} else {
    echo "<div style='background:#f8d7da; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>❌ Solicitud no encontrada</h3>";
    echo "<p>No se encontró la solicitud con ID 32.</p>";
    echo "</div>";
}

// 7. Verificar configuración del sistema de auto-asignación
echo "<h2>⚙️ Configuración del Sistema</h2>";
$query_config = "SELECT * FROM configuracion_auto_asignacion WHERE id = 1";
$result_config = $conn->query($query_config);

if ($result_config->num_rows > 0) {
    $config = $result_config->fetch_assoc();
    echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>Configuración Actual</h3>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr><th style='padding:10px; background:#007bff; color:white;'>Parámetro</th><th style='padding:10px; background:#007bff; color:white;'>Valor</th></tr>";
    foreach ($config as $param => $valor) {
        echo "<tr><td style='padding:10px; font-weight:bold;'>$param</td><td style='padding:10px;'>" . ($valor ?: 'NULL') . "</td></tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<div style='background:#fff3cd; padding:15px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>⚠️ Configuración no encontrada</h3>";
    echo "<p>No se encontró la configuración del sistema de auto-asignación.</p>";
    echo "</div>";
}

$conn->close();
?>

<style>
table {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 10px 0;
}

th {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

tr:hover {
    background-color: #e3f2fd !important;
    transform: scale(1.01);
    transition: all 0.3s ease;
}

h1, h2, h3 {
    color: #333;
}
</style>
