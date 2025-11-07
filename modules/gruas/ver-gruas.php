<?php
require_once 'conexion.php';

echo "<h1>🔍 VER GRÚAS EN LA BASE DE DATOS</h1>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
table { width: 100%; border-collapse: collapse; background: white; margin: 20px 0; }
th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
th { background: #6a0dad; color: white; }
.highlight { background: yellow; font-weight: bold; }
.error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
.info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
pre { background: #f4f4f4; padding: 15px; border-radius: 4px; }
</style>";

// 1. Verificar que la tabla existe
$check_tabla = $conn->query("SHOW TABLES LIKE 'gruas'");
if (!$check_tabla || $check_tabla->num_rows == 0) {
    echo "<div class='error'><h2>❌ ERROR: La tabla 'gruas' NO existe</h2>";
    echo "<p>Necesitas crear la tabla primero.</p></div>";
    exit;
}

echo "<div class='success'>✅ La tabla 'gruas' existe</div>";

// 2. Contar total
$result_total = $conn->query("SELECT COUNT(*) as total FROM gruas");
$total = $result_total->fetch_assoc()['total'];

echo "<h2>📊 Total de grúas: <strong>$total</strong></h2>";

if ($total == 0) {
    echo "<div class='error'>";
    echo "<h3>⚠️ NO HAY GRÚAS EN LA BASE DE DATOS</h3>";
    echo "<p>Debes agregar grúas desde el módulo de Gestión de Grúas (Gruas.php)</p>";
    echo "<p>O ejecuta este SQL para agregar una grúa de prueba:</p>";
    echo "<pre>INSERT INTO gruas (Placa, Tipo, Estado, Marca, Modelo, Anio) 
VALUES ('TEST-001', 'Plataforma', 'Disponible', 'Volvo', 'FH16', '2020');</pre>";
    echo "</div>";
    exit;
}

// 3. Ver estructura de la tabla
echo "<h2>📋 Estructura de la Tabla</h2>";
$estructura = $conn->query("DESCRIBE gruas");
echo "<table>";
echo "<tr><th>Campo</th><th>Tipo</th></tr>";
$tiene_estado = false;
$nombre_col_estado = '';

while ($row = $estructura->fetch_assoc()) {
    $es_estado = (strtolower($row['Field']) == 'estado');
    if ($es_estado) {
        $tiene_estado = true;
        $nombre_col_estado = $row['Field'];
    }
    
    echo "<tr" . ($es_estado ? " class='highlight'" : "") . ">";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "</tr>";
}
echo "</table>";

if (!$tiene_estado) {
    echo "<div class='error'><h3>❌ NO HAY COLUMNA DE ESTADO</h3>";
    echo "<p>La tabla grúas no tiene una columna llamada 'estado' o 'Estado'</p></div>";
    exit;
}

echo "<div class='info'>Columna de estado encontrada: <strong>$nombre_col_estado</strong></div>";

// 4. Mostrar TODAS las grúas
echo "<h2>🚛 TODAS LAS GRÚAS</h2>";
$todas = $conn->query("SELECT * FROM gruas ORDER BY ID");
echo "<table>";
echo "<tr>";

// Obtener nombres de columnas
$primera = $todas->fetch_assoc();
if ($primera) {
    foreach ($primera as $key => $value) {
        $es_estado = (strtolower($key) == 'estado');
        echo "<th" . ($es_estado ? " class='highlight'" : "") . ">$key</th>";
    }
    echo "</tr>";
    
    // Mostrar primera fila
    echo "<tr>";
    foreach ($primera as $key => $value) {
        $es_estado = (strtolower($key) == 'estado');
        echo "<td" . ($es_estado ? " class='highlight'" : "") . ">" . htmlspecialchars($value ?? 'NULL') . "</td>";
    }
    echo "</tr>";
    
    // Mostrar resto de filas
    while ($row = $todas->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            $es_estado = (strtolower($key) == 'estado');
            echo "<td" . ($es_estado ? " class='highlight'" : "") . ">" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
}
echo "</table>";

// 5. Agrupar por estado
echo "<h2>📊 Grúas por Estado</h2>";
$estados = $conn->query("SELECT $nombre_col_estado as estado, COUNT(*) as cantidad FROM gruas GROUP BY $nombre_col_estado");

echo "<table>";
echo "<tr><th>Estado</th><th>Cantidad</th></tr>";

$lista_estados = [];
while ($row = $estados->fetch_assoc()) {
    $lista_estados[] = "'" . $row['estado'] . "'";
    echo "<tr>";
    echo "<td class='highlight'><strong>" . htmlspecialchars($row['estado']) . "</strong></td>";
    echo "<td>" . $row['cantidad'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// 6. SOLUCIÓN
echo "<h2>🔧 SOLUCIÓN</h2>";

echo "<div class='info'>";
echo "<h3>Estados encontrados en tu BD:</h3>";
echo "<p>" . implode(", ", $lista_estados) . "</p>";
echo "</div>";

// Probar con la consulta actual del sistema
echo "<h3>🧪 Prueba con la consulta actual del sistema:</h3>";
$query_sistema = "SELECT COUNT(*) as total FROM gruas 
                  WHERE LOWER($nombre_col_estado) IN ('disponible', 'activo', 'libre', 'available')";
echo "<pre>$query_sistema</pre>";
$result_sistema = $conn->query($query_sistema);
$encontradas = $result_sistema->fetch_assoc()['total'];

if ($encontradas > 0) {
    echo "<div class='success'>✅ El sistema debería encontrar <strong>$encontradas</strong> grúas disponibles</div>";
    echo "<p><strong>RECARGA menu-auto-asignacion.php</strong></p>";
} else {
    echo "<div class='error'>❌ El sistema NO encuentra grúas disponibles con esos estados</div>";
    
    echo "<h3>💡 Soluciones:</h3>";
    
    // Opción 1: Cambiar los estados en la BD
    echo "<h4>Opción 1: Actualizar los estados en la BD</h4>";
    echo "<p>Ejecuta este SQL para cambiar todos los estados a 'Disponible':</p>";
    
    if (count($lista_estados) > 0) {
        $estados_actuales = implode(", ", $lista_estados);
        echo "<pre>UPDATE gruas SET $nombre_col_estado = 'Disponible' WHERE $nombre_col_estado IN ($estados_actuales);</pre>";
        
        echo "<form method='POST'>";
        echo "<button type='submit' name='actualizar_estados' style='background: #6a0dad; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;'>
              ✅ ACTUALIZAR ESTADOS AUTOMÁTICAMENTE</button>";
        echo "</form>";
    }
    
    // Opción 2: Agregar los estados actuales al sistema
    echo "<h4>Opción 2: Mostrar el código SQL necesario</h4>";
    echo "<p>Tus estados actuales son: " . implode(", ", $lista_estados) . "</p>";
}

// Procesar actualización automática
if (isset($_POST['actualizar_estados'])) {
    echo "<h2>⚙️ Actualizando Estados...</h2>";
    
    $sql_update = "UPDATE gruas SET $nombre_col_estado = 'Disponible'";
    
    if ($conn->query($sql_update)) {
        $afectadas = $conn->affected_rows;
        echo "<div class='success'>";
        echo "<h3>✅ Estados Actualizados</h3>";
        echo "<p>Se actualizaron <strong>$afectadas</strong> grúas</p>";
        echo "<p><a href='menu-auto-asignacion.php' style='background: #6a0dad; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; margin-top: 10px;'>
              ➜ IR AL PANEL DE AUTO-ASIGNACIÓN</a></p>";
        echo "</div>";
        
        // Mostrar resultado
        echo "<h3>Resultado:</h3>";
        $verificar = $conn->query("SELECT $nombre_col_estado as estado, COUNT(*) as cantidad FROM gruas GROUP BY $nombre_col_estado");
        echo "<table>";
        echo "<tr><th>Estado</th><th>Cantidad</th></tr>";
        while ($row = $verificar->fetch_assoc()) {
            echo "<tr>";
            echo "<td class='highlight'><strong>" . htmlspecialchars($row['estado']) . "</strong></td>";
            echo "<td>" . $row['cantidad'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ Error al actualizar: " . $conn->error . "</div>";
    }
}

echo "<hr>";
echo "<h3>🔗 Navegación</h3>";
echo "<a href='menu-auto-asignacion.php' style='background: #6a0dad; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin: 5px;'>Panel Auto-Asignación</a> ";
echo "<a href='Gruas.php' style='background: #6a0dad; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin: 5px;'>Gestión de Grúas</a> ";
echo "<a href='javascript:location.reload()' style='background: #6a0dad; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin: 5px;'>Refrescar</a>";
?>


