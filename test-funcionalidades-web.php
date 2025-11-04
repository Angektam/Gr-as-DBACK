<?php
/**
 * Test de Funcionalidades Web del Sistema DBACK
 * Prueba las páginas principales y sus funcionalidades
 */

echo "<h1>🌐 Test de Funcionalidades Web - Sistema DBACK</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

$tests_pasados = 0;
$tests_fallidos = 0;
$errores = [];

function testWeb($nombre, $url, $contenido_esperado = null) {
    global $tests_pasados, $tests_fallidos, $errores;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header' => "User-Agent: DBACK-Test/1.0\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        $tests_fallidos++;
        $errores[] = "$nombre: No se pudo cargar la página";
        echo "<p style='color:red'>❌ $nombre - No se pudo cargar</p>";
        return false;
    }
    
    $exito = true;
    if ($contenido_esperado) {
        if (strpos($response, $contenido_esperado) === false) {
            $exito = false;
            $errores[] = "$nombre: No se encontró el contenido esperado '$contenido_esperado'";
        }
    }
    
    if ($exito) {
        $tests_pasados++;
        echo "<p style='color:green'>✅ $nombre</p>";
    } else {
        $tests_fallidos++;
        echo "<p style='color:red'>❌ $nombre - Contenido no encontrado</p>";
    }
    
    return $exito;
}

echo "<h2>🏠 1. Páginas Principales</h2>";

// Test 1: Página principal
testWeb("Página Principal (index.html)", "http://localhost/DBACK-main/index.html", "DBACK");

// Test 2: Nueva solicitud
testWeb("Nueva Solicitud", "http://localhost/DBACK-main/solicitud.php", "Nueva Solicitud");

// Test 3: Procesar solicitudes
testWeb("Procesar Solicitudes", "http://localhost/DBACK-main/procesar-solicitud.php", "Solicitudes");

// Test 4: Gestión de grúas
testWeb("Gestión de Grúas", "http://localhost/DBACK-main/Gruas.php", "Grúas");

echo "<h2>🤖 2. Sistema de Auto-Asignación</h2>";

// Test 5: Menú auto-asignación
testWeb("Menú Auto-Asignación", "http://localhost/DBACK-main/menu-auto-asignacion.php", "Auto-Asignación");

// Test 6: Configuración auto-asignación
testWeb("Configuración Auto-Asignación", "http://localhost/DBACK-main/configuracion-auto-asignacion.php", "Configuración");

echo "<h2>🗺️ 3. Funcionalidades de Mapa</h2>";

// Test 7: Detalle de solicitud (con mapa)
testWeb("Detalle de Solicitud con Mapa", "http://localhost/DBACK-main/detalle-solicitud.php?id=17", "Mapa GPS");

echo "<h2>📊 4. Verificación de Contenido Específico</h2>";

// Test 8: Verificar que las páginas contengan elementos específicos
$paginas_especificas = [
    "solicitud.php" => ["form", "ubicacion_origen", "ubicacion_destino"],
    "procesar-solicitud.php" => ["table", "solicitud"],
    "Gruas.php" => ["table", "Placa", "Marca"],
    "menu-auto-asignacion.php" => ["historial", "asignaciones"],
    "detalle-solicitud.php?id=17" => ["mapa-servicio", "distancia-total"]
];

foreach ($paginas_especificas as $pagina => $elementos) {
    $url = "http://localhost/DBACK-main/$pagina";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header' => "User-Agent: DBACK-Test/1.0\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $todos_elementos = true;
        foreach ($elementos as $elemento) {
            if (strpos($response, $elemento) === false) {
                $todos_elementos = false;
                break;
            }
        }
        
        if ($todos_elementos) {
            $tests_pasados++;
            echo "<p style='color:green'>✅ $pagina - Elementos encontrados</p>";
        } else {
            $tests_fallidos++;
            $errores[] = "$pagina: Elementos específicos no encontrados";
            echo "<p style='color:red'>❌ $pagina - Elementos no encontrados</p>";
        }
    } else {
        $tests_fallidos++;
        $errores[] = "$pagina: No se pudo cargar";
        echo "<p style='color:red'>❌ $pagina - No se pudo cargar</p>";
    }
}

echo "<h2>🔧 5. Verificación de Recursos</h2>";

// Test 9: Verificar archivos CSS y JS
$recursos = [
    "CSS/panel-solicitud.CSS",
    "CSS/styles.css",
    "index-styles.css"
];

foreach ($recursos as $recurso) {
    if (file_exists($recurso)) {
        $tests_pasados++;
        echo "<p style='color:green'>✅ Recurso $recurso existe</p>";
    } else {
        $tests_fallidos++;
        $errores[] = "Recurso $recurso no encontrado";
        echo "<p style='color:red'>❌ Recurso $recurso no encontrado</p>";
    }
}

echo "<h2>📱 6. Verificación de Responsividad</h2>";

