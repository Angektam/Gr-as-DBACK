<?php
// Configuración de la página
$page_title = 'Procesar Solicitudes - Grúas DBACK';
$additional_css = ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'];

require_once '../../conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../Login.php");
    exit();
}

// Configuración de paginación
$registros_por_pagina = 8;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Construir la consulta base con filtros (ajustada a la tabla 'solicitudes')
$query = "SELECT SQL_CALC_FOUND_ROWS id, nombre_completo, telefono, tipo_servicio, ubicacion, 
          fecha_solicitud, IFNULL(estado, 'pendiente') as estado 
          FROM solicitudes 
          WHERE 1=1";

$params = [];
$types = '';

// Aplicar filtros
if (!empty($filtro_tipo)) {
    $query .= " AND tipo_servicio = ?";
    $params[] = $filtro_tipo;
    $types .= 's';
}

if (!empty($filtro_estado)) {
    $query .= " AND IFNULL(estado, 'pendiente') = ?";
    $params[] = $filtro_estado;
    $types .= 's';
}

// Ordenar por ID en lugar de fecha
$query .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $registros_por_pagina;
$params[] = $offset;

// Debug: Mostrar consulta si hay parámetro debug
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<div class='alert alert-info'>";
    echo "<strong>Debug - Consulta SQL:</strong><br>";
    echo "<code>" . htmlspecialchars($query) . "</code><br>";
    echo "<strong>Parámetros:</strong> " . implode(', ', $params) . "<br>";
    echo "<strong>Tipos:</strong> " . $types;
    echo "</div>";
}

// Preparar y ejecutar consulta
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Error en la consulta: " . $conn->error);
}

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Obtener total de registros
$total_registros = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Debug: Mostrar información de debug
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<div class='alert alert-warning'>";
    echo "<strong>Debug - Información:</strong><br>";
    echo "Total de registros: $total_registros<br>";
    echo "Página actual: $pagina_actual<br>";
    echo "Registros por página: $registros_por_pagina<br>";
    echo "Offset: $offset<br>";
    echo "Filtro tipo: '$filtro_tipo'<br>";
    echo "Filtro estado: '$filtro_estado'";
    echo "</div>";
}

// Tipos de servicio disponibles
$tipos_servicio = [
    '' => 'Seleccione una opción',
    'remolque' => 'Remolque',
    'bateria' => 'Cambio de batería',
    'gasolina' => 'Suministro de gasolina',
    'llanta' => 'Cambio de llanta',
    'arranque' => 'Servicio de arranque',
    'otro' => 'Otro servicio'
];

// Estados posibles con clases de Bootstrap
$estados = [
    'pendiente' => ['text' => 'Pendiente', 'class' => 'bg-warning text-dark'],
    'asignada' => ['text' => 'Servicio Pendiente', 'class' => 'bg-info text-white'],
    'en_proceso' => ['text' => 'En Proceso', 'class' => 'bg-primary text-white'],
    'completada' => ['text' => 'Completada', 'class' => 'bg-success text-white'],
    'cancelada' => ['text' => 'Cancelada', 'class' => 'bg-secondary text-white']
];

// Mensajes flash
$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
?>

<?php include '../../components/header-component.php'; ?>
    <style>
    :root {
    --primary-color: #0d6efd;
    --secondary-color: #6c757d;
    --success-color: #198754;
    --info-color: #0dcaf0;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --light-color: #f8f9fa;
    --dark-color: #212529;
    --border-radius: 8px;
    --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background-color: #f5f7fa;
    color: #333;
    line-height: 1.6;
}

/* Header y navegación */
.border-bottom {
    border-bottom: 2px solid rgba(0, 0, 0, 0.1) !important;
    padding-bottom: 1rem;
}

/* Tarjetas de solicitud */
.card-solicitud {
    border-radius: var(--border-radius);
    border: 1px solid rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: var(--transition);
    background-color: white;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.card-solicitud:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow);
    border-color: rgba(var(--primary-color), 0.2);
}

