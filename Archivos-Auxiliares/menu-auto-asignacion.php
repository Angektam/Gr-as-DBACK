<?php
// Configuración de la página
$page_title = 'Auto-Asignación - Grúas DBACK';
$additional_css = ['./CSS/MenuAdmin.css', 'https://cdn.jsdelivr.net/npm/chart.js'];

require_once 'conexion.php';
require_once 'AutoAsignacionGruas.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

$autoAsignacion = new AutoAsignacionGruas($conn);
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['guardar_configuracion'])) {
        $parametros_actualizados = 0;
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'param_') === 0) {
                $parametro = substr($key, 6); // Remover 'param_'
                if ($autoAsignacion->actualizarConfiguracion($parametro, $value)) {
                    $parametros_actualizados++;
                }
            }
        }
        
        if ($parametros_actualizados > 0) {
            $mensaje = "Se actualizaron $parametros_actualizados parámetros correctamente";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "No se pudieron actualizar los parámetros";
            $tipo_mensaje = "error";
        }
    }
    
    if (isset($_POST['procesar_pendientes'])) {
        $resultados = $autoAsignacion->procesarSolicitudesPendientes();
        $exitosos = count(array_filter($resultados, function($r) { return $r['resultado']['success']; }));
        $mensaje = "Se procesaron " . count($resultados) . " solicitudes. $exitosos asignaciones exitosas.";
        $tipo_mensaje = $exitosos > 0 ? "success" : "warning";
    }
    
    if (isset($_POST['resetear_configuracion'])) {
        if ($autoAsignacion->resetearConfiguracion()) {
            $mensaje = "Configuración restablecida a valores por defecto";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al restablecer la configuración";
            $tipo_mensaje = "error";
        }
    }
}

// Obtener configuración actual
$configuracion = $autoAsignacion->obtenerConfiguracion();

// Obtener estadísticas
$estadisticas = $autoAsignacion->obtenerEstadisticas();

// Obtener solicitudes pendientes
$query_pendientes = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente' AND grua_asignada_id IS NULL";
$result_pendientes = $conn->query($query_pendientes);
$solicitudes_pendientes = $result_pendientes->fetch_assoc()['total'];

// Obtener grúas disponibles
$query_gruas = "SELECT COUNT(*) as total FROM gruas WHERE estado = 'disponible'";
$result_gruas = $conn->query($query_gruas);
$gruas_disponibles = $result_gruas->fetch_assoc()['total'];

// Obtener información del usuario
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_cargo = $_SESSION['usuario_cargo'] ?? 'Operador';
?>

<?php include 'header-component.php'; ?>