// Test 10: Verificar meta viewport en páginas principales
$paginas_responsive = [
    "index.html",
    "solicitud.php",
    "detalle-solicitud.php"
];

foreach ($paginas_responsive as $pagina) {
    $url = "http://localhost/DBACK-main/$pagina";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header' => "User-Agent: DBACK-Test/1.0\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        if (strpos($response, 'viewport') !== false || strpos($response, 'responsive') !== false) {
            $tests_pasados++;
            echo "<p style='color:green'>✅ $pagina - Responsive</p>";
        } else {
            $tests_fallidos++;
            $errores[] = "$pagina: No tiene configuración responsive";
            echo "<p style='color:red'>❌ $pagina - No responsive</p>";
        }
    }
}

echo "<h2>🔒 7. Verificación de Seguridad</h2>";

// Test 11: Verificar que no haya información sensible expuesta
$paginas_seguras = [
    "solicitud.php",
    "procesar-solicitud.php",
    "detalle-solicitud.php"
];

foreach ($paginas_seguras as $pagina) {
    $url = "http://localhost/DBACK-main/$pagina";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header' => "User-Agent: DBACK-Test/1.0\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $info_sensible = ['password', 'secret', 'key', 'token'];
        $encontrado = false;
        
        foreach ($info_sensible as $sensible) {
            if (stripos($response, $sensible) !== false) {
                $encontrado = true;
                break;
            }
        }
        
        if (!$encontrado) {
            $tests_pasados++;
            echo "<p style='color:green'>✅ $pagina - Segura</p>";
        } else {
            $tests_fallidos++;
            $errores[] = "$pagina: Posible información sensible expuesta";
            echo "<p style='color:red'>❌ $pagina - Posible información sensible</p>";
        }
    }
}

echo "<h2>📈 8. Resumen de Tests Web</h2>";

$total_tests = $tests_pasados + $tests_fallidos;
$porcentaje_exito = $total_tests > 0 ? round(($tests_pasados / $total_tests) * 100, 2) : 0;

echo "<div style='background:" . ($porcentaje_exito >= 90 ? '#e8f5e8' : ($porcentaje_exito >= 70 ? '#fff3cd' : '#f8d7da')) . "; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<h3>📋 Resultados de Tests Web</h3>";
echo "<p><strong>Tests pasados:</strong> $tests_pasados</p>";
echo "<p><strong>Tests fallidos:</strong> $tests_fallidos</p>";
echo "<p><strong>Total de tests:</strong> $total_tests</p>";
echo "<p><strong>Porcentaje de éxito:</strong> $porcentaje_exito%</p>";

if ($porcentaje_exito >= 90) {
    echo "<p style='color:green; font-weight:bold;'>🎉 ¡Sistema web funcionando excelentemente!</p>";
} elseif ($porcentaje_exito >= 70) {
    echo "<p style='color:orange; font-weight:bold;'>⚠️ Sistema web funcionando bien con algunas mejoras necesarias</p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>❌ Sistema web necesita atención urgente</p>";
}
echo "</div>";

if (!empty($errores)) {
    echo "<h3>❌ Errores Encontrados en Tests Web</h3>";
    echo "<ul>";
    foreach ($errores as $error) {
        echo "<li style='color:red;'>$error</li>";
    }
    echo "</ul>";
}

echo "<h2>🔗 Enlaces de Prueba Manual</h2>";
echo "<div style='background:#f0f8ff; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<p><strong>Para pruebas manuales, visita estos enlaces:</strong></p>";
echo "<ul>";
echo "<li><a href='index.html' target='_blank'>🏠 Página Principal</a></li>";
echo "<li><a href='solicitud.php' target='_blank'>📝 Nueva Solicitud</a></li>";
echo "<li><a href='procesar-solicitud.php' target='_blank'>📋 Procesar Solicitudes</a></li>";
echo "<li><a href='detalle-solicitud.php?id=17' target='_blank'>🔍 Detalle de Solicitud (ID: 17)</a></li>";
echo "<li><a href='Gruas.php' target='_blank'>🚛 Gestión de Grúas</a></li>";
echo "<li><a href='menu-auto-asignacion.php' target='_blank'>🤖 Auto-Asignación</a></li>";
echo "<li><a href='configuracion-auto-asignacion.php' target='_blank'>⚙️ Configuración</a></li>";
echo "</ul>";
echo "</div>";

echo "<h2>💡 Recomendaciones para Tests Web</h2>";
echo "<div style='background:#e3f2fd; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<ul>";
echo "<li>Probar el formulario de nueva solicitud con datos reales</li>";
echo "<li>Verificar que el mapa GPS funcione correctamente</li>";
echo "<li>Probar la funcionalidad de auto-asignación</li>";
echo "<li>Verificar que todas las páginas se vean bien en móviles</li>";
echo "<li>Probar la navegación entre páginas</li>";
echo "<li>Verificar que los formularios validen correctamente</li>";
echo "</ul>";
echo "</div>";
?>
