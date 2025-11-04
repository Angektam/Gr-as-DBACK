<?php
/**
 * GESTIÓN DE GRÚAS - VERSIÓN MEJORADA
 * Sistema completo para administrar la flota de grúas
 */

require_once 'conexion.php';
// La sesión ya se inicia en config.php

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login.php");
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

// ==================== PROCESAR ACCIONES ====================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // AGREGAR GRÚA
    if ($action === 'add') {
        $placa = strtoupper($conn->real_escape_string(trim($_POST['placa'])));
        $marca = $conn->real_escape_string(trim($_POST['marca']));
        $modelo = $conn->real_escape_string(trim($_POST['modelo']));
        $tipo = $conn->real_escape_string($_POST['tipo']);
        $estado = $conn->real_escape_string($_POST['estado']);
        
        $query = "INSERT INTO gruas (Placa, Marca, Modelo, Tipo, Estado) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $placa, $marca, $modelo, $tipo, $estado);
        
        if ($stmt->execute()) {
            $tipo_mensaje = 'success';
            $mensaje = '✅ Grúa agregada exitosamente';
        } else {
            $tipo_mensaje = 'error';
            $mensaje = '❌ Error al agregar grúa: ' . $conn->error;
        }
    }
    
    // EDITAR GRÚA
    elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $placa = strtoupper($conn->real_escape_string(trim($_POST['placa'])));
        $marca = $conn->real_escape_string(trim($_POST['marca']));
        $modelo = $conn->real_escape_string(trim($_POST['modelo']));
        $tipo = $conn->real_escape_string($_POST['tipo']);
        $estado = $conn->real_escape_string($_POST['estado']);
        
        $query = "UPDATE gruas SET Placa=?, Marca=?, Modelo=?, Tipo=?, Estado=? WHERE ID=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $placa, $marca, $modelo, $tipo, $estado, $id);
        
        if ($stmt->execute()) {
            $tipo_mensaje = 'success';
            $mensaje = '✅ Grúa actualizada exitosamente';
        } else {
            $tipo_mensaje = 'error';
            $mensaje = '❌ Error al actualizar grúa';
        }
    }
    
    // ELIMINAR GRÚA
    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        $query = "DELETE FROM gruas WHERE ID=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $tipo_mensaje = 'success';
            $mensaje = '✅ Grúa eliminada exitosamente';
        } else {
            $tipo_mensaje = 'error';
            $mensaje = '❌ Error al eliminar grúa';
        }
    }
}

// ==================== FILTROS Y BÚSQUEDA ====================

$busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string(trim($_GET['busqueda'])) : '';
$filtro_tipo = isset($_GET['tipo']) ? $conn->real_escape_string($_GET['tipo']) : '';
$filtro_estado = isset($_GET['estado']) ? $conn->real_escape_string($_GET['estado']) : '';

// ==================== PAGINACIÓN ====================

$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// ==================== CONSULTA CON FILTROS ====================

$where_conditions = ["1=1"];
$params = [];
$types = "";

if (!empty($busqueda)) {
    $where_conditions[] = "(Placa LIKE ? OR Marca LIKE ? OR Modelo LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param]);
    $types .= "sss";
}

if (!empty($filtro_tipo)) {
    $where_conditions[] = "Tipo = ?";
    $params[] = $filtro_tipo;
    $types .= "s";
}

if (!empty($filtro_estado)) {
    $where_conditions[] = "Estado = ?";
    $params[] = $filtro_estado;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Contar total de registros
$count_query = "SELECT COUNT(*) as total FROM gruas WHERE $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_registros = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener grúas con paginación
$query = "SELECT * FROM gruas WHERE $where_clause ORDER BY ID DESC LIMIT ? OFFSET ?";
$params[] = $registros_por_pagina;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$gruas = [];
while ($row = $result->fetch_assoc()) {
    $gruas[] = $row;
}

// ==================== ESTADÍSTICAS ====================

$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN Estado = 'Activa' THEN 1 ELSE 0 END) as activas,
                SUM(CASE WHEN Estado = 'Mantenimiento' THEN 1 ELSE 0 END) as mantenimiento,
                SUM(CASE WHEN Estado = 'Inactiva' THEN 1 ELSE 0 END) as inactivas
                FROM gruas";
$stats = $conn->query($stats_query)->fetch_assoc();

