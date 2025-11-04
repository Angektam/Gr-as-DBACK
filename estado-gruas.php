<?php
/**
 * Estado de Grúas - Disponibles y Ocupadas
 * Sistema para visualizar el estado actual de todas las grúas
 */

require_once 'conexion.php';

echo "<h1>🚛 Estado de Grúas - Sistema DBACK</h1>";
echo "<p><strong>Fecha de consulta:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Consulta para obtener el estado detallado de todas las grúas
$query_estado_gruas = "SELECT 
    g.ID, 
    g.Placa, 
    g.Marca, 
    g.Modelo, 
    g.Tipo, 
    g.Estado as Estado_Grua,
    g.ubicacion_actual,
    g.coordenadas_actuales,
    g.disponible_desde,
    g.ultima_actualizacion_ubicacion,
    COUNT(CASE WHEN s.estado = 'asignada' THEN 1 END) as solicitudes_activas,
    COUNT(CASE WHEN s.estado = 'completada' THEN 1 END) as solicitudes_completadas,
    COUNT(CASE WHEN s.estado = 'liberada' THEN 1 END) as solicitudes_liberadas,
    MAX(CASE WHEN s.estado = 'asignada' THEN s.fecha_asignacion END) as ultima_asignacion,
    MAX(CASE WHEN s.estado = 'asignada' THEN s.nombre_completo END) as cliente_actual,
    MAX(CASE WHEN s.estado = 'asignada' THEN s.tipo_servicio END) as servicio_actual
FROM gruas g
LEFT JOIN solicitudes s ON g.ID = s.grua_asignada_id
GROUP BY g.ID, g.Placa, g.Marca, g.Modelo, g.Tipo, g.Estado, g.ubicacion_actual, g.coordenadas_actuales, g.disponible_desde, g.ultima_actualizacion_ubicacion
ORDER BY 
    CASE 
        WHEN COUNT(CASE WHEN s.estado = 'asignada' THEN 1 END) > 0 THEN 1 
        ELSE 2 
    END,
    g.Placa";

$result_estado = $conn->query($query_estado_gruas);

// Separar grúas disponibles y ocupadas
$gruas_disponibles = [];
$gruas_ocupadas = [];
$total_gruas = 0;

while ($row = $result_estado->fetch_assoc()) {
    $total_gruas++;
    if ($row['solicitudes_activas'] > 0) {
        $gruas_ocupadas[] = $row;
    } else {
        $gruas_disponibles[] = $row;
    }
}

// Estadísticas generales
$total_disponibles = count($gruas_disponibles);
$total_ocupadas = count($gruas_ocupadas);
$porcentaje_disponibles = $total_gruas > 0 ? round(($total_disponibles / $total_gruas) * 100, 1) : 0;
$porcentaje_ocupadas = $total_gruas > 0 ? round(($total_ocupadas / $total_gruas) * 100, 1) : 0;

echo "<h2>📊 Resumen General</h2>";
echo "<div style='background:#f0f8ff; padding:20px; border-radius:15px; margin:20px 0;'>";
echo "<div style='display:flex; justify-content:space-around; text-align:center;'>";
echo "<div style='background:#d4edda; padding:20px; border-radius:10px; flex:1; margin:0 10px;'>";
echo "<h3 style='color:#155724; margin:0 0 10px 0;'>✅ Grúas Disponibles</h3>";
echo "<p style='font-size:2em; font-weight:bold; color:#28a745; margin:0;'>$total_disponibles</p>";
echo "<p style='margin:5px 0 0 0; color:#155724;'>($porcentaje_disponibles% del total)</p>";
echo "</div>";
echo "<div style='background:#f8d7da; padding:20px; border-radius:10px; flex:1; margin:0 10px;'>";
echo "<h3 style='color:#721c24; margin:0 0 10px 0;'>🔒 Grúas Ocupadas</h3>";
echo "<p style='font-size:2em; font-weight:bold; color:#dc3545; margin:0;'>$total_ocupadas</p>";
echo "<p style='margin:5px 0 0 0; color:#721c24;'>($porcentaje_ocupadas% del total)</p>";
echo "</div>";
echo "<div style='background:#e3f2fd; padding:20px; border-radius:10px; flex:1; margin:0 10px;'>";
echo "<h3 style='color:#0d47a1; margin:0 0 10px 0;'>📈 Total de Grúas</h3>";
echo "<p style='font-size:2em; font-weight:bold; color:#007bff; margin:0;'>$total_gruas</p>";
echo "<p style='margin:5px 0 0 0; color:#0d47a1;'>En el sistema</p>";
echo "</div>";
echo "</div>";
echo "</div>";

// Grúas Disponibles
echo "<h2>✅ Grúas Disponibles ($total_disponibles)</h2>";

