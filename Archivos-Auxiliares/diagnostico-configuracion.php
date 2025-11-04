<?php
/**
 * Script de diagnóstico para verificar configuración PHP
 */

echo "<h1>🔍 Diagnóstico de Configuración PHP</h1>";
echo "<style>
body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
.container{max-width:1200px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#28a745;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}
.error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}
.info{color:#17a2b8;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0;}
.btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin:5px;text-decoration:none;display:inline-block;}
.btn:hover{background:#0056b3;}
table{border-collapse:collapse;width:100%;margin:10px 0;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background-color:#f2f2f2;}
</style>";

echo "<div class='container'>";

// Incluir configuración mejorada
require_once 'config-solicitud-mejorado.php';

echo "<h2>📊 Configuración PHP Actual</h2>";

$limites = verificarLimitesPHP();
echo "<table>";
echo "<tr><th>Configuración</th><th>Valor Actual</th><th>Estado</th></tr>";

foreach ($limites as $clave => $valor) {
    $estado = "✅ OK";
    $color = "success";
    
    switch ($clave) {
        case 'post_max_size':
            $bytes = convertirTamañoABytes($valor);
            if ($bytes < 50 * 1024 * 1024) { // Menos de 50MB
                $estado = "⚠️ Bajo";
                $color = "warning";
            }
            break;
        case 'upload_max_filesize':
            $bytes = convertirTamañoABytes($valor);
            if ($bytes < 20 * 1024 * 1024) { // Menos de 20MB
                $estado = "⚠️ Bajo";
                $color = "warning";
            }
            break;
        case 'memory_limit':
            $bytes = convertirTamañoABytes($valor);
            if ($bytes < 512 * 1024 * 1024) { // Menos de 512MB
                $estado = "⚠️ Bajo";
                $color = "warning";
            }
            break;
        case 'max_execution_time':
            if ($valor < 600) { // Menos de 10 minutos
                $estado = "⚠️ Bajo";
                $color = "warning";
            }
            break;
    }
    
    echo "<tr>";
    echo "<td><strong>$clave</strong></td>";
    echo "<td>$valor</td>";
    echo "<td class='$color'>$estado</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🔍 Verificación de POST</h2>";

$verificacionPOST = verificarTamañoPOST();
if ($verificacionPOST['valido']) {
    echo "<div class='success'>";
    echo "<h3>✅ POST Válido</h3>";
    echo "<p>" . $verificacionPOST['mensaje'] . "</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ POST Inválido</h3>";
    echo "<p>" . $verificacionPOST['mensaje'] . "</p>";
    echo "</div>";
}

echo "<h2>📁 Información del Servidor</h2>";

echo "<table>";
echo "<tr><th>Variable</th><th>Valor</th></tr>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Script Name</td><td>" . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Request Method</td><td>" . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Content Length</td><td>" . ($_SERVER['CONTENT_LENGTH'] ?? '0') . " bytes</td></tr>";
echo "<tr><td>Content Type</td><td>" . ($_SERVER['CONTENT_TYPE'] ?? 'Unknown') . "</td></tr>";
echo "</table>";

echo "<h2>🧪 Prueba de Archivos</h2>";

// Crear directorio de prueba si no existe
$testDir = "test_uploads";
if (!file_exists($testDir)) {
    mkdir($testDir, 0755, true);
}

echo "<div class='info'>";
echo "<h3>Directorio de Prueba:</h3>";
echo "<p>Directorio creado: <code>$testDir</code></p>";
echo "<p>Permisos: " . substr(sprintf('%o', fileperms($testDir)), -4) . "</p>";
echo "<p>Escribible: " . (is_writable($testDir) ? "✅ Sí" : "❌ No") . "</p>";
echo "</div>";

echo "<h2>📝 Logs del Sistema</h2>";

$logFiles = ['error_log.txt', 'activity_log.txt'];
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $size = filesize($logFile);
        $lastModified = date('Y-m-d H:i:s', filemtime($logFile));
        echo "<div class='info'>";
        echo "<h3>📄 $logFile</h3>";
        echo "<p>Tamaño: " . round($size / 1024, 2) . " KB</p>";
        echo "<p>Última modificación: $lastModified</p>";
        echo "<p>Últimas 5 líneas:</p>";
        echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;max-height:150px;overflow-y:auto;'>";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -5);
        foreach ($lastLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div class='warning'>";
        echo "<h3>📄 $logFile</h3>";
        echo "<p>Archivo no existe aún</p>";
        echo "</div>";
    }
}

echo "<h2>🔧 Recomendaciones</h2>";

echo "<div class='warning'>";
echo "<h3>Si los límites son bajos:</h3>";
echo "<ol>";
echo "<li><strong>Archivo php.ini:</strong> Editar directamente el archivo de configuración PHP</li>";
echo "<li><strong>Archivo .htaccess:</strong> Usar las directivas incluidas en el proyecto</li>";
echo "<li><strong>Servidor web:</strong> Configurar límites a nivel de Apache/Nginx</li>";
echo "<li><strong>Contactar hosting:</strong> Solicitar aumento de límites al proveedor</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🚀 Enlaces de Prueba</h2>";
echo "<p><a href='solicitud.php' target='_blank' class='btn'>📝 Probar Formulario</a></p>";
echo "<p><a href='verificar-correcciones-solicitud.php' target='_blank' class='btn'>🔍 Ver Correcciones</a></p>";

echo "<h2>📋 Información de Debug</h2>";

echo "<div class='info'>";
echo "<h3>Variables $_SERVER relevantes:</h3>";
echo "<pre style='background:#f8f9fa;padding:10px;border-radius:5px;max-height:200px;overflow-y:auto;'>";
$relevantVars = [
    'CONTENT_LENGTH',
    'CONTENT_TYPE',
    'REQUEST_METHOD',
    'HTTP_USER_AGENT',
    'REMOTE_ADDR',
    'SERVER_SOFTWARE',
    'DOCUMENT_ROOT'
];

foreach ($relevantVars as $var) {
    if (isset($_SERVER[$var])) {
        echo "$var: " . htmlspecialchars($_SERVER[$var]) . "\n";
    }
}
echo "</pre>";
echo "</div>";

echo "<h2>✅ Estado del Diagnóstico</h2>";

$allGood = true;
foreach ($limites as $clave => $valor) {
    if ($clave === 'post_max_size' && convertirTamañoABytes($valor) < 50 * 1024 * 1024) {
        $allGood = false;
    }
    if ($clave === 'upload_max_filesize' && convertirTamañoABytes($valor) < 20 * 1024 * 1024) {
        $allGood = false;
    }
    if ($clave === 'memory_limit' && convertirTamañoABytes($valor) < 512 * 1024 * 1024) {
        $allGood = false;
    }
}

if ($allGood && $verificacionPOST['valido']) {
    echo "<div class='success'>";
    echo "<h3>🎉 ¡Configuración Óptima!</h3>";
    echo "<p>Todos los límites están configurados correctamente para manejar archivos grandes.</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ Configuración Necesita Ajustes</h3>";
    echo "<p>Algunos límites necesitan ser aumentados para manejar archivos grandes correctamente.</p>";
    echo "</div>";
}

echo "</div>";
?>
