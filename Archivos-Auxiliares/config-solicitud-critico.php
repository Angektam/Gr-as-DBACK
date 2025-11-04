<?php
/**
 * Solución definitiva para el error de POST Content-Length
 * Este archivo debe ser incluido ANTES de cualquier procesamiento
 */

// Configuración crítica - aplicar inmediatamente
ini_set('post_max_size', '100M');          // 100MB para POST
ini_set('upload_max_filesize', '50M');     // 50MB para archivos individuales
ini_set('max_file_uploads', '50');         // Máximo número de archivos
ini_set('max_execution_time', 0);          // Sin límite de tiempo
ini_set('max_input_time', 0);              // Sin límite para procesar entrada
ini_set('memory_limit', '1024M');          // 1GB de memoria
ini_set('max_input_vars', 50000);          // Máximo número de variables

// Configuración adicional crítica
ini_set('file_uploads', '1');              // Habilitar subida de archivos
ini_set('upload_tmp_dir', sys_get_temp_dir()); // Directorio temporal
ini_set('default_socket_timeout', 600);    // Timeout de socket
ini_set('max_input_nesting_level', 128);   // Nivel de anidamiento

// Configurar manejo de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Función para verificar si el POST es demasiado grande ANTES de procesarlo
function verificarTamañoPOSTCritico() {
    $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
    $postMaxSize = ini_get('post_max_size');
    
    // Convertir post_max_size a bytes
    $postMaxBytes = 0;
    if (preg_match('/(\d+)([KMG]?)/i', $postMaxSize, $matches)) {
        $value = (int)$matches[1];
        $unit = strtoupper($matches[2] ?? '');
        
        switch ($unit) {
            case 'G':
                $postMaxBytes = $value * 1024 * 1024 * 1024;
                break;
            case 'M':
                $postMaxBytes = $value * 1024 * 1024;
                break;
            case 'K':
                $postMaxBytes = $value * 1024;
                break;
            default:
                $postMaxBytes = $value;
        }
    }
    
    if ($contentLength > $postMaxBytes) {
        return [
            'valido' => false,
            'mensaje' => "ERROR CRÍTICO: El tamaño del formulario ($contentLength bytes) excede el límite permitido ($postMaxBytes bytes). Límite actual: $postMaxSize",
            'content_length' => $contentLength,
            'post_max_bytes' => $postMaxBytes,
            'post_max_size' => $postMaxSize
        ];
    }
    
    return [
        'valido' => true,
        'mensaje' => "POST válido: $contentLength bytes (límite: $postMaxSize)",
        'content_length' => $contentLength,
        'post_max_bytes' => $postMaxBytes,
        'post_max_size' => $postMaxSize
    ];
}

