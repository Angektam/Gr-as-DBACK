<?php
require_once 'conexion.php';

echo "<h1>🔍 Estructura de la Tabla gruas</h1>";
$query = "DESCRIBE gruas";
$result = $conn->query($query);

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

$conn->close();
?>
