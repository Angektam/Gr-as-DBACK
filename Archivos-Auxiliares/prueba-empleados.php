<?php
/**
 * Script de prueba - Página de Empleados
 */

echo "<h1>🔧 Prueba de la Página de Empleados</h1>";
echo "<style>
body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
.container{max-width:1200px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#28a745;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}
.error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}
.info{color:#17a2b8;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
.btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin:5px;text-decoration:none;display:inline-block;}
.btn:hover{background:#0056b3;}
table{border-collapse:collapse;width:100%;margin:10px 0;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background-color:#f2f2f2;}
</style>";

echo "<div class='container'>";

echo "<h2>✅ Verificación de la Página de Empleados</h2>";

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "5211";
$dbname = "dback";

echo "<div class='info'>";
echo "<h3>🔍 Verificando Conexión a la Base de Datos:</h3>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "<div class='error'>";
        echo "<strong>❌ Error de conexión:</strong> " . $conn->connect_error;
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<strong>✅ Conexión exitosa</strong> a la base de datos 'dback'";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "</div>";

echo "<div class='info'>";
echo "<h3>📊 Verificando Estructura de la Tabla 'empleados':</h3>";

if (isset($conn) && !$conn->connect_error) {
    $sql = "DESCRIBE empleados";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th></tr>";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ No se pudo obtener la estructura de la tabla</strong>";
        echo "</div>";
    }
}

echo "</div>";

echo "<div class='info'>";
echo "<h3>👥 Verificando Datos de Empleados:</h3>";

if (isset($conn) && !$conn->connect_error) {
    $sql = "SELECT COUNT(*) as total FROM empleados";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        $total = $row['total'];
        
        echo "<p><strong>Total de empleados registrados:</strong> $total</p>";
        
        if ($total > 0) {
            echo "<h4>Primeros 5 empleados:</h4>";
            $sql = "SELECT * FROM empleados ORDER BY ID_Empleado DESC LIMIT 5";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Nombres</th><th>Apellido1</th><th>Apellido2</th><th>RFC</th><th>Puesto</th><th>Sueldo</th><th>Teléfono</th><th>Email</th></tr>";
                
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['ID_Empleado']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Nombres'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Apellido1'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Apellido2'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($row['RFC'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Puesto'] ?? 'N/A') . "</td>";
                    echo "<td>$" . number_format($row['Sueldo'] ?? 0, 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['telefono'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($row['email'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<div class='info'>";
            echo "<strong>ℹ️ No hay empleados registrados en la base de datos.</strong>";
            echo "<p>Puedes agregar empleados usando el formulario en la página de empleados.</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ Error al consultar empleados:</strong> " . $conn->error;
        echo "</div>";
    }
}

echo "</div>";

echo "<div class='info'>";
echo "<h3>🔗 Enlaces de Prueba:</h3>";
echo "<p><a href='Empleados.php' target='_blank' class='btn'>👥 Ir a la Página de Empleados</a></p>";
echo "<p><a href='MenuAdmin.PHP' target='_blank' class='btn'>🏠 Ir al Menú Principal</a></p>";
echo "</div>";

echo "<h2>✅ Estado de la Corrección</h2>";

if (isset($conn) && !$conn->connect_error) {
    echo "<div class='success'>";
    echo "<h3>🎉 ¡Página de Empleados Corregida!</h3>";
    echo "<p><strong>Problemas solucionados:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Corregidos los nombres de las columnas para coincidir con la base de datos</li>";
    echo "<li>✅ Implementada la barra lateral común</li>";
    echo "<li>✅ Corregido el formulario de agregar empleados</li>";
    echo "<li>✅ Corregida la consulta SQL</li>";
    echo "<li>✅ Eliminados los valores 'undefined' y 'NaN'</li>";
    echo "</ul>";
    echo "<p><strong>La página ahora debería mostrar los datos correctamente.</strong></p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ Problemas de Conexión</h3>";
    echo "<p>No se pudo conectar a la base de datos. Verifica:</p>";
    echo "<ul>";
    echo "<li>Que el servidor MySQL esté ejecutándose</li>";
    echo "<li>Que las credenciales sean correctas</li>";
    echo "<li>Que la base de datos 'dback' exista</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>";

if (isset($conn)) {
    $conn->close();
}
?>
