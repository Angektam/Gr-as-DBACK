<?php
/**
 * Script para verificar y configurar el sistema de auto-asignación
 * Ejecutar este archivo desde el navegador para verificar la instalación
 */

require_once 'conexion.php';

echo "<h1>Verificación del Sistema de Auto-Asignación</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// Función para ejecutar SQL
function ejecutarSQL($conn, $sql, $descripcion) {
    echo "<h3>$descripcion</h3>";
    if ($conn->multi_query($sql)) {
        echo "<p class='success'>✅ $descripcion - Ejecutado correctamente</p>";
        
        // Procesar todos los resultados
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        return true;
    } else {
        echo "<p class='error'>❌ Error en $descripcion: " . $conn->error . "</p>";
        return false;
    }
}

// Verificar si las tablas ya existen
echo "<h2>1. Verificando tablas existentes</h2>";

$tablas_requeridas = [
    'configuracion_auto_asignacion',
    'historial_asignaciones', 
    'configuracion_tipos_servicio'
];

$tablas_existentes = [];
foreach ($tablas_requeridas as $tabla) {
    $result = $conn->query("SHOW TABLES LIKE '$tabla'");
    if ($result->num_rows > 0) {
        echo "<p class='info'>📋 Tabla '$tabla' ya existe</p>";
        $tablas_existentes[] = $tabla;
    } else {
        echo "<p class='error'>❌ Tabla '$tabla' no existe</p>";
    }
}

