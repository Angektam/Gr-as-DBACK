<?php
/**
 * INSTALADOR DEL SISTEMA MEJORADO DE EMPLEADOS
 * Ejecuta automáticamente el script SQL
 */

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "5211";
$dbname = "dback";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "<h1>🚀 Instalador del Sistema Mejorado de Empleados</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>";

// Leer el archivo SQL
$sql_file = 'configuracion-empleados-mejorado.sql';
if (!file_exists($sql_file)) {
    die("❌ Error: No se encontró el archivo $sql_file");
}

$sql_content = file_get_contents($sql_file);

// Dividir en sentencias individuales
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;
$errors = [];

echo "<h2>📊 Ejecutando instalación...</h2>";
echo "<ul style='list-style: none; padding: 0;'>";

foreach ($statements as $statement) {
    if (empty($statement) || substr(trim($statement), 0, 2) === '--') {
        continue;
    }
    
    // Saltar DELIMITER statements
    if (stripos($statement, 'DELIMITER') !== false) {
        continue;
    }
    
    try {
        if ($conn->query($statement)) {
            $success_count++;
            // Extraer el tipo de operación
            $type = 'Ejecutado';
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE[^`]*`([^`]+)`/', $statement, $matches);
                $table = $matches[1] ?? 'tabla';
                echo "<li>✅ Tabla creada: <strong>$table</strong></li>";
            } elseif (stripos($statement, 'ALTER TABLE') !== false) {
                preg_match('/ALTER TABLE[^`]*`?([^\s`]+)`?/', $statement, $matches);
                $table = $matches[1] ?? 'tabla';
                echo "<li>✅ Tabla modificada: <strong>$table</strong></li>";
            } elseif (stripos($statement, 'CREATE VIEW') !== false) {
                preg_match('/CREATE[^`]*VIEW[^`]*`([^`]+)`/', $statement, $matches);
                $view = $matches[1] ?? 'vista';
                echo "<li>✅ Vista creada: <strong>$view</strong></li>";
            } elseif (stripos($statement, 'CREATE PROCEDURE') !== false) {
                preg_match('/CREATE PROCEDURE[^`]*`?([^\s`(]+)`?/', $statement, $matches);
                $proc = $matches[1] ?? 'procedimiento';
                echo "<li>✅ Procedimiento creado: <strong>$proc</strong></li>";
            } elseif (stripos($statement, 'CREATE FUNCTION') !== false) {
                preg_match('/CREATE FUNCTION[^`]*`?([^\s`(]+)`?/', $statement, $matches);
                $func = $matches[1] ?? 'función';
                echo "<li>✅ Función creada: <strong>$func</strong></li>";
            } elseif (stripos($statement, 'CREATE INDEX') !== false) {
                echo "<li>✅ Índice creado</li>";
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                echo "<li>✅ Datos insertados</li>";
            } elseif (stripos($statement, 'UPDATE') !== false) {
                echo "<li>✅ Datos actualizados</li>";
            }
        } else {
            $error_count++;
            $error_msg = $conn->error;
            
            // Ignorar errores de "ya existe"
            if (stripos($error_msg, 'already exists') !== false || 
                stripos($error_msg, 'Duplicate column') !== false ||
                stripos($error_msg, 'Multiple primary key') !== false) {
                echo "<li>⚠️ Ya existe (saltado)</li>";
                $error_count--;
                $success_count++;
            } else {
                $errors[] = $error_msg;
                echo "<li>❌ Error: " . htmlspecialchars($error_msg) . "</li>";
            }
        }
    } catch (Exception $e) {
        $error_count++;
        $errors[] = $e->getMessage();
        echo "<li>❌ Excepción: " . htmlspecialchars($e->getMessage()) . "</li>";
    }
    
    flush();
}

echo "</ul>";

echo "<h2>📈 Resultado de la Instalación</h2>";
echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<p><strong>✅ Operaciones exitosas:</strong> $success_count</p>";
echo "<p><strong>❌ Errores:</strong> $error_count</p>";
echo "</div>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 2px solid #28a745; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🎉 ¡Instalación Completada Exitosamente!</h3>";
    echo "<p>El sistema mejorado de empleados ha sido instalado correctamente.</p>";
    echo "<p><strong>Nuevas columnas agregadas:</strong></p>";
    echo "<ul>";
    echo "<li>✅ departamento</li>";
    echo "<li>✅ direccion</li>";
    echo "<li>✅ estado (activo/inactivo)</li>";
    echo "<li>✅ fecha_baja</li>";
    echo "<li>✅ fecha_creacion</li>";
    echo "<li>✅ fecha_actualizacion</li>";
    echo "</ul>";
    echo "<p><strong>Nuevas tablas creadas:</strong></p>";
    echo "<ul>";
    echo "<li>✅ historial_empleados</li>";
    echo "<li>✅ documentos_empleados</li>";
    echo "<li>✅ asistencias</li>";
    echo "<li>✅ vacaciones</li>";
    echo "<li>✅ evaluaciones_desempeno</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='Empleados.php' style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 1.2em;'>";
    echo "🚀 Ir al Sistema de Empleados";
    echo "</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>⚠️ Instalación completada con algunos errores</h3>";
    echo "<p>Se encontraron $error_count errores durante la instalación:</p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "<p><strong>Nota:</strong> Algunos errores son normales si ya ejecutaste este instalador antes.</p>";
    echo "</div>";
}

// Verificar que las columnas existan
echo "<h2>🔍 Verificación de Columnas</h2>";
$check_query = "SHOW COLUMNS FROM empleados";
$result = $conn->query($check_query);

if ($result) {
    echo "<table border='1' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #667eea; color: white;'>";
    echo "<th style='padding: 10px;'>Columna</th>";
    echo "<th style='padding: 10px;'>Tipo</th>";
    echo "<th style='padding: 10px;'>Nulo</th>";
    echo "<th style='padding: 10px;'>Default</th>";
    echo "</tr>";
    
    $columnas_nuevas = ['departamento', 'direccion', 'estado', 'fecha_baja', 'fecha_creacion', 'fecha_actualizacion'];
    
    while ($row = $result->fetch_assoc()) {
        $es_nueva = in_array($row['Field'], $columnas_nuevas);
        $style = $es_nueva ? "background: #d4edda;" : "";
        
        echo "<tr style='$style'>";
        echo "<td style='padding: 10px;'><strong>" . ($es_nueva ? '🆕 ' : '') . htmlspecialchars($row['Field']) . "</strong></td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td style='padding: 10px;'>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Estadísticas
echo "<h2>📊 Estadísticas</h2>";
$stats = $conn->query("SELECT COUNT(*) as total FROM empleados")->fetch_assoc();
echo "<p>Total de empleados en el sistema: <strong>" . $stats['total'] . "</strong></p>";

// Actualizar empleados existentes sin estado
$update_query = "UPDATE empleados SET estado = 'activo' WHERE estado IS NULL OR estado = ''";
$conn->query($update_query);
$updated = $conn->affected_rows;

if ($updated > 0) {
    echo "<p style='color: green;'>✅ Se actualizaron $updated empleados a estado 'activo'</p>";
}

echo "</div>";

$conn->close();
?>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }
    
    h1, h2 {
        color: #2c3e50;
    }
    
    li {
        padding: 5px 0;
    }
</style>

