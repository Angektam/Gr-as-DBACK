<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificador de Sesiones - DBACK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
        }
        .container {
            max-width: 900px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .file-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .file-item.ok {
            background: #d4edda;
            border-left: 5px solid #28a745;
        }
        .file-item.warning {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
        }
        .file-item.error {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
        }
        .code-snippet {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 10px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">
            <i class="fas fa-shield-alt"></i> Verificador de Sesiones
        </h1>
        <p class="text-center text-muted mb-4">
            Detecta problemas de <code>session_start()</code> duplicado en archivos PHP
        </p>
        
        <?php
        $directorio = __DIR__;
        $archivos_php = glob($directorio . '/*.php');
        
        $problemas = [];
        $correctos = [];
        $advertencias = [];
        
        foreach ($archivos_php as $archivo) {
            $nombre = basename($archivo);
            
            // Excluir archivos que no necesitan verificación
            $excluir = [
                'verificar-sesiones.php',
                'config.php',
                'Login.php',
                'cerrar_sesion.php',
                'instalar-',
                'verificar-',
                'fix-',
                '-backup-',
                'prueba-',
                'test-',
                'Test.php'
            ];
            
            $excluido = false;
            foreach ($excluir as $patron) {
                if (strpos($nombre, $patron) !== false) {
                    $excluido = true;
                    break;
                }
            }
            
            if ($excluido) continue;
            
            // Leer el contenido del archivo
            $contenido = file_get_contents($archivo);
            $lineas = file($archivo);
            
            // Buscar si incluye conexion.php o config.php
            $incluye_conexion = preg_match('/require.*[\'"]conexion\.php[\'"]/i', $contenido);
            $incluye_config = preg_match('/require.*[\'"]config\.php[\'"]/i', $contenido);
            
            // Buscar session_start()
            $tiene_session_start = preg_match('/session_start\s*\(\s*\)/i', $contenido);
            
            if ($incluye_conexion && $tiene_session_start) {
                // PROBLEMA: Incluye conexion.php Y tiene session_start()
                $linea_session = 0;
                foreach ($lineas as $num => $linea) {
                    if (preg_match('/session_start\s*\(\s*\)/i', $linea)) {
                        $linea_session = $num + 1;
                        break;
                    }
                }
                
                $problemas[] = [
                    'archivo' => $nombre,
                    'linea' => $linea_session,
                    'mensaje' => 'Tiene session_start() después de incluir conexion.php'
                ];
            } elseif ($incluye_conexion && !$tiene_session_start) {
                // CORRECTO: Incluye conexion.php sin session_start()
                $correctos[] = [
                    'archivo' => $nombre,
                    'mensaje' => 'Usa sesión centralizada correctamente'
                ];
            } elseif (!$incluye_conexion && $tiene_session_start) {
                // ADVERTENCIA: Maneja su propia sesión
                $advertencias[] = [
                    'archivo' => $nombre,
                    'mensaje' => 'Maneja su propia sesión (no usa conexion.php)'
                ];
            } else {
                // No usa sesiones
                $correctos[] = [
                    'archivo' => $nombre,
                    'mensaje' => 'No requiere sesión'
                ];
            }
        }
        
        // Mostrar problemas
        if (!empty($problemas)) {
            echo "<div class='alert alert-danger'>";
            echo "<h4><i class='fas fa-exclamation-triangle'></i> Problemas Encontrados</h4>";
            echo "<p>Los siguientes archivos tienen <code>session_start()</code> duplicado:</p>";
            echo "</div>";
            
            foreach ($problemas as $problema) {
                echo "<div class='file-item error'>";
                echo "<strong>❌ {$problema['archivo']}</strong> (Línea {$problema['linea']})<br>";
                echo "<small>{$problema['mensaje']}</small>";
                echo "<div class='code-snippet'>";
                echo "// Solución: Eliminar session_start() de este archivo<br>";
                echo "require_once 'conexion.php';<br>";
                echo "<span style='color: #ff6b6b;'>// session_start(); // ← ELIMINAR ESTA LÍNEA</span><br>";
                echo "// La sesión ya se inicia en config.php";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-success'>";
            echo "<h4><i class='fas fa-check-circle'></i> ¡Sin Problemas!</h4>";
            echo "<p>No se encontraron problemas de sesión duplicada.</p>";
            echo "</div>";
        }
        
        // Mostrar advertencias
        if (!empty($advertencias)) {
            echo "<h5 class='mt-4'><i class='fas fa-info-circle'></i> Advertencias</h5>";
            foreach ($advertencias as $adv) {
                echo "<div class='file-item warning'>";
                echo "<strong>⚠️ {$adv['archivo']}</strong><br>";
                echo "<small>{$adv['mensaje']}</small>";
                echo "</div>";
            }
        }
        
        // Mostrar archivos correctos
        if (!empty($correctos)) {
            echo "<h5 class='mt-4'><i class='fas fa-check'></i> Archivos Correctos</h5>";
            foreach ($correctos as $correcto) {
                echo "<div class='file-item ok'>";
                echo "<strong>✅ {$correcto['archivo']}</strong><br>";
                echo "<small>{$correcto['mensaje']}</small>";
                echo "</div>";
            }
        }
        
        // Resumen
        echo "<div class='mt-4 p-4 rounded' style='background: #e9ecef;'>";
        echo "<h5>📊 Resumen</h5>";
        echo "<div class='row'>";
        echo "<div class='col-md-4'>";
        echo "<p class='text-danger'><strong>❌ Problemas:</strong> " . count($problemas) . "</p>";
        echo "</div>";
        echo "<div class='col-md-4'>";
        echo "<p class='text-warning'><strong>⚠️ Advertencias:</strong> " . count($advertencias) . "</p>";
        echo "</div>";
        echo "<div class='col-md-4'>";
        echo "<p class='text-success'><strong>✅ Correctos:</strong> " . count($correctos) . "</p>";
        echo "</div>";
        echo "</div>";
        
        if (count($problemas) == 0) {
            echo "<div class='alert alert-success mt-3'>";
            echo "<strong>🎉 Excelente!</strong> Todos los archivos están configurados correctamente.";
            echo "</div>";
        } else {
            echo "<div class='alert alert-danger mt-3'>";
            echo "<strong>⚠️ Acción Requerida:</strong> Corrige los archivos con problemas para evitar errores de sesión.";
            echo "</div>";
        }
        echo "</div>";
        ?>
        
        <div class='mt-4 p-4 rounded' style='background: #e3f2fd;'>
            <h5><i class='fas fa-lightbulb'></i> Regla de Oro</h5>
            <p><strong>Si tu archivo incluye <code>conexion.php</code>, NO uses <code>session_start()</code></strong></p>
            <p class='mb-0'>La sesión ya se inicia automáticamente en <code>config.php</code></p>
        </div>
        
        <div class="text-center mt-4">
            <a href="MenuAdmin.PHP" class="btn btn-primary">
                <i class="fas fa-home"></i> Volver al Menú
            </a>
            <a href="CORRECCIONES-SESION.md" class="btn btn-info" target="_blank">
                <i class="fas fa-book"></i> Ver Documentación
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

