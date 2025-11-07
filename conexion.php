<?php
require_once 'config.php';

// Usar la función centralizada para obtener la conexión
$conn = get_database_connection();

// Nota: La función limpiarDatos() ahora está en utils/validaciones.php
// Si necesitas usarla, incluye: require_once 'utils/validaciones.php';
?>