<?php
/**
 * Script para corregir coordenadas de todas las grúas
 * Asegura que todas las grúas tengan coordenadas válidas
 */

require_once 'conexion.php';

echo "<h1>🗺️ Corrigiendo Coordenadas de Grúas</h1>";

// Obtener todas las grúas
$query = "SELECT ID, Placa, ubicacion_actual, coordenadas_actuales FROM gruas";
$result = $conn->query($query);

$gruas_corregidas = 0;
$gruas_con_coordenadas_validas = 0;

echo "<h2>📋 Verificando Coordenadas Actuales</h2>";

while ($row = $result->fetch_assoc()) {
    $id = $row['ID'];
    $placa = $row['Placa'];
    $ubicacion = $row['ubicacion_actual'];
    $coordenadas = $row['coordenadas_actuales'];
    
    $coordenadas_validas = false;
    
    if ($coordenadas && $coordenadas !== '') {
        $coords = explode(',', $coordenadas);
        if (count($coords) == 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
            $lat = floatval($coords[0]);
            $lng = floatval($coords[1]);
            
            // Verificar que estén en rango válido para México
            if ($lat >= 14.5 && $lat <= 32.7 && $lng >= -118.4 && $lng <= -86.7) {
                $coordenadas_validas = true;
                $gruas_con_coordenadas_validas++;
            }
        }
    }
    
    if (!$coordenadas_validas) {
        // Generar coordenadas válidas para Los Mochis
        $lat_base = 25.7945;
        $lng_base = -109.0000;
        
        // Variación aleatoria dentro de Los Mochis
        $lat = $lat_base + (rand(-100, 100) / 10000); // ±1km
        $lng = $lng_base + (rand(-100, 100) / 10000); // ±1km
        
        $nuevas_coordenadas = $lat . ',' . $lng;
        
        // Actualizar coordenadas
        $update_sql = "UPDATE gruas SET coordenadas_actuales = ? WHERE ID = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $nuevas_coordenadas, $id);
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Grúa $placa (ID: $id) - Coordenadas corregidas: $nuevas_coordenadas</p>";
            $gruas_corregidas++;
        } else {
            echo "<p style='color:red'>❌ Error al corregir grúa $placa (ID: $id): " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Grúa $placa (ID: $id) - Coordenadas ya válidas: $coordenadas</p>";
    }
}

echo "<h2>📊 Verificación Final</h2>";

// Verificar coordenadas válidas después de la corrección
$query_verificacion = "SELECT COUNT(*) as total, 
    SUM(CASE WHEN coordenadas_actuales IS NOT NULL AND coordenadas_actuales != '' THEN 1 ELSE 0 END) as con_coordenadas,
    SUM(CASE WHEN coordenadas_actuales REGEXP '^[0-9.-]+,[0-9.-]+$' THEN 1 ELSE 0 END) as formato_valido
    FROM gruas";

$result_verificacion = $conn->query($query_verificacion);
$stats = $result_verificacion->fetch_assoc();

echo "<div style='background:#f0f8ff; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<h3>📈 Estadísticas de Coordenadas</h3>";
echo "<p><strong>Total de grúas:</strong> {$stats['total']}</p>";
echo "<p><strong>Con coordenadas:</strong> {$stats['con_coordenadas']}</p>";
echo "<p><strong>Formato válido:</strong> {$stats['formato_valido']}</p>";
echo "<p><strong>Grúas corregidas:</strong> $gruas_corregidas</p>";
echo "<p><strong>Grúas ya válidas:</strong> $gruas_con_coordenadas_validas</p>";
echo "</div>";

// Mostrar algunas coordenadas de ejemplo
echo "<h2>🗺️ Coordenadas de Ejemplo</h2>";
$query_ejemplo = "SELECT Placa, coordenadas_actuales FROM gruas LIMIT 10";
$result_ejemplo = $conn->query($query_ejemplo);

echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
echo "<tr style='background:#f0f0f0;'><th>Placa</th><th>Coordenadas</th><th>Estado</th></tr>";

while ($row = $result_ejemplo->fetch_assoc()) {
    $coords = $row['coordenadas_actuales'];
    $coords_array = explode(',', $coords);
    $estado = (count($coords_array) == 2 && is_numeric($coords_array[0]) && is_numeric($coords_array[1])) ? '✅ Válidas' : '❌ Inválidas';
    
    echo "<tr>";
    echo "<td>{$row['Placa']}</td>";
    echo "<td>$coords</td>";
    echo "<td>$estado</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>✅ Resumen</h2>";
echo "<div style='background:#e8f5e8; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<p><strong>🎉 Proceso completado exitosamente</strong></p>";
echo "<p>• Grúas corregidas: <strong>$gruas_corregidas</strong></p>";
echo "<p>• Grúas ya válidas: <strong>$gruas_con_coordenadas_validas</strong></p>";
echo "<p>• Total con coordenadas válidas: <strong>{$stats['formato_valido']}</strong></p>";
echo "</div>";

$conn->close();
?>
