<?php
/**
 * Test Específico del Mapa GPS
 * Verifica la funcionalidad del mapa en detalle-solicitud.php
 */

require_once 'conexion.php';

echo "<h1>🗺️ Test Específico del Mapa GPS</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

$tests_pasados = 0;
$tests_fallidos = 0;
$errores = [];

function testMapa($nombre, $condicion, $mensaje = '') {
    global $tests_pasados, $tests_fallidos, $errores;
    
    if ($condicion) {
        $tests_pasados++;
        echo "<p style='color:green'>✅ $nombre</p>";
    } else {
        $tests_fallidos++;
        $errores[] = "$nombre: $mensaje";
        echo "<p style='color:red'>❌ $nombre - $mensaje</p>";
    }
}

echo "<h2>🔍 1. Verificación de Datos para el Mapa</h2>";

// Obtener una solicitud con destino para probar
$query = "SELECT id, nombre_completo, ubicacion, ubicacion_destino FROM solicitudes WHERE ubicacion_destino IS NOT NULL AND ubicacion_destino != '' LIMIT 1";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $solicitud = $result->fetch_assoc();
    $solicitud_id = $solicitud['id'];
    $ubicacion_origen = $solicitud['ubicacion'];
    $ubicacion_destino = $solicitud['ubicacion_destino'];
    
    testMapa("Solicitud con destino encontrada", true, "ID: $solicitud_id");
    testMapa("Ubicación origen definida", !empty($ubicacion_origen), "Origen: $ubicacion_origen");
    testMapa("Ubicación destino definida", !empty($ubicacion_destino), "Destino: $ubicacion_destino");
    
    echo "<div style='background:#f0f8ff; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h3>📋 Datos de la Solicitud de Prueba</h3>";
    echo "<p><strong>ID:</strong> $solicitud_id</p>";
    echo "<p><strong>Cliente:</strong> " . htmlspecialchars($solicitud['nombre_completo']) . "</p>";
    echo "<p><strong>Origen:</strong> " . htmlspecialchars($ubicacion_origen) . "</p>";
    echo "<p><strong>Destino:</strong> " . htmlspecialchars($ubicacion_destino) . "</p>";
    echo "</div>";
} else {
    testMapa("Solicitud con destino encontrada", false, "No hay solicitudes con destino");
    echo "<p style='color:red'>❌ No se puede probar el mapa sin solicitudes con destino</p>";
    exit;
}

echo "<h2>🗺️ 2. Verificación de Librerías del Mapa</h2>";

// Verificar que el archivo detalle-solicitud.php contenga las librerías necesarias
$contenido_detalle = file_get_contents('detalle-solicitud.php');

$librerias_requeridas = [
    'leaflet.js' => 'Leaflet JavaScript',
    'leaflet-routing-machine.js' => 'Leaflet Routing Machine JavaScript',
    'leaflet.css' => 'Leaflet CSS',
    'leaflet-routing-machine.css' => 'Leaflet Routing Machine CSS'
];

foreach ($librerias_requeridas as $libreria => $nombre) {
    testMapa("Librería $nombre incluida", strpos($contenido_detalle, $libreria) !== false);
}

echo "<h2>🔧 3. Verificación de Funciones JavaScript</h2>";

$funciones_requeridas = [
    'inicializarMapa',
    'geocodificarYMostrarRuta',
    'mostrarRuta',
    'calcularDistanciaDirecta',
    'debugMapa'
];

foreach ($funciones_requeridas as $funcion) {
    testMapa("Función $funcion definida", strpos($contenido_detalle, "function $funcion") !== false);
}

echo "<h2>🌐 4. Verificación de APIs Externas</h2>";

// Test de conectividad a Nominatim
$url_origen = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($ubicacion_origen) . "&limit=1&countrycodes=mx";
$url_destino = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($ubicacion_destino) . "&limit=1&countrycodes=mx";

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'header' => "User-Agent: DBACK-Test/1.0\r\n"
    ]
]);

// Test geocodificación origen
$response_origen = @file_get_contents($url_origen, false, $context);
if ($response_origen !== false) {
    $data_origen = json_decode($response_origen, true);
    testMapa("Geocodificación origen exitosa", !empty($data_origen), "Datos: " . json_encode($data_origen[0] ?? []));
} else {
    testMapa("Geocodificación origen exitosa", false, "Sin conexión a Nominatim");
}

// Test geocodificación destino
$response_destino = @file_get_contents($url_destino, false, $context);
if ($response_destino !== false) {
    $data_destino = json_decode($response_destino, true);
    testMapa("Geocodificación destino exitosa", !empty($data_destino), "Datos: " . json_encode($data_destino[0] ?? []));
} else {
    testMapa("Geocodificación destino exitosa", false, "Sin conexión a Nominatim");
}

echo "<h2>📊 5. Verificación de Elementos HTML del Mapa</h2>";

