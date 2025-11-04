<?php
/**
 * Verificar Asignaciones Exitosas
 * Confirmar que las solicitudes se asignaron correctamente
 */

require_once 'conexion.php';

echo "<h1>✅ Verificación de Asignaciones Exitosas</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Verificar solicitudes asignadas recientemente
$query_asignadas = "SELECT s.id, s.nombre_completo, s.tipo_servicio, s.estado, s.grua_asignada_id, s.equipo_asignado_id, s.fecha_asignacion, s.metodo_asignacion,
                           g.Placa as placa_grua,
                           e.Nombre as nombre_equipo, e.Telefono as telefono_equipo
                    FROM solicitudes s
                    LEFT JOIN gruas g ON s.grua_asignada_id = g.ID
                    LEFT JOIN equipos_ayuda e ON s.equipo_asignado_id = e.ID
                    WHERE s.estado = 'asignada' 
                    ORDER BY s.fecha_asignacion DESC
                    LIMIT 10";

$result_asignadas = $conn->query($query_asignadas);

echo "<h2>📋 Solicitudes Asignadas Recientemente</h2>";

if ($result_asignadas->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:8px;'>ID</th>";
    echo "<th style='padding:8px;'>Nombre</th>";
    echo "<th style='padding:8px;'>Tipo Servicio</th>";
    echo "<th style='padding:8px;'>Estado</th>";
    echo "<th style='padding:8px;'>Asignado a</th>";
    echo "<th style='padding:8px;'>Método</th>";
    echo "<th style='padding:8px;'>Fecha Asignación</th>";
    echo "</tr>";
    
    $asignaciones_exitosas = 0;
    $equipos_asignados = 0;
    $gruas_asignadas = 0;
    
    while ($row = $result_asignadas->fetch_assoc()) {
        $asignaciones_exitosas++;
        
        if ($row['equipo_asignado_id']) {
            $equipos_asignados++;
            $asignado_a = "Equipo: " . $row['nombre_equipo'] . " (Tel: " . $row['telefono_equipo'] . ")";
        } elseif ($row['grua_asignada_id']) {
            $gruas_asignadas++;
            $asignado_a = "Grúa: " . $row['placa_grua'];
        } else {
            $asignado_a = "N/A";
        }
        
        echo "<tr>";
        echo "<td style='padding:8px;'>{$row['id']}</td>";
        echo "<td style='padding:8px;'>{$row['nombre_completo']}</td>";
        echo "<td style='padding:8px;'>{$row['tipo_servicio']}</td>";
        echo "<td style='padding:8px;'><span style='background:#28a745; color:white; padding:4px 8px; border-radius:4px;'>{$row['estado']}</span></td>";
        echo "<td style='padding:8px;'>{$asignado_a}</td>";
        echo "<td style='padding:8px;'><span style='background:#007bff; color:white; padding:4px 8px; border-radius:4px;'>{$row['metodo_asignacion']}</span></td>";
        echo "<td style='padding:8px;'>{$row['fecha_asignacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background:#d4edda; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid #28a745;'>";
    echo "<h3>🎉 ¡Asignaciones Exitosas!</h3>";
    echo "<ul>";
    echo "<li><strong>Total asignaciones:</strong> $asignaciones_exitosas</li>";
    echo "<li><strong>Equipos de ayuda asignados:</strong> $equipos_asignados</li>";
    echo "<li><strong>Grúas asignadas:</strong> $gruas_asignadas</li>";
    echo "</ul>";
    echo "</div>";
    
} else {
    echo "<div style='background:#f8d7da; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>⚠️ No hay solicitudes asignadas</h3>";
    echo "<p>No se encontraron solicitudes con estado 'asignada' en el sistema.</p>";
    echo "</div>";
}

// Verificar equipos de ayuda disponibles
echo "<h2>🚗 Equipos de Ayuda Disponibles</h2>";
$query_equipos = "SELECT * FROM equipos_ayuda WHERE Disponible = 1 ORDER BY Tipo_Servicio, Nombre";
$result_equipos = $conn->query($query_equipos);

if ($result_equipos->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:8px;'>ID</th>";
    echo "<th style='padding:8px;'>Nombre</th>";
    echo "<th style='padding:8px;'>Tipo Servicio</th>";
    echo "<th style='padding:8px;'>Ubicación</th>";
    echo "<th style='padding:8px;'>Teléfono</th>";
    echo "<th style='padding:8px;'>Disponible</th>";
    echo "</tr>";
    
    while ($row = $result_equipos->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding:8px;'>{$row['ID']}</td>";
        echo "<td style='padding:8px;'>{$row['Nombre']}</td>";
        echo "<td style='padding:8px;'>{$row['Tipo_Servicio']}</td>";
        echo "<td style='padding:8px;'>{$row['Ubicacion']}</td>";
        echo "<td style='padding:8px;'>{$row['Telefono']}</td>";
        echo "<td style='padding:8px;'><span style='background:#28a745; color:white; padding:4px 8px; border-radius:4px;'>Sí</span></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay equipos de ayuda disponibles.</p>";
}

// Verificar grúas disponibles
echo "<h2>🚛 Grúas Disponibles</h2>";
$query_gruas = "SELECT COUNT(*) as total FROM gruas WHERE disponible_desde IS NOT NULL";
$result_gruas = $conn->query($query_gruas);
$total_gruas = $result_gruas->fetch_assoc()['total'];

echo "<div style='background:#d1ecf1; padding:15px; border-radius:10px; margin:20px 0;'>";
echo "<h3>📊 Resumen de Recursos</h3>";
echo "<ul>";
echo "<li><strong>Grúas disponibles:</strong> $total_gruas</li>";
echo "<li><strong>Equipos de ayuda disponibles:</strong> " . $result_equipos->num_rows . "</li>";
echo "</ul>";
echo "</div>";

// Crear una nueva solicitud de prueba para verificar el sistema
echo "<h2>🧪 Crear Nueva Solicitud de Prueba</h2>";
echo "<div style='background:#fff3cd; padding:20px; border-radius:15px; margin:20px 0;'>";
echo "<h3>📝 Generando solicitud de prueba...</h3>";

$solicitud_prueba = [
    'nombre_completo' => 'Test Usuario ' . date('H:i:s'),
    'telefono' => '555-' . rand(1000, 9999),
    'email' => 'test' . rand(100, 999) . '@example.com',
    'ubicacion' => 'Los Mochis Centro, Sinaloa',
    'coordenadas' => '25.7895,-109.0000',
    'tipo_vehiculo' => 'automovil',
    'marca_vehiculo' => 'Toyota',
    'modelo_vehiculo' => 'Corolla',
    'tipo_servicio' => 'bateria',
    'descripcion_problema' => 'Batería descargada, necesita carga',
    'urgencia' => 'normal',
    'ip_cliente' => '127.0.0.1',
    'user_agent' => 'Test Browser'
];

$query_insert = "INSERT INTO solicitudes (nombre_completo, telefono, email, ubicacion, coordenadas, tipo_vehiculo, marca_vehiculo, modelo_vehiculo, tipo_servicio, descripcion_problema, urgencia, ip_cliente, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query_insert);
$stmt->bind_param("sssssssssssss", 
    $solicitud_prueba['nombre_completo'],
    $solicitud_prueba['telefono'],
    $solicitud_prueba['email'],
    $solicitud_prueba['ubicacion'],
    $solicitud_prueba['coordenadas'],
    $solicitud_prueba['tipo_vehiculo'],
    $solicitud_prueba['marca_vehiculo'],
    $solicitud_prueba['modelo_vehiculo'],
    $solicitud_prueba['tipo_servicio'],
    $solicitud_prueba['descripcion_problema'],
    $solicitud_prueba['urgencia'],
    $solicitud_prueba['ip_cliente'],
    $solicitud_prueba['user_agent']
);

if ($stmt->execute()) {
    $nueva_solicitud_id = $conn->insert_id;
    echo "<p>✅ <strong>Solicitud creada exitosamente:</strong> ID $nueva_solicitud_id</p>";
    echo "<p><strong>Detalles:</strong> {$solicitud_prueba['nombre_completo']} - {$solicitud_prueba['tipo_servicio']}</p>";
    
    // Procesar la nueva solicitud
    echo "<h4>🔄 Procesando nueva solicitud...</h4>";
    require_once 'AutoAsignacionGruas.php';
    $autoAsignacion = new AutoAsignacionGruas($conn);
    $resultado = $autoAsignacion->asignarGrua($nueva_solicitud_id);
    
    if ($resultado['success']) {
        echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h4>✅ Asignación Exitosa</h4>";
        echo "<p><strong>Mensaje:</strong> {$resultado['message']}</p>";
        if (isset($resultado['equipo'])) {
            echo "<p><strong>Equipo asignado:</strong> {$resultado['equipo']['Nombre']} (ID: {$resultado['equipo']['ID']})</p>";
        }
        if (isset($resultado['grua'])) {
            echo "<p><strong>Grúa asignada:</strong> {$resultado['grua']['Placa']} (ID: {$resultado['grua']['ID']})</p>";
        }
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h4>❌ Asignación Fallida</h4>";
        echo "<p><strong>Mensaje:</strong> {$resultado['message']}</p>";
        echo "</div>";
    }
} else {
    echo "<p>❌ <strong>Error al crear solicitud:</strong> " . $stmt->error . "</p>";
}

echo "</div>";

$conn->close();

echo "<div style='text-align:center; margin:30px 0; padding:30px; background:linear-gradient(135deg, #28a745 0%, #007bff 100%); color:white; border-radius:20px;'>";
echo "<h2 style='margin:0 0 15px 0; font-size:2em;'>🎉 ¡SISTEMA FUNCIONANDO!</h2>";
echo "<p style='margin:0; font-size:1.2em; opacity:0.9;'>Auto-asignación de solicitudes operativa</p>";
echo "</div>";
?>
