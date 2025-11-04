<?php
/**
 * Script para corregir coordenadas de grúas y mejorar el cálculo de distancias
 */

require_once 'conexion.php';

echo "<h1>🔧 Corrigiendo Coordenadas de Grúas</h1>";

// Verificar estado actual de las grúas
echo "<h2>📊 Estado Actual de las Grúas</h2>";
$query = "SELECT ID, Placa, Tipo, Estado, ubicacion_actual, coordenadas_actuales FROM gruas";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Placa</th><th>Tipo</th><th>Estado</th><th>Ubicación</th><th>Coordenadas</th><th>Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $status = $row['coordenadas_actuales'] ? '✅ Con GPS' : '❌ Sin GPS';
        echo "<tr>";
        echo "<td>{$row['ID']}</td>";
        echo "<td>{$row['Placa']}</td>";
        echo "<td>{$row['Tipo']}</td>";
        echo "<td>{$row['Estado']}</td>";
        echo "<td>" . ($row['ubicacion_actual'] ?: 'No especificada') . "</td>";
        echo "<td>" . ($row['coordenadas_actuales'] ?: 'No disponible') . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Agregar coordenadas de prueba para grúas que no las tienen
echo "<h2>📍 Agregando Coordenadas de Prueba</h2>";

$coordenadas_prueba = [
    1 => '25.7945,-109.0000', // Los Mochis Centro
    2 => '25.8000,-108.9900', // Los Mochis Norte
    3 => '25.7900,-109.0100', // Los Mochis Sur
    4 => '25.7850,-108.9950', // Los Mochis Este
    5 => '25.8050,-109.0050'  // Los Mochis Oeste
];

$ubicaciones_prueba = [
    1 => 'Los Mochis Centro, Sinaloa',
    2 => 'Los Mochis Norte, Sinaloa', 
    3 => 'Los Mochis Sur, Sinaloa',
    4 => 'Los Mochis Este, Sinaloa',
    5 => 'Los Mochis Oeste, Sinaloa'
];

$gruas_actualizadas = 0;

foreach ($coordenadas_prueba as $grua_id => $coordenadas) {
    $ubicacion = $ubicaciones_prueba[$grua_id];
    
    $sql = "UPDATE gruas SET 
            ubicacion_actual = ?,
            coordenadas_actuales = ?,
            disponible_desde = NULL,
            ultima_actualizacion_ubicacion = NOW()
            WHERE ID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $ubicacion, $coordenadas, $grua_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<p style='color:green'>✅ Grúa ID $grua_id actualizada con coordenadas: $coordenadas</p>";
            $gruas_actualizadas++;
        } else {
            echo "<p style='color:orange'>⚠️ Grúa ID $grua_id no existe o ya tiene coordenadas</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Error al actualizar grúa ID $grua_id: " . $stmt->error . "</p>";
    }
}

// Verificar grúas disponibles después de la actualización
echo "<h2>🚛 Grúas Disponibles Después de la Actualización</h2>";
$query_disponibles = "SELECT * FROM gruas_disponibles";
$result_disponibles = $conn->query($query_disponibles);

if ($result_disponibles->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#e8f5e8;'><th>ID</th><th>Placa</th><th>Tipo</th><th>Ubicación</th><th>Coordenadas</th><th>GPS</th></tr>";
    
    while ($row = $result_disponibles->fetch_assoc()) {
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

// Verificar historial de asignaciones
echo "<h2>📋 Verificando Historial de Asignaciones</h2>";
$query_historial = "SELECT ha.*, s.nombre_completo, s.ubicacion as ubicacion_solicitud, 
                           g.Placa, g.Tipo, g.coordenadas_actuales as coordenadas_grua
                   FROM historial_asignaciones ha
                   LEFT JOIN solicitudes s ON ha.solicitud_id = s.id
                   LEFT JOIN gruas g ON ha.grua_id = g.ID
                   ORDER BY ha.fecha_asignacion DESC 
                   LIMIT 5";

$result_historial = $conn->query($query_historial);

if ($result_historial->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>Fecha</th><th>Solicitud</th><th>Grúa</th><th>Distancia Guardada</th><th>Coordenadas Grúa</th><th>Ubicación Solicitud</th></tr>";
    
    while ($row = $result_historial->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha_asignacion'])) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre_completo'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['Placa'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['distancia_km'] ? round($row['distancia_km'], 2) . ' km' : 'N/A') . "</td>";
        echo "<td>" . ($row['coordenadas_grua'] ?: 'No disponible') . "</td>";
        echo "<td>" . htmlspecialchars($row['ubicacion_solicitud'] ?? 'No disponible') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>⚠️ No hay historial de asignaciones</p>";
}

echo "<h2>✅ Resumen</h2>";
echo "<p>Grúas actualizadas: <strong>$gruas_actualizadas</strong></p>";
echo "<p>Total de grúas disponibles: <strong>" . $result_disponibles->num_rows . "</strong></p>";

echo "<h2>🔗 Enlaces Útiles</h2>";
echo "<p><a href='configuracion-auto-asignacion.php'>⚙️ Configuración Auto-Asignación</a></p>";
echo "<p><a href='menu-auto-asignacion.php'>📋 Menú Auto-Asignación</a></p>";
echo "<p><a href='probar-auto-asignacion.php'>🧪 Probar Auto-Asignación</a></p>";

$conn->close();
?>
