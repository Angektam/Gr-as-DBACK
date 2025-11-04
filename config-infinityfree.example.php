<?php
/**
 * Configuración para InfinityFree
 * Copia este archivo como config.php y completa con tus datos
 */

// Configuración de la base de datos de InfinityFree
// IMPORTANTE: Reemplaza estos valores con los datos de tu base de datos en InfinityFree
define('DB_HOST', 'sqlxxx.infinityfree.com'); // Tu host MySQL (ej: sql123.infinityfree.com)
define('DB_USER', 'epiz_xxxxx'); // Tu usuario de BD (ej: epiz_12345678)
define('DB_PASS', 'tu_contraseña'); // Tu contraseña de BD
define('DB_NAME', 'epiz_xxxxx_dback'); // Nombre completo de BD (ej: epiz_12345678_dback)

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Grúas DBACK');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'production'); // production para InfinityFree

// URL base de la aplicación - CAMBIAR por tu URL de InfinityFree
define('APP_URL', 'https://tu-subdominio.infinityfreeapp.com'); // Ejemplo: https://dback-gruas.infinityfreeapp.com
define('APP_PATH', '/');

// Configuración de sesiones para producción
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // HTTPS requerido
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600); // 1 hora

// Configuración de errores para producción
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Configuración de zona horaria
date_default_timezone_set('America/Mazatlan');

// Configuración de archivos subidos
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

/**
 * Función para conectar a la base de datos
 */
function get_database_connection() {
    static $connection = null;
    
    if ($connection === null) {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($connection->connect_error) {
            die('Error de conexión: ' . $connection->connect_error);
        }
        
        $connection->set_charset('utf8mb4');
    }
    
    return $connection;
}

/**
 * Función para verificar sesión de usuario
 */
function check_user_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['usuario_id']) || isset($_SESSION['usuario_nombre']);
}

/**
 * Función para verificar permisos de administrador
 */
function check_admin_permissions() {
    return check_user_session() && ($_SESSION['usuario_cargo'] === 'Administrador' || $_SESSION['usuario_tipo'] === 'admin');
}

/**
 * Función para redirigir con mensaje
 */
function redirect_with_message($url, $message, $type = 'info') {
    $_SESSION['mensaje'] = $message;
    $_SESSION['tipo_mensaje'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Función para mostrar mensajes
 */
function show_message() {
    if (isset($_SESSION['mensaje'])) {
        $message = $_SESSION['mensaje'];
        $type = $_SESSION['tipo_mensaje'] ?? 'info';
        
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        
        $alert_class = match($type) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info'
        };
        
        return "<div class='alert $alert_class alert-dismissible fade show' role='alert'>
                    $message
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    
    return '';
}

/**
 * Función para generar token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Función para verificar token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

