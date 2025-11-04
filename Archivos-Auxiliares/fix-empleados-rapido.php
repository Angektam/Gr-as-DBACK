<?php
/**
 * SOLUCIÓN RÁPIDA - AGREGAR COLUMNA ESTADO
 * Ejecuta este archivo UNA VEZ para agregar las columnas necesarias
 */

$servername = "localhost";
$username = "root";
$password = "5211";
$dbname = "dback";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "<html><head><meta charset='UTF-8'><title>Fix Empleados</title></head><body>";
echo "<div style='font-family: Arial; max-width: 800px; margin: 50px auto; padding: 30px; background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);'>";
echo "<h1 style='color: #667eea;'>🔧 Reparación Rápida - Sistema de Empleados</h1>";

$errores = 0;
$exitos = 0;

// 1. Agregar columna estado
echo "<h3>1️⃣ Agregando columna 'estado'...</h3>";
$sql = "ALTER TABLE empleados ADD COLUMN estado ENUM('activo', 'inactivo') DEFAULT 'activo' AFTER licencia";
if ($conn->query($sql) === TRUE) {
    echo "✅ Columna 'estado' agregada exitosamente<br>";
    $exitos++;
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "⚠️ La columna 'estado' ya existe (OK)<br>";
        $exitos++;
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
        $errores++;
    }
}

// 2. Agregar columna departamento
echo "<h3>2️⃣ Agregando columna 'departamento'...</h3>";
$sql = "ALTER TABLE empleados ADD COLUMN departamento VARCHAR(100) AFTER Puesto";
if ($conn->query($sql) === TRUE) {
    echo "✅ Columna 'departamento' agregada exitosamente<br>";
    $exitos++;
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "⚠️ La columna 'departamento' ya existe (OK)<br>";
        $exitos++;
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
        $errores++;
    }
}

// 3. Agregar columna direccion
echo "<h3>3️⃣ Agregando columna 'direccion'...</h3>";
$sql = "ALTER TABLE empleados ADD COLUMN direccion VARCHAR(255) AFTER email";
if ($conn->query($sql) === TRUE) {
    echo "✅ Columna 'direccion' agregada exitosamente<br>";
    $exitos++;
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "⚠️ La columna 'direccion' ya existe (OK)<br>";
        $exitos++;
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
        $errores++;
    }
}

// 4. Agregar columna fecha_baja
echo "<h3>4️⃣ Agregando columna 'fecha_baja'...</h3>";
$sql = "ALTER TABLE empleados ADD COLUMN fecha_baja DATETIME NULL AFTER estado";
if ($conn->query($sql) === TRUE) {
    echo "✅ Columna 'fecha_baja' agregada exitosamente<br>";
    $exitos++;
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "⚠️ La columna 'fecha_baja' ya existe (OK)<br>";
        $exitos++;
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
        $errores++;
    }
}

// 5. Actualizar empleados existentes
echo "<h3>5️⃣ Actualizando empleados existentes...</h3>";
$sql = "UPDATE empleados SET estado = 'activo' WHERE estado IS NULL OR estado = ''";
if ($conn->query($sql) === TRUE) {
    $affected = $conn->affected_rows;
    echo "✅ $affected empleados actualizados a estado 'activo'<br>";
    $exitos++;
} else {
    echo "❌ Error: " . $conn->error . "<br>";
    $errores++;
}

// 6. Crear tabla historial_empleados
echo "<h3>6️⃣ Creando tabla 'historial_empleados'...</h3>";
$sql = "CREATE TABLE IF NOT EXISTS historial_empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    usuario_id INT,
    accion VARCHAR(255) NOT NULL,
    detalles TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(ID_Empleado) ON DELETE CASCADE,
    INDEX idx_empleado (empleado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'historial_empleados' creada exitosamente<br>";
    $exitos++;
} else {
    echo "❌ Error: " . $conn->error . "<br>";
    $errores++;
}

// Resumen
echo "<div style='margin: 30px 0; padding: 20px; background: " . ($errores == 0 ? "#d4edda" : "#fff3cd") . "; border-radius: 10px;'>";
echo "<h2>📊 Resumen</h2>";
echo "<p><strong>✅ Operaciones exitosas:</strong> $exitos</p>";
echo "<p><strong>❌ Errores:</strong> $errores</p>";

if ($errores == 0) {
    echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center;'>";
    echo "<h2>🎉 ¡REPARACIÓN COMPLETADA!</h2>";
    echo "<p style='font-size: 1.2em;'>El sistema de empleados está listo para usar</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 20px 0;'>";
    echo "<a href='Empleados.php' style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 1.3em;'>";
    echo "🚀 IR AL SISTEMA DE EMPLEADOS";
    echo "</a>";
    echo "</div>";
    
    echo "<p style='text-align: center; color: #666; margin-top: 20px;'>";
    echo "Ahora puedes cerrar esta ventana y usar Empleados.php sin problemas";
    echo "</p>";
} else {
    echo "<div style='background: #dc3545; color: white; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>⚠️ Se encontraron algunos errores</h3>";
    echo "<p>Por favor, revisa los mensajes arriba para más detalles.</p>";
    echo "</div>";
}
echo "</div>";

// Verificar estructura final
echo "<h3>7️⃣ Verificación de Estructura de Tabla</h3>";
$result = $conn->query("SHOW COLUMNS FROM empleados");
echo "<table border='1' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
echo "<tr style='background: #667eea; color: white;'>";
echo "<th style='padding: 10px;'>Columna</th>";
echo "<th style='padding: 10px;'>Tipo</th>";
echo "<th style='padding: 10px;'>Null</th>";
echo "<th style='padding: 10px;'>Default</th>";
echo "</tr>";

$columnas_importantes = ['estado', 'departamento', 'direccion', 'fecha_baja'];
while ($row = $result->fetch_assoc()) {
    $es_importante = in_array($row['Field'], $columnas_importantes);
    $style = $es_importante ? "background: #d4edda; font-weight: bold;" : "";
    
    echo "<tr style='$style'>";
    echo "<td style='padding: 10px;'>" . ($es_importante ? "🆕 " : "") . htmlspecialchars($row['Field']) . "</td>";
    echo "<td style='padding: 10px;'>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td style='padding: 10px;'>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td style='padding: 10px;'>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "</div></body></html>";

$conn->close();
?>

