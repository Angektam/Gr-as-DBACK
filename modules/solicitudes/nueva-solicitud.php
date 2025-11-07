<?php
// Configuración de la página
$page_title = 'Nueva Solicitud - Grúas DBACK';
$additional_css = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css',
    '../../CSS/panel-solicitud.css'
];

require_once '../../conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../Login.php");
    exit();
}

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar y limpiar datos
    $cliente_id = intval($_POST['cliente_id']);
    $fecha_servicio = $conn->real_escape_string($_POST['fecha_servicio']);
    $ubicacion_origen = $conn->real_escape_string($_POST['ubicacion_origen']);
    $ubicacion_destino= $conn->real_escape_string($_POST['ubicacion_destino']);
    $distancia_km = floatval($_POST['distancia_km']);
    $tipo_servicio = $conn->real_escape_string($_POST['tipo_servicio']);
    $urgencia = $conn->real_escape_string($_POST['urgencia']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    
    // Calcular costo basado en distancia y tipo de servicio
    $costo = calcularCosto($distancia_km, $tipo_servicio, $urgencia);
    
    // Insertar solicitud
    $query = "INSERT INTO solicitudes (cliente_id, fecha_servicio, ubicacion_origen, ubicacion_destino, distancia_km, tipo_servicio, urgencia, estado, costo, descripcion) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssdssds", $cliente_id, $fecha_servicio, $ubicacion_origen, $ubicacion_destino, $distancia_km, $tipo_servicio, $urgencia, $costo, $descripcion);
    
    if ($stmt->execute()) {
        $mensaje = "Solicitud creada exitosamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al crear la solicitud: " . $stmt->error;
        $tipo_mensaje = "danger";
    }
    $stmt->close();
}

// Función para calcular costo
function calcularCosto($distancia, $tipo_servicio, $urgencia) {
    $costo_base = 50; // Costo base
    $costo_km = 15; // Costo por kilómetro
    
    $costo = $costo_base + ($distancia * $costo_km);
    
    // Ajustar por tipo de servicio
    switch ($tipo_servicio) {
        case 'emergencia':
            $costo *= 1.5;
            break;
        case 'traslado':
            $costo *= 1.2;
            break;
    }
    
    // Ajustar por urgencia
    switch ($urgencia) {
        case 'urgente':
            $costo *= 1.3;
            break;
        case 'normal':
            $costo *= 1.0;
            break;
    }
    
    return round($costo, 2);
}

// Obtener lista de clientes
$query = "SELECT id, nombre, telefono FROM clientes ORDER BY nombre";
$clientes_result = $conn->query($query);
?>

<?php include '../../components/header-component.php'; ?>

<div class="container-fluid">
  <!-- Encabezado -->
  <header class="py-4">
    <div class="d-flex align-items-center">
      <a href="panel-solicitud.php" class="btn btn-outline-primary me-3">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
      <h1 class="h2 mb-0"><i class="bi bi-plus-circle me-2"></i> Nueva Solicitud</h1>
    </div>
  </header>

  <!-- Mensaje de resultado -->
  <?php if (isset($mensaje)): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
      <?php echo $mensaje; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Formulario -->
  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="bi bi-clipboard-plus me-2"></i> Información de la Solicitud</h5>
        </div>
        <div class="card-body">
          <form method="POST" id="solicitudForm">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select class="form-select" id="cliente_id" name="cliente_id" required>
                  <option value="">Seleccionar cliente...</option>
                  <?php while ($cliente = $clientes_result->fetch_assoc()): ?>
                    <option value="<?php echo $cliente['id']; ?>">
                      <?php echo htmlspecialchars($cliente['nombre']); ?> - <?php echo htmlspecialchars($cliente['telefono']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="fecha_servicio" class="form-label">Fecha del Servicio</label>
                <input type="datetime-local" class="form-control" id="fecha_servicio" name="fecha_servicio" required>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="ubicacion_origen" class="form-label">Ubicación de Origen</label>
                <input type="text" class="form-control" id="ubicacion_origen" name="ubicacion_origen" required>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="ubicacion_destino" class="form-label">Ubicación de Destino</label>
                <input type="text" class="form-control" id="ubicacion_destino" name="ubicacion_destino" required>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="distancia_km" class="form-label">Distancia (km)</label>
                <input type="number" class="form-control" id="distancia_km" name="distancia_km" step="0.1" min="0" required>
              </div>
              
              <div class="col-md-4 mb-3">
                <label for="tipo_servicio" class="form-label">Tipo de Servicio</label>
                <select class="form-select" id="tipo_servicio" name="tipo_servicio" required>
                  <option value="">Seleccionar...</option>
                  <option value="emergencia">Emergencia</option>
                  <option value="traslado">Traslado</option>
                  <option value="mantenimiento">Mantenimiento</option>
                </select>
              </div>
              
              <div class="col-md-4 mb-3">
                <label for="urgencia" class="form-label">Urgencia</label>
                <select class="form-select" id="urgencia" name="urgencia" required>
                  <option value="">Seleccionar...</option>
                  <option value="urgente">Urgente</option>
                  <option value="normal">Normal</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="descripcion" class="form-label">Descripción</label>
              <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i> Crear Solicitud
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="bi bi-calculator me-2"></i> Estimación de Costo</h5>
        </div>
        <div class="card-body">
          <div id="costo-estimado">
            <p class="text-muted">Complete el formulario para ver el costo estimado</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calcular costo estimado en tiempo real
    const form = document.getElementById('solicitudForm');
    const distanciaInput = document.getElementById('distancia_km');
    const tipoServicioSelect = document.getElementById('tipo_servicio');
    const urgenciaSelect = document.getElementById('urgencia');
    const costoDiv = document.getElementById('costo-estimado');

    function calcularCostoEstimado() {
        const distancia = parseFloat(distanciaInput.value) || 0;
        const tipoServicio = tipoServicioSelect.value;
        const urgencia = urgenciaSelect.value;

        if (distancia > 0 && tipoServicio && urgencia) {
            let costo = 50 + (distancia * 15);
            
            // Ajustar por tipo de servicio
            switch (tipoServicio) {
                case 'emergencia':
                    costo *= 1.5;
                    break;
                case 'traslado':
                    costo *= 1.2;
                    break;
            }
            
            // Ajustar por urgencia
            switch (urgencia) {
                case 'urgente':
                    costo *= 1.3;
                    break;
            }
            
            costoDiv.innerHTML = `
                <div class="text-center">
                    <h4 class="text-primary">$${costo.toFixed(2)} MXN</h4>
                    <small class="text-muted">Costo estimado</small>
                </div>
            `;
        } else {
            costoDiv.innerHTML = '<p class="text-muted">Complete el formulario para ver el costo estimado</p>';
        }
    }

    distanciaInput.addEventListener('input', calcularCostoEstimado);
    tipoServicioSelect.addEventListener('change', calcularCostoEstimado);
    urgenciaSelect.addEventListener('change', calcularCostoEstimado);
});
</script>

<?php include '../../components/footer-component.php'; ?>