<?php
require_once 'conexion.php';

echo "<h1>Creando Vistas Faltantes</h1>";

// Crear vista gruas_disponibles
$sql_vista_gruas = "
CREATE OR REPLACE VIEW `gruas_disponibles` AS
SELECT 
    g.ID,
    g.Placa,
    g.Marca,
    g.Modelo,
    g.Tipo,
    g.Estado,
    g.ubicacion_actual,
    g.coordenadas_actuales,
    g.disponible_desde,
    g.ultima_actualizacion_ubicacion,
    CASE 
        WHEN g.coordenadas_actuales IS NOT NULL THEN 1
        ELSE 0
    END as tiene_coordenadas
FROM gruas g
WHERE g.Estado = 'Activa'
AND (g.disponible_desde IS NULL OR g.disponible_desde <= NOW())
ORDER BY g.ultima_actualizacion_ubicacion DESC;
";

if ($conn->query($sql_vista_gruas)) {
    echo "<p style='color:green'>✅ Vista 'gruas_disponibles' creada correctamente</p>";
} else {
    echo "<p style='color:red'>❌ Error al crear vista 'gruas_disponibles': " . $conn->error . "</p>";
}

// Crear vista solicitudes_pendientes_asignacion
$sql_vista_solicitudes = "
CREATE OR REPLACE VIEW `solicitudes_pendientes_asignacion` AS
SELECT 
    s.id,
    s.nombre_completo,
    s.telefono,
    s.ubicacion,
    s.coordenadas,
    s.tipo_vehiculo,
    s.tipo_servicio,
    s.urgencia,
    s.distancia_km,
    s.fecha_solicitud,
    s.estado,
    s.grua_asignada_id,
    CASE 
        WHEN s.coordenadas IS NOT NULL THEN 1
        ELSE 0
    END as tiene_coordenadas
FROM solicitudes s
WHERE s.estado = 'pendiente'
AND s.grua_asignada_id IS NULL
ORDER BY 
    CASE s.urgencia
        WHEN 'emergencia' THEN 1
        WHEN 'urgente' THEN 2
        WHEN 'normal' THEN 3
        ELSE 4
    END,
    s.fecha_solicitud ASC;
";

if ($conn->query($sql_vista_solicitudes)) {
    echo "<p style='color:green'>✅ Vista 'solicitudes_pendientes_asignacion' creada correctamente</p>";
} else {
    echo "<p style='color:red'>❌ Error al crear vista 'solicitudes_pendientes_asignacion': " . $conn->error . "</p>";
}

echo "<p><a href='probar-auto-asignacion.php'>🧪 Probar Auto-Asignación Nuevamente</a></p>";

$conn->close();
?>
