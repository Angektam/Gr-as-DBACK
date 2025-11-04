<?php
require_once 'conexion.php';

echo "<h1>Agregando Coordenadas de Prueba a las Grúas</h1>";

// Actualizar grúas con coordenadas de prueba
$coordenadas_prueba = [
    1 => '25.7945,-109.0000', // Los Mochis
    2 => '25.8000,-108.9900'  // Cerca de Los Mochis
];

foreach ($coordenadas_prueba as $grua_id => $coordenadas) {
    $sql = "UPDATE gruas SET 
            ubicacion_actual = 'Los Mochis, Sinaloa - Ubicación de prueba',
            coordenadas_actuales = ?,
            disponible_desde = NULL
            WHERE ID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $coordenadas, $grua_id);
    
    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ Grúa ID $grua_id actualizada con coordenadas: $coordenadas</p>";
    } else {
        echo "<p style='color:red'>❌ Error al actualizar grúa ID $grua_id: " . $stmt->error . "</p>";
    }
}

// Verificar grúas disponibles
echo "<h2>Verificando Grúas Disponibles</h2>";
$result = $conn->query("SELECT * FROM gruas_disponibles");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>ID</th><th>Placa</th><th>Tipo</th><th>Ubicación</th><th>Coordenadas</th><th>GPS</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $gps_status = $row['tiene_coordenadas'] ? '✅ Con GPS' : '❌ Sin GPS';
        echo "<tr>";
        echo "<td>{$row['ID']}</td>";
        echo "<td>{$row['Placa']}</td>";
        echo "<td>{$row['Tipo']}</td>";
        echo "<td>{$row['ubicacion_actual']}</td>";
        echo "<td>{$row['coordenadas_actuales']}</td>";
        echo "<td>$gps_status</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>❌ No hay grúas disponibles</p>";
}

echo "<p><a href='probar-auto-asignacion.php'>🧪 Probar Auto-Asignación Nuevamente</a></p>";

$conn->close();
?>
