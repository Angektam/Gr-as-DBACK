<?php
/**
 * Script para actualizar la tabla reparacion-servicio
 * Agrega las columnas Proveedor y Factura si no existen
 */

require_once 'conexion.php';

echo "<h1>🔧 Actualizando Tabla de Gastos</h1>";

// Verificar estructura actual de la tabla
echo "<h2>📋 Estructura Actual de la Tabla</h2>";
$result_desc = $conn->query("DESCRIBE `reparacion-servicio`");
if ($result_desc->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result_desc->fetch_assoc()) {
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
} else {
    echo "<p style='color:red'>❌ No se pudo obtener la estructura de la tabla</p>";
}

// Verificar si las columnas ya existen
$campos_existentes = [];
$result_desc = $conn->query("DESCRIBE `reparacion-servicio`");
while ($row = $result_desc->fetch_assoc()) {
    $campos_existentes[] = $row['Field'];
}

echo "<h2>🔄 Agregando Columnas Faltantes</h2>";

// Agregar columna Proveedor si no existe
if (!in_array('Proveedor', $campos_existentes)) {
    $sql_proveedor = "ALTER TABLE `reparacion-servicio` ADD COLUMN Proveedor VARCHAR(100) DEFAULT NULL AFTER Costo";
    if ($conn->query($sql_proveedor)) {
        echo "<p style='color:green'>✅ Columna 'Proveedor' agregada correctamente</p>";
    } else {
        echo "<p style='color:red'>❌ Error al agregar columna 'Proveedor': " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:blue'>ℹ️ Columna 'Proveedor' ya existe</p>";
}

// Agregar columna Factura si no existe
if (!in_array('Factura', $campos_existentes)) {
    $sql_factura = "ALTER TABLE `reparacion-servicio` ADD COLUMN Factura VARCHAR(50) DEFAULT NULL AFTER Proveedor";
    if ($conn->query($sql_factura)) {
        echo "<p style='color:green'>✅ Columna 'Factura' agregada correctamente</p>";
    } else {
        echo "<p style='color:red'>❌ Error al agregar columna 'Factura': " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:blue'>ℹ️ Columna 'Factura' ya existe</p>";
}

// Verificar estructura final
echo "<h2>📊 Estructura Final de la Tabla</h2>";
$result_desc = $conn->query("DESCRIBE `reparacion-servicio`");
if ($result_desc->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result_desc->fetch_assoc()) {
        $color = '';
        if ($row['Field'] == 'Proveedor' || $row['Field'] == 'Factura') {
            $color = 'background:#d4edda;';
        }
        echo "<tr style='$color'>";
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

// Agregar algunos datos de prueba
echo "<h2>📝 Agregando Datos de Prueba</h2>";

$datos_prueba = [
    [
        'ID_Grua' => 1,
        'Tipo' => 'Reparacion',
        'Descripcion' => 'Cambio de aceite y filtro',
        'Fecha' => date('Y-m-d'),
        'Hora' => date('H:i'),
        'Costo' => 150.00,
        'Proveedor' => 'Taller Mecánico Central',
        'Factura' => 'FAC-001'
    ],
    [
        'ID_Grua' => 2,
        'Tipo' => 'Gasolina',
        'Descripcion' => 'Carga de combustible',
        'Fecha' => date('Y-m-d'),
        'Hora' => date('H:i'),
        'Costo' => 80.00,
        'Proveedor' => 'Gasolinera Shell',
        'Factura' => 'FAC-002'
    ],
    [
        'ID_Grua' => 3,
        'Tipo' => 'Reparacion',
        'Descripcion' => 'Revisión general del vehículo',
        'Fecha' => date('Y-m-d'),
        'Hora' => date('H:i'),
        'Costo' => 200.00,
        'Proveedor' => 'Servicio Técnico Los Mochis',
        'Factura' => 'FAC-003'
    ]
];

$gastos_agregados = 0;
foreach ($datos_prueba as $gasto) {
    $sql_insert = "INSERT INTO `reparacion-servicio` (ID_Grua, Tipo, Descripcion, Fecha, Hora, Costo, Proveedor, Factura) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt) {
        $stmt->bind_param("issssdss", 
            $gasto['ID_Grua'], 
            $gasto['Tipo'], 
            $gasto['Descripcion'], 
            $gasto['Fecha'], 
            $gasto['Hora'], 
            $gasto['Costo'], 
            $gasto['Proveedor'], 
            $gasto['Factura']
        );
        
        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Gasto agregado: {$gasto['Descripcion']} - $" . number_format($gasto['Costo'], 2) . "</p>";
            $gastos_agregados++;
        } else {
            echo "<p style='color:red'>❌ Error al agregar gasto: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Verificar datos agregados
echo "<h2>📊 Verificación de Datos</h2>";
$result_count = $conn->query("SELECT COUNT(*) as total FROM `reparacion-servicio`");
$total_gastos = $result_count->fetch_assoc()['total'];

$result_recent = $conn->query("SELECT * FROM `reparacion-servicio` ORDER BY ID_Gasto DESC LIMIT 5");
if ($result_recent->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;margin:10px 0;'>";
    echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Tipo</th><th>Descripción</th><th>Costo</th><th>Proveedor</th><th>Factura</th></tr>";
    while ($row = $result_recent->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['ID_Gasto']}</td>";
        echo "<td>{$row['Tipo']}</td>";
        echo "<td>" . htmlspecialchars($row['Descripcion']) . "</td>";
        echo "<td>$" . number_format($row['Costo'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($row['Proveedor'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['Factura'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>✅ Resumen</h2>";
echo "<div style='background:#e8f5e8; padding:15px; border-radius:8px; margin:10px 0;'>";
echo "<p><strong>🎉 Actualización completada exitosamente</strong></p>";
echo "<p>• Gastos agregados: <strong>$gastos_agregados</strong></p>";
echo "<p>• Total de gastos en la tabla: <strong>$total_gastos</strong></p>";
echo "<p>• Columnas agregadas: <strong>Proveedor, Factura</strong></p>";
echo "</div>";

echo "<h2>🔗 Enlaces de Prueba</h2>";
echo "<p><a href='Gastos-mejorado.php' target='_blank'>📊 Ver Sistema de Gastos Mejorado</a></p>";
echo "<p><a href='Gastos.php' target='_blank'>📋 Ver Sistema de Gastos Original</a></p>";

$conn->close();
?>
