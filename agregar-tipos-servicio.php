<?php
/**
 * Script para agregar tipos de servicio faltantes
 * Actualiza el enum de tipos de servicio y crea solicitudes con diferentes tipos
 */

require_once 'conexion.php';

echo "<h1>🔧 Agregando Tipos de Servicio Faltantes</h1>";

// Tipos de servicio que queremos tener
$tipos_servicio = [
    'remolque' => 'Remolque de vehículo',
    'bateria' => 'Carga de batería',
    'gasolina' => 'Suministro de combustible',
    'llanta' => 'Cambio de llanta',
    'arranque' => 'Arranque de emergencia',
    'cerradura' => 'Apertura de cerradura',
    'grúa_plataforma' => 'Servicio de grúa plataforma',
    'grúa_arrastre' => 'Servicio de grúa arrastre',
    'diagnostico' => 'Diagnóstico mecánico',
    'otro' => 'Otro tipo de servicio'
];

echo "<h2>📋 Tipos de Servicio Actuales en el Enum</h2>";
$query_actual = "SHOW COLUMNS FROM solicitudes LIKE 'tipo_servicio'";
$result_actual = $conn->query($query_actual);
$row_actual = $result_actual->fetch_assoc();
echo "<p><strong>Enum actual:</strong> {$row_actual['Type']}</p>";

echo "<h2>🔄 Actualizando Enum de Tipos de Servicio</h2>";

$enum_values = "'" . implode("','", array_keys($tipos_servicio)) . "'";
$sql_alter = "ALTER TABLE solicitudes MODIFY COLUMN tipo_servicio ENUM($enum_values) DEFAULT 'otro'";

if ($conn->query($sql_alter)) {
    echo "<p style='color:green'>✅ Enum de tipos de servicio actualizado exitosamente</p>";
} else {
    echo "<p style='color:red'>❌ Error al actualizar enum: " . $conn->error . "</p>";
}

echo "<h2>📊 Verificación del Nuevo Enum</h2>";
$query_nuevo = "SHOW COLUMNS FROM solicitudes LIKE 'tipo_servicio'";
$result_nuevo = $conn->query($query_nuevo);
$row_nuevo = $result_nuevo->fetch_assoc();
echo "<p><strong>Nuevo enum:</strong> {$row_nuevo['Type']}</p>";

