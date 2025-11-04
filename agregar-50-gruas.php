<?php
/**
 * Script para agregar 50 grúas al sistema DBACK
 */

require_once 'conexion.php';

echo "<h1>🚛 Agregando 50 Grúas al Sistema DBACK</h1>";

// Datos para generar grúas realistas
$marcas = ['International', 'Freightliner', 'Peterbilt', 'Kenworth', 'Volvo', 'Mack', 'Hino', 'Isuzu', 'Ford', 'Chevrolet'];
$modelos = ['HX520', 'Cascadia', '579', 'T680', 'VNL', 'Anthem', 'HX', 'NPR', 'F-650', 'Silverado'];
$tipos = ['Plataforma', 'Arrastre', 'Remolque'];
$estados = ['Activa', 'Mantenimiento', 'Inactiva'];

// Ubicaciones en Los Mochis y alrededores
$ubicaciones = [
    'Los Mochis Centro, Sinaloa',
    'Zona Industrial, Los Mochis, Sinaloa',
    'Avenida Álvaro Obregón, Los Mochis, Sinaloa',
    'Boulevard 10 de Mayo, Los Mochis, Sinaloa',
    'Calle Leyes de Reforma, Los Mochis, Sinaloa',
    'Ejido Mochis, Ahome, Sinaloa',
    'Universidad Autónoma de Occidente, Los Mochis, Sinaloa',
    'Centro Comercial, Los Mochis, Sinaloa',
    'Zona Hotelera, Los Mochis, Sinaloa',
    'Terminal de Autobuses, Los Mochis, Sinaloa',
    'Hospital General, Los Mochis, Sinaloa',
    'Aeropuerto, Los Mochis, Sinaloa',
    'Puerto, Los Mochis, Sinaloa',
    'Zona Residencial Norte, Los Mochis, Sinaloa',
    'Zona Residencial Sur, Los Mochis, Sinaloa'
];

// Coordenadas aproximadas para Los Mochis y alrededores
$coordenadas_base = [
    [25.7945, -109.0000], // Centro de Los Mochis
    [25.8000, -108.9900], // Norte
    [25.7900, -109.0100], // Sur
    [25.7850, -108.9950], // Este
    [25.8050, -109.0050], // Oeste
    [25.8100, -108.9800], // Zona Industrial
    [25.7800, -109.0200], // Universidad
    [25.7950, -108.9850], // Centro Comercial
    [25.8200, -108.9750], // Aeropuerto
    [25.7600, -109.0300]  // Puerto
];

$gruas_agregadas = 0;
$errores = 0;

// Obtener el siguiente ID disponible
$query_max_id = "SELECT MAX(ID) as max_id FROM gruas";
$result_max_id = $conn->query($query_max_id);
$max_id = $result_max_id->fetch_assoc()['max_id'] ?: 0;
$siguiente_id = $max_id + 1;

echo "<h2>📋 Generando 50 Grúas (empezando desde ID $siguiente_id)...</h2>";

for ($i = $siguiente_id; $i < $siguiente_id + 50; $i++) {
    // Generar datos aleatorios
    $marca = $marcas[array_rand($marcas)];
    $modelo = $modelos[array_rand($modelos)];
    $tipo = $tipos[array_rand($tipos)];
    $estado = $estados[array_rand($estados)];
    $ubicacion = $ubicaciones[array_rand($ubicaciones)];
    
    // Generar placa (formato corto para varchar(7))
    $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    $placa = $letras[array_rand($letras)] . $letras[array_rand($letras)] . '-' . 
             str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
    
    // Generar coordenadas aleatorias cerca de Los Mochis
    $coord_base = $coordenadas_base[array_rand($coordenadas_base)];
    $lat = $coord_base[0] + (rand(-50, 50) / 10000); // Variación de ~500m
    $lng = $coord_base[1] + (rand(-50, 50) / 10000);
    $coordenadas = $lat . ',' . $lng;
    
    // Determinar disponibilidad
    $disponible_desde = null;
    if ($estado === 'Mantenimiento') {
        $disponible_desde = date('Y-m-d H:i:s', strtotime('+' . rand(1, 7) . ' days'));
    }
    
    // Insertar grúa
    $sql = "INSERT INTO gruas (ID, Placa, Marca, Modelo, Tipo, Estado, ubicacion_actual, coordenadas_actuales, disponible_desde) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $i, $placa, $marca, $modelo, $tipo, $estado, $ubicacion, $coordenadas, $disponible_desde);
    
    if ($stmt->execute()) {
        echo "<p style='color:green'>✅ Grúa #$i agregada: $placa - $marca $modelo ($tipo) - $estado</p>";
        $gruas_agregadas++;
    } else {
        echo "<p style='color:red'>❌ Error al agregar grúa #$i: " . $stmt->error . "</p>";
        $errores++;
    }
}

