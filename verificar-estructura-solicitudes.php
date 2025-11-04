<?php
/**
 * Verificar Estructura de la Tabla Solicitudes
 */

require_once 'conexion.php';

echo "<h1>🔍 Verificar Estructura de la Tabla Solicitudes</h1>";

// Verificar estructura de la tabla
$query = "DESCRIBE solicitudes";
$result = $conn->query($query);

echo "<h2>📋 Estructura Actual de la Tabla 'solicitudes'</h2>";
echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:8px;'>Campo</th>";
echo "<th style='padding:8px;'>Tipo</th>";
echo "<th style='padding:8px;'>Nulo</th>";
echo "<th style='padding:8px;'>Clave</th>";
echo "<th style='padding:8px;'>Por Defecto</th>";
echo "<th style='padding:8px;'>Extra</th>";
echo "</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td style='padding:8px;'>{$row['Field']}</td>";
    echo "<td style='padding:8px;'>{$row['Type']}</td>";
    echo "<td style='padding:8px;'>{$row['Null']}</td>";
    echo "<td style='padding:8px;'>{$row['Key']}</td>";
    echo "<td style='padding:8px;'>{$row['Default']}</td>";
    echo "<td style='padding:8px;'>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Verificar si hay datos en la tabla
$query_count = "SELECT COUNT(*) as total FROM solicitudes";
$result_count = $conn->query($query_count);
$total_solicitudes = $result_count->fetch_assoc()['total'];

echo "<h2>📊 Datos en la Tabla</h2>";
echo "<p><strong>Total de solicitudes:</strong> $total_solicitudes</p>";

if ($total_solicitudes > 0) {
    // Mostrar algunas solicitudes existentes
    $query_sample = "SELECT * FROM solicitudes LIMIT 3";
    $result_sample = $conn->query($query_sample);
    
    echo "<h3>Muestra de datos existentes:</h3>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    
    $first_row = true;
    while ($row = $result_sample->fetch_assoc()) {
        if ($first_row) {
            echo "<tr style='background:#f0f0f0;'>";
            foreach (array_keys($row) as $column) {
                echo "<th style='padding:8px;'>$column</th>";
            }
            echo "</tr>";
            $first_row = false;
        }
        
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td style='padding:8px;'>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>