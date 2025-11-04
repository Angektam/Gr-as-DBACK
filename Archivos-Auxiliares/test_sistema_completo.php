<?php
/**
 * Prueba completa del sistema DBACK
 * Verifica que todos los componentes funcionen correctamente
 */

require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Sistema DBACK - Prueba Completa</title>";
echo "    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>";
echo "</head>";
echo "<body class='bg-light'>";
echo "    <div class='container mt-5'>";
echo "        <div class='row justify-content-center'>";
echo "            <div class='col-md-10'>";
echo "                <div class='card'>";
echo "                    <div class='card-header bg-primary text-white'>";
echo "                        <h3 class='mb-0'><i class='fas fa-truck'></i> Sistema DBACK - Diagnóstico Completo</h3>";
echo "                    </div>";
echo "                    <div class='card-body'>";

// Verificar conexión a la base de datos
echo "                        <div class='row mb-4'>";
echo "                            <div class='col-md-6'>";
try {
    $conn = get_database_connection();
    echo "                                <div class='alert alert-success'>";
    echo "                                    <h5><i class='fas fa-database'></i> Base de Datos</h5>";
    echo "                                    <p class='mb-0'>✅ Conectado exitosamente a: <strong>" . DB_NAME . "</strong></p>";
    echo "                                </div>";
} catch (Exception $e) {
    echo "                                <div class='alert alert-danger'>";
    echo "                                    <h5><i class='fas fa-database'></i> Base de Datos</h5>";
    echo "                                    <p class='mb-0'>❌ Error: " . $e->getMessage() . "</p>";
    echo "                                </div>";
}

// Verificar archivos principales
echo "                            </div>";
echo "                            <div class='col-md-6'>";
echo "                                <div class='alert alert-info'>";
echo "                                    <h5><i class='fas fa-file-code'></i> Archivos Principales</h5>";
echo "                                    <ul class='mb-0'>";

$main_files = [
    'Login.php' => 'Sistema de Login',
    'MenuAdmin.PHP' => 'Menú Principal',
    'conexion.php' => 'Conexión BD',
    'config.php' => 'Configuración',
    'CSS' => 'Estilos CSS',
    'index.html' => 'Página Web',
    'solicitud.php' => 'Solicitudes',
    'Gruas.php' => 'Gestión Grúas',
    'Empleados.php' => 'Gestión Empleados',
    'Gastos.php' => 'Gestión Gastos',
    'Reportes.php' => 'Reportes',
    'configuracion-auto-asignacion.php' => 'Auto-Asignación'
];

foreach ($main_files as $file => $description) {
    if (file_exists($file)) {
        echo "                                        <li>✅ <strong>$file</strong> - $description</li>";
    } else {
        echo "                                        <li>❌ <strong>$file</strong> - $description (No encontrado)</li>";
    }
}

echo "                                    </ul>";
echo "                                </div>";
echo "                            </div>";
echo "                        </div>";

// Verificar directorios importantes
echo "                        <div class='row mb-4'>";
echo "                            <div class='col-md-6'>";
echo "                                <div class='alert alert-warning'>";
echo "                                    <h5><i class='fas fa-folder'></i> Directorios</h5>";
echo "                                    <ul class='mb-0'>";

$directories = [
    'CSS' => 'Estilos',
    'Elementos' => 'Imágenes',
    'uploads' => 'Archivos Subidos',
    'test_uploads' => 'Pruebas'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        echo "                                        <li>✅ <strong>$dir/</strong> - $description</li>";
    } else {
        echo "                                        <li>❌ <strong>$dir/</strong> - $description (No encontrado)</li>";
    }
}

echo "                                    </ul>";
echo "                                </div>";
echo "                            </div>";
echo "                            <div class='col-md-6'>";
echo "                                <div class='alert alert-secondary'>";
echo "                                    <h5><i class='fas fa-info-circle'></i> Información del Sistema</h5>";
echo "                                    <ul class='mb-0'>";
echo "                                        <li><strong>Versión:</strong> " . APP_VERSION . "</li>";
echo "                                        <li><strong>Entorno:</strong> " . APP_ENV . "</li>";
echo "                                        <li><strong>PHP:</strong> " . PHP_VERSION . "</li>";
echo "                                        <li><strong>Servidor:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "</li>";
echo "                                        <li><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</li>";
echo "                                    </ul>";
echo "                                </div>";
echo "                            </div>";
echo "                        </div>";

// Enlaces de navegación
echo "                        <div class='text-center mt-4'>";
echo "                            <h5 class='mb-3'>Enlaces de Prueba</h5>";
echo "                            <div class='btn-group' role='group'>";
echo "                                <a href='index.html' class='btn btn-outline-primary'>";
echo "                                    <i class='fas fa-home'></i> Página Web";
echo "                                </a>";
echo "                                <a href='Login.php' class='btn btn-outline-success'>";
echo "                                    <i class='fas fa-sign-in-alt'></i> Login";
echo "                                </a>";
echo "                                <a href='MenuAdmin.PHP' class='btn btn-outline-info'>";
echo "                                    <i class='fas fa-cogs'></i> Administración";
echo "                                </a>";
echo "                                <a href='solicitud.php' class='btn btn-outline-warning'>";
echo "                                    <i class='fas fa-plus'></i> Nueva Solicitud";
echo "                                </a>";
echo "                            </div>";
echo "                        </div>";

echo "                    </div>";
echo "                </div>";
echo "            </div>";
echo "        </div>";
echo "    </div>";
echo "</body>";
echo "</html>";
?>