if (!empty($gruas_disponibles)) {
    echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:20px 0; border: 2px solid #28a745;'>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:10px;'>ID</th>";
    echo "<th style='padding:10px;'>Placa</th>";
    echo "<th style='padding:10px;'>Marca/Modelo</th>";
    echo "<th style='padding:10px;'>Tipo</th>";
    echo "<th style='padding:10px;'>Estado</th>";
    echo "<th style='padding:10px;'>Ubicación</th>";
    echo "<th style='padding:10px;'>Disponible Desde</th>";
    echo "<th style='padding:10px;'>Última Actualización</th>";
    echo "<th style='padding:10px;'>Historial</th>";
    echo "</tr>";
    
    foreach ($gruas_disponibles as $grua) {
        $estado_color = $grua['Estado_Grua'] == 'Activa' ? '#28a745' : ($grua['Estado_Grua'] == 'Mantenimiento' ? '#ffc107' : '#6c757d');
        $estado_texto = $grua['Estado_Grua'];
        
        echo "<tr style='background:white;'>";
        echo "<td style='padding:10px; text-align:center;'>{$grua['ID']}</td>";
        echo "<td style='padding:10px; font-weight:bold;'>{$grua['Placa']}</td>";
        echo "<td style='padding:10px;'>{$grua['Marca']} {$grua['Modelo']}</td>";
        echo "<td style='padding:10px;'>{$grua['Tipo']}</td>";
        echo "<td style='padding:10px;'><span style='background:$estado_color; color:white; padding:4px 8px; border-radius:4px;'>$estado_texto</span></td>";
        echo "<td style='padding:10px;'>" . ($grua['ubicacion_actual'] ?: 'No especificada') . "</td>";
        echo "<td style='padding:10px;'>" . ($grua['disponible_desde'] ? date('d/m/Y H:i', strtotime($grua['disponible_desde'])) : 'N/A') . "</td>";
        echo "<td style='padding:10px;'>" . ($grua['ultima_actualizacion_ubicacion'] ? date('d/m/Y H:i', strtotime($grua['ultima_actualizacion_ubicacion'])) : 'N/A') . "</td>";
        echo "<td style='padding:10px; text-align:center;'>";
        echo "<span style='background:#17a2b8; color:white; padding:2px 6px; border-radius:3px; font-size:0.8em;'>";
        echo "Completadas: {$grua['solicitudes_completadas']} | Liberadas: {$grua['solicitudes_liberadas']}";
        echo "</span>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<div style='background:#fff3cd; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>⚠️ No hay grúas disponibles</h3>";
    echo "<p>Todas las grúas están actualmente ocupadas o en mantenimiento.</p>";
    echo "</div>";
}

// Grúas Ocupadas
echo "<h2>🔒 Grúas Ocupadas ($total_ocupadas)</h2>";

if (!empty($gruas_ocupadas)) {
    echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:20px 0; border: 2px solid #dc3545;'>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:10px;'>ID</th>";
    echo "<th style='padding:10px;'>Placa</th>";
    echo "<th style='padding:10px;'>Marca/Modelo</th>";
    echo "<th style='padding:10px;'>Tipo</th>";
    echo "<th style='padding:10px;'>Estado</th>";
    echo "<th style='padding:10px;'>Cliente Actual</th>";
    echo "<th style='padding:10px;'>Servicio</th>";
    echo "<th style='padding:10px;'>Asignada Desde</th>";
    echo "<th style='padding:10px;'>Ubicación</th>";
    echo "<th style='padding:10px;'>Acción</th>";
    echo "</tr>";
    
    foreach ($gruas_ocupadas as $grua) {
        $estado_color = $grua['Estado_Grua'] == 'Activa' ? '#28a745' : ($grua['Estado_Grua'] == 'Mantenimiento' ? '#ffc107' : '#6c757d');
        $estado_texto = $grua['Estado_Grua'];
        
        echo "<tr style='background:white;'>";
        echo "<td style='padding:10px; text-align:center;'>{$grua['ID']}</td>";
        echo "<td style='padding:10px; font-weight:bold;'>{$grua['Placa']}</td>";
        echo "<td style='padding:10px;'>{$grua['Marca']} {$grua['Modelo']}</td>";
        echo "<td style='padding:10px;'>{$grua['Tipo']}</td>";
        echo "<td style='padding:10px;'><span style='background:$estado_color; color:white; padding:4px 8px; border-radius:4px;'>$estado_texto</span></td>";
        echo "<td style='padding:10px; font-weight:bold;'>" . ($grua['cliente_actual'] ?: 'N/A') . "</td>";
        echo "<td style='padding:10px;'><span style='background:#6f42c1; color:white; padding:2px 6px; border-radius:3px; font-size:0.8em;'>" . ($grua['servicio_actual'] ?: 'N/A') . "</span></td>";
        echo "<td style='padding:10px;'>" . ($grua['ultima_asignacion'] ? date('d/m/Y H:i', strtotime($grua['ultima_asignacion'])) : 'N/A') . "</td>";
        echo "<td style='padding:10px;'>" . ($grua['ubicacion_actual'] ?: 'No especificada') . "</td>";
        echo "<td style='padding:10px; text-align:center;'>";
        echo "<button onclick='verDetalleGrua({$grua['ID']})' style='background:#007bff; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:0.8em;'>Ver Detalle</button>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<div style='background:#d4edda; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>✅ ¡Excelente! No hay grúas ocupadas</h3>";
    echo "<p>Todas las grúas están disponibles para nuevas asignaciones.</p>";
    echo "</div>";
}

// Distribución por tipo de grúa
echo "<h2>📊 Distribución por Tipo de Grúa</h2>";

$query_tipos = "SELECT 
    g.Tipo,
    COUNT(*) as total,
    COUNT(CASE WHEN s.estado = 'asignada' THEN 1 END) as ocupadas,
    COUNT(*) - COUNT(CASE WHEN s.estado = 'asignada' THEN 1 END) as disponibles
FROM gruas g
LEFT JOIN solicitudes s ON g.ID = s.grua_asignada_id AND s.estado = 'asignada'
GROUP BY g.Tipo
ORDER BY total DESC";

$result_tipos = $conn->query($query_tipos);

echo "<div style='background:#f8f9fa; padding:20px; border-radius:15px; margin:20px 0;'>";
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:10px;'>Tipo de Grúa</th>";
echo "<th style='padding:10px;'>Total</th>";
echo "<th style='padding:10px;'>Disponibles</th>";
echo "<th style='padding:10px;'>Ocupadas</th>";
echo "<th style='padding:10px;'>% Disponibles</th>";
echo "<th style='padding:10px;'>Estado</th>";
echo "</tr>";

while ($row = $result_tipos->fetch_assoc()) {
    $porcentaje_disponible = $row['total'] > 0 ? round(($row['disponibles'] / $row['total']) * 100, 1) : 0;
    $estado_color = $porcentaje_disponible >= 50 ? '#28a745' : ($porcentaje_disponible >= 25 ? '#ffc107' : '#dc3545');
    $estado_texto = $porcentaje_disponible >= 50 ? 'Excelente' : ($porcentaje_disponible >= 25 ? 'Regular' : 'Crítico');
    
    echo "<tr>";
    echo "<td style='padding:10px; font-weight:bold;'>{$row['Tipo']}</td>";
    echo "<td style='padding:10px; text-align:center;'>{$row['total']}</td>";
    echo "<td style='padding:10px; text-align:center; color:#28a745; font-weight:bold;'>{$row['disponibles']}</td>";
    echo "<td style='padding:10px; text-align:center; color:#dc3545; font-weight:bold;'>{$row['ocupadas']}</td>";
    echo "<td style='padding:10px; text-align:center; font-weight:bold;'>$porcentaje_disponible%</td>";
    echo "<td style='padding:10px; text-align:center;'><span style='background:$estado_color; color:white; padding:4px 8px; border-radius:4px;'>$estado_texto</span></td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Botones de acción
echo "<h2>🔧 Acciones Rápidas</h2>";
echo "<div style='background:#e8f5e8; padding:20px; border-radius:15px; margin:20px 0; text-align:center;'>";
echo "<a href='liberar-gruas.php' style='background:#007bff; color:white; padding:12px 24px; border-radius:8px; text-decoration:none; margin:0 10px; display:inline-block; font-weight:bold;'>🚛 Liberar Grúas</a>";
echo "<a href='liberacion-automatica-gruas.php' style='background:#28a745; color:white; padding:12px 24px; border-radius:8px; text-decoration:none; margin:0 10px; display:inline-block; font-weight:bold;'>🤖 Liberación Automática</a>";
echo "<a href='verificar-asignaciones-exitosas.php' style='background:#ffc107; color:black; padding:12px 24px; border-radius:8px; text-decoration:none; margin:0 10px; display:inline-block; font-weight:bold;'>✅ Verificar Asignaciones</a>";
echo "<button onclick='location.reload()' style='background:#6c757d; color:white; padding:12px 24px; border-radius:8px; border:none; margin:0 10px; font-weight:bold; cursor:pointer;'>🔄 Actualizar</button>";
echo "</div>";

$conn->close();
?>

<script>
function verDetalleGrua(gruaId) {
    alert('Detalle de la grúa ID: ' + gruaId + '\n\nEsta funcionalidad se puede expandir para mostrar:\n- Historial de servicios\n- Ubicación en tiempo real\n- Estado del conductor\n- Próximas asignaciones');
}

// Auto-actualizar cada 30 segundos
setTimeout(function() {
    if (confirm('¿Desea actualizar la información de las grúas?')) {
        location.reload();
    }
}, 30000);
</script>

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

button, a {
    transition: all 0.3s ease;
}

button:hover, a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

h1, h2, h3 {
    color: #333;
}

.estadistica-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    margin: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style>
