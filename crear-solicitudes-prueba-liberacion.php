<?php
/**
 * Crear Solicitudes de Prueba para Sistema de Liberación
 * Generar solicitudes pendientes para probar la liberación y reasignación
 */

require_once 'conexion.php';

echo "<h1>🧪 Creando Solicitudes de Prueba para Liberación</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Datos de prueba para solicitudes
$solicitudes_prueba = [
    [
        'nombre_completo' => 'Carlos Mendoza',
        'telefono' => '555-1001',
        'email' => 'carlos.mendoza@example.com',
        'ubicacion' => 'Los Mochis Centro, Sinaloa',
        'coordenadas' => '25.7895,-109.0000',
        'tipo_vehiculo' => 'automovil',
        'marca_vehiculo' => 'Honda',
        'modelo_vehiculo' => 'Civic',
        'tipo_servicio' => 'remolque',
        'descripcion_problema' => 'Motor no enciende, necesita remolque',
        'urgencia' => 'urgente'
    ],
    [
        'nombre_completo' => 'María Rodríguez',
        'telefono' => '555-1002',
        'email' => 'maria.rodriguez@example.com',
        'ubicacion' => 'Los Mochis Norte, Sinaloa',
        'coordenadas' => '25.8014,-108.9824',
        'tipo_vehiculo' => 'camioneta',
        'marca_vehiculo' => 'Ford',
        'modelo_vehiculo' => 'Ranger',
        'tipo_servicio' => 'bateria',
        'descripcion_problema' => 'Batería descargada, necesita carga',
        'urgencia' => 'normal'
    ],
    [
        'nombre_completo' => 'Luis García',
        'telefono' => '555-1003',
        'email' => 'luis.garcia@example.com',
        'ubicacion' => 'Los Mochis Sur, Sinaloa',
        'coordenadas' => '25.7814,-109.0024',
        'tipo_vehiculo' => 'automovil',
        'marca_vehiculo' => 'Nissan',
        'modelo_vehiculo' => 'Sentra',
        'tipo_servicio' => 'gasolina',
        'descripcion_problema' => 'Se quedó sin gasolina, necesita ayuda',
        'urgencia' => 'emergencia'
    ],
    [
        'nombre_completo' => 'Ana López',
        'telefono' => '555-1004',
        'email' => 'ana.lopez@example.com',
        'ubicacion' => 'Los Mochis Este, Sinaloa',
        'coordenadas' => '25.7914,-108.9724',
        'tipo_vehiculo' => 'camioneta',
        'marca_vehiculo' => 'Chevrolet',
        'modelo_vehiculo' => 'Silverado',
        'tipo_servicio' => 'llanta',
        'descripcion_problema' => 'Pinchadura de llanta, necesita cambio',
        'urgencia' => 'normal'
    ],
    [
        'nombre_completo' => 'Roberto Silva',
        'telefono' => '555-1005',
        'email' => 'roberto.silva@example.com',
        'ubicacion' => 'Los Mochis Oeste, Sinaloa',
        'coordenadas' => '25.8014,-109.0124',
        'tipo_vehiculo' => 'automovil',
        'marca_vehiculo' => 'Toyota',
        'modelo_vehiculo' => 'Corolla',
        'tipo_servicio' => 'arranque',
        'descripcion_problema' => 'Problema de arranque, no enciende',
        'urgencia' => 'urgente'
    ]
];

$query_insert = "INSERT INTO solicitudes (nombre_completo, telefono, email, ubicacion, coordenadas, tipo_vehiculo, marca_vehiculo, modelo_vehiculo, tipo_servicio, descripcion_problema, urgencia, ip_cliente, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query_insert);
$solicitudes_creadas = 0;
$errores = [];

foreach ($solicitudes_prueba as $index => $solicitud) {
    $ip_cliente = '127.0.0.1';
    $user_agent = 'Test Browser - Liberación';
    
    $stmt->bind_param("sssssssssssss", 
        $solicitud['nombre_completo'],
        $solicitud['telefono'],
        $solicitud['email'],
        $solicitud['ubicacion'],
        $solicitud['coordenadas'],
        $solicitud['tipo_vehiculo'],
        $solicitud['marca_vehiculo'],
        $solicitud['modelo_vehiculo'],
        $solicitud['tipo_servicio'],
        $solicitud['descripcion_problema'],
        $solicitud['urgencia'],
        $ip_cliente,
        $user_agent
    );
    
    if ($stmt->execute()) {
        $solicitud_id = $conn->insert_id;
        $solicitudes_creadas++;
        
        echo "<div style='background:#d4edda; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h4>✅ Solicitud #" . ($index + 1) . " creada exitosamente</h4>";
        echo "<p><strong>ID:</strong> $solicitud_id</p>";
        echo "<p><strong>Cliente:</strong> {$solicitud['nombre_completo']}</p>";
        echo "<p><strong>Servicio:</strong> {$solicitud['tipo_servicio']} - {$solicitud['descripcion_problema']}</p>";
        echo "<p><strong>Urgencia:</strong> {$solicitud['urgencia']}</p>";
        echo "</div>";
    } else {
        $errores[] = "Error en solicitud #" . ($index + 1) . ": " . $stmt->error;
        
        echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; margin:10px 0;'>";
        echo "<h4>❌ Error en solicitud #" . ($index + 1) . "</h4>";
        echo "<p><strong>Error:</strong> " . $stmt->error . "</p>";
        echo "</div>";
    }
}

// Resumen
echo "<h2>📊 Resumen de Creación</h2>";
echo "<div style='background:#f0f8ff; padding:20px; border-radius:15px; margin:20px 0;'>";
echo "<h3>📈 Resultados</h3>";
echo "<ul>";
echo "<li><strong>Solicitudes creadas:</strong> $solicitudes_creadas</li>";
echo "<li><strong>Errores:</strong> " . count($errores) . "</li>";
echo "</ul>";

if (count($errores) > 0) {
    echo "<h4>Errores encontrados:</h4>";
    echo "<ul>";
    foreach ($errores as $error) {
        echo "<li style='color:red;'>$error</li>";
    }
    echo "</ul>";
}
echo "</div>";

// Verificar estado actual
$query_verificar = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'";
$result_verificar = $conn->query($query_verificar);
$total_pendientes = $result_verificar->fetch_assoc()['total'];

echo "<h2>🔍 Estado Actual del Sistema</h2>";
echo "<div style='background:#e8f5e8; padding:20px; border-radius:15px; margin:20px 0;'>";
echo "<h3>📋 Solicitudes Pendientes</h3>";
echo "<p><strong>Total:</strong> $total_pendientes solicitudes pendientes</p>";
echo "<p>Estas solicitudes están listas para ser asignadas cuando se liberen grúas.</p>";
echo "</div>";

$conn->close();

echo "<div style='text-align:center; margin:30px 0; padding:30px; background:linear-gradient(135deg, #28a745 0%, #007bff 100%); color:white; border-radius:20px;'>";
echo "<h2 style='margin:0 0 15px 0; font-size:2em;'>✅ Solicitudes de Prueba Creadas</h2>";
echo "<p style='margin:0; font-size:1.2em; opacity:0.9;'>Sistema listo para probar liberación y reasignación</p>";
echo "<p style='margin:10px 0 0 0; font-size:0.9rem; opacity:0.7;'>Ahora puedes probar el sistema en <a href='liberar-gruas.php' style='color:white; text-decoration:underline;'>liberar-gruas.php</a></p>";
echo "</div>";
?>
