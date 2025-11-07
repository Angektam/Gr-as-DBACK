<?php
/**
 * Componente de Barra Lateral Mejorada con ARIA
 * Archivo: sidebar-component.php
 */

// Calcular la ruta base si no está definida (debe venir de header-component.php)
if (!isset($base_path)) {
    $calling_file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? __FILE__;
    $calling_dir = dirname($calling_file);
    
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

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Usuario';
$cargo = isset($_SESSION['usuario_cargo']) ? $_SESSION['usuario_cargo'] : '';
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : 'admin';
?>

<!-- Barra lateral mejorada con ARIA -->
<nav class="sidebar" aria-label="Menú principal">
    <div class="sidebar_header">
        <img src="<?php echo $base_path; ?>assets/images/LogoDBACK.png" class="sidebar_icon sidebar_icon--logo" alt="Logo DBACK" width="30" height="30">
        <span class="sidebar_text">Grúas DBACK</span>
    </div>

    <ul class="sidebar_list" role="menubar">
        <li class="sidebar_element" role="menuitem" onclick="showSection('dashboard')" tabindex="0" aria-label="Inicio">
            <i class="fas fa-home sidebar_icon" aria-hidden="true"></i>
            <span class="sidebar_text">Inicio</span>
        </li>
        
        <li class="sidebar_element" role="menuitem" onclick="showSection('gruas')" tabindex="0" aria-label="Grúas">
            <a href="<?php echo $base_path; ?>admin/Gruas.php" class="sidebar_link">
                <i class="fas fa-truck sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Grúas</span>
            </a>
        </li>
        
        <li class="sidebar_element" role="menuitem" onclick="showSection('gastos')" tabindex="0" aria-label="Gastos">
            <a href="<?php echo $base_path; ?>admin/Gastos.php" class="sidebar_link">
                <i class="fas fa-money-bill-wave sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Gastos</span>
            </a>
        </li>
        
        <li class="sidebar_element" role="menuitem" onclick="showSection('empleados')" tabindex="0" aria-label="Empleados">
            <a href="<?php echo $base_path; ?>admin/Empleados.php" class="sidebar_link">
                <i class="fas fa-users sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Empleados</span>
            </a>
        </li>

        <li class="sidebar_element" role="menuitem" onclick="showSection('panel-solicitud')" tabindex="0" aria-label="Panel de solicitud">
            <a href="<?php echo $base_path; ?>modules/solicitudes/procesar-solicitud.php" class="sidebar_link">
                <i class="fas fa-clipboard-list sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Panel de solicitud</span>
            </a>
        </li>
        
        <li class="sidebar_element" role="menuitem" onclick="showSection('auto-asignacion')" tabindex="0" aria-label="Auto-asignación">
            <a href="<?php echo $base_path; ?>admin/menu-auto-asignacion.php" class="sidebar_link">
                <i class="fas fa-robot sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Auto-Asignación</span>
            </a>
        </li>

        <?php if ($usuario_tipo === 'admin'): ?>
        <li class="sidebar_element" role="menuitem" onclick="showSection('configuracion')" tabindex="0" aria-label="Configuración">
            <a href="<?php echo $base_path; ?>admin/configuracion-auto-asignacion.php" class="sidebar_link">
                <i class="fas fa-cog sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Configuración</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="sidebar_element" role="menuitem" onclick="showSection('reportes')" tabindex="0" aria-label="Reportes">
            <a href="<?php echo $base_path; ?>admin/Reportes.php" class="sidebar_link">
                <i class="fas fa-chart-bar sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Reportes</span>
            </a>
        </li>

        <li class="sidebar_element" role="menuitem" onclick="showSection('solicitud')" tabindex="0" aria-label="Nueva solicitud">
            <a href="<?php echo $base_path; ?>solicitud.php" class="sidebar_link">
                <i class="fas fa-plus-circle sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Nueva Solicitud</span>
            </a>
        </li>
    </ul>

    <div class="sidebar_footer">
        <div class="sidebar_element" role="contentinfo">
            <i class="fas fa-user-circle sidebar_icon" aria-hidden="true"></i>
            <div>
                <div class="sidebar_text sidebar_title"><?php echo htmlspecialchars($nombre); ?></div>
                <div class="sidebar_text sidebar_info"><?php echo htmlspecialchars($cargo); ?></div>
            </div>
        </div>
        
        <div class="sidebar_element" role="menuitem">
            <a href="../cerrar_sesion.php" class="sidebar_link" aria-label="Cerrar sesión">
                <i class="fas fa-sign-out-alt sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Cerrar Sesión</span>
            </a>
        </div>
    </div>
</nav>
