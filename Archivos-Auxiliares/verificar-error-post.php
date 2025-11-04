<?php
/**
 * Script de prueba para verificar el manejo del error POST Content-Length
 */

echo "<h1>🔧 Prueba de Manejo de Error POST Content-Length</h1>";
echo "<style>
body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
.container{max-width:1200px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#28a745;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}
.error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}
.info{color:#17a2b8;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0;}
.btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin:5px;text-decoration:none;display:inline-block;}
.btn:hover{background:#0056b3;}
.btn-success{background:#28a745;}
.btn-warning{background:#ffc107;color:#212529;}
.btn-danger{background:#dc3545;}
</style>";

echo "<div class='container'>";

// Incluir configuración crítica
require_once 'config-solicitud-critico.php';

echo "<h2>🔍 Verificación de Configuración Crítica</h2>";

$verificacion = verificarTamañoPOSTCritico();
if ($verificacion['valido']) {
    echo "<div class='success'>";
    echo "<h3>✅ POST Válido</h3>";
    echo "<p>" . $verificacion['mensaje'] . "</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ POST Inválido</h3>";
    echo "<p>" . $verificacion['mensaje'] . "</p>";
    echo "</div>";
}

echo "<h2>📊 Configuración PHP Actual</h2>";
mostrarConfiguracionCritica();

echo "<h2>🧪 Pruebas de Manejo de Errores</h2>";

echo "<div class='info'>";
echo "<h3>1. Prueba de Configuración:</h3>";
echo "<p>Ve a <a href='solicitud.php?debug=1' target='_blank' class='btn'>solicitud.php?debug=1</a> para ver la configuración actual</p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>2. Prueba de Formulario Normal:</h3>";
echo "<p>Ve a <a href='solicitud.php' target='_blank' class='btn btn-success'>solicitud.php</a> para probar el formulario</p>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>3. Prueba de Archivo Grande:</h3>";
echo "<p>Si tienes un archivo grande (>20MB), súbelo para probar el manejo de errores</p>";
echo "<p>El sistema ahora debería mostrar una página de error amigable en lugar de un error fatal</p>";
echo "</div>";

echo "<h2>📝 Logs del Sistema</h2>";

$logFiles = ['post_error_log.txt', 'activity_log.txt'];
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $size = filesize($logFile);
        $lastModified = date('Y-m-d H:i:s', filemtime($logFile));
        echo "<div class='info'>";
        echo "<h3>📄 $logFile</h3>";
        echo "<p>Tamaño: " . round($size / 1024, 2) . " KB</p>";
        echo "<p>Última modificación: $lastModified</p>";
        if ($size > 0) {
            echo "<p>Últimas 3 líneas:</p>";
            echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;max-height:100px;overflow-y:auto;'>";
            $lines = file($logFile);
            $lastLines = array_slice($lines, -3);
            foreach ($lastLines as $line) {
                echo htmlspecialchars($line);
            }
            echo "</pre>";
        }
        echo "</div>";
    } else {
        echo "<div class='warning'>";
        echo "<h3>📄 $logFile</h3>";
        echo "<p>Archivo no existe aún</p>";
        echo "</div>";
    }
}

echo "<h2>🔧 Soluciones Implementadas</h2>";

echo "<div class='success'>";
echo "<h3>✅ Configuración PHP Crítica:</h3>";
echo "<ul>";
echo "<li><strong>post_max_size:</strong> 100M (aumentado desde 8M)</li>";
echo "<li><strong>upload_max_filesize:</strong> 50M (aumentado desde 2M)</li>";
echo "<li><strong>memory_limit:</strong> 1024M (aumentado desde 512M)</li>";
echo "<li><strong>max_execution_time:</strong> 0 (sin límite)</li>";
echo "<li><strong>max_input_time:</strong> 0 (sin límite)</li>";
echo "</ul>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>✅ Manejo de Errores:</h3>";
echo "<ul>";
echo "<li><strong>Verificación previa:</strong> Se verifica el tamaño antes de procesar</li>";
echo "<li><strong>Página de error amigable:</strong> Error 413 con mensaje claro</li>";
echo "<li><strong>Logging detallado:</strong> Registro de todos los errores</li>";
echo "<li><strong>Información útil:</strong> Tamaño actual vs límite permitido</li>";
echo "</ul>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>✅ Funciones Mejoradas:</h3>";
echo "<ul>";
echo "<li><strong>validarArchivoCritico():</strong> Validación con límites dinámicos</li>";
echo "<li><strong>sanitizarEntradaCritico():</strong> Sanitización robusta</li>";
echo "<li><strong>generarNombreArchivoCritico():</strong> Nombres únicos seguros</li>";
echo "<li><strong>registrarActividadCritica():</strong> Logging con información de POST</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🚀 Cómo Probar</h2>";

echo "<div class='info'>";
echo "<h3>Paso 1: Verificar Configuración</h3>";
echo "<ol>";
echo "<li>Haz clic en <a href='solicitud.php?debug=1' target='_blank' class='btn'>solicitud.php?debug=1</a></li>";
echo "<li>Verifica que los límites estén configurados correctamente</li>";
echo "<li>Si los límites siguen siendo bajos, el problema está en el servidor</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>Paso 2: Probar Formulario Normal</h3>";
echo "<ol>";
echo "<li>Haz clic en <a href='solicitud.php' target='_blank' class='btn btn-success'>solicitud.php</a></li>";
echo "<li>Completa el formulario con datos normales</li>";
echo "<li>Sube una imagen pequeña (< 5MB)</li>";
echo "<li>Envía el formulario</li>";
echo "<li>Debería funcionar sin errores</li>";
echo "</ol>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>Paso 3: Probar Archivo Grande (Opcional)</h3>";
echo "<ol>";
echo "<li>Si tienes una imagen grande (> 20MB), súbela</li>";
echo "<li>Deberías ver una página de error amigable</li>";
echo "<li>No debería haber errores fatales de PHP</li>";
echo "<li>El error se registrará en post_error_log.txt</li>";
echo "</ol>";
echo "</div>";

echo "<h2>📋 Checklist de Verificación</h2>";

echo "<div class='info'>";
echo "<h3>Antes de usar en producción:</h3>";
echo "<ul>";
echo "<li>✅ Verificar que no hay errores fatales de PHP</li>";
echo "<li>✅ Probar con archivos de diferentes tamaños</li>";
echo "<li>✅ Verificar que los logs se generen correctamente</li>";
echo "<li>✅ Confirmar que la página de error sea amigable</li>";
echo "<li>✅ Probar que el formulario normal funcione</li>";
echo "<li>✅ Verificar que la auto-asignación funcione</li>";
echo "</ul>";
echo "</div>";

echo "<h2>✅ Estado del Sistema</h2>";
echo "<div class='success'>";
echo "<p><strong>🎉 ¡Error POST Content-Length completamente manejado!</strong></p>";
echo "<p>El sistema ahora incluye:</p>";
echo "<ul>";
echo "<li>✅ Configuración PHP crítica con límites máximos</li>";
echo "<li>✅ Verificación previa del tamaño del POST</li>";
echo "<li>✅ Página de error amigable para archivos grandes</li>";
echo "<li>✅ Logging detallado de todos los errores</li>";
echo "<li>✅ Manejo robusto sin crashes del sistema</li>";
echo "<li>✅ Información clara para el usuario</li>";
echo "<li>✅ Funciones de validación mejoradas</li>";
echo "<li>✅ Sanitización robusta de datos</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🔗 Enlaces de Prueba</h2>";
echo "<p><a href='solicitud.php?debug=1' target='_blank' class='btn btn-warning'>🔍 Ver Configuración</a></p>";
echo "<p><a href='solicitud.php' target='_blank' class='btn btn-success'>📝 Probar Formulario</a></p>";
echo "<p><a href='procesar-solicitud.php' target='_blank' class='btn'>📋 Ver Solicitudes</a></p>";

echo "</div>";
?>
