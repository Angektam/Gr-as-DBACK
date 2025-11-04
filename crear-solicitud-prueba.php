<?php
/**
 * Crear Solicitud de Prueba - Sistema DBACK
 * Crea una solicitud de prueba para testear el sistema de auto-asignación
 */

require_once 'conexion.php';

echo "<h1>🧪 Crear Solicitud de Prueba - Sistema DBACK</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Datos de prueba para diferentes tipos de servicios
$solicitudes_prueba = [
    [
        'nombre' => 'Juan Pérez',
        'telefono' => '6681234567',
        'email' => 'juan.perez@test.com',
        'ubicacion_origen' => 'Centro de Los Mochis, Sinaloa',
        'ubicacion_destino' => 'Aeropuerto de Los Mochis, Sinaloa',
        'vehiculo' => 'automovil',
        'marca' => 'Toyota',
        'modelo' => 'Corolla 2020',
        'tipo_servicio' => 'remolque',
        'descripcion' => 'Vehículo con falla mecánica, necesita remolque al taller',
        'urgencia' => 'normal',
        'coordenadas' => '25.7895,-109.0000'
    ],
    [
        'nombre' => 'María González',
        'telefono' => '6687654321',
        'email' => 'maria.gonzalez@test.com',
        'ubicacion_origen' => 'Zona Dorada, Los Mochis',
        'ubicacion_destino' => 'Casa del cliente, Los Mochis',
        'vehiculo' => 'automovil',
        'marca' => 'Nissan',
        'modelo' => 'Sentra 2019',
        'tipo_servicio' => 'gasolina',
        'descripcion' => 'Se quedó sin gasolina, necesita suministro de combustible',
        'urgencia' => 'urgente',
        'coordenadas' => '25.7850,-108.9950'
    ],
    [
        'nombre' => 'Carlos López',
        'telefono' => '6689876543',
        'email' => 'carlos.lopez@test.com',
        'ubicacion_origen' => 'Carretera Internacional, Los Mochis',
        'ubicacion_destino' => 'Taller mecánico, Los Mochis',
        'vehiculo' => 'camioneta',
        'marca' => 'Ford',
        'modelo' => 'Ranger 2021',
        'tipo_servicio' => 'bateria',
        'descripcion' => 'Batería descargada, necesita arranque o cambio de batería',
        'urgencia' => 'emergencia',
        'coordenadas' => '25.7950,-108.9850'
    ],
    [
        'nombre' => 'Ana Martínez',
        'telefono' => '6685551234',
        'email' => 'ana.martinez@test.com',
        'ubicacion_origen' => 'Plaza Sendero, Los Mochis',
        'ubicacion_destino' => 'Domicilio particular, Los Mochis',
        'vehiculo' => 'automovil',
        'marca' => 'Honda',
        'modelo' => 'Civic 2018',
        'tipo_servicio' => 'llanta',
        'descripcion' => 'Pinchadura de llanta, necesita cambio de neumático',
        'urgencia' => 'normal',
        'coordenadas' => '25.7800,-109.0100'
    ]
];

echo "<h2>📝 Creando Solicitudes de Prueba</h2>";

$solicitudes_creadas = 0;
$errores = [];

foreach ($solicitudes_prueba as $index => $solicitud) {
    echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:10px 0; border-left: 5px solid #007bff;'>";
    echo "<h3>Solicitud " . ($index + 1) . ": {$solicitud['tipo_servicio']} - {$solicitud['nombre']}</h3>";
    
    // Insertar solicitud en la base de datos
    $query = "INSERT INTO solicitudes (
        nombre_completo, telefono, email, ubicacion, ubicacion_destino,
        tipo_vehiculo, marca_vehiculo, modelo_vehiculo, tipo_servicio, descripcion_problema, urgencia,
        coordenadas, estado, consentimiento_datos
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', 1)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssssss", 
        $solicitud['nombre'],
        $solicitud['telefono'],
        $solicitud['email'],
        $solicitud['ubicacion_origen'],
        $solicitud['ubicacion_destino'],
        $solicitud['vehiculo'],
        $solicitud['marca'],
        $solicitud['modelo'],
        $solicitud['tipo_servicio'],
        $solicitud['descripcion'],
        $solicitud['urgencia'],
        $solicitud['coordenadas']
    );
    
    if ($stmt->execute()) {
        $solicitud_id = $conn->insert_id;
        echo "<p style='color:green;'>✅ <strong>Creada exitosamente</strong> - ID: $solicitud_id</p>";
        echo "<ul>";
        echo "<li><strong>Tipo de servicio:</strong> {$solicitud['tipo_servicio']}</li>";
        echo "<li><strong>Urgencia:</strong> {$solicitud['urgencia']}</li>";
        echo "<li><strong>Ubicación:</strong> {$solicitud['ubicacion_origen']}</li>";
        echo "<li><strong>Coordenadas:</strong> {$solicitud['coordenadas']}</li>";
        echo "</ul>";
        $solicitudes_creadas++;
    } else {
        $error = "Error al crear solicitud: " . $stmt->error;
        echo "<p style='color:red;'>❌ <strong>Error:</strong> $error</p>";
        $errores[] = $error;
    }
    
    $stmt->close();
    echo "</div>";
}

