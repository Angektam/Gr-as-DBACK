<?php
require_once 'conexion.php';

echo "<h1>Agregando Grúa de Plataforma para Pruebas</h1>";

// Insertar una grúa de plataforma
$sql_insert = "INSERT INTO gruas (ID, Placa, Marca, Modelo, Tipo, Estado, ubicacion_actual, coordenadas_actuales) 
               VALUES (3, 'PLT001', 'International', 'HX520', 'Plataforma', 'Activa', 'Los Mochis, Sinaloa - Ubicación de prueba', '25.7900,-109.0100')";

if ($conn->query($sql_insert)) {
    echo "<p style='color:green'>✅ Grúa de plataforma agregada correctamente</p>";
} else {
    echo "<p style='color:red'>❌ Error al agregar grúa: " . $conn->error . "</p>";
}

// Verificar todas las grúas
echo "<h2>Grúas disponibles después del cambio:</h2>";
$result = $conn->query("SELECT * FROM gruas_disponibles");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>ID</th><th>Placa</th><th>Tipo</th><th>Estado</th><th>Coordenadas</th><th>GPS</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $gps_status = $row['tiene_coordenadas'] ? '✅ Con GPS' : '❌ Sin GPS';
        echo "<tr>";
        echo "<td>{$row['ID']}</td>";
        echo "<td>{$row['Placa']}</td>";
        echo "<td>{$row['Tipo']}</td>";
        echo "<td>{$row['Estado']}</td>";
        echo "<td>{$row['coordenadas_actuales']}</td>";
        echo "<td>$gps_status</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<p><a href='probar-auto-asignacion.php'>🧪 Probar Auto-Asignación Nuevamente</a></p>";

$conn->close();
?>