// Obtener tipos únicos
$tipos_query = "SELECT DISTINCT Tipo FROM gruas WHERE Tipo IS NOT NULL ORDER BY Tipo";
$tipos_result = $conn->query($tipos_query);
$tipos = [];
while ($row = $tipos_result->fetch_assoc()) {
    $tipos[] = $row['Tipo'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grúas - DBACK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./CSS/Gruas.css">
</head>
<body>
    <div class="main-container">
        <a href="MenuAdmin.PHP" class="back-button">
            <i class="fas fa-arrow-left"></i> Volver al Menú
        </a>
        
        <div class="header-section">
            <h1><i class="fas fa-truck"></i> Gestión de Grúas</h1>
            <p class="text-muted">Sistema de administración y seguimiento de flota de grúas</p>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
                <div class="stat-card">
                <div class="number"><?php echo $stats['total']; ?></div>
                <div class="label">Total Grúas</div>
                </div>
            <div class="stat-card success">
                <div class="number"><?php echo $stats['activas']; ?></div>
                <div class="label">Activas</div>
                </div>
            <div class="stat-card warning">
                <div class="number"><?php echo $stats['mantenimiento']; ?></div>
                <div class="label">Mantenimiento</div>
                </div>
            <div class="stat-card danger">
                <div class="number"><?php echo $stats['inactivas']; ?></div>
                <div class="label">Inactivas</div>
                </div>
            </div>
            
        <!-- Filtros y Búsqueda -->
        <div class="filters-section">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="busqueda" class="form-control" 
                           placeholder="🔍 Buscar por placa, marca o modelo..." 
                           value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <div class="col-md-3">
                    <select name="tipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo); ?>" 
                                <?php echo $filtro_tipo == $tipo ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="Activa" <?php echo $filtro_estado == 'Activa' ? 'selected' : ''; ?>>Activa</option>
                        <option value="Mantenimiento" <?php echo $filtro_estado == 'Mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                        <option value="Inactiva" <?php echo $filtro_estado == 'Inactiva' ? 'selected' : ''; ?>>Inactiva</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
            </form>
                </div>
                
        <!-- Botones de Acción -->
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-success" onclick="abrirModalAgregar()">
                    <i class="fas fa-plus"></i> Nueva Grúa
                </button>
            </div>
            <div class="text-muted">
                Mostrando <?php echo count($gruas); ?> de <?php echo $total_registros; ?> grúas
                </div>
            </div>
            
        <!-- Tabla de Grúas -->
        <div class="table-container">
            <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php if (empty($gruas)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron grúas</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($gruas as $grua): ?>
                        <tr>
                            <td><?php echo $grua['ID']; ?></td>
                            <td><strong><?php echo htmlspecialchars($grua['Placa']); ?></strong></td>
                            <td><?php echo htmlspecialchars($grua['Marca']); ?></td>
                            <td><?php echo htmlspecialchars($grua['Modelo']); ?></td>
                            <td><?php echo htmlspecialchars($grua['Tipo']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $grua['Estado'] == 'Activa' ? 'success' : 
                                        ($grua['Estado'] == 'Mantenimiento' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo $grua['Estado']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editarGrua(<?php echo json_encode($grua); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="eliminarGrua(<?php echo $grua['ID']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($pagina_actual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&tipo=<?php echo urlencode($filtro_tipo); ?>&estado=<?php echo urlencode($filtro_estado); ?>">
                        Anterior
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>&tipo=<?php echo urlencode($filtro_tipo); ?>&estado=<?php echo urlencode($filtro_estado); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagina_actual < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&tipo=<?php echo urlencode($filtro_tipo); ?>&estado=<?php echo urlencode($filtro_estado); ?>">
                        Siguiente
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        </div>
        
    <!-- Modal Agregar/Editar Grúa -->
    <div class="modal fade" id="gruaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Agregar Grúa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                <form id="gruaForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="gruaId">
                        
                        <div class="mb-3">
                            <label class="form-label">Placa *</label>
                            <input type="text" name="placa" id="placa" class="form-control" maxlength="10" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Marca *</label>
                            <input type="text" name="marca" id="marca" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Modelo *</label>
                            <input type="text" name="modelo" id="modelo" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo *</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="Plataforma">Plataforma</option>
                                <option value="Arrastre">Arrastre</option>
                                <option value="Remolque">Remolque</option>
                                <option value="Grúa">Grúa</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Estado *</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="Activa">Activa</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Inactiva">Inactiva</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar
                        </button>
            </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('gruaModal'));
        
        function abrirModalAgregar() {
            document.getElementById('modalTitle').textContent = 'Agregar Grúa';
            document.getElementById('formAction').value = 'add';
            document.getElementById('gruaForm').reset();
            modal.show();
        }
        
        function editarGrua(grua) {
            document.getElementById('modalTitle').textContent = 'Editar Grúa';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('gruaId').value = grua.ID;
            document.getElementById('placa').value = grua.Placa;
            document.getElementById('marca').value = grua.Marca;
            document.getElementById('modelo').value = grua.Modelo;
            document.getElementById('tipo').value = grua.Tipo;
            document.getElementById('estado').value = grua.Estado;
            modal.show();
        }
        
        function eliminarGrua(id) {
            if (confirm('¿Estás seguro de eliminar esta grúa?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Convertir placa a mayúsculas
        document.getElementById('placa').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