<div class="container">
    <header class="admin-header">
        <nav aria-label="Navegación administrativa">
            <a href="MenuAdmin.PHP" class="back-button" aria-label="Volver al menú administrativo">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                <span>Volver al Menú</span>
            </a>
        </nav>
        <h1><i class="fas fa-robot"></i> Gestión de Auto-Asignación</h1>
        <p>Configura y gestiona el sistema automático de asignación de grúas</p>
    </header>

    <?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje == 'success' ? 'success' : ($tipo_mensaje == 'error' ? 'danger' : 'warning'); ?> alert-dismissible fade show">
        <i class="fas fa-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : ($tipo_mensaje == 'error' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stats-card warning">
            <div class="stats-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $solicitudes_pendientes; ?></div>
                <div class="stats-label">Solicitudes Pendientes</div>
            </div>
        </div>
        <div class="stats-card success">
            <div class="stats-icon">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $gruas_disponibles; ?></div>
                <div class="stats-label">Grúas Disponibles</div>
            </div>
        </div>
        <div class="stats-card info">
            <div class="stats-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo $estadisticas['asignaciones_automaticas'] ?? 0; ?></div>
                <div class="stats-label">Auto-Asignaciones</div>
            </div>
        </div>
        <div class="stats-card danger">
            <div class="stats-icon">
                <i class="fas fa-stopwatch"></i>
            </div>
            <div class="stats-content">
                <div class="stats-number"><?php echo round($estadisticas['tiempo_promedio_segundos'] ?? 0, 1); ?>s</div>
                <div class="stats-label">Tiempo Promedio</div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="action-panel">
        <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
        <div class="action-buttons">
            <form method="POST" class="action-form">
                <button type="submit" name="procesar_pendientes" class="btn btn-success btn-lg" id="procesarBtn">
                    <i class="fas fa-play-circle"></i> Procesar Solicitudes Pendientes
                </button>
            </form>
            <a href="procesar-solicitud.php" class="btn btn-primary btn-lg">
                <i class="fas fa-list-ul"></i> Ver Todas las Solicitudes
            </a>
            <button class="btn btn-info btn-lg" onclick="mostrarAyuda()">
                <i class="fas fa-question-circle"></i> Ayuda
            </button>
        </div>
        <p class="action-description">Procesa hasta 10 solicitudes pendientes automáticamente</p>
    </div>

    <!-- Configuración Principal -->
    <div class="config-panel">
        <h3><i class="fas fa-cogs"></i> Configuración de Auto-Asignación</h3>
        
        <form method="POST" id="configForm">
            <div class="config-sections">
                <!-- Configuración General -->
                <div class="config-section">
                    <h4><i class="fas fa-sliders-h"></i> Configuración General</h4>
                    
                    <div class="form-group">
                        <label class="form-label">Auto-Asignación Habilitada</label>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="param_auto_asignacion_habilitada" value="1" 
                                       <?php echo ($configuracion['auto_asignacion_habilitada'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label"><?php echo ($configuracion['auto_asignacion_habilitada'] ?? '0') == '1' ? 'Habilitada' : 'Deshabilitada'; ?></span>
                        </div>
                        <small class="form-text">Activa o desactiva el sistema de auto-asignación</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="radio_busqueda" class="form-label">Radio de Búsqueda (km)</label>
                        <input type="number" class="form-control" id="radio_busqueda" 
                               name="param_radio_busqueda_km" 
                               value="<?php echo $configuracion['radio_busqueda_km'] ?? '50'; ?>" 
                               min="1" max="200">
                        <small class="form-text">Distancia máxima para buscar grúas cercanas</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="tiempo_maximo" class="form-label">Tiempo Máximo de Espera (minutos)</label>
                        <input type="number" class="form-control" id="tiempo_maximo" 
                               name="param_tiempo_maximo_espera_minutos" 
                               value="<?php echo $configuracion['tiempo_maximo_espera_minutos'] ?? '30'; ?>" 
                               min="5" max="120">
                        <small class="form-text">Tiempo antes de asignar cualquier grúa disponible</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="distancia_maxima" class="form-label">Distancia Máxima (km)</label>
                        <input type="number" class="form-control" id="distancia_maxima" 
                               name="param_distancia_maxima_km" 
                               value="<?php echo $configuracion['distancia_maxima_km'] ?? '200'; ?>" 
                               min="10" max="500">
                        <small class="form-text">Distancia máxima para considerar una grúa</small>
                    </div>
                </div>
                
                <!-- Configuración Avanzada -->
                <div class="config-section">
                    <h4><i class="fas fa-microchip"></i> Configuración Avanzada</h4>
                    
                    <div class="form-group">
                        <label class="form-label">Considerar Tipo de Servicio</label>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="param_considerar_tipo_servicio" value="1" 
                                       <?php echo ($configuracion['considerar_tipo_servicio'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label"><?php echo ($configuracion['considerar_tipo_servicio'] ?? '0') == '1' ? 'Sí' : 'No'; ?></span>
                        </div>
                        <small class="form-text">Considera el tipo de servicio al asignar grúas</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="peso_maximo" class="form-label">Peso Máximo Vehículo (kg)</label>
                        <input type="number" class="form-control" id="peso_maximo" 
                               name="param_peso_maximo_vehiculo_kg" 
                               value="<?php echo $configuracion['peso_maximo_vehiculo_kg'] ?? '3500'; ?>" 
                               min="500" max="10000">
                        <small class="form-text">Peso máximo para asignar grúa de plataforma</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="reintentos" class="form-label">Reintentos de Asignación</label>
                        <input type="number" class="form-control" id="reintentos" 
                               name="param_reintentos_asignacion" 
                               value="<?php echo $configuracion['reintentos_asignacion'] ?? '3'; ?>" 
                               min="1" max="10">
                        <small class="form-text">Número de reintentos si falla la asignación</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="tiempo_reintentos" class="form-label">Tiempo Entre Reintentos (minutos)</label>
                        <input type="number" class="form-control" id="tiempo_reintentos" 
                               name="param_tiempo_entre_reintentos_minutos" 
                               value="<?php echo $configuracion['tiempo_entre_reintentos_minutos'] ?? '5'; ?>" 
                               min="1" max="30">
                        <small class="form-text">Tiempo de espera entre reintentos</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notificar Asignación</label>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" name="param_notificar_asignacion" value="1" 
                                       <?php echo ($configuracion['notificar_asignacion'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label"><?php echo ($configuracion['notificar_asignacion'] ?? '0') == '1' ? 'Sí' : 'No'; ?></span>
                        </div>
                        <small class="form-text">Envía notificaciones cuando se asigna una grúa</small>
                    </div>
                </div>
                
                <!-- Prioridades -->
                <div class="config-section">
                    <h4><i class="fas fa-sort-amount-down"></i> Prioridades de Urgencia</h4>
                    
                    <div class="form-group">
                        <label for="prioridad_urgencia" class="form-label">Orden de Prioridad</label>
                        <input type="text" class="form-control" id="prioridad_urgencia" 
                               name="param_prioridad_urgencia" 
                               value="<?php echo $configuracion['prioridad_urgencia'] ?? 'emergencia,urgente,normal'; ?>">
                        <small class="form-text">Orden de prioridad separado por comas (emergencia,urgente,normal)</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="guardar_configuracion" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
                <button type="submit" name="resetear_configuracion" class="btn btn-warning btn-lg" onclick="return confirm('¿Estás seguro de restablecer la configuración a valores por defecto?')">
                    <i class="fas fa-undo"></i> Restablecer Configuración
                </button>
            </div>
        </form>
    </div>

    <!-- Gráfico de Rendimiento -->
    <div class="chart-panel">
        <h3><i class="fas fa-chart-line"></i> Rendimiento del Sistema</h3>
        <canvas id="performanceChart" width="400" height="200"></canvas>
    </div>

    <!-- Historial de Asignaciones -->
    <div class="history-panel">
        <h3><i class="fas fa-history"></i> Historial de Asignaciones</h3>
        <?php
        $query_historial = "SELECT ha.*, s.nombre_completo, g.Placa, g.Tipo 
                           FROM historial_asignaciones ha
                           JOIN solicitudes s ON ha.solicitud_id = s.id
                           JOIN gruas g ON ha.grua_id = g.ID
                           ORDER BY ha.fecha_asignacion DESC 
                           LIMIT 10";
        $result_historial = $conn->query($query_historial);
        ?>
        
        <?php if ($result_historial->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Solicitud</th>
                        <th>Grúa</th>
                        <th>Método</th>
                        <th>Distancia</th>
                        <th>Tiempo</th>
                        <th>Criterios</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_historial->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_asignacion'])); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                        <td>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($row['Placa']); ?></span>
                            <small class="text-muted"><?php echo htmlspecialchars($row['Tipo']); ?></small>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $row['metodo_asignacion'] == 'automatica' ? 'success' : 'info'; ?>">
                                <?php echo $row['metodo_asignacion'] == 'automatica' ? 'Automática' : 'Manual'; ?>
                            </span>
                        </td>
                        <td><?php echo $row['distancia_km'] ? round($row['distancia_km'], 2) . ' km' : 'N/A'; ?></td>
                        <td><?php echo $row['tiempo_asignacion_segundos'] ? $row['tiempo_asignacion_segundos'] . 'ms' : 'N/A'; ?></td>
                        <td>
                            <small class="text-muted"><?php echo htmlspecialchars($row['criterios_usados'] ?? 'N/A'); ?></small>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="no-data">
            <i class="fas fa-inbox"></i>
            <p>No hay asignaciones registradas</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    margin: -30px -30px 30px -30px;
    border-radius: 0 0 20px 20px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: all 0.2s ease;
    margin-bottom: 20px;
}

.back-button:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    gap: 20px;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-card.warning {
    border-left: 5px solid #f39c12;
}

.stats-card.success {
    border-left: 5px solid #27ae60;
}

.stats-card.info {
    border-left: 5px solid #3498db;
}

.stats-card.danger {
    border-left: 5px solid #e74c3c;
}

.stats-icon {
    font-size: 2.5rem;
    color: #7f8c8d;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 5px;
    color: #2c3e50;
}

.stats-label {
    color: #7f8c8d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.action-panel, .config-panel, .chart-panel, .history-panel {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.action-panel h3, .config-panel h3, .chart-panel h3, .history-panel h3 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #ecf0f1;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.action-description {
    color: #7f8c8d;
    font-style: italic;
}

.config-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.config-section h4 {
    color: #3498db;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ecf0f1;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #ecf0f1;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    outline: none;
}

.form-text {
    color: #7f8c8d;
    font-size: 0.85rem;
    margin-top: 5px;
}

.toggle-container {
    display: flex;
    align-items: center;
    gap: 15px;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #3498db;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.toggle-label {
    font-weight: 500;
    color: #2c3e50;
}

.form-actions {
    text-align: center;
    padding-top: 20px;
    border-top: 2px solid #ecf0f1;
}

.btn {
    border-radius: 10px;
    padding: 12px 25px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 5px;
}

.btn-primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn-lg {
    padding: 15px 30px;
    font-size: 1.1rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table th, .table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.table tr:hover {
    background-color: #f5f5f5;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge-primary {
    background-color: #3498db;
    color: white;
}

.badge-success {
    background-color: #27ae60;
    color: white;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.no-data i {
    font-size: 3rem;
    margin-bottom: 15px;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: none;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
}

.close {
    float: right;
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .config-sections {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin: 5px 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar estado de los toggles
    document.querySelectorAll('.toggle-switch input').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const span = this.parentElement.nextElementSibling;
            if (this.checked) {
                span.textContent = this.name.includes('habilitada') ? 'Habilitada' : 
                                  this.name.includes('servicio') ? 'Sí' : 'Sí';
            } else {
                span.textContent = this.name.includes('habilitada') ? 'Deshabilitada' : 
                                  this.name.includes('servicio') ? 'No' : 'No';
            }
        });
    });
    
    // Confirmar procesamiento de solicitudes
    document.getElementById('procesarBtn').addEventListener('click', function(e) {
        if (!confirm('¿Estás seguro de procesar las solicitudes pendientes? Esto asignará grúas automáticamente.')) {
            e.preventDefault();
        }
    });
    
    // Gráfico de rendimiento
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Asignaciones Automáticas',
                    data: [12, 19, 3, 5, 2, 3, 8],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    fill: true,
                    tension: 0.3
                }, {
                    label: 'Asignaciones Manuales',
                    data: [2, 3, 20, 5, 1, 4, 2],
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Rendimiento Semanal del Sistema'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Asignaciones'
                        }
                    }
                }
            }
        });
    }
    
    // Auto-refresh de estadísticas cada 30 segundos
    setInterval(function() {
        // Aquí podrías agregar AJAX para actualizar las estadísticas sin recargar la página
        console.log('Actualizando estadísticas...');
    }, 30000);
});

function mostrarAyuda() {
    alert('Sistema de Auto-Asignación\n\n' +
          'Este sistema permite configurar la asignación automática de grúas a solicitudes.\n\n' +
          'Parámetros principales:\n' +
          '• Radio de búsqueda: Distancia máxima para buscar grúas\n' +
          '• Tiempo máximo: Tiempo de espera antes de asignar cualquier grúa\n' +
          '• Distancia máxima: Límite de distancia para considerar una grúa\n\n' +
          'El sistema considera la ubicación, tipo de servicio y disponibilidad de las grúas.');
}
</script>

<?php include 'footer-component.php'; ?>