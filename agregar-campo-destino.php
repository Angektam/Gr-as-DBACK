<?php
/**
 * Script para agregar el campo ubicacion_destino a la tabla solicitudes
 */

require_once 'conexion.php';

echo "<h1>🔧 Agregando Campo de Destino a Solicitudes</h1>";

// Verificar si el campo ya existe
$query_check = "SHOW COLUMNS FROM solicitudes LIKE 'ubicacion_destino'";
$result_check = $conn->query($query_check);

if ($result_check->num_rows > 0) {
    echo "<p style='color:orange'>⚠️ El campo 'ubicacion_destino' ya existe en la tabla solicitudes</p>";
} else {
    // Agregar el campo ubicacion_destino
    $sql_add_field = "ALTER TABLE solicitudes ADD COLUMN ubicacion_destino TEXT NULL AFTER ubicacion";
    
    if ($conn->query($sql_add_field)) {
        echo "<p style='color:green'>✅ Campo 'ubicacion_destino' agregado correctamente</p>";
    } else {
        echo "<p style='color:red'>❌ Error al agregar campo: " . $conn->error . "</p>";
    }
}

// Verificar estructura actual de la tabla
echo "<h2>📋 Estructura Actual de la Tabla Solicitudes</h2>";
$query_structure = "DESCRIBE solicitudes";
$result_structure = $conn->query($query_structure);

if ($result_structure->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result_structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Verificar datos existentes
echo "<h2>📊 Solicitudes Existentes</h2>";
$query_data = "SELECT id, nombre_completo, ubicacion, ubicacion_destino, estado FROM solicitudes ORDER BY id DESC LIMIT 10";
$result_data = $conn->query($query_data);

if ($result_data->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Nombre</th><th>Origen</th><th>Destino</th><th>Estado</th></tr>";
    
    while ($row = $result_data->fetch_assoc()) {
        $destino = $row['ubicacion_destino'] ?: 'No definido';
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ubicacion']) . "</td>";
        echo "<td>" . htmlspecialchars($destino) . "</td>";
        echo "<td>{$row['estado']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>⚠️ No hay solicitudes en la base de datos</p>";
}

// Agregar algunos datos de prueba si no hay solicitudes
$query_count = "SELECT COUNT(*) as total FROM solicitudes";
$result_count = $conn->query($query_count);
$count = $result_count->fetch_assoc()['total'];

if ($count == 0) {
    echo "<h2>🧪 Agregando Datos de Prueba</h2>";
    
    $sql_test = "INSERT INTO solicitudes (nombre_completo, telefono, email, ubicacion, ubicacion_destino, tipo_vehiculo, marca_vehiculo, modelo_vehiculo, tipo_servicio, descripcion_problema, urgencia, estado, consentimiento_datos) VALUES 
    ('Juan Pérez', '6681234567', 'juan@email.com', 'Calle Bosque de Sauces, Los Mochis, Sinaloa', 'Taller Mecánico Central, Los Mochis, Sinaloa', 'automovil', 'Toyota', 'Corolla', 'remolque', 'Falla en el motor', 'normal', 'pendiente', 1),
    ('María García', '6687654321', 'maria@email.com', 'Avenida Álvaro Obregón, Los Mochis, Sinaloa', 'Casa del cliente, Los Mochis, Sinaloa', 'camioneta', 'Nissan', 'NP300', 'bateria', 'Batería descargada', 'urgente', 'asignada', 1),
    ('Carlos López', '6689876543', 'carlos@email.com', 'Zona Industrial, Los Mochis, Sinaloa', 'Taller Especializado, Los Mochis, Sinaloa', 'camion', 'Freightliner', 'Cascadia', 'llanta', 'Pinchadura de llanta', 'emergencia', 'en_proceso', 1)";
    
    if ($conn->query($sql_test)) {
        echo "<p style='color:green'>✅ Datos de prueba agregados correctamente</p>";
    } else {
        echo "<p style='color:red'>❌ Error al agregar datos de prueba: " . $conn->error . "</p>";
    }
}

echo "<h2>🔗 Enlaces Útiles</h2>";
echo "<p><a href='detalle-solicitud.php?id=1'>📋 Ver Detalle de Solicitud (ID: 1)</a></p>";
echo "<p><a href='procesar-solicitud.php'>📝 Procesar Solicitudes</a></p>";
echo "<p><a href='solicitud.php'>➕ Nueva Solicitud</a></p>";

$conn->close();
?>
