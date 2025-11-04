<?php
/**
 * Actualizar el enum de estados en la tabla solicitudes
 */

require_once 'conexion.php';

echo "<h1>🔧 Actualizando Enum de Estados en Solicitudes</h1>";

// Estados actuales en el enum
echo "<h2>📋 Estados Actuales en el Enum</h2>";
$query_actual = "SHOW COLUMNS FROM solicitudes LIKE 'estado'";
$result_actual = $conn->query($query_actual);
$row_actual = $result_actual->fetch_assoc();
echo "<p><strong>Enum actual:</strong> {$row_actual['Type']}</p>";

// Nuevos estados que queremos agregar
$nuevos_estados = [
    'pendiente' => 'Solicitud recibida, esperando asignación',
    'asignada' => 'Grúa asignada, en camino al cliente', 
    'en_camino' => 'Grúa en ruta hacia el cliente',
    'en_proceso' => 'Servicio en ejecución',
    'completada' => 'Servicio finalizado exitosamente',
    'cancelada' => 'Solicitud cancelada por el cliente',
    'reagendada' => 'Solicitud reagendada para otra fecha',
    'en_espera' => 'Esperando confirmación del cliente'
];

echo "<h2>🔄 Actualizando Enum</h2>";

$enum_values = "'" . implode("','", array_keys($nuevos_estados)) . "'";
$sql_alter = "ALTER TABLE solicitudes MODIFY COLUMN estado ENUM($enum_values) DEFAULT 'pendiente'";

if ($conn->query($sql_alter)) {
    echo "<p style='color:green'>✅ Enum de estados actualizado exitosamente</p>";
} else {
    echo "<p style='color:red'>❌ Error al actualizar enum: " . $conn->error . "</p>";
}

echo "<h2>📊 Verificación del Nuevo Enum</h2>";
$query_nuevo = "SHOW COLUMNS FROM solicitudes LIKE 'estado'";
$result_nuevo = $conn->query($query_nuevo);
$row_nuevo = $result_nuevo->fetch_assoc();
echo "<p><strong>Nuevo enum:</strong> {$row_nuevo['Type']}</p>";

echo "<h2>📋 Lista de Estados Disponibles</h2>";
echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
echo "<tr style='background:#f0f0f0;'><th>Estado</th><th>Descripción</th></tr>";
foreach ($nuevos_estados as $estado => $descripcion) {
    echo "<tr>";
    echo "<td><strong>$estado</strong></td>";
    echo "<td>$descripcion</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>✅ Resumen</h2>";
echo "<div style='background:#e8f5e8; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<p><strong>🎉 Enum actualizado exitosamente</strong></p>";
echo "<p>• Estados disponibles: <strong>" . count($nuevos_estados) . "</strong></p>";
echo "<p>• Ahora se pueden usar todos los estados en las solicitudes</p>";
echo "</div>";

$conn->close();
?>
