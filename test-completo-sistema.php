<?php
/**
 * Test Completo del Sistema DBACK
 * Verifica todas las funcionalidades principales
 */

require_once 'conexion.php';

echo "<h1>🧪 Test Completo del Sistema DBACK</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

$tests_pasados = 0;
$tests_fallidos = 0;
$errores = [];

// Función para registrar resultados de tests
function registrarTest($nombre, $exito, $mensaje = '') {
    global $tests_pasados, $tests_fallidos, $errores;
    
    if ($exito) {
        $tests_pasados++;
        echo "<p style='color:green'>✅ $nombre</p>";
    } else {
        $tests_fallidos++;
        $errores[] = $nombre . ": " . $mensaje;
        echo "<p style='color:red'>❌ $nombre - $mensaje</p>";
    }
}

echo "<h2>🔍 1. Verificación de Base de Datos</h2>";

// Test 1: Verificar conexión a base de datos
try {
    $conn->ping();
    registrarTest("Conexión a base de datos", true);
} catch (Exception $e) {
    registrarTest("Conexión a base de datos", false, $e->getMessage());
}

// Test 2: Verificar tablas principales
$tablas_requeridas = ['solicitudes', 'gruas', 'usuarios', 'historial_asignaciones'];
foreach ($tablas_requeridas as $tabla) {
    $result = $conn->query("SHOW TABLES LIKE '$tabla'");
    registrarTest("Tabla $tabla existe", $result->num_rows > 0);
}

// Test 3: Verificar campos críticos en solicitudes
$campos_solicitudes = ['id', 'nombre_completo', 'telefono', 'ubicacion', 'ubicacion_destino', 'tipo_servicio', 'estado'];
$result = $conn->query("DESCRIBE solicitudes");
$campos_existentes = [];
while ($row = $result->fetch_assoc()) {
    $campos_existentes[] = $row['Field'];
}

foreach ($campos_solicitudes as $campo) {
    registrarTest("Campo $campo en solicitudes", in_array($campo, $campos_existentes));
}

// Test 4: Verificar campos críticos en gruas
$campos_gruas = ['ID', 'Placa', 'Marca', 'Modelo', 'Tipo', 'Estado', 'coordenadas_actuales'];
$result = $conn->query("DESCRIBE gruas");
$campos_gruas_existentes = [];
while ($row = $result->fetch_assoc()) {
    $campos_gruas_existentes[] = $row['Field'];
}

foreach ($campos_gruas as $campo) {
    registrarTest("Campo $campo en gruas", in_array($campo, $campos_gruas_existentes));
}

echo "<h2>📊 2. Verificación de Datos</h2>";

// Test 5: Contar solicitudes
$result = $conn->query("SELECT COUNT(*) as total FROM solicitudes");
$total_solicitudes = $result->fetch_assoc()['total'];
registrarTest("Solicitudes en base de datos", $total_solicitudes > 0, "Total: $total_solicitudes");

// Test 6: Contar grúas
$result = $conn->query("SELECT COUNT(*) as total FROM gruas");
$total_gruas = $result->fetch_assoc()['total'];
registrarTest("Grúas en base de datos", $total_gruas > 0, "Total: $total_gruas");

// Test 7: Verificar grúas activas
$result = $conn->query("SELECT COUNT(*) as activas FROM gruas WHERE Estado = 'Activa'");
$gruas_activas = $result->fetch_assoc()['activas'];
registrarTest("Grúas activas disponibles", $gruas_activas > 0, "Activas: $gruas_activas");

// Test 8: Verificar vista gruas_disponibles
$result = $conn->query("SELECT COUNT(*) as disponibles FROM gruas_disponibles");
if ($result) {
    $gruas_disponibles = $result->fetch_assoc()['disponibles'];
    registrarTest("Vista gruas_disponibles funcionando", true, "Disponibles: $gruas_disponibles");
} else {
    registrarTest("Vista gruas_disponibles funcionando", false, $conn->error);
}

// Test 9: Verificar solicitudes con destino
$result = $conn->query("SELECT COUNT(*) as con_destino FROM solicitudes WHERE ubicacion_destino IS NOT NULL AND ubicacion_destino != ''");
$solicitudes_con_destino = $result->fetch_assoc()['con_destino'];
registrarTest("Solicitudes con destino definido", $solicitudes_con_destino > 0, "Con destino: $solicitudes_con_destino");

echo "<h2>🗺️ 3. Verificación de Funcionalidades de Mapa</h2>";

// Test 10: Verificar que las grúas tengan coordenadas
$result = $conn->query("SELECT COUNT(*) as con_coordenadas FROM gruas WHERE coordenadas_actuales IS NOT NULL AND coordenadas_actuales != ''");
$gruas_con_coordenadas = $result->fetch_assoc()['con_coordenadas'];
registrarTest("Grúas con coordenadas GPS", $gruas_con_coordenadas > 0, "Con coordenadas: $gruas_con_coordenadas");

