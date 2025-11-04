<?php
/**
 * Script para agregar estados de solicitudes faltantes
 * Actualiza solicitudes existentes con estados más variados
 */

require_once 'conexion.php';

echo "<h1>📋 Agregando Estados de Solicitudes Faltantes</h1>";

// Estados que queremos tener en el sistema
$estados_disponibles = [
    'pendiente' => 'Solicitud recibida, esperando asignación',
    'asignada' => 'Grúa asignada, en camino al cliente',
    'en_camino' => 'Grúa en ruta hacia el cliente',
    'en_proceso' => 'Servicio en ejecución',
    'completada' => 'Servicio finalizado exitosamente',
    'cancelada' => 'Solicitud cancelada por el cliente',
    'reagendada' => 'Solicitud reagendada para otra fecha',
    'en_espera' => 'Esperando confirmación del cliente'
];

echo "<h2>📊 Estados Actuales en el Sistema</h2>";

// Verificar estados actuales
$query_estados = "SELECT estado, COUNT(*) as cantidad FROM solicitudes GROUP BY estado ORDER BY cantidad DESC";
$result_estados = $conn->query($query_estados);

echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
echo "<tr style='background:#f0f0f0;'><th>Estado Actual</th><th>Cantidad</th><th>Porcentaje</th></tr>";

$total_solicitudes = 0;
$estados_actuales = [];

while ($row = $result_estados->fetch_assoc()) {
    $estados_actuales[] = $row['estado'];
    $total_solicitudes += $row['cantidad'];
    $porcentaje = round(($row['cantidad'] / $total_solicitudes) * 100, 1);
    echo "<tr>";
    echo "<td>{$row['estado']}</td>";
    echo "<td>{$row['cantidad']}</td>";
    echo "<td>$porcentaje%</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🔄 Actualizando Estados de Solicitudes</h2>";

// Obtener todas las solicitudes
$query_solicitudes = "SELECT id, estado, fecha_solicitud FROM solicitudes ORDER BY id";
$result_solicitudes = $conn->query($query_solicitudes);

$solicitudes_actualizadas = 0;
$distribucion_estados = [];

// Inicializar contadores
foreach ($estados_disponibles as $estado => $descripcion) {
    $distribucion_estados[$estado] = 0;
}

while ($row = $result_solicitudes->fetch_assoc()) {
    $id = $row['id'];
    $estado_actual = $row['estado'];
    $fecha_creacion = $row['fecha_solicitud'];
    
    // Lógica para asignar estados basada en la fecha y ID
    $nuevo_estado = $estado_actual; // Mantener el actual por defecto
    
    // Si es muy reciente (últimas 2 horas), mantener como pendiente
    $hora_creacion = strtotime($fecha_creacion);
    $hora_actual = time();
    $diferencia_horas = ($hora_actual - $hora_creacion) / 3600;
    
    if ($diferencia_horas < 2) {
        $nuevo_estado = 'pendiente';
    } elseif ($diferencia_horas < 4) {
        $nuevo_estado = 'asignada';
    } elseif ($diferencia_horas < 6) {
        $nuevo_estado = 'en_camino';
    } elseif ($diferencia_horas < 8) {
        $nuevo_estado = 'en_proceso';
    } elseif ($diferencia_horas < 24) {
        $nuevo_estado = 'completada';
    } else {
        // Para solicitudes más antiguas, distribución aleatoria
        $estados_probables = ['completada', 'completada', 'completada', 'cancelada', 'reagendada'];
        $nuevo_estado = $estados_probables[array_rand($estados_probables)];
    }
    
    // Actualizar solo si el estado cambió
    if ($nuevo_estado !== $estado_actual) {
        $update_sql = "UPDATE solicitudes SET estado = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $nuevo_estado, $id);
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Solicitud ID $id: '$estado_actual' → '$nuevo_estado'</p>";
            $solicitudes_actualizadas++;
        } else {
            echo "<p style='color:red'>❌ Error al actualizar solicitud ID $id: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Solicitud ID $id: Mantiene estado '$estado_actual'</p>";
    }
    
    $distribucion_estados[$nuevo_estado]++;
}

echo "<h2>📊 Nueva Distribución de Estados</h2>";

// Verificar la nueva distribución
$query_nueva_distribucion = "SELECT estado, COUNT(*) as cantidad FROM solicitudes GROUP BY estado ORDER BY cantidad DESC";
$result_nueva = $conn->query($query_nueva_distribucion);

echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
echo "<tr style='background:#f0f0f0;'><th>Estado</th><th>Cantidad</th><th>Porcentaje</th><th>Descripción</th></tr>";

$total_nuevo = 0;
while ($row = $result_nueva->fetch_assoc()) {
    $total_nuevo += $row['cantidad'];
}

$result_nueva = $conn->query($query_nueva_distribucion);
while ($row = $result_nueva->fetch_assoc()) {
    $estado = $row['estado'];
    $cantidad = $row['cantidad'];
    $porcentaje = round(($cantidad / $total_nuevo) * 100, 1);
    $descripcion = $estados_disponibles[$estado] ?? 'Estado personalizado';
    
    $color = '';
    switch ($estado) {
        case 'pendiente': $color = 'background:#fff3cd;'; break;
        case 'asignada': $color = 'background:#d1ecf1;'; break;
        case 'en_camino': $color = 'background:#d4edda;'; break;
        case 'en_proceso': $color = 'background:#f8d7da;'; break;
        case 'completada': $color = 'background:#d1ecf1;'; break;
        case 'cancelada': $color = 'background:#f8d7da;'; break;
        case 'reagendada': $color = 'background:#fff3cd;'; break;
        case 'en_espera': $color = 'background:#e2e3e5;'; break;
    }
    
    echo "<tr style='$color'>";
    echo "<td><strong>$estado</strong></td>";
    echo "<td>$cantidad</td>";
    echo "<td>$porcentaje%</td>";
    echo "<td>$descripcion</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🎯 Estados Disponibles en el Sistema</h2>";
echo "<div style='background:#f0f8ff; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<h3>📋 Lista Completa de Estados</h3>";
echo "<ul>";
foreach ($estados_disponibles as $estado => $descripcion) {
    $usado = in_array($estado, array_column($result_nueva->fetch_all(MYSQLI_ASSOC), 'estado')) ? '✅' : '❌';
    echo "<li><strong>$estado:</strong> $descripcion $usado</li>";
}
echo "</ul>";
echo "</div>";

echo "<h2>✅ Resumen</h2>";
echo "<div style='background:#e8f5e8; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<p><strong>🎉 Proceso completado exitosamente</strong></p>";
echo "<p>• Solicitudes actualizadas: <strong>$solicitudes_actualizadas</strong></p>";
echo "<p>• Total de solicitudes: <strong>$total_nuevo</strong></p>";
echo "<p>• Estados diferentes en uso: <strong>" . count(array_unique(array_column($result_nueva->fetch_all(MYSQLI_ASSOC), 'estado'))) . "</strong></p>";
echo "</div>";

echo "<h2>🔗 Verificar Cambios</h2>";
echo "<p><a href='procesar-solicitud.php' target='_blank'>📋 Ver Solicitudes Actualizadas</a></p>";
echo "<p><a href='test-completo-sistema.php' target='_blank'>🧪 Ejecutar Test Completo</a></p>";

$conn->close();
?>
