<?php
/**
 * Agregar columna equipo_asignado a la tabla solicitudes
 * Necesaria para el sistema de equipos de ayuda
 */

require_once 'conexion.php';

echo "<h1>🔧 Agregando Columna equipo_asignado</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar si la columna ya existe
$query_verificar = "SHOW COLUMNS FROM solicitudes LIKE 'equipo_asignado'";
$result_verificar = $conn->query($query_verificar);

if ($result_verificar->num_rows > 0) {
    echo "<div style='background:#d4edda; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid #28a745;'>";
    echo "<h3>✅ Columna ya existe</h3>";
    echo "<p>La columna 'equipo_asignado' ya existe en la tabla 'solicitudes'.</p>";
    echo "</div>";
} else {
    // Agregar la columna
    $query_agregar = "ALTER TABLE solicitudes ADD COLUMN equipo_asignado_id INT(11) NULL AFTER grua_asignada_id";
    
    if ($conn->query($query_agregar)) {
        echo "<div style='background:#d4edda; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid #28a745;'>";
        echo "<h3>✅ Columna agregada exitosamente</h3>";
        echo "<p>Se agregó la columna 'equipo_asignado_id' a la tabla 'solicitudes'.</p>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid #dc3545;'>";
        echo "<h3>❌ Error al agregar columna</h3>";
        echo "<p><strong>Error:</strong> " . $conn->error . "</p>";
        echo "</div>";
    }
}

// Verificar estructura actual de la tabla
echo "<h2>📋 Estructura Actual de la Tabla solicitudes</h2>";
$query_estructura = "DESCRIBE solicitudes";
$result_estructura = $conn->query($query_estructura);

echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'>";
echo "<th style='padding:8px;'>Campo</th>";
echo "<th style='padding:8px;'>Tipo</th>";
echo "<th style='padding:8px;'>Nulo</th>";
echo "<th style='padding:8px;'>Clave</th>";
echo "<th style='padding:8px;'>Por Defecto</th>";
echo "<th style='padding:8px;'>Extra</th>";
echo "</tr>";

while ($row = $result_estructura->fetch_assoc()) {
    echo "<tr>";
    echo "<td style='padding:8px;'>{$row['Field']}</td>";
    echo "<td style='padding:8px;'>{$row['Type']}</td>";
    echo "<td style='padding:8px;'>{$row['Null']}</td>";
    echo "<td style='padding:8px;'>{$row['Key']}</td>";
    echo "<td style='padding:8px;'>{$row['Default']}</td>";
    echo "<td style='padding:8px;'>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Crear tabla de equipos de ayuda si no existe
echo "<h2>🚗 Verificando Tabla de Equipos de Ayuda</h2>";

$query_verificar_equipos = "SHOW TABLES LIKE 'equipos_ayuda'";
$result_verificar_equipos = $conn->query($query_verificar_equipos);

if ($result_verificar_equipos->num_rows > 0) {
    echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:10px 0;'>";
    echo "<h3>✅ Tabla equipos_ayuda existe</h3>";
    echo "<p>La tabla de equipos de ayuda ya existe.</p>";
    echo "</div>";
} else {
    // Crear tabla de equipos de ayuda
    $query_crear_equipos = "
    CREATE TABLE equipos_ayuda (
        ID INT(11) NOT NULL AUTO_INCREMENT,
        Nombre VARCHAR(100) NOT NULL,
        Tipo_Servicio ENUM('gasolina', 'pila', 'bateria', 'general') NOT NULL,
        Ubicacion VARCHAR(255) NOT NULL,
        Coordenadas VARCHAR(50) NULL,
        Disponible TINYINT(1) DEFAULT 1,
        Telefono VARCHAR(20) NULL,
        Capacidad_Maxima INT(11) DEFAULT 1,
        Equipamiento TEXT NULL,
        Fecha_Creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        Fecha_Actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (ID),
        INDEX idx_tipo_servicio (Tipo_Servicio),
        INDEX idx_disponible (Disponible),
        INDEX idx_ubicacion (Ubicacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($query_crear_equipos)) {
        echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h3>✅ Tabla equipos_ayuda creada</h3>";
        echo "<p>Se creó la tabla de equipos de ayuda exitosamente.</p>";
        echo "</div>";
        
        // Insertar equipos de ayuda de prueba
        $equipos_prueba = [
            ['Equipo Gasolina 1', 'gasolina', 'Los Mochis Centro', '25.7914,-108.9924', '555-0001'],
            ['Equipo Gasolina 2', 'gasolina', 'Los Mochis Norte', '25.8014,-108.9824', '555-0002'],
            ['Equipo Batería 1', 'bateria', 'Los Mochis Sur', '25.7814,-109.0024', '555-0003'],
            ['Equipo Batería 2', 'bateria', 'Los Mochis Este', '25.7914,-108.9724', '555-0004'],
            ['Equipo General', 'general', 'Los Mochis Oeste', '25.8014,-109.0124', '555-0005']
        ];
        
        $stmt = $conn->prepare("INSERT INTO equipos_ayuda (Nombre, Tipo_Servicio, Ubicacion, Coordenadas, Telefono) VALUES (?, ?, ?, ?, ?)");
        
        $equipos_insertados = 0;
        foreach ($equipos_prueba as $equipo) {
            $stmt->bind_param("sssss", $equipo[0], $equipo[1], $equipo[2], $equipo[3], $equipo[4]);
            if ($stmt->execute()) {
                $equipos_insertados++;
            }
        }
        
        echo "<div style='background:#d1ecf1; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h3>📊 Equipos de Prueba Insertados</h3>";
        echo "<p><strong>Total:</strong> $equipos_insertados equipos de ayuda</p>";
        echo "</div>";
        
    } else {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h3>❌ Error al crear tabla</h3>";
        echo "<p><strong>Error:</strong> " . $conn->error . "</p>";
        echo "</div>";
    }
}

// Verificar equipos disponibles
$query_equipos = "SELECT COUNT(*) as total FROM equipos_ayuda WHERE Disponible = 1";
$result_equipos = $conn->query($query_equipos);
$total_equipos = $result_equipos->fetch_assoc()['total'];

echo "<div style='background:#d1ecf1; padding:15px; border-radius:10px; margin:20px 0;'>";
echo "<h3>🚗 Equipos de Ayuda Disponibles</h3>";
echo "<p><strong>Total:</strong> $total_equipos equipos</p>";
echo "</div>";

$conn->close();

echo "<div style='text-align:center; margin:30px 0; padding:30px; background:linear-gradient(135deg, #28a745 0%, #007bff 100%); color:white; border-radius:20px;'>";
echo "<h2 style='margin:0 0 15px 0; font-size:2em;'>✅ Configuración Completada</h2>";
echo "<p style='margin:0; font-size:1.2em; opacity:0.9;'>Sistema listo para procesar solicitudes con equipos de ayuda</p>";
echo "</div>";
?>