// Test 11: Verificar formato de coordenadas
$result = $conn->query("SELECT coordenadas_actuales FROM gruas WHERE coordenadas_actuales IS NOT NULL LIMIT 5");
$coordenadas_validas = 0;
while ($row = $result->fetch_assoc()) {
    $coords = explode(',', $row['coordenadas_actuales']);
    if (count($coords) == 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
        $coordenadas_validas++;
    }
}
registrarTest("Formato de coordenadas válido", $coordenadas_validas > 0, "Coordenadas válidas: $coordenadas_validas");

echo "<h2>🤖 4. Verificación de Auto-Asignación</h2>";

// Test 12: Verificar tabla historial_asignaciones
$result = $conn->query("SELECT COUNT(*) as total FROM historial_asignaciones");
if ($result) {
    $total_historial = $result->fetch_assoc()['total'];
    registrarTest("Tabla historial_asignaciones funcionando", true, "Registros: $total_historial");
} else {
    registrarTest("Tabla historial_asignaciones funcionando", false, $conn->error);
}

// Test 13: Verificar configuración de auto-asignación
$result = $conn->query("SELECT COUNT(*) as config FROM configuracion_auto_asignacion");
if ($result) {
    $config_existe = $result->fetch_assoc()['config'] > 0;
    registrarTest("Configuración de auto-asignación", $config_existe);
} else {
    registrarTest("Configuración de auto-asignación", false, "Tabla no existe");
}

echo "<h2>📋 5. Verificación de Solicitudes</h2>";

// Test 14: Verificar estados de solicitudes
$estados_validos = ['pendiente', 'asignada', 'en_camino', 'en_proceso', 'completada', 'cancelada'];
$result = $conn->query("SELECT DISTINCT estado FROM solicitudes");
$estados_existentes = [];
while ($row = $result->fetch_assoc()) {
    $estados_existentes[] = $row['estado'];
}

foreach ($estados_validos as $estado) {
    registrarTest("Estado '$estado' válido", in_array($estado, $estados_existentes));
}

// Test 15: Verificar tipos de servicio
$tipos_servicio_validos = ['remolque', 'bateria', 'gasolina', 'llanta', 'arranque', 'otro'];
$result = $conn->query("SELECT DISTINCT tipo_servicio FROM solicitudes");
$tipos_existentes = [];
while ($row = $result->fetch_assoc()) {
    $tipos_existentes[] = $row['tipo_servicio'];
}

foreach ($tipos_servicio_validos as $tipo) {
    registrarTest("Tipo de servicio '$tipo' válido", in_array($tipo, $tipos_existentes));
}

echo "<h2>🚛 6. Verificación de Grúas</h2>";

// Test 16: Verificar tipos de grúas
$tipos_gruas_validos = ['Plataforma', 'Arrastre', 'Remolque'];
$result = $conn->query("SELECT DISTINCT Tipo FROM gruas");
$tipos_gruas_existentes = [];
while ($row = $result->fetch_assoc()) {
    $tipos_gruas_existentes[] = $row['Tipo'];
}

foreach ($tipos_gruas_validos as $tipo) {
    registrarTest("Tipo de grúa '$tipo' válido", in_array($tipo, $tipos_gruas_existentes));
}

// Test 17: Verificar estados de grúas
$estados_gruas_validos = ['Activa', 'Mantenimiento', 'Inactiva'];
$result = $conn->query("SELECT DISTINCT Estado FROM gruas");
$estados_gruas_existentes = [];
while ($row = $result->fetch_assoc()) {
    $estados_gruas_existentes[] = $row['Estado'];
}

foreach ($estados_gruas_validos as $estado) {
    registrarTest("Estado de grúa '$estado' válido", in_array($estado, $estados_gruas_existentes));
}

// Test 18: Verificar placas únicas
$result = $conn->query("SELECT Placa, COUNT(*) as count FROM gruas GROUP BY Placa HAVING count > 1");
$placas_duplicadas = $result->num_rows;
registrarTest("Placas de grúas únicas", $placas_duplicadas == 0, "Placas duplicadas: $placas_duplicadas");

echo "<h2>🔧 7. Verificación de Archivos del Sistema</h2>";

// Test 19: Verificar archivos principales
$archivos_principales = [
    'solicitud.php',
    'detalle-solicitud.php',
    'procesar-solicitud.php',
    'Gruas.php',
    'menu-auto-asignacion.php',
    'configuracion-auto-asignacion.php',
    'AutoAsignacionGruas.php'
];

foreach ($archivos_principales as $archivo) {
    registrarTest("Archivo $archivo existe", file_exists($archivo));
}

// Test 20: Verificar archivos de configuración
$archivos_config = [
    'conexion.php',
    'config.php',
    'index.html'
];