// Verificar grúas agregadas
echo "<h2>📊 Verificación de Grúas</h2>";

$query_total = "SELECT COUNT(*) as total FROM gruas";
$result_total = $conn->query($query_total);
$total_gruas = $result_total->fetch_assoc()['total'];

$query_activas = "SELECT COUNT(*) as activas FROM gruas WHERE Estado = 'Activa'";
$result_activas = $conn->query($query_activas);
$gruas_activas = $result_activas->fetch_assoc()['activas'];

$query_disponibles = "SELECT COUNT(*) as disponibles FROM gruas_disponibles";
$result_disponibles = $conn->query($query_disponibles);
$gruas_disponibles = $result_disponibles->fetch_assoc()['disponibles'];

echo "<div style='background:#f0f8ff; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<h3>📈 Estadísticas del Sistema</h3>";
echo "<p><strong>Total de grúas:</strong> $total_gruas</p>";
echo "<p><strong>Grúas activas:</strong> $gruas_activas</p>";
echo "<p><strong>Grúas disponibles:</strong> $gruas_disponibles</p>";
echo "<p><strong>Grúas agregadas en esta sesión:</strong> $gruas_agregadas</p>";
echo "<p><strong>Errores:</strong> $errores</p>";
echo "</div>";

// Mostrar algunas grúas recién agregadas
echo "<h2>🚛 Últimas Grúas Agregadas</h2>";
$query_recientes = "SELECT Placa, Marca, Modelo, Tipo, Estado, ubicacion_actual FROM gruas ORDER BY ID DESC LIMIT 10";
$result_recientes = $conn->query($query_recientes);

if ($result_recientes->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>Placa</th><th>Marca</th><th>Modelo</th><th>Tipo</th><th>Estado</th><th>Ubicación</th></tr>";
    
    while ($row = $result_recientes->fetch_assoc()) {
        $estado_color = '';
        switch($row['Estado']) {
            case 'Activa': $estado_color = 'color:green;'; break;
            case 'Mantenimiento': $estado_color = 'color:orange;'; break;
            case 'Disponible': $estado_color = 'color:blue;'; break;
        }
        
        echo "<tr>";
        echo "<td><strong>{$row['Placa']}</strong></td>";
        echo "<td>{$row['Marca']}</td>";
        echo "<td>{$row['Modelo']}</td>";
        echo "<td>{$row['Tipo']}</td>";
        echo "<td style='$estado_color'><strong>{$row['Estado']}</strong></td>";
        echo "<td>{$row['ubicacion_actual']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Verificar vista de grúas disponibles
echo "<h2>🔍 Verificación de Vista gruas_disponibles</h2>";
$query_vista = "SELECT COUNT(*) as total_vista FROM gruas_disponibles";
$result_vista = $conn->query($query_vista);

if ($result_vista) {
    $total_vista = $result_vista->fetch_assoc()['total_vista'];
    echo "<p style='color:green'>✅ Vista 'gruas_disponibles' funcionando: $total_vista grúas disponibles</p>";
} else {
    echo "<p style='color:red'>❌ Error en vista 'gruas_disponibles': " . $conn->error . "</p>";
}

echo "<h2>✅ Resumen Final</h2>";
echo "<div style='background:#e8f5e8; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<p><strong>🎉 Proceso completado exitosamente</strong></p>";
echo "<p>• Grúas agregadas: <strong>$gruas_agregadas</strong></p>";
echo "<p>• Errores: <strong>$errores</strong></p>";
echo "<p>• Total en sistema: <strong>$total_gruas</strong></p>";
echo "<p>• Disponibles ahora: <strong>$gruas_disponibles</strong></p>";
echo "</div>";

echo "<h2>🔗 Enlaces Útiles</h2>";
echo "<p><a href='Gruas.php'>🚛 Gestión de Grúas</a></p>";
echo "<p><a href='menu-auto-asignacion.php'>🤖 Auto-Asignación</a></p>";
echo "<p><a href='procesar-solicitud.php'>📋 Procesar Solicitudes</a></p>";
echo "<p><a href='probar-auto-asignacion.php'>🧪 Probar Auto-Asignación</a></p>";

$conn->close();
?>