// Función para mostrar información de configuración crítica
function mostrarConfiguracionCritica() {
    $limites = [
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'max_input_vars' => ini_get('max_input_vars')
    ];
    
    echo "<div style='background:#fff3cd;border:1px solid #ffeaa7;padding:15px;margin:10px 0;border-radius:5px;'>";
    echo "<h3 style='color:#856404;margin-top:0;'>⚠️ Configuración PHP Crítica</h3>";
    echo "<ul style='margin:0;'>";
    foreach ($limites as $clave => $valor) {
        echo "<li><strong>$clave:</strong> $valor</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Función para manejar el error de POST de manera elegante
function manejarErrorPOST($verificacion) {
    // Log del error
    $logFile = 'post_error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = "[$timestamp] IP: $ip - POST_ERROR: {$verificacion['mensaje']} - UserAgent: $userAgent\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Mostrar página de error amigable
    http_response_code(413); // Payload Too Large
    
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Archivo Demasiado Grande - Grúas DBACK</title>";
    echo "<style>";
    echo "body{font-family:Arial,sans-serif;margin:0;padding:20px;background:#f8f9fa;}";
    echo ".container{max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
    echo ".error-icon{font-size:4rem;text-align:center;color:#dc3545;margin-bottom:20px;}";
    echo ".error-title{color:#dc3545;text-align:center;margin-bottom:20px;}";
    echo ".error-message{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin:20px 0;}";
    echo ".btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;text-decoration:none;display:inline-block;margin:10px 5px;}";
    echo ".btn:hover{background:#0056b3;}";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container'>";
    echo "<div class='error-icon'>⚠️</div>";
    echo "<h1 class='error-title'>Archivo Demasiado Grande</h1>";
    echo "<div class='error-message'>";
    echo "<strong>Error:</strong> El archivo que intentas subir es demasiado grande.<br>";
    echo "<strong>Tamaño del formulario:</strong> " . round($verificacion['content_length'] / 1024 / 1024, 2) . " MB<br>";
    echo "<strong>Límite permitido:</strong> " . round($verificacion['post_max_bytes'] / 1024 / 1024, 2) . " MB<br>";
    echo "<strong>Configuración actual:</strong> " . $verificacion['post_max_size'];
    echo "</div>";
    echo "<p>Por favor:</p>";
    echo "<ul>";
    echo "<li>Reduce el tamaño de la imagen (máximo 20MB)</li>";
    echo "<li>Usa un formato de imagen más eficiente (JPG en lugar de PNG)</li>";
    echo "<li>Comprime la imagen antes de subirla</li>";
    echo "</ul>";
    echo "<div style='text-align:center;'>";
    echo "<a href='solicitud.php' class='btn'>🔄 Intentar de Nuevo</a>";
    echo "<a href='index.html' class='btn'>🏠 Ir al Inicio</a>";
    echo "</div>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
    
    exit;
}

// Verificar tamaño del POST inmediatamente
if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $verificacion = verificarTamañoPOSTCritico();
    
    if (!$verificacion['valido']) {
        manejarErrorPOST($verificacion);
    }
}

// Función para validar email
function validarEmailMejorado($email) {
    if (empty($email)) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para validar teléfono
function validarTelefonoMejorado($telefono) {
    if (empty($telefono)) {
        return false;
    }
    
    // Remover caracteres no numéricos excepto + y espacios
    $telefono = preg_replace('/[^0-9+\s\-\(\)]/', '', $telefono);
    return strlen($telefono) >= 10 && strlen($telefono) <= 20;
}

// Función para validar archivo con límites dinámicos
function validarArchivoCritico($archivo) {
    if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return ['valido' => false, 'mensaje' => 'No se pudo subir el archivo'];
    }
    
    $uploadMaxSize = ini_get('upload_max_filesize');
    $maxBytes = 0;
    
    if (preg_match('/(\d+)([KMG]?)/i', $uploadMaxSize, $matches)) {
        $value = (int)$matches[1];
        $unit = strtoupper($matches[2] ?? '');
        
        switch ($unit) {
            case 'G':
                $maxBytes = $value * 1024 * 1024 * 1024;
                break;
            case 'M':
                $maxBytes = $value * 1024 * 1024;
                break;
            case 'K':
                $maxBytes = $value * 1024;
                break;
            default:
                $maxBytes = $value;
        }
    }
    
    if ($archivo['size'] > $maxBytes) {
        $tamañoMB = round($maxBytes / 1024 / 1024, 1);
        return ['valido' => false, 'mensaje' => "El archivo es demasiado grande. Máximo $tamañoMB MB permitido"];
    }
    
    // Validar tipo de archivo usando múltiples métodos
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Método 1: Usar finfo si está disponible
    $tipoArchivo = null;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipoArchivo = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);
    }
    
    // Método 2: Usar mime_content_type si está disponible
    if (!$tipoArchivo && function_exists('mime_content_type')) {
        $tipoArchivo = mime_content_type($archivo['tmp_name']);
    }
    
    // Método 3: Validar por extensión como fallback
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    // Verificar tipo MIME si está disponible
    if ($tipoArchivo && !in_array($tipoArchivo, $tiposPermitidos)) {
        return ['valido' => false, 'mensaje' => 'Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, GIF, WebP)'];
    }
    
    // Verificar extensión como fallback
    if (!in_array($extension, $extensionesPermitidas)) {
        return ['valido' => false, 'mensaje' => 'Extensión de archivo no permitida. Solo se permiten imágenes (JPG, PNG, GIF, WebP)'];
    }
    
    return ['valido' => true, 'mensaje' => 'Archivo válido'];
}

// Función para sanitizar datos de entrada
function sanitizarEntradaCritico($dato, $longitudMaxima = null) {
    if ($dato === null) {
        return '';
    }
    
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato, ENT_QUOTES, 'UTF-8');
    
    if ($longitudMaxima && strlen($dato) > $longitudMaxima) {
        $dato = substr($dato, 0, $longitudMaxima);
    }
    
    return $dato;
}

// Función para generar nombre único de archivo
function generarNombreArchivoCritico($archivoOriginal) {
    $extension = strtolower(pathinfo($archivoOriginal, PATHINFO_EXTENSION));
    $nombreBase = pathinfo($archivoOriginal, PATHINFO_FILENAME);
    $nombreBase = preg_replace('/[^a-zA-Z0-9_-]/', '', $nombreBase);
    
    if (empty($nombreBase)) {
        $nombreBase = 'archivo';
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $random = mt_rand(1000, 9999);
    
    return $nombreBase . '_' . $timestamp . '_' . $random . '.' . $extension;
}

// Función para crear directorio si no existe
function crearDirectorioCritico($ruta) {
    if (!file_exists($ruta)) {
        return mkdir($ruta, 0755, true);
    }
    return true;
}

// Función para obtener información del cliente
function obtenerInfoClienteCritico() {
    return [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s'),
        'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 0,
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
    ];
}

// Función para registrar actividad
function registrarActividadCritica($actividad, $detalles = '') {
    $logFile = 'activity_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
    
    $logEntry = "[$timestamp] IP: $ip - CONTENT_LENGTH: $contentLength - $actividad";
    if ($detalles) {
        $logEntry .= " - $detalles";
    }
    $logEntry .= "\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Registrar carga del archivo de configuración crítica
registrarActividadCritica('Configuración PHP crítica cargada', 'Límites máximos aplicados');
?>