foreach ($archivos_config as $archivo) {
    registrarTest("Archivo de configuración $archivo existe", file_exists($archivo));
}

echo "<h2>🌐 8. Verificación de APIs Externas</h2>";

// Test 21: Verificar conectividad a Nominatim
$url = "https://nominatim.openstreetmap.org/search?format=json&q=Los+Mochis+Sinaloa&limit=1";
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'header' => "User-Agent: DBACK-Test/1.0\r\n"
    ]
]);

$response = @file_get_contents($url, false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    registrarTest("API Nominatim funcionando", !empty($data));
} else {
    registrarTest("API Nominatim funcionando", false, "Sin conexión");
}

echo "<h2>📈 9. Estadísticas del Sistema</h2>";

// Mostrar estadísticas detalladas
echo "<div style='background:#f0f8ff; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<h3>📊 Resumen de Datos</h3>";

$stats = [
    'Solicitudes totales' => $total_solicitudes,
    'Solicitudes con destino' => $solicitudes_con_destino,
    'Grúas totales' => $total_gruas,
    'Grúas activas' => $gruas_activas,
    'Grúas con coordenadas' => $gruas_con_coordenadas,
    'Coordenadas válidas' => $coordenadas_validas
];

foreach ($stats as $nombre => $valor) {
    echo "<p><strong>$nombre:</strong> $valor</p>";
}
echo "</div>";

echo "<h2>🎯 10. Resumen de Tests</h2>";

$total_tests = $tests_pasados + $tests_fallidos;
$porcentaje_exito = $total_tests > 0 ? round(($tests_pasados / $total_tests) * 100, 2) : 0;

echo "<div style='background:" . ($porcentaje_exito >= 90 ? '#e8f5e8' : ($porcentaje_exito >= 70 ? '#fff3cd' : '#f8d7da')) . "; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<h3>📋 Resultados Finales</h3>";
echo "<p><strong>Tests pasados:</strong> $tests_pasados</p>";
echo "<p><strong>Tests fallidos:</strong> $tests_fallidos</p>";
echo "<p><strong>Total de tests:</strong> $total_tests</p>";
echo "<p><strong>Porcentaje de éxito:</strong> $porcentaje_exito%</p>";

if ($porcentaje_exito >= 90) {
    echo "<p style='color:green; font-weight:bold;'>🎉 ¡Sistema funcionando excelentemente!</p>";
} elseif ($porcentaje_exito >= 70) {
    echo "<p style='color:orange; font-weight:bold;'>⚠️ Sistema funcionando bien con algunas mejoras necesarias</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>❌ Sistema necesita atención urgente</p>";
}
echo "</div>";

if (!empty($errores)) {
    echo "<h3>❌ Errores Encontrados</h3>";
    echo "<ul>";
    foreach ($errores as $error) {
        echo "<li style='color:red;'>$error</li>";
    }
    echo "</ul>";
}

echo "<h2>🔗 Enlaces de Verificación</h2>";
echo "<p><a href='index.html' target='_blank'>🏠 Página Principal</a></p>";
echo "<p><a href='solicitud.php' target='_blank'>📝 Nueva Solicitud</a></p>";
echo "<p><a href='procesar-solicitud.php' target='_blank'>📋 Procesar Solicitudes</a></p>";
echo "<p><a href='detalle-solicitud.php?id=17' target='_blank'>🔍 Detalle de Solicitud (ID: 17)</a></p>";
echo "<p><a href='Gruas.php' target='_blank'>🚛 Gestión de Grúas</a></p>";
echo "<p><a href='menu-auto-asignacion.php' target='_blank'>🤖 Auto-Asignación</a></p>";
echo "<p><a href='configuracion-auto-asignacion.php' target='_blank'>⚙️ Configuración</a></p>";

echo "<h2>💡 Recomendaciones</h2>";
echo "<div style='background:#e3f2fd; padding:15px; border-radius:8px; margin:10px 0;'>";

if ($porcentaje_exito >= 90) {
    echo "<p>✅ El sistema está funcionando correctamente. Recomendaciones:</p>";
    echo "<ul>";
    echo "<li>Realizar pruebas de carga con múltiples usuarios</li>";
    echo "<li>Configurar respaldos automáticos de la base de datos</li>";
    echo "<li>Monitorear el rendimiento del sistema de auto-asignación</li>";
    echo "<li>Implementar logs de auditoría para seguimiento</li>";
    echo "</ul>";
} else {
    echo "<p>⚠️ Se encontraron problemas que requieren atención:</p>";
    echo "<ul>";
    if ($tests_fallidos > 0) {
        echo "<li>Revisar los errores listados arriba</li>";
        echo "<li>Verificar la configuración de la base de datos</li>";
        echo "<li>Comprobar la conectividad de red</li>";
    }
    echo "<li>Ejecutar este test periódicamente para monitoreo</li>";
    echo "</ul>";
}
echo "</div>";

$conn->close();
?>