$elementos_html = [
    'mapa-servicio' => 'Contenedor del mapa',
    'distancia-total' => 'Elemento de distancia total',
    'tiempo-estimado' => 'Elemento de tiempo estimado',
    'info-destino' => 'Alerta informativa de destino'
];

foreach ($elementos_html as $elemento => $descripcion) {
    testMapa("Elemento $descripcion", strpos($contenido_detalle, "id=\"$elemento\"") !== false);
}

echo "<h2>🎯 6. Test de Funcionalidad Completa</h2>";

// Crear un test HTML simple para probar el mapa
$test_html = "
<!DOCTYPE html>
<html>
<head>
    <title>Test Mapa GPS</title>
    <link rel='stylesheet' href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' />
    <link rel='stylesheet' href='https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css' />
</head>
<body>
    <div id='mapa-test' style='height: 400px; width: 100%;'></div>
    <div id='distancia-test'>Calculando...</div>
    <div id='tiempo-test'>Calculando...</div>
    
    <script src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'></script>
    <script src='https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js'></script>
    <script>
        // Test básico del mapa
        try {
            const mapa = L.map('mapa-test').setView([25.7945, -109.0000], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapa);
            
            // Test de geocodificación
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=Los+Mochis+Sinaloa&limit=1')
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        document.getElementById('distancia-test').textContent = 'Geocodificación exitosa: ' + data[0].display_name;
                        document.getElementById('tiempo-test').textContent = 'Coordenadas: ' + data[0].lat + ', ' + data[0].lon;
                    }
                })
                .catch(error => {
                    document.getElementById('distancia-test').textContent = 'Error: ' + error.message;
                });
        } catch (error) {
            document.getElementById('distancia-test').textContent = 'Error JavaScript: ' + error.message;
        }
    </script>
</body>
</html>";

file_put_contents('test-mapa-simple.html', $test_html);
testMapa("Archivo de test del mapa creado", file_exists('test-mapa-simple.html'));

echo "<h2>📈 7. Resumen de Tests del Mapa</h2>";

$total_tests = $tests_pasados + $tests_fallidos;
$porcentaje_exito = $total_tests > 0 ? round(($tests_pasados / $total_tests) * 100, 2) : 0;

echo "<div style='background:" . ($porcentaje_exito >= 90 ? '#e8f5e8' : ($porcentaje_exito >= 70 ? '#fff3cd' : '#f8d7da')) . "; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<h3>📋 Resultados de Tests del Mapa</h3>";
echo "<p><strong>Tests pasados:</strong> $tests_pasados</p>";
echo "<p><strong>Tests fallidos:</strong> $tests_fallidos</p>";
echo "<p><strong>Total de tests:</strong> $total_tests</p>";
echo "<p><strong>Porcentaje de éxito:</strong> $porcentaje_exito%</p>";

if ($porcentaje_exito >= 90) {
    echo "<p style='color:green; font-weight:bold;'>🎉 ¡Mapa GPS funcionando excelentemente!</p>";
} elseif ($porcentaje_exito >= 70) {
    echo "<p style='color:orange; font-weight:bold;'>⚠️ Mapa GPS funcionando bien con algunas mejoras necesarias</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>❌ Mapa GPS necesita atención urgente</p>";
}
echo "</div>";

if (!empty($errores)) {
    echo "<h3>❌ Errores Encontrados en el Mapa</h3>";
    echo "<ul>";
    foreach ($errores as $error) {
        echo "<li style='color:red;'>$error</li>";
    }
    echo "</ul>";
}

echo "<h2>🔗 Enlaces de Prueba del Mapa</h2>";
echo "<div style='background:#f0f8ff; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<p><strong>Para probar el mapa GPS:</strong></p>";
echo "<ul>";
echo "<li><a href='detalle-solicitud.php?id=$solicitud_id' target='_blank'>🗺️ Ver Mapa en Detalle de Solicitud (ID: $solicitud_id)</a></li>";
echo "<li><a href='test-mapa-simple.html' target='_blank'>🧪 Test Simple del Mapa</a></li>";
echo "</ul>";
echo "</div>";

echo "<h2>💡 Recomendaciones para el Mapa GPS</h2>";
echo "<div style='background:#e3f2fd; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<ul>";
echo "<li>Verificar que el mapa muestre ambos puntos (origen y destino)</li>";
echo "<li>Probar la funcionalidad de geocodificación con diferentes direcciones</li>";
echo "<li>Verificar que el cálculo de distancia funcione correctamente</li>";
echo "<li>Probar el botón de debug para diagnosticar problemas</li>";
echo "<li>Verificar que la ruta se dibuje correctamente entre puntos</li>";
echo "<li>Probar en diferentes navegadores (Chrome, Firefox, Safari)</li>";
echo "</ul>";
echo "</div>";

$conn->close();
?>
