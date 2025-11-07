<?php
/**
 * Componente de Barra Lateral Mejorada con ARIA
 * Archivo: sidebar-component.php
 */

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
        <img src="../assets/images/LogoDBACK.png" class="sidebar_icon sidebar_icon--logo" alt="Logo DBACK" width="30" height="30">
        <span class="sidebar_text">Grúas DBACK</span>
    </div>

    <ul class="sidebar_list" role="menubar">
        <li class="sidebar_element" role="menuitem" onclick="showSection('dashboard')" tabindex="0" aria-label="Inicio">
            <i class="fas fa-home sidebar_icon" aria-hidden="true"></i>
            <span class="sidebar_text">Inicio</span>
        </li>
        
        <li class="sidebar_element" role="menuitem" onclick="showSection('gruas')" tabindex="0" aria-label="Grúas">
            <a href="Gruas.php" class="sidebar_link">
                <i class="fas fa-truck sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Grúas</span>
            </a>
        </li>
        
        <li class="sidebar_element" role="menuitem" onclick="showSection('gastos')" tabindex="0" aria-label="Gastos">
            <a href="Gastos.php" class="sidebar_link">
                <i class="fas fa-money-bill-wave sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Gastos</span>
            </a>
        </li>
        
        <li class="sidebar_element" role="menuitem" onclick="showSection('empleados')" tabindex="0" aria-label="Empleados">
            <a href="Empleados.php" class="sidebar_link">
                <i class="fas fa-users sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Empleados</span>
            </a>
        </li>

        <li class="sidebar_element" role="menuitem" onclick="showSection('panel-solicitud')" tabindex="0" aria-label="Panel de solicitud">
            <a href="../modules/solicitudes/gestion-solicitud.php" class="sidebar_link">
                <i class="fas fa-clipboard-list sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Panel de solicitud</span>
            </a>
        </li>
        
        <li class="sidebar_element" role="menuitem" onclick="showSection('auto-asignacion')" tabindex="0" aria-label="Auto-asignación">
            <a href="menu-auto-asignacion.php" class="sidebar_link">
                <i class="fas fa-robot sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Auto-Asignación</span>
            </a>
        </li>

        <?php if ($usuario_tipo === 'admin'): ?>
        <li class="sidebar_element" role="menuitem" onclick="showSection('configuracion')" tabindex="0" aria-label="Configuración">
            <a href="configuracion-auto-asignacion.php" class="sidebar_link">
                <i class="fas fa-cog sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Configuración</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="sidebar_element" role="menuitem" onclick="showSection('reportes')" tabindex="0" aria-label="Reportes">
            <a href="Reportes.php" class="sidebar_link">
                <i class="fas fa-chart-bar sidebar_icon" aria-hidden="true"></i>
                <span class="sidebar_text">Reportes</span>
            </a>
        </li>

        <li class="sidebar_element" role="menuitem" onclick="showSection('solicitud')" tabindex="0" aria-label="Nueva solicitud">
            <a href="../solicitud.php" class="sidebar_link">
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