.card-header {
    background-color: rgba(0, 0, 0, 0.02);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 0.75rem 1rem;
}

.card-body {
    flex: 1;
    padding: 1.25rem;
}

.card-footer {
    background-color: rgba(0, 0, 0, 0.02);
    padding: 0.75rem 1rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

/* Badges de estado */
.badge {
    font-weight: 500;
    letter-spacing: 0.5px;
    padding: 5px 10px;
    border-radius: 50px;
    font-size: 0.75rem;
    text-transform: uppercase;
}

/* Botones */
.btn {
    border-radius: var(--border-radius);
    font-weight: 500;
    transition: var(--transition);
}

.btn-sm {
    padding: 0.35rem 0.65rem;
    font-size: 0.825rem;
}

.btn-outline-primary {
    border-color: rgba(var(--primary-color), 0.3);
}

.btn-outline-primary:hover {
    background-color: rgba(var(--primary-color), 0.1);
}

/* Contenedor de filtros */
.filtros-container {
    background-color: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--box-shadow);
    margin-bottom: 2rem;
}

.form-select, .form-control {
    border-radius: var(--border-radius);
    border: 1px solid rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.form-select:focus, .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-color), 0.1);
}

/* Paginación */
.pagination {
    margin-top: 2rem;
}

.page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.page-link {
    color: var(--primary-color);
    border-radius: var(--border-radius) !important;
    margin: 0 3px;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

/* Alertas */
.alert {
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: none;
}

/* Textos y títulos */
.card-title {
    color: var(--dark-color);
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.fw-bold {
    color: #495057;
    font-weight: 600 !important;
}

.id-solicitud {
    font-size: 0.85rem;
    color: var(--secondary-color);
    opacity: 0.8;
}

.text-muted {
    opacity: 0.7;
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.col {
    animation: fadeIn 0.4s ease forwards;
    opacity: 0;
}

/* Retrasos de animación para cada tarjeta */
.col:nth-child(1) { animation-delay: 0.05s; }
.col:nth-child(2) { animation-delay: 0.1s; }
.col:nth-child(3) { animation-delay: 0.15s; }
.col:nth-child(4) { animation-delay: 0.2s; }
.col:nth-child(5) { animation-delay: 0.25s; }
.col:nth-child(6) { animation-delay: 0.3s; }
.col:nth-child(7) { animation-delay: 0.35s; }
.col:nth-child(8) { animation-delay: 0.4s; }

/* Efecto hover para iconos */
.bi {
    transition: var(--transition);
}

.btn:hover .bi {
    transform: scale(1.1);
}

/* Responsive design */
@media (max-width: 768px) {
    .filtros-container .col-md-4 {
        margin-bottom: 1rem;
    }
    
    .card-title {
        font-size: 1rem;
    }
    
    .btn-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .border-bottom {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .id-solicitud {
        margin-top: 0.5rem;
    }
    
    .filtros-container {
        padding: 1rem;
    }
}

/* Estados con colores específicos */
.bg-warning {
    background-color: var(--warning-color) !important;
    color: var(--dark-color);
}

.bg-info {
    background-color: var(--info-color) !important;
    color: white;
}

.bg-primary {
    background-color: var(--primary-color) !important;
    color: white;
}

.bg-success {
    background-color: var(--success-color) !important;
    color: white;
}

.bg-secondary {
    background-color: var(--secondary-color) !important;
    color: white;
}

/* Mejoras para el contenedor principal */
.container-fluid {
    padding: 0 2rem;
    max-width: 1800px;
}

@media (max-width: 992px) {
    .container-fluid {
        padding: 0 1.5rem;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 0 1rem;
    }
}

/* Efecto de carga suave */
main {
    opacity: 0;
    animation: fadeIn 0.5s ease forwards 0.2s;
}

/* Mejora para los elementos de información en tarjetas */
.card-body div {
    margin-bottom: 0.6rem;
    display: flex;
    flex-wrap: wrap;
}

.card-body span.fw-bold {
    min-width: 80px;
    color: var(--secondary-color);
}

/* Mejora para el footer de las tarjetas */
.card-footer .d-flex {
    gap: 0.5rem;
}

.card-footer .btn {
    flex: 1;
    text-align: center;
    white-space: nowrap;
}
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-12 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <a href="MenuAdmin.PHP" class="btn btn-outline-secondary me-3" title="Volver al menú">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h1 class="h2 mb-0">Procesar Solicitudes</h1>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="id-solicitud me-3">Mostrando solicitudes ordenadas por ID</div>
                        <a href="corregir-solicitudes-pendientes.php" class="btn btn-warning btn-sm" title="Corregir solicitudes pendientes">
                            <i class="bi bi-tools"></i> Corregir
                        </a>
                        <a href="debug-solicitudes-pendientes.php" class="btn btn-info btn-sm" title="Diagnosticar problema">
                            <i class="bi bi-bug"></i> Diagnosticar
                        </a>
                        <a href="procesar-solicitud.php?debug=1" class="btn btn-outline-secondary btn-sm" title="Modo debug">
                            <i class="bi bi-code-slash"></i> Debug
                        </a>
                    </div>
                </div>

                <?php if ($mensaje): ?>
                <div class="alert alert-<?= $tipo_mensaje == 'exito' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="filtros-container mb-4">
                    <form method="get" class="row g-3">
                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo de servicio</label>
                            <select id="tipo" name="tipo" class="form-select">
                                <?php foreach ($tipos_servicio as $valor => $texto): ?>
                                    <option value="<?= $valor ?>" <?= $filtro_tipo == $valor ? 'selected' : '' ?>>
                                        <?= $texto ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($estados as $valor => $info): ?>
                                    <option value="<?= $valor ?>" <?= $filtro_estado == $valor ? 'selected' : '' ?>>
                                        <?= $info['text'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="?" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 mb-4">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($solicitud = $result->fetch_assoc()): 
                            $estado = strtolower($solicitud['estado']);
                            $info_estado = $estados[$estado] ?? $estados['pendiente'];
                            $tipo_servicio = $tipos_servicio[strtolower($solicitud['tipo_servicio'])] ?? $solicitud['tipo_servicio'];
                            $fecha_formateada = date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud']));
                        ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm card-solicitud">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="badge <?= $info_estado['class'] ?> rounded-pill">
                                        <?= $info_estado['text'] ?>
                                    </span>
                                    <small class="fw-bold">ID: <?= $solicitud['id'] ?></small>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($solicitud['nombre_completo']) ?></h5>
                                    <div class="mb-2">
                                        <span class="fw-bold">Servicio:</span>
                                        <span class="ms-2"><?= htmlspecialchars($tipo_servicio) ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="fw-bold">Teléfono:</span>
                                        <span class="ms-2"><?= htmlspecialchars($solicitud['telefono']) ?></span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="fw-bold">Ubicación:</span>
                                        <span class="ms-2"><?= htmlspecialchars($solicitud['ubicacion']) ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-clock"></i> <?= $fecha_formateada ?>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <div class="d-flex justify-content-between">
                                        <a href="detalle-solicitud.php?id=<?= $solicitud['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i> Detalles
                                        </a>
                                        
                                        <?php if ($estado == 'pendiente'): ?>
                                            <a href="gestion-solicitud.php?accion=aceptar&id=<?= $solicitud['id'] ?>" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Aceptar solicitud">
                                                <i class="bi bi-check-circle"></i> Aceptar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center py-4">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                No se encontraron solicitudes con los filtros aplicados
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination justify-content-center mt-4">
                        <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])) ?>" 
                               aria-label="Anterior">
                                &laquo;
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                            <a class="page-link" 
                               href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])) ?>" 
                               aria-label="Siguiente">
                                &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }, 5000);
            });
        });
    </script>
<?php include '../../components/footer-component.php'; ?>