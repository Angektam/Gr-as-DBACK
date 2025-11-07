<?php
/**
 * Calcular la ruta base relativa desde el archivo que incluye este componente hasta la raíz del proyecto
 */
if (!isset($base_path)) {
    // Obtener la ruta del archivo que está incluyendo este componente
    $calling_file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__;
    $calling_dir = dirname($calling_file);
    $component_dir = __DIR__;
    
    // Calcular niveles para subir a la raíz
    // Si el archivo está en modules/solicitudes/, necesitamos subir 2 niveles
    // Si está en admin/, necesitamos subir 1 nivel
    // Si está en la raíz, no necesitamos subir
    
    $relative_path = str_replace(realpath($_SERVER['DOCUMENT_ROOT'] . '/..'), '', realpath($calling_dir));
    $levels = substr_count(str_replace('\\', '/', $relative_path), '/');
    
    // Si estamos en modules/solicitudes/ (2 niveles), o admin/ (1 nivel), etc.
    if (strpos(str_replace('\\', '/', $calling_dir), 'modules/solicitudes') !== false) {
        $base_path = '../../';
    } elseif (strpos(str_replace('\\', '/', $calling_dir), 'admin') !== false || strpos(str_replace('\\', '/', $calling_dir), '/admin') !== false) {
        $base_path = '../';
    } elseif (strpos(str_replace('\\', '/', $calling_dir), 'modules') !== false) {
        $base_path = '../../';
    } else {
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