// Crear tablas si no existen
if (count($tablas_existentes) < count($tablas_requeridas)) {
    echo "<h2>2. Creando tablas faltantes</h2>";
    
    // Crear tabla de configuración
    $sql_config = "
    CREATE TABLE IF NOT EXISTS `configuracion_auto_asignacion` (
      `id` int NOT NULL AUTO_INCREMENT,
      `parametro` varchar(50) NOT NULL,
      `valor` text NOT NULL,
      `descripcion` text,
      `activo` tinyint(1) DEFAULT 1,
      `fecha_actualizacion` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `parametro_unico` (`parametro`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ";
    
    ejecutarSQL($conn, $sql_config, "Crear tabla configuracion_auto_asignacion");
    
    // Insertar parámetros por defecto
    $sql_params = "
    INSERT IGNORE INTO `configuracion_auto_asignacion` (`parametro`, `valor`, `descripcion`, `activo`) VALUES
    ('auto_asignacion_habilitada', '1', 'Habilitar/deshabilitar auto-asignación automática de grúas', 1),
    ('radio_busqueda_km', '50', 'Radio de búsqueda en kilómetros para encontrar grúas cercanas', 1),
    ('prioridad_urgencia', 'emergencia,urgente,normal', 'Orden de prioridad por urgencia (separado por comas)', 1),
    ('tiempo_maximo_espera_minutos', '30', 'Tiempo máximo de espera antes de asignar cualquier grúa disponible', 1),
    ('considerar_tipo_servicio', '1', 'Considerar el tipo de servicio para asignar grúa apropiada', 1),
    ('peso_maximo_vehiculo_kg', '3500', 'Peso máximo del vehículo para asignar grúa de plataforma', 1),
    ('distancia_maxima_km', '200', 'Distancia máxima para considerar una grúa', 1),
    ('notificar_asignacion', '1', 'Enviar notificación cuando se asigne una grúa automáticamente', 1),
    ('reintentos_asignacion', '3', 'Número de reintentos si falla la asignación', 1),
    ('tiempo_entre_reintentos_minutos', '5', 'Tiempo entre reintentos en minutos', 1);
    ";
    
    ejecutarSQL($conn, $sql_params, "Insertar parámetros por defecto");
    
    // Verificar si necesitamos agregar campos a solicitudes
    $result = $conn->query("SHOW COLUMNS FROM solicitudes LIKE 'grua_asignada_id'");
    if ($result->num_rows == 0) {
        $sql_solicitudes = "
        ALTER TABLE `solicitudes` 
        ADD COLUMN `grua_asignada_id` int DEFAULT NULL AFTER `estado`,
        ADD COLUMN `fecha_asignacion` datetime DEFAULT NULL AFTER `grua_asignada_id`,
        ADD COLUMN `metodo_asignacion` enum('manual','automatica') DEFAULT NULL AFTER `fecha_asignacion`,
        ADD COLUMN `coordenadas_grua` varchar(50) DEFAULT NULL AFTER `metodo_asignacion`,
        ADD KEY `fk_grua_asignada` (`grua_asignada_id`),
        ADD CONSTRAINT `fk_grua_asignada` FOREIGN KEY (`grua_asignada_id`) REFERENCES `gruas` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;
        ";
        
        ejecutarSQL($conn, $sql_solicitudes, "Agregar campos de asignación a solicitudes");
    } else {
        echo "<p class='info'>📋 Campos de asignación ya existen en tabla solicitudes</p>";
    }
    
    // Verificar si necesitamos agregar campos a gruas
    $result = $conn->query("SHOW COLUMNS FROM gruas LIKE 'ubicacion_actual'");
    if ($result->num_rows == 0) {
        $sql_gruas = "
        ALTER TABLE `gruas` 
        ADD COLUMN `ubicacion_actual` varchar(200) DEFAULT NULL AFTER `Estado`,
        ADD COLUMN `coordenadas_actuales` varchar(50) DEFAULT NULL AFTER `ubicacion_actual`,
        ADD COLUMN `disponible_desde` datetime DEFAULT NULL AFTER `coordenadas_actuales`,
        ADD COLUMN `ultima_actualizacion_ubicacion` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `disponible_desde`;
        ";
        
        ejecutarSQL($conn, $sql_gruas, "Agregar campos de ubicación a gruas");
    } else {
        echo "<p class='info'>📋 Campos de ubicación ya existen en tabla gruas</p>";
    }
    
    // Crear tabla de historial
    $sql_historial = "
    CREATE TABLE IF NOT EXISTS `historial_asignaciones` (
      `id` int NOT NULL AUTO_INCREMENT,
      `solicitud_id` int NOT NULL,
      `grua_id` int NOT NULL,
      `metodo_asignacion` enum('manual','automatica') NOT NULL,
      `criterios_usados` text,
      `distancia_km` decimal(10,2) DEFAULT NULL,
      `tiempo_asignacion_segundos` int DEFAULT NULL,
      `fecha_asignacion` datetime DEFAULT CURRENT_TIMESTAMP,
      `usuario_asignador` varchar(100) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `fk_historial_solicitud` (`solicitud_id`),
      KEY `fk_historial_grua` (`grua_id`),
      CONSTRAINT `fk_historial_solicitud` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_historial_grua` FOREIGN KEY (`grua_id`) REFERENCES `gruas` (`ID`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ";
    
    ejecutarSQL($conn, $sql_historial, "Crear tabla historial_asignaciones");
    
    // Crear tabla de configuración de tipos de servicio
    $sql_tipos = "
    CREATE TABLE IF NOT EXISTS `configuracion_tipos_servicio` (
      `id` int NOT NULL AUTO_INCREMENT,
      `tipo_servicio` enum('remolque','bateria','gasolina','llanta','arranque','otro') NOT NULL,
      `tipo_grua_preferido` enum('Plataforma','Arrastre','Remolque') NOT NULL,
      `peso_maximo_kg` int DEFAULT NULL,
      `prioridad` int DEFAULT 1,
      `activo` tinyint(1) DEFAULT 1,
      PRIMARY KEY (`id`),
      UNIQUE KEY `tipo_servicio_unico` (`tipo_servicio`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    ";
    
    ejecutarSQL($conn, $sql_tipos, "Crear tabla configuracion_tipos_servicio");
    
    // Insertar configuración de tipos de servicio
    $sql_tipos_data = "
    INSERT IGNORE INTO `configuracion_tipos_servicio` (`tipo_servicio`, `tipo_grua_preferido`, `peso_maximo_kg`, `prioridad`, `activo`) VALUES
    ('remolque', 'Plataforma', 3500, 1, 1),
    ('bateria', 'Arrastre', 2000, 2, 1),
    ('gasolina', 'Arrastre', 2000, 2, 1),
    ('llanta', 'Arrastre', 2000, 2, 1),
    ('arranque', 'Arrastre', 2000, 2, 1),
    ('otro', 'Plataforma', 3500, 3, 1);
    ";
    
    ejecutarSQL($conn, $sql_tipos_data, "Insertar configuración de tipos de servicio");
}

echo "<h2>3. Verificando configuración actual</h2>";

$result = $conn->query("SELECT parametro, valor, descripcion FROM configuracion_auto_asignacion WHERE activo = 1");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>Parámetro</th><th>Valor</th><th>Descripción</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['parametro']}</td><td>{$row['valor']}</td><td>{$row['descripcion']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No se encontraron parámetros de configuración</p>";
}

echo "<h2>4. Verificando datos de prueba</h2>";

// Verificar grúas disponibles
$result = $conn->query("SELECT COUNT(*) as total FROM gruas WHERE Estado = 'Activa'");
$gruas_activas = $result->fetch_assoc()['total'];
echo "<p class='info'>📋 Grúas activas: $gruas_activas</p>";

// Verificar solicitudes pendientes
$result = $conn->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente' AND grua_asignada_id IS NULL");
$solicitudes_pendientes = $result->fetch_assoc()['total'];
echo "<p class='info'>📋 Solicitudes pendientes: $solicitudes_pendientes</p>";

// Mostrar grúas disponibles
$result = $conn->query("SELECT ID, Placa, Marca, Modelo, Tipo, Estado FROM gruas WHERE Estado = 'Activa' LIMIT 5");
if ($result->num_rows > 0) {
    echo "<h3>Grúas disponibles:</h3>";
    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>ID</th><th>Placa</th><th>Marca</th><th>Modelo</th><th>Tipo</th><th>Estado</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['ID']}</td><td>{$row['Placa']}</td><td>{$row['Marca']}</td><td>{$row['Modelo']}</td><td>{$row['Tipo']}</td><td>{$row['Estado']}</td></tr>";
    }
    echo "</table>";
}

echo "<h2>5. Probando la clase AutoAsignacionGruas</h2>";

try {
    require_once 'AutoAsignacionGruas.php';
    $autoAsignacion = new AutoAsignacionGruas($conn);
    
    echo "<p class='success'>✅ Clase AutoAsignacionGruas cargada correctamente</p>";
    
    $configuracion = $autoAsignacion->obtenerConfiguracion();
    echo "<p class='info'>📋 Configuración cargada: " . count($configuracion) . " parámetros</p>";
    
    $habilitada = $autoAsignacion->estaHabilitada();
    echo "<p class='" . ($habilitada ? 'success' : 'error') . "'>" . 
         ($habilitada ? '✅' : '❌') . " Auto-asignación: " . 
         ($habilitada ? 'HABILITADA' : 'DESHABILITADA') . "</p>";
    
    // Probar estadísticas
    $estadisticas = $autoAsignacion->obtenerEstadisticas();
    echo "<h3>Estadísticas actuales:</h3>";
    echo "<pre>";
    print_r($estadisticas);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al cargar AutoAsignacionGruas: " . $e->getMessage() . "</p>";
}

echo "<h2>6. Enlaces de prueba</h2>";
echo "<p><a href='configuracion-auto-asignacion.php' target='_blank'>🔧 Panel de Configuración</a></p>";
echo "<p><a href='solicitud.php' target='_blank'>📝 Crear Solicitud de Prueba</a></p>";
echo "<p><a href='procesar-solicitud.php' target='_blank'>📋 Ver Solicitudes</a></p>";

echo "<h2>7. Instrucciones para probar</h2>";
echo "<ol>";
echo "<li><strong>Crear una solicitud:</strong> Ve a <a href='solicitud.php'>solicitud.php</a> y crea una solicitud de prueba</li>";
echo "<li><strong>Verificar asignación:</strong> Ve a <a href='procesar-solicitud.php'>procesar-solicitud.php</a> para ver si se asignó automáticamente</li>";
echo "<li><strong>Configurar parámetros:</strong> Ve a <a href='configuracion-auto-asignacion.php'>configuracion-auto-asignacion.php</a> para ajustar la configuración</li>";
echo "<li><strong>Ver historial:</strong> En el panel de configuración puedes ver el historial de asignaciones</li>";
echo "</ol>";

echo "<h2>8. Logs del sistema</h2>";
echo "<p>Los logs se guardan en: <code>auto_asignacion.log</code></p>";
echo "<p>Para ver logs en tiempo real, ejecuta: <code>tail -f auto_asignacion.log</code></p>";

$conn->close();

echo "<hr>";
echo "<p><strong>✅ Verificación completada</strong></p>";
echo "<p>Si todo está en verde, el sistema de auto-asignación está listo para usar.</p>";
?>
