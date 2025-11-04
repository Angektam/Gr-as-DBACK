<?php
require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

echo "<h1>🔍 Debug del Sistema de Auto-Asignación</h1>";

try {
    $autoAsignacion = new AutoAsignacionGruas($conn);
    
    echo "<h2>1. Verificando configuración</h2>";
    $config = $autoAsignacion->obtenerConfiguracion();
    echo "<pre>";
    print_r($config);
    echo "</pre>";
    
    echo "<h2>2. Verificando grúas disponibles directamente</h2>";
    $query = "SELECT * FROM gruas_disponibles";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Placa</th><th>Tipo</th><th>Estado</th><th>Coordenadas</th><th>Disponible Desde</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['ID']}</td>";
            echo "<td>{$row['Placa']}</td>";
            echo "<td>{$row['Tipo']}</td>";
            echo "<td>{$row['Estado']}</td>";
            echo "<td>{$row['coordenadas_actuales']}</td>";
            echo "<td>{$row['disponible_desde']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red'>❌ No hay grúas en la vista gruas_disponibles</p>";
    }
    
    echo "<h2>3. Verificando configuración de tipos de servicio</h2>";
    $query_tipos = "SELECT * FROM configuracion_tipos_servicio";
    $result_tipos = $conn->query($query_tipos);
    
    if ($result_tipos->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Tipo Servicio</th><th>Tipo Grúa Preferido</th><th>Peso Máximo</th><th>Prioridad</th><th>Activo</th></tr>";
        while ($row = $result_tipos->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['tipo_servicio']}</td>";
            echo "<td>{$row['tipo_grua_preferido']}</td>";
            echo "<td>{$row['peso_maximo_kg']}</td>";
            echo "<td>{$row['prioridad']}</td>";
            echo "<td>{$row['activo']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red'>❌ No hay configuración de tipos de servicio</p>";
    }
    
    echo "<h2>4. Probando método buscarGruasDisponibles directamente</h2>";
    
    // Crear una solicitud de prueba para el método
    $solicitud_prueba = [
        'tipo_servicio' => 'remolque',
        'coordenadas' => '25.7945,-109.0000',
        'urgencia' => 'normal'
    ];
    
    // Usar reflexión para acceder al método privado
    $reflection = new ReflectionClass($autoAsignacion);
    $method = $reflection->getMethod('buscarGruasDisponibles');
    $method->setAccessible(true);
    
    $gruas_encontradas = $method->invoke($autoAsignacion, $solicitud_prueba);
    
    echo "<p>Grúas encontradas: " . count($gruas_encontradas) . "</p>";
    
    if (count($gruas_encontradas) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Placa</th><th>Tipo</th><th>Distancia</th><th>Tiene Coordenadas</th></tr>";
        foreach ($gruas_encontradas as $grua) {
            echo "<tr>";
            echo "<td>{$grua['ID']}</td>";
            echo "<td>{$grua['Placa']}</td>";
            echo "<td>{$grua['Tipo']}</td>";
            echo "<td>" . ($grua['distancia'] ?? 'N/A') . "</td>";
            echo "<td>" . ($grua['tiene_coordenadas'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red'>❌ No se encontraron grúas en el método buscarGruasDisponibles</p>";
    }
    
    echo "<h2>5. Verificando consulta SQL directamente</h2>";
    
    $radio_busqueda = $config['radio_busqueda_km'] ?? 50;
    $considerar_tipo = $config['considerar_tipo_servicio'] ?? '1';
    
    echo "<p>Radio de búsqueda: $radio_busqueda km</p>";
    echo "<p>Considerar tipo servicio: $considerar_tipo</p>";
    
    $query_debug = "SELECT g.*, gd.tiene_coordenadas FROM gruas_disponibles gd 
                    JOIN gruas g ON gd.ID = g.ID 
                    WHERE 1=1";
    
    if ($considerar_tipo == '1') {
        $query_debug .= " AND g.Tipo = 'Plataforma'"; // Para servicio 'remolque'
    }
    
    $query_debug .= " ORDER BY g.ultima_actualizacion_ubicacion DESC";
    
    echo "<p>Query SQL: <code>$query_debug</code></p>";
    
    $result_debug = $conn->query($query_debug);
    
    if ($result_debug->num_rows > 0) {
        echo "<p style='color:green'>✅ Query devuelve " . $result_debug->num_rows . " grúas</p>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Placa</th><th>Tipo</th><th>Estado</th></tr>";
        while ($row = $result_debug->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['ID']}</td>";
            echo "<td>{$row['Placa']}</td>";
            echo "<td>{$row['Tipo']}</td>";
            echo "<td>{$row['Estado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red'>❌ Query no devuelve grúas</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
