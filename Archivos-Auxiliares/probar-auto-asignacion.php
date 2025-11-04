<?php
/**
 * Script de prueba para verificar que la auto-asignación funciona
 * Este archivo simula una solicitud y prueba la asignación automática
 */

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

echo "<h1>🧪 Prueba del Sistema de Auto-Asignación</h1>";
echo "<style>
body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
.container{max-width:1200px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#28a745;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}
.error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}
.info{color:#17a2b8;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0;}
table{border-collapse:collapse;width:100%;margin:10px 0;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background-color:#f2f2f2;}
.btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin:5px;}
.btn:hover{background:#0056b3;}
.btn-success{background:#28a745;}
.btn-warning{background:#ffc107;color:#212529;}
</style>";

echo "<div class='container'>";

try {
    $autoAsignacion = new AutoAsignacionGruas($conn);
    
    echo "<div class='info'>✅ Sistema de auto-asignación inicializado correctamente</div>";
    
    // 1. Verificar configuración
    echo "<h2>1. 📋 Verificando Configuración</h2>";
    $configuracion = $autoAsignacion->obtenerConfiguracion();
    
    echo "<table>";
    echo "<tr><th>Parámetro</th><th>Valor</th><th>Estado</th></tr>";
    
    $parametros_importantes = [
        'auto_asignacion_habilitada' => 'Auto-asignación',
        'radio_busqueda_km' => 'Radio de búsqueda',
        'considerar_tipo_servicio' => 'Considerar tipo servicio',
        'tiempo_maximo_espera_minutos' => 'Tiempo máximo espera'
    ];
    
    foreach ($parametros_importantes as $param => $nombre) {
        $valor = $configuracion[$param] ?? 'No configurado';
        $estado = $valor == '1' ? '✅ Habilitado' : ($valor == '0' ? '❌ Deshabilitado' : '⚠️ ' . $valor);
        echo "<tr><td>$nombre</td><td>$valor</td><td>$estado</td></tr>";
    }
    echo "</table>";
    
    // 2. Verificar grúas disponibles
    echo "<h2>2. 🚛 Verificando Grúas Disponibles</h2>";
    $query_gruas = "SELECT ID, Placa, Marca, Modelo, Tipo, Estado, coordenadas_actuales FROM gruas WHERE Estado = 'Activa'";
    $result_gruas = $conn->query($query_gruas);
    
    if ($result_gruas->num_rows > 0) {
        echo "<div class='success'>✅ Se encontraron {$result_gruas->num_rows} grúas activas</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Placa</th><th>Marca</th><th>Modelo</th><th>Tipo</th><th>Coordenadas</th></tr>";
        
        while ($row = $result_gruas->fetch_assoc()) {
            $coords = $row['coordenadas_actuales'] ? '✅ GPS' : '❌ Sin GPS';
            echo "<tr><td>{$row['ID']}</td><td>{$row['Placa']}</td><td>{$row['Marca']}</td><td>{$row['Modelo']}</td><td>{$row['Tipo']}</td><td>$coords</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ No hay grúas activas disponibles</div>";
    }
    
    // 3. Crear solicitud de prueba
    echo "<h2>3. 📝 Creando Solicitud de Prueba</h2>";
    
    // Datos de prueba
    $datos_prueba = [
        'nombre_completo' => 'Cliente Prueba Auto-Asignación',
        'telefono' => '6681234567',
        'email' => 'prueba@test.com',
        'ubicacion' => 'Los Mochis, Sinaloa - Ubicación de prueba',
        'coordenadas' => '25.7945,-109.0000', // Coordenadas de Los Mochis
        'tipo_vehiculo' => 'automovil',
        'marca_vehiculo' => 'Toyota',
        'modelo_vehiculo' => 'Corolla',
        'tipo_servicio' => 'remolque',
        'descripcion_problema' => 'Solicitud de prueba para verificar auto-asignación',
        'urgencia' => 'normal',
        'distancia_km' => 5.5,
        'costo_estimado' => 500.00,
        'consentimiento_datos' => 1,
        'ip_cliente' => '127.0.0.1',
        'user_agent' => 'Script de Prueba'
    ];
    
    // Insertar solicitud de prueba
    $sql_insert = "INSERT INTO solicitudes (
        nombre_completo, telefono, email, ubicacion, coordenadas, tipo_vehiculo, 
        marca_vehiculo, modelo_vehiculo, tipo_servicio, descripcion_problema, 
        urgencia, distancia_km, costo_estimado, consentimiento_datos, 
        ip_cliente, user_agent, estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')";
    
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sssssssssssdssss", 
        $datos_prueba['nombre_completo'],
        $datos_prueba['telefono'],
        $datos_prueba['email'],
        $datos_prueba['ubicacion'],
        $datos_prueba['coordenadas'],
        $datos_prueba['tipo_vehiculo'],
        $datos_prueba['marca_vehiculo'],
        $datos_prueba['modelo_vehiculo'],
        $datos_prueba['tipo_servicio'],
        $datos_prueba['descripcion_problema'],
        $datos_prueba['urgencia'],
        $datos_prueba['distancia_km'],
        $datos_prueba['costo_estimado'],
        $datos_prueba['consentimiento_datos'],
        $datos_prueba['ip_cliente'],
        $datos_prueba['user_agent']
    );
    
    if ($stmt->execute()) {
        $solicitud_id = $conn->insert_id;
        echo "<div class='success'>✅ Solicitud de prueba creada con ID: $solicitud_id</div>";
        
        // 4. Probar auto-asignación
        echo "<h2>4. 🤖 Probando Auto-Asignación</h2>";
        
        $inicio_tiempo = microtime(true);
        $resultado = $autoAsignacion->asignarGrua($solicitud_id);
        $tiempo_total = round((microtime(true) - $inicio_tiempo) * 1000);
        
        if ($resultado['success']) {
            echo "<div class='success'>";
            echo "✅ <strong>¡Auto-asignación exitosa!</strong><br>";
            echo "🚛 Grúa asignada: {$resultado['grua']['Placa']} ({$resultado['grua']['Tipo']})<br>";
            echo "⏱️ Tiempo de asignación: {$resultado['tiempo_asignacion_ms']}ms<br>";
            echo "📊 Puntuación: {$resultado['grua']['puntuacion']}<br>";
            echo "📋 Criterios: {$resultado['grua']['criterios']}<br>";
            echo "</div>";
            
            // Verificar en base de datos
            $query_verificar = "SELECT s.*, g.Placa, g.Tipo FROM solicitudes s 
                               LEFT JOIN gruas g ON s.grua_asignada_id = g.ID 
                               WHERE s.id = ?";
            $stmt_verificar = $conn->prepare($query_verificar);
            $stmt_verificar->bind_param("i", $solicitud_id);
            $stmt_verificar->execute();
            $solicitud_actualizada = $stmt_verificar->get_result()->fetch_assoc();
            
            echo "<h3>📋 Estado de la solicitud después de la asignación:</h3>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>Estado</td><td>{$solicitud_actualizada['estado']}</td></tr>";
            echo "<tr><td>Grúa Asignada</td><td>{$solicitud_actualizada['Placa']} ({$solicitud_actualizada['Tipo']})</td></tr>";
            echo "<tr><td>Método Asignación</td><td>{$solicitud_actualizada['metodo_asignacion']}</td></tr>";
            echo "<tr><td>Fecha Asignación</td><td>{$solicitud_actualizada['fecha_asignacion']}</td></tr>";
            echo "</table>";
            
        } else {
            echo "<div class='error'>";
            echo "❌ <strong>Auto-asignación falló:</strong><br>";
            echo "📝 Mensaje: {$resultado['message']}<br>";
            echo "⏱️ Tiempo transcurrido: {$tiempo_total}ms<br>";
            echo "</div>";
        }
        
        // 5. Verificar historial
        echo "<h2>5. 📚 Verificando Historial</h2>";
        $query_historial = "SELECT * FROM historial_asignaciones WHERE solicitud_id = ? ORDER BY fecha_asignacion DESC LIMIT 1";
        $stmt_historial = $conn->prepare($query_historial);
        $stmt_historial->bind_param("i", $solicitud_id);
        $stmt_historial->execute();
        $historial = $stmt_historial->get_result()->fetch_assoc();
        
        if ($historial) {
            echo "<div class='success'>✅ Registro encontrado en historial</div>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>Método</td><td>{$historial['metodo_asignacion']}</td></tr>";
            echo "<tr><td>Distancia</td><td>{$historial['distancia_km']} km</td></tr>";
            echo "<tr><td>Tiempo Asignación</td><td>{$historial['tiempo_asignacion_segundos']}ms</td></tr>";
            echo "<tr><td>Criterios</td><td>{$historial['criterios_usados']}</td></tr>";
            echo "<tr><td>Fecha</td><td>{$historial['fecha_asignacion']}</td></tr>";
            echo "</table>";
        } else {
            echo "<div class='warning'>⚠️ No se encontró registro en historial</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Error al crear solicitud de prueba: " . $stmt->error . "</div>";
    }
    
    // 6. Estadísticas generales
    echo "<h2>6. 📊 Estadísticas del Sistema</h2>";
    $estadisticas = $autoAsignacion->obtenerEstadisticas();
    
    echo "<table>";
    echo "<tr><th>Métrica</th><th>Valor</th></tr>";
    echo "<tr><td>Total Asignaciones</td><td>{$estadisticas['total_asignaciones']}</td></tr>";
    echo "<tr><td>Asignaciones Automáticas</td><td>{$estadisticas['asignaciones_automaticas']}</td></tr>";
    echo "<tr><td>Asignaciones Manuales</td><td>{$estadisticas['asignaciones_manuales']}</td></tr>";
    echo "<tr><td>Tiempo Promedio (ms)</td><td>" . round($estadisticas['tiempo_promedio_segundos'], 2) . "</td></tr>";
    echo "<tr><td>Distancia Promedio (km)</td><td>" . round($estadisticas['distancia_promedio_km'], 2) . "</td></tr>";
    echo "</table>";
    
    // 7. Enlaces útiles
    echo "<h2>7. 🔗 Enlaces Útiles</h2>";
    echo "<p><a href='configuracion-auto-asignacion.php' class='btn'>⚙️ Panel de Configuración</a></p>";
    echo "<p><a href='procesar-solicitud.php' class='btn'>📋 Ver Todas las Solicitudes</a></p>";
    echo "<p><a href='verificar-auto-asignacion.php' class='btn btn-warning'>🔍 Verificación Completa</a></p>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error en la prueba: " . $e->getMessage() . "</div>";
}

echo "<h2>8. 🧹 Limpieza (Opcional)</h2>";
echo "<p>Si quieres eliminar la solicitud de prueba:</p>";
echo "<form method='POST'>";
echo "<button type='submit' name='limpiar' class='btn btn-warning'>🗑️ Eliminar Solicitud de Prueba</button>";
echo "</form>";

// Limpiar solicitud de prueba si se solicita
if (isset($_POST['limpiar'])) {
    $sql_limpiar = "DELETE FROM solicitudes WHERE nombre_completo = 'Cliente Prueba Auto-Asignación'";
    if ($conn->query($sql_limpiar)) {
        echo "<div class='success'>✅ Solicitud de prueba eliminada</div>";
    } else {
        echo "<div class='error'>❌ Error al eliminar: " . $conn->error . "</div>";
    }
}

echo "</div>";

$conn->close();
?>
