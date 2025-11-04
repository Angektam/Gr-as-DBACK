<?php
require_once 'config.php';

// Usar la función centralizada para obtener la conexión
$conn = get_database_connection();

// Función para limpiar datos de entrada (mantener compatibilidad)
function limpiarDatos($conn, $data) {
    return $conn->real_escape_string(trim($data));
}
?>