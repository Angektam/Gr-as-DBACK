<?php
/**
 * Sistema de Rutas Centralizado
 * Define las rutas base del proyecto para facilitar la organización
 */

// Determinar la ruta base del proyecto
$root_path = dirname(__DIR__);

// Rutas de directorios
define('ROOT_PATH', $root_path);
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('API_PATH', ROOT_PATH . '/api');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('COMPONENTS_PATH', ROOT_PATH . '/components');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('UTILS_PATH', ROOT_PATH . '/utils');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Rutas web (URLs relativas desde la raíz del servidor)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$base_url = rtrim($script_name, '/');

// Definir rutas web relativas
define('BASE_URL', $base_url);
define('ADMIN_URL', BASE_URL . '/admin');
define('API_URL', BASE_URL . '/api');
define('MODULES_URL', BASE_URL . '/modules');
define('COMPONENTS_URL', BASE_URL . '/components');
define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');
define('UPLOADS_URL', BASE_URL . '/uploads');
define('PUBLIC_URL', BASE_URL . '/public');

// Helper functions para rutas
function asset_url($path) {
    return ASSETS_URL . '/' . ltrim($path, '/');
}

function css_url($path) {
    return CSS_URL . '/' . ltrim($path, '/');
}

function js_url($path) {
    return JS_URL . '/' . ltrim($path, '/');
}

function image_url($path) {
    return IMAGES_URL . '/' . ltrim($path, '/');
}

function admin_url($path = '') {
    return ADMIN_URL . ($path ? '/' . ltrim($path, '/') : '');
}

function api_url($path = '') {
    return API_URL . ($path ? '/' . ltrim($path, '/') : '');
}

function module_url($module, $path = '') {
    return MODULES_URL . '/' . $module . ($path ? '/' . ltrim($path, '/') : '');
}

function component_path($file) {
    return COMPONENTS_PATH . '/' . ltrim($file, '/');
}

function require_component($file) {
    $path = component_path($file);
    if (file_exists($path)) {
        require_once $path;
    } else {
        error_log("Component not found: {$path}");
    }
}
?>

