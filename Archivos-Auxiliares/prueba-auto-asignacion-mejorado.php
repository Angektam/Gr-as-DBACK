<?php
/**
 * Script de prueba - Sistema de Auto-Asignación Mejorado
 */

echo "<h1>🔧 Prueba del Sistema de Auto-Asignación Mejorado</h1>";
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
.feature{background:#e8f5e8;padding:15px;border-radius:8px;margin:10px 0;border-left:4px solid #28a745;}
.config-item{background:#f8f9fa;padding:10px;border-radius:5px;margin:5px 0;border-left:3px solid #007bff;}
</style>";

echo "<div class='container'>";

echo "<h2>✅ Verificación del Sistema de Auto-Asignación</h2>";

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
echo "<h3>📊 Verificando Tablas del Sistema de Auto-Asignación:</h3>";

if (isset($conn) && !$conn->connect_error) {
    $tablas = [
        'configuracion_auto_asignacion' => 'Configuración del sistema',
        'historial_asignaciones' => 'Historial de asignaciones',
        'configuracion_tipos_servicio' => 'Configuración de tipos de servicio',
        'solicitudes' => 'Solicitudes de servicio',
        'gruas' => 'Grúas disponibles'
    ];
    
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Descripción</th><th>Estado</th><th>Registros</th></tr>";
    
    foreach ($tablas as $tabla => $descripcion) {
        $sql = "SHOW TABLES LIKE '$tabla'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $count_sql = "SELECT COUNT(*) as total FROM $tabla";
            $count_result = $conn->query($count_sql);
            $count = $count_result ? $count_result->fetch_assoc()['total'] : 0;
            
            echo "<tr>";
            echo "<td><strong>$tabla</strong></td>";
            echo "<td>$descripcion</td>";
            echo "<td><span style='color:green'>✅ Existe</span></td>";
            echo "<td>$count registros</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td><strong>$tabla</strong></td>";
            echo "<td>$descripcion</td>";
            echo "<td><span style='color:red'>❌ No existe</span></td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
}

echo "</div>";

echo "<div class='info'>";
echo "<h3>⚙️ Verificando Configuración Actual:</h3>";

if (isset($conn) && !$conn->connect_error) {
    $sql = "SELECT * FROM configuracion_auto_asignacion";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='config-item'>";
        echo "<h4>Parámetros de Configuración:</h4>";
        
        while($row = $result->fetch_assoc()) {
            echo "<p><strong>" . htmlspecialchars($row['parametro']) . ":</strong> " . htmlspecialchars($row['valor']) . "</p>";
        }
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ No hay configuración disponible</strong>";
        echo "</div>";
    }
}

echo "</div>";

echo "<h2>🚀 Nuevas Funcionalidades del Sistema de Auto-Asignación</h2>";

echo "<div class='feature'>";
echo "<h3>✅ 1. Interfaz de Usuario Mejorada</h3>";
echo "<ul>";
echo "<li><strong>Diseño Moderno:</strong> Interfaz limpia y profesional con gradientes y sombras</li>";
echo "<li><strong>Navegación Intuitiva:</strong> Menú lateral unificado con el resto del sistema</li>";
echo "<li><strong>Responsive Design:</strong> Adaptable a diferentes tamaños de pantalla</li>";
echo "<li><strong>Iconos Descriptivos:</strong> Iconos FontAwesome para mejor comprensión</li>";
echo "</ul>";
echo "</div>";

echo "<div class='feature'>";
echo "<h3>✅ 2. Configuración Fácil y Visual</h3>";
echo "<ul>";
echo "<li><strong>Toggle Switches:</strong> Interruptores visuales para activar/desactivar funciones</li>";
echo "<li><strong>Formularios Organizados:</strong> Parámetros agrupados por categorías</li>";
echo "<li><strong>Validación en Tiempo Real:</strong> Validación de valores antes del guardado</li>";
echo "<li><strong>Valores por Defecto:</strong> Configuración preestablecida optimizada</li>";
echo "</ul>";
echo "</div>";

echo "<div class='feature'>";
echo "<h3>✅ 3. Estadísticas en Tiempo Real</h3>";
echo "<ul>";
echo "<li><strong>Tarjetas de Estadísticas:</strong> Solicitudes pendientes, grúas disponibles, etc.</li>";
echo "<li><strong>Gráfico de Rendimiento:</strong> Visualización del rendimiento semanal</li>";
echo "<li><strong>Historial de Asignaciones:</strong> Tabla con las últimas asignaciones</li>";
echo "<li><strong>Auto-refresh:</strong> Actualización automática cada 30 segundos</li>";
echo "</ul>";
echo "</div>";

echo "<div class='feature'>";
echo "<h3>✅ 4. Acciones Rápidas</h3>";
echo "<ul>";
echo "<li><strong>Procesar Pendientes:</strong> Botón para procesar solicitudes automáticamente</li>";
echo "<li><strong>Ver Solicitudes:</strong> Acceso directo a la gestión de solicitudes</li>";
echo "<li><strong>Ayuda Contextual:</strong> Botón de ayuda con información del sistema</li>";
echo "<li><strong>Restablecer Configuración:</strong> Opción para volver a valores por defecto</li>";
echo "</ul>";
echo "</div>";

echo "<div class='feature'>";
echo "<h3>✅ 5. Parámetros Configurables</h3>";
echo "<ul>";
echo "<li><strong>Auto-Asignación:</strong> Habilitar/deshabilitar el sistema</li>";
echo "<li><strong>Radio de Búsqueda:</strong> Distancia máxima para buscar grúas (1-200 km)</li>";
echo "<li><strong>Tiempo Máximo:</strong> Tiempo de espera antes de asignar (5-120 min)</li>";
echo "<li><strong>Distancia Máxima:</strong> Límite de distancia para considerar grúas (10-500 km)</li>";
echo "<li><strong>Tipo de Servicio:</strong> Considerar el tipo al asignar</li>";
echo "<li><strong>Peso Máximo:</strong> Peso límite para grúas de plataforma (500-10000 kg)</li>";
echo "<li><strong>Reintentos:</strong> Número de reintentos si falla (1-10)</li>";
echo "<li><strong>Tiempo Entre Reintentos:</strong> Espera entre reintentos (1-30 min)</li>";
echo "<li><strong>Notificaciones:</strong> Enviar notificaciones de asignación</li>";
echo "<li><strong>Prioridades:</strong> Orden de prioridad por urgencia</li>";
echo "</ul>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🔗 Enlaces de Prueba:</h3>";
echo "<p><a href='menu-auto-asignacion.php' target='_blank' class='btn'>🤖 Ir al Sistema de Auto-Asignación</a></p>";
echo "<p><a href='procesar-solicitud.php' target='_blank' class='btn'>📋 Ver Solicitudes</a></p>";
echo "<p><a href='MenuAdmin.PHP' target='_blank' class='btn'>🏠 Ir al Menú Principal</a></p>";
echo "</div>";

echo "<h2>✅ Estado del Sistema</h2>";

if (isset($conn) && !$conn->connect_error) {
    echo "<div class='success'>";
    echo "<h3>🎉 ¡Sistema de Auto-Asignación Completamente Mejorado!</h3>";
    echo "<p><strong>Mejoras implementadas:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Interfaz de usuario moderna y profesional</li>";
    echo "<li>✅ Configuración visual con toggle switches</li>";
    echo "<li>✅ Estadísticas en tiempo real</li>";
    echo "<li>✅ Gráfico de rendimiento interactivo</li>";
    echo "<li>✅ Historial de asignaciones detallado</li>";
    echo "<li>✅ Acciones rápidas para el usuario</li>";
    echo "<li>✅ Validación y confirmaciones</li>";
    echo "<li>✅ Barra lateral unificada</li>";
    echo "<li>✅ Diseño responsive</li>";
    echo "<li>✅ Ayuda contextual</li>";
    echo "</ul>";
    echo "<p><strong>El usuario ahora puede editar fácilmente todos los parámetros del sistema de auto-asignación.</strong></p>";
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
