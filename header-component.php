<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de gestión para Grúas DBACK">
    <title><?php echo isset($page_title) ? $page_title : 'Grúas DBACK'; ?></title>
    
    <!-- Estilos principales -->
    <link rel="stylesheet" href="./CSS/MenuAdmin.CSS">
    <link rel="stylesheet" href="./CSS/Styles.CSS">
    
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