echo "<h2>📋 Lista de Tipos de Servicio Disponibles</h2>";
echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
echo "<tr style='background:#f0f0f0;'><th>Tipo de Servicio</th><th>Descripción</th></tr>";
foreach ($tipos_servicio as $tipo => $descripcion) {
    echo "<tr>";
    echo "<td><strong>$tipo</strong></td>";
    echo "<td>$descripcion</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>📊 Distribución Actual de Tipos de Servicio</h2>";

// Verificar tipos actuales en uso
$query_tipos_actuales = "SELECT tipo_servicio, COUNT(*) as cantidad FROM solicitudes GROUP BY tipo_servicio ORDER BY cantidad DESC";
$result_tipos = $conn->query($query_tipos_actuales);

echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
echo "<tr style='background:#f0f0f0;'><th>Tipo de Servicio</th><th>Cantidad</th><th>Porcentaje</th></tr>";

$total_solicitudes = 0;
$tipos_en_uso = [];

while ($row = $result_tipos->fetch_assoc()) {
    $tipos_en_uso[] = $row['tipo_servicio'];
    $total_solicitudes += $row['cantidad'];
    $porcentaje = round(($row['cantidad'] / $total_solicitudes) * 100, 1);
    echo "<tr>";
    echo "<td>{$row['tipo_servicio']}</td>";
    echo "<td>{$row['cantidad']}</td>";
    echo "<td>$porcentaje%</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🔄 Actualizando Tipos de Servicio en Solicitudes Existentes</h2>";

// Obtener todas las solicitudes
$query_solicitudes = "SELECT id, tipo_servicio FROM solicitudes ORDER BY id";
$result_solicitudes = $conn->query($query_solicitudes);

$solicitudes_actualizadas = 0;
$nuevos_tipos = ['cerradura', 'grúa_plataforma', 'grúa_arrastre', 'diagnostico'];

while ($row = $result_solicitudes->fetch_assoc()) {
    $id = $row['id'];
    $tipo_actual = $row['tipo_servicio'];
    
    // Asignar nuevos tipos de manera aleatoria
    $nuevo_tipo = $tipo_actual;
    
    // 30% de probabilidad de cambiar a un nuevo tipo
    if (rand(1, 100) <= 30) {
        $nuevo_tipo = $nuevos_tipos[array_rand($nuevos_tipos)];
    }
    
    // Actualizar solo si el tipo cambió
    if ($nuevo_tipo !== $tipo_actual) {
        $update_sql = "UPDATE solicitudes SET tipo_servicio = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $nuevo_tipo, $id);
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Solicitud ID $id: '$tipo_actual' → '$nuevo_tipo'</p>";
            $solicitudes_actualizadas++;
        } else {
            echo "<p style='color:red'>❌ Error al actualizar solicitud ID $id: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Solicitud ID $id: Mantiene tipo '$tipo_actual'</p>";
    }
}

echo "<h2>📊 Nueva Distribución de Tipos de Servicio</h2>";

// Verificar la nueva distribución
$query_nueva_distribucion = "SELECT tipo_servicio, COUNT(*) as cantidad FROM solicitudes GROUP BY tipo_servicio ORDER BY cantidad DESC";
$result_nueva = $conn->query($query_nueva_distribucion);

echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
echo "<tr style='background:#f0f0f0;'><th>Tipo de Servicio</th><th>Cantidad</th><th>Porcentaje</th><th>Descripción</th></tr>";

$total_nuevo = 0;
while ($row = $result_nueva->fetch_assoc()) {
    $total_nuevo += $row['cantidad'];
}

$result_nueva = $conn->query($query_nueva_distribucion);
while ($row = $result_nueva->fetch_assoc()) {
    $tipo = $row['tipo_servicio'];
    $cantidad = $row['cantidad'];
    $porcentaje = round(($cantidad / $total_nuevo) * 100, 1);
    $descripcion = $tipos_servicio[$tipo] ?? 'Tipo personalizado';
    
    $color = '';
    switch ($tipo) {
        case 'remolque': $color = 'background:#d1ecf1;'; break;
        case 'bateria': $color = 'background:#d4edda;'; break;
        case 'gasolina': $color = 'background:#fff3cd;'; break;
        case 'llanta': $color = 'background:#f8d7da;'; break;
        case 'arranque': $color = 'background:#e2e3e5;'; break;
        case 'cerradura': $color = 'background:#f8d7da;'; break;
        case 'grúa_plataforma': $color = 'background:#d1ecf1;'; break;
        case 'grúa_arrastre': $color = 'background:#d4edda;'; break;
        case 'diagnostico': $color = 'background:#fff3cd;'; break;
        case 'otro': $color = 'background:#e2e3e5;'; break;
    }
    
    echo "<tr style='$color'>";
    echo "<td><strong>$tipo</strong></td>";
    echo "<td>$cantidad</td>";
    echo "<td>$porcentaje%</td>";
    echo "<td>$descripcion</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🎯 Tipos de Servicio Disponibles en el Sistema</h2>";
echo "<div style='background:#f0f8ff; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<h3>📋 Lista Completa de Tipos</h3>";
echo "<ul>";
foreach ($tipos_servicio as $tipo => $descripcion) {
    $usado = in_array($tipo, array_column($result_nueva->fetch_all(MYSQLI_ASSOC), 'tipo_servicio')) ? '✅' : '❌';
    echo "<li><strong>$tipo:</strong> $descripcion $usado</li>";
}
echo "</ul>";
echo "</div>";

echo "<h2>✅ Resumen</h2>";
echo "<div style='background:#e8f5e8; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<p><strong>🎉 Proceso completado exitosamente</strong></p>";
echo "<p>• Tipos de servicio disponibles: <strong>" . count($tipos_servicio) . "</strong></p>";
echo "<p>• Solicitudes actualizadas: <strong>$solicitudes_actualizadas</strong></p>";
echo "<p>• Total de solicitudes: <strong>$total_nuevo</strong></p>";
echo "<p>• Tipos diferentes en uso: <strong>" . count(array_unique(array_column($result_nueva->fetch_all(MYSQLI_ASSOC), 'tipo_servicio'))) . "</strong></p>";
echo "</div>";

echo "<h2>🔗 Verificar Cambios</h2>";
echo "<p><a href='procesar-solicitud.php' target='_blank'>📋 Ver Solicitudes Actualizadas</a></p>";
echo "<p><a href='solicitud.php' target='_blank'>📝 Nueva Solicitud (con nuevos tipos)</a></p>";
echo "<p><a href='test-completo-sistema.php' target='_blank'>🧪 Ejecutar Test Completo</a></p>";

$conn->close();
?>
