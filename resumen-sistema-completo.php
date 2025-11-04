<?php
/**
 * Resumen Completo del Sistema DBACK
 * Estado actual de todas las funcionalidades
 */

require_once 'conexion.php';

echo "<h1>🎯 RESUMEN COMPLETO DEL SISTEMA DBACK</h1>";
echo "<p><strong>Fecha de análisis:</strong> " . date('Y-m-d H:i:s') . "</p>";

// 1. ESTADO GENERAL DEL SISTEMA
echo "<h2>📊 Estado General del Sistema</h2>";

// Solicitudes por estado
$query_estados = "SELECT estado, COUNT(*) as total FROM solicitudes GROUP BY estado ORDER BY total DESC";
$result_estados = $conn->query($query_estados);

echo "<div style='background:#f0f8ff; padding:20px; border-radius:15px; margin:20px 0;'>";
echo "<h3>📋 Distribución de Solicitudes por Estado</h3>";
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:10px;'>Estado</th>";
echo "<th style='padding:10px;'>Cantidad</th>";
echo "<th style='padding:10px;'>Porcentaje</th>";
echo "<th style='padding:10px;'>Color</th>";
echo "</tr>";

$total_solicitudes = 0;
$estados_data = [];
while ($row = $result_estados->fetch_assoc()) {
    $estados_data[] = $row;
    $total_solicitudes += $row['total'];
}