echo "<h2>📊 Resumen de Creación</h2>";

echo "<div style='background:" . ($solicitudes_creadas > 0 ? "#d4edda" : "#f8d7da") . "; padding:20px; border-radius:15px; margin:20px 0; border: 2px solid " . ($solicitudes_creadas > 0 ? "#28a745" : "#dc3545") . ";'>";
echo "<h3>" . ($solicitudes_creadas > 0 ? "✅ Solicitudes Creadas Exitosamente" : "❌ Error al Crear Solicitudes") . "</h3>";
echo "<ul>";
echo "<li><strong>Total creadas:</strong> $solicitudes_creadas de " . count($solicitudes_prueba) . "</li>";
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

if ($solicitudes_creadas > 0) {
    echo "<h2>🚀 Próximos Pasos</h2>";
    echo "<div style='background:#e3f2fd; padding:20px; border-radius:15px; margin:20px 0;'>";
    echo "<h3>🧪 Ahora puedes probar el sistema de auto-asignación</h3>";
    echo "<ol>";
    echo "<li><a href='test-auto-asignacion.php' style='color:#2196f3; text-decoration:none; font-weight:bold;'>Ejecutar Test de Auto-Asignación</a> - Probar el sistema completo</li>";
    echo "<li><a href='procesar-solicitud.php' style='color:#28a745; text-decoration:none; font-weight:bold;'>Ver Lista de Solicitudes</a> - Revisar las solicitudes creadas</li>";
    echo "<li><a href='menu-auto-asignacion.php' style='color:#ffc107; text-decoration:none; font-weight:bold;'>Configurar Auto-Asignación</a> - Ajustar parámetros del sistema</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h2>🔍 Tipos de Servicios Creados</h2>";
    echo "<div style='background:#f8f9fa; padding:15px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>📋 Solicitudes de Prueba Incluidas</h3>";
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th style='padding:8px;'>Tipo de Servicio</th>";
    echo "<th style='padding:8px;'>Requiere</th>";
    echo "<th style='padding:8px;'>Urgencia</th>";
    echo "<th style='padding:8px;'>Descripción</th>";
    echo "</tr>";
    
    $tipos_servicio = [
        'remolque' => ['requiere' => 'Grúa', 'descripcion' => 'Vehículo con falla mecánica'],
        'gasolina' => ['requiere' => 'Equipo de Ayuda', 'descripcion' => 'Suministro de combustible'],
        'pila' => ['requiere' => 'Equipo de Ayuda', 'descripcion' => 'Batería descargada'],
        'llanta' => ['requiere' => 'Grúa', 'descripcion' => 'Pinchadura de llanta']
    ];
    
    foreach ($solicitudes_prueba as $solicitud) {
        $tipo_info = $tipos_servicio[$solicitud['tipo_servicio']];
        echo "<tr>";
        echo "<td style='padding:8px;'>{$solicitud['tipo_servicio']}</td>";
        echo "<td style='padding:8px;'>{$tipo_info['requiere']}</td>";
        echo "<td style='padding:8px;'>{$solicitud['urgencia']}</td>";
        echo "<td style='padding:8px;'>{$tipo_info['descripcion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

echo "<div style='text-align:center; margin:30px 0; padding:20px; background:linear-gradient(135deg, #27ae60 0%, #5a7ba7 100%); color:white; border-radius:15px;'>";
echo "<h3 style='margin:0 0 10px 0;'>🧪 Solicitudes de Prueba Creadas</h3>";
echo "<p style='margin:0; opacity:0.9;'>Sistema DBACK - Datos de prueba para testing</p>";
echo "<p style='margin:10px 0 0 0; font-size:0.9rem; opacity:0.8;'>Creadas el " . date('d/m/Y H:i:s') . "</p>";
echo "</div>";

$conn->close();
?>
