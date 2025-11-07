<?php
/**
 * Calcular la ruta base relativa desde el archivo que incluye este componente hasta la raíz del proyecto
 */
if (!isset($base_path)) {
    // Obtener la ruta del archivo que está incluyendo este componente
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    $calling_file = null;
    
    // Buscar el primer archivo que no sea este componente ni sidebar-component
    foreach ($backtrace as $trace) {
        if (isset($trace['file']) && 
            strpos($trace['file'], 'header-component.php') === false && 
            strpos($trace['file'], 'sidebar-component.php') === false) {
            $calling_file = $trace['file'];
            break;
        }
    }
    
    if (!$calling_file) {
        $calling_file = __FILE__;
    }
    
    $calling_dir = str_replace('\\', '/', dirname(realpath($calling_file)));
    $root_dir = str_replace('\\', '/', realpath(dirname(__DIR__)));
    
    // Normalizar rutas
    $calling_dir_normalized = str_replace('\\', '/', $calling_dir);
    
    // Determinar la ruta base según la ubicación del archivo que incluye el componente
    if (strpos($calling_dir_normalized, '/modules/solicitudes') !== false || 
        strpos($calling_dir_normalized, '\\modules\\solicitudes') !== false) {
        // Desde modules/solicitudes/ necesitamos subir 2 niveles
        $base_path = '../../';
    } elseif (strpos($calling_dir_normalized, '/admin') !== false || 
              strpos($calling_dir_normalized, '\\admin') !== false ||
              strpos($calling_dir_normalized, '/admin/') !== false ||
              strpos($calling_dir_normalized, '\\admin\\') !== false) {
        // Desde admin/ necesitamos subir 1 nivel
        $base_path = '../';
    } elseif (strpos($calling_dir_normalized, '/modules') !== false || 
              strpos($calling_dir_normalized, '\\modules') !== false) {
        // Desde cualquier otro módulo necesitamos subir 2 niveles
        $base_path = '../../';
    } else {
        // Desde la raíz, no necesitamos subir
        $base_path = './';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de gestión para Grúas DBACK">
    <title><?php echo isset($page_title) ? $page_title : 'Grúas DBACK'; ?></title>
    
    <!-- Estilos principales -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>CSS/MenuAdmin.CSS">
    <link rel="stylesheet" href="<?php echo $base_path; ?>CSS/Styles.CSS">
    
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet para mapas (si es necesario) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Chart.js para gráficos (si es necesario) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Estilos adicionales específicos de la página -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <!-- Contenedor principal -->
    <div class="app-container">
        <?php include 'sidebar-component.php'; ?>
        
        <!-- Contenido principal -->
        <main class="main-content" id="main-content">
            <!-- El contenido específico de cada página se incluirá aquí -->