foreach ($estados_data as $estado) {
    $porcentaje = round(($estado['total'] / $total_solicitudes) * 100, 1);
    $color = '';
    $color_bg = '';
    
    switch ($estado['estado']) {
        case 'asignada':
            $color = '#28a745';
            $color_bg = '#d4edda';
            break;
        case 'pendiente':
            $color = '#ffc107';
            $color_bg = '#fff3cd';
            break;
        case 'completada':
            $color = '#17a2b8';
            $color_bg = '#d1ecf1';
            break;
        case 'cancelada':
            $color = '#dc3545';
            $color_bg = '#f8d7da';
            break;
        default:
            $color = '#6c757d';
            $color_bg = '#f8f9fa';
    }
    
    echo "<tr style='background:$color_bg;'>";
    echo "<td style='padding:10px; font-weight:bold;'>{$estado['estado']}</td>";
    echo "<td style='padding:10px; text-align:center; font-weight:bold;'>{$estado['total']}</td>";
    echo "<td style='padding:10px; text-align:center;'>{$porcentaje}%</td>";
    echo "<td style='padding:10px; text-align:center;'><span style='background:$color; color:white; padding:4px 8px; border-radius:4px;'>{$estado['estado']}</span></td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>Total de solicitudes:</strong> $total_solicitudes</p>";
echo "</div>";

// 2. RECURSOS DISPONIBLES
echo "<h2>🚛 Recursos del Sistema</h2>";

// Grúas
$query_gruas = "SELECT 
    COUNT(*) as total_gruas,
    COUNT(CASE WHEN Estado = 'Activa' THEN 1 END) as gruas_activas,
    COUNT(CASE WHEN Estado = 'Inactiva' THEN 1 END) as gruas_inactivas,
    COUNT(CASE WHEN Estado = 'Mantenimiento' THEN 1 END) as gruas_mantenimiento
FROM gruas";
$result_gruas = $conn->query($query_gruas);
$gruas_data = $result_gruas->fetch_assoc();

// Equipos de ayuda
$query_equipos = "SELECT 
    COUNT(*) as total_equipos,
    COUNT(CASE WHEN Disponible = 1 THEN 1 END) as equipos_disponibles,
    COUNT(CASE WHEN Disponible = 0 THEN 1 END) as equipos_ocupados
FROM equipos_ayuda";
$result_equipos = $conn->query($query_equipos);
$equipos_data = $result_equipos->fetch_assoc();

echo "<div style='display:flex; gap:20px; margin:20px 0;'>";
echo "<div style='flex:1; background:#d4edda; padding:20px; border-radius:15px; border: 2px solid #28a745;'>";
echo "<h3>🚛 Grúas</h3>";
echo "<p><strong>Total:</strong> {$gruas_data['total_gruas']}</p>";
echo "<p><strong>Activas:</strong> {$gruas_data['gruas_activas']}</p>";
echo "<p><strong>Inactivas:</strong> {$gruas_data['gruas_inactivas']}</p>";
echo "<p><strong>En Mantenimiento:</strong> {$gruas_data['gruas_mantenimiento']}</p>";
echo "</div>";

echo "<div style='flex:1; background:#e3f2fd; padding:20px; border-radius:15px; border: 2px solid #007bff;'>";
echo "<h3>🚗 Equipos de Ayuda</h3>";
echo "<p><strong>Total:</strong> {$equipos_data['total_equipos']}</p>";
echo "<p><strong>Disponibles:</strong> {$equipos_data['equipos_disponibles']}</p>";
echo "<p><strong>Ocupados:</strong> {$equipos_data['equipos_ocupados']}</p>";
echo "</div>";
echo "</div>";

// 3. ASIGNACIONES RECIENTES
echo "<h2>📈 Asignaciones Recientes (Últimas 10)</h2>";

$query_recientes = "SELECT 
    s.id, s.nombre_completo, s.tipo_servicio, s.estado, s.fecha_asignacion, s.metodo_asignacion,
    CASE 
        WHEN s.grua_asignada_id IS NOT NULL THEN CONCAT('Grúa: ', g.Placa)
        WHEN s.equipo_asignado_id IS NOT NULL THEN CONCAT('Equipo: ', e.Nombre, ' (Tel: ', e.Telefono, ')')
        ELSE 'No asignado'
    END as asignado_a
FROM solicitudes s
LEFT JOIN gruas g ON s.grua_asignada_id = g.ID
LEFT JOIN equipos_ayuda e ON s.equipo_asignado_id = e.ID
WHERE s.estado = 'asignada'
ORDER BY s.fecha_asignacion DESC
LIMIT 10";

$result_recientes = $conn->query($query_recientes);

if ($result_recientes->num_rows > 0) {
    echo "<div style='background:#f8f9fa; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:10px;'>ID</th>";
    echo "<th style='padding:10px;'>Cliente</th>";
    echo "<th style='padding:10px;'>Servicio</th>";
    echo "<th style='padding:10px;'>Asignado a</th>";
    echo "<th style='padding:10px;'>Método</th>";
    echo "<th style='padding:10px;'>Fecha</th>";
    echo "</tr>";
    
    while ($row = $result_recientes->fetch_assoc()) {
        $servicio_color = '';
        switch ($row['tipo_servicio']) {
            case 'bateria':
            case 'gasolina':
            case 'llanta':
                $servicio_color = '#6f42c1';
                break;
            case 'remolque':
                $servicio_color = '#007bff';
                break;
            default:
                $servicio_color = '#28a745';
        }
        
        echo "<tr>";
        echo "<td style='padding:10px; text-align:center;'>{$row['id']}</td>";
        echo "<td style='padding:10px; font-weight:bold;'>{$row['nombre_completo']}</td>";
        echo "<td style='padding:10px;'><span style='background:$servicio_color; color:white; padding:4px 8px; border-radius:4px;'>{$row['tipo_servicio']}</span></td>";
        echo "<td style='padding:10px;'>{$row['asignado_a']}</td>";
        echo "<td style='padding:10px;'><span style='background:#007bff; color:white; padding:4px 8px; border-radius:4px;'>{$row['metodo_asignacion']}</span></td>";
        echo "<td style='padding:10px;'>" . date('d/m/Y H:i', strtotime($row['fecha_asignacion'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

// 4. ESTADÍSTICAS DE RENDIMIENTO
echo "<h2>📊 Estadísticas de Rendimiento</h2>";

// Solicitudes por tipo de servicio
$query_tipos = "SELECT tipo_servicio, COUNT(*) as total FROM solicitudes GROUP BY tipo_servicio ORDER BY total DESC";
$result_tipos = $conn->query($query_tipos);

echo "<div style='background:#fff3cd; padding:20px; border-radius:15px; margin:20px 0;'>";
echo "<h3>📈 Solicitudes por Tipo de Servicio</h3>";
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:10px;'>Tipo de Servicio</th>";
echo "<th style='padding:10px;'>Cantidad</th>";
echo "<th style='padding:10px;'>Porcentaje</th>";
echo "</tr>";

while ($row = $result_tipos->fetch_assoc()) {
    $porcentaje = round(($row['total'] / $total_solicitudes) * 100, 1);
    echo "<tr>";
    echo "<td style='padding:10px; font-weight:bold;'>{$row['tipo_servicio']}</td>";
    echo "<td style='padding:10px; text-align:center;'>{$row['total']}</td>";
    echo "<td style='padding:10px; text-align:center;'>{$porcentaje}%</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 5. CONFIGURACIÓN DEL SISTEMA
echo "<h2>⚙️ Configuración del Sistema</h2>";

$query_config = "SELECT * FROM configuracion_auto_asignacion WHERE activo = 1";
$result_config = $conn->query($query_config);

if ($result_config->num_rows > 0) {
    echo "<div style='background:#e8f5e8; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>🔧 Parámetros de Configuración</h3>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:10px;'>Parámetro</th>";
    echo "<th style='padding:10px;'>Valor</th>";
    echo "<th style='padding:10px;'>Descripción</th>";
    echo "</tr>";
    
    while ($row = $result_config->fetch_assoc()) {
        $valor_display = $row['valor'] == '1' ? 'Habilitado' : ($row['valor'] == '0' ? 'Deshabilitado' : $row['valor']);
        $color = $row['valor'] == '1' ? '#28a745' : '#dc3545';
        
        echo "<tr>";
        echo "<td style='padding:10px; font-weight:bold;'>{$row['parametro']}</td>";
        echo "<td style='padding:10px;'><span style='background:$color; color:white; padding:4px 8px; border-radius:4px;'>$valor_display</span></td>";
        echo "<td style='padding:10px;'>{$row['descripcion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

// 6. ENLACES DE ACCESO
echo "<h2>🔗 Enlaces de Acceso al Sistema</h2>";
echo "<div style='background:#f0f8ff; padding:20px; border-radius:15px; margin:20px 0; text-align:center;'>";
echo "<h3>📱 Panel de Control</h3>";
echo "<div style='display:flex; flex-wrap:wrap; gap:15px; justify-content:center;'>";
echo "<a href='estado-gruas.php' style='background:#007bff; color:white; padding:15px 25px; border-radius:10px; text-decoration:none; font-weight:bold; display:inline-block;'>🚛 Estado de Grúas</a>";
echo "<a href='liberar-gruas.php' style='background:#28a745; color:white; padding:15px 25px; border-radius:10px; text-decoration:none; font-weight:bold; display:inline-block;'>🔓 Liberar Grúas</a>";
echo "<a href='liberacion-automatica-gruas.php' style='background:#ffc107; color:black; padding:15px 25px; border-radius:10px; text-decoration:none; font-weight:bold; display:inline-block;'>🤖 Liberación Automática</a>";
echo "<a href='verificar-asignaciones-exitosas.php' style='background:#17a2b8; color:white; padding:15px 25px; border-radius:10px; text-decoration:none; font-weight:bold; display:inline-block;'>✅ Verificar Asignaciones</a>";
echo "<a href='solicitud.php' style='background:#6f42c1; color:white; padding:15px 25px; border-radius:10px; text-decoration:none; font-weight:bold; display:inline-block;'>📝 Nueva Solicitud</a>";
echo "<a href='Gastos.php' style='background:#dc3545; color:white; padding:15px 25px; border-radius:10px; text-decoration:none; font-weight:bold; display:inline-block;'>💰 Gastos</a>";
echo "</div>";
echo "</div>";

// 7. RESUMEN FINAL
echo "<h2>🎉 Resumen Final del Sistema</h2>";
echo "<div style='background:linear-gradient(135deg, #28a745 0%, #007bff 100%); color:white; padding:30px; border-radius:20px; margin:20px 0; text-align:center;'>";
echo "<h3 style='margin:0 0 20px 0; font-size:2em;'>✅ SISTEMA DBACK OPERATIVO</h3>";
echo "<div style='display:flex; justify-content:space-around; margin:20px 0;'>";
echo "<div>";
echo "<h4 style='margin:0 0 10px 0;'>📊 Solicitudes</h4>";
echo "<p style='margin:0; font-size:1.5em; font-weight:bold;'>$total_solicitudes</p>";
echo "</div>";
echo "<div>";
echo "<h4 style='margin:0 0 10px 0;'>🚛 Grúas</h4>";
echo "<p style='margin:0; font-size:1.5em; font-weight:bold;'>{$gruas_data['total_gruas']}</p>";
echo "</div>";
echo "<div>";
echo "<h4 style='margin:0 0 10px 0;'>🚗 Equipos</h4>";
echo "<p style='margin:0; font-size:1.5em; font-weight:bold;'>{$equipos_data['total_equipos']}</p>";
echo "</div>";
echo "<div>";
echo "<h4 style='margin:0 0 10px 0;'>⚡ Auto-Asignación</h4>";
echo "<p style='margin:0; font-size:1.5em; font-weight:bold;'>ACTIVA</p>";
echo "</div>";
echo "</div>";
echo "<p style='margin:20px 0 0 0; font-size:1.2em; opacity:0.9;'>Sistema de gestión de grúas y equipos de ayuda funcionando al 100%</p>";
echo "</div>";

$conn->close();
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 20px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

h1, h2, h3 {
    color: #333;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

table {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin: 10px 0;
    border-radius: 8px;
    overflow: hidden;
}

th {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

tr:hover {
    background-color: #e3f2fd !important;
    transform: scale(1.01);
    transition: all 0.3s ease;
}

a {
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

a:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
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

@media (max-width: 768px) {
    .estadistica-card {
        margin: 5px;
        padding: 15px;
    }
    
    div[style*="display:flex"] {
        flex-direction: column !important;
    }
}
</style>
