<?php
/**
 * Script para actualizar las solicitudes existentes con destinos de prueba
 */

require_once 'conexion.php';

echo "<h1>🔄 Actualizando Destinos de Prueba</h1>";

// Destinos de prueba para Los Mochis, Sinaloa
$destinos_prueba = [
    'Taller Mecánico Central, Los Mochis, Sinaloa',
    'Casa del cliente, Los Mochis, Sinaloa', 
    'Taller Especializado, Los Mochis, Sinaloa',
    'Taller de Reparación, Los Mochis, Sinaloa',
    'Garage Automotriz, Los Mochis, Sinaloa',
    'Centro de Servicio, Los Mochis, Sinaloa',
    'Taller de Motores, Los Mochis, Sinaloa',
    'Servicio Técnico, Los Mochis, Sinaloa',
    'Taller de Frenos, Los Mochis, Sinaloa',
    'Centro Automotriz, Los Mochis, Sinaloa'
];

// Obtener solicitudes que no tienen destino
$query = "SELECT id, ubicacion FROM solicitudes WHERE ubicacion_destino IS NULL OR ubicacion_destino = ''";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<h2>📋 Solicitudes sin destino encontradas: " . $result->num_rows . "</h2>";
    
    $contador = 0;
    while ($row = $result->fetch_assoc()) {
        $destino = $destinos_prueba[array_rand($destinos_prueba)];
        
        $update_sql = "UPDATE solicitudes SET ubicacion_destino = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $destino, $row['id']);
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Solicitud ID {$row['id']} actualizada con destino: $destino</p>";
            $contador++;
        } else {
            echo "<p style='color:red'>❌ Error al actualizar solicitud ID {$row['id']}: " . $stmt->error . "</p>";
        }
    }
    
    echo "<h2>✅ Resumen</h2>";
    echo "<p>Solicitudes actualizadas: <strong>$contador</strong></p>";
} else {
    echo "<p style='color:orange'>⚠️ No hay solicitudes sin destino</p>";
}

// Verificar solicitudes actualizadas
echo "<h2>📊 Solicitudes Actualizadas</h2>";
$query_verificar = "SELECT id, nombre_completo, ubicacion, ubicacion_destino, estado FROM solicitudes ORDER BY id DESC LIMIT 10";
$result_verificar = $conn->query($query_verificar);

if ($result_verificar->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Nombre</th><th>Origen</th><th>Destino</th><th>Estado</th></tr>";
    
    while ($row = $result_verificar->fetch_assoc()) {
        $destino = $row['ubicacion_destino'] ?: 'No definido';
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ubicacion']) . "</td>";
        echo "<td>" . htmlspecialchars($destino) . "</td>";
        echo "<td>{$row['estado']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>🔗 Enlaces de Prueba</h2>";
echo "<p><a href='detalle-solicitud.php?id=17'>📋 Ver Detalle de Solicitud (ID: 17)</a></p>";
echo "<p><a href='detalle-solicitud.php?id=16'>📋 Ver Detalle de Solicitud (ID: 16)</a></p>";
echo "<p><a href='detalle-solicitud.php?id=15'>📋 Ver Detalle de Solicitud (ID: 15)</a></p>";

$conn->close();
?>
