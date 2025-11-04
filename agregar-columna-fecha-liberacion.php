<?php
/**
 * Agregar columna fecha_liberacion a la tabla solicitudes
 * Para rastrear cuándo se liberan las grúas
 */

require_once 'conexion.php';

echo "<h1>🔧 Agregando Columna fecha_liberacion</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar si la columna ya existe
$query_verificar = "SHOW COLUMNS FROM solicitudes LIKE 'fecha_liberacion'";
$result_verificar = $conn->query($query_verificar);

if ($result_verificar->num_rows > 0) {
    echo "<div style='background:#d4edda; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid #28a745;'>";
    echo "<h3>✅ Columna ya existe</h3>";
    echo "<p>La columna 'fecha_liberacion' ya existe en la tabla 'solicitudes'.</p>";
    echo "</div>";
} else {
    // Agregar la columna
    $query_agregar = "ALTER TABLE solicitudes ADD COLUMN fecha_liberacion DATETIME NULL AFTER fecha_asignacion";
    
    if ($conn->query($query_agregar)) {
        echo "<div style='background:#d4edda; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid #28a745;'>";
        echo "<h3>✅ Columna agregada exitosamente</h3>";
        echo "<p>Se agregó la columna 'fecha_liberacion' a la tabla 'solicitudes'.</p>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid #dc3545;'>";
        echo "<h3>❌ Error al agregar columna</h3>";
        echo "<p><strong>Error:</strong> " . $conn->error . "</p>";
        echo "</div>";
    }
}

// Agregar estado 'liberada' al ENUM si no existe
$query_verificar_estado = "SHOW COLUMNS FROM solicitudes WHERE Field = 'estado'";
$result_estado = $conn->query($query_verificar_estado);
$estado_info = $result_estado->fetch_assoc();

if (strpos($estado_info['Type'], 'liberada') === false) {
    $query_agregar_estado = "ALTER TABLE solicitudes MODIFY COLUMN estado ENUM('pendiente','asignada','en_camino','en_proceso','completada','cancelada','reagendada','en_espera','liberada') DEFAULT 'pendiente'";
    
    if ($conn->query($query_agregar_estado)) {
        echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h3>✅ Estado 'liberada' agregado</h3>";
        echo "<p>Se agregó el estado 'liberada' al ENUM de la columna 'estado'.</p>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h3>❌ Error al agregar estado</h3>";
        echo "<p><strong>Error:</strong> " . $conn->error . "</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:10px 0;'>";
    echo "<h3>✅ Estado 'liberada' ya existe</h3>";
    echo "<p>El estado 'liberada' ya existe en el ENUM de la columna 'estado'.</p>";
    echo "</div>";
}

// Verificar estructura actual de la tabla
echo "<h2>📋 Estructura Actual de la Tabla solicitudes</h2>";
$query_estructura = "DESCRIBE solicitudes";
$result_estructura = $conn->query($query_estructura);

echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:8px;'>Campo</th>";
echo "<th style='padding:8px;'>Tipo</th>";
echo "<th style='padding:8px;'>Nulo</th>";
echo "<th style='padding:8px;'>Clave</th>";
echo "<th style='padding:8px;'>Por Defecto</th>";
echo "<th style='padding:8px;'>Extra</th>";
echo "</tr>";

while ($row = $result_estructura->fetch_assoc()) {
    $highlight = '';
    if (in_array($row['Field'], ['fecha_liberacion', 'estado'])) {
        $highlight = 'background:#d4edda;';
    }
    
    echo "<tr style='$highlight'>";
    echo "<td style='padding:8px;'>{$row['Field']}</td>";
    echo "<td style='padding:8px;'>{$row['Type']}</td>";
    echo "<td style='padding:8px;'>{$row['Null']}</td>";
    echo "<td style='padding:8px;'>{$row['Key']}</td>";
    echo "<td style='padding:8px;'>{$row['Default']}</td>";
    echo "<td style='padding:8px;'>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();

echo "<div style='text-align:center; margin:30px 0; padding:30px; background:linear-gradient(135deg, #28a745 0%, #007bff 100%); color:white; border-radius:20px;'>";
echo "<h2 style='margin:0 0 15px 0; font-size:2em;'>✅ Configuración Completada</h2>";
echo "<p style='margin:0; font-size:1.2em; opacity:0.9;'>Sistema de liberación de grúas listo</p>";
echo "</div>";
?>
