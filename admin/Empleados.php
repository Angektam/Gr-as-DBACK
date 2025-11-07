<?php
/**
 * GESTIÓN DE EMPLEADOS - VERSIÓN MEJORADA
 * Sistema completo para administrar empleados con:
 * - CRUD completo
 * - Búsqueda y filtros avanzados
 * - Paginación
 * - Estadísticas en tiempo real
 * - Exportación a Excel
 * - Validaciones
 * - Estados (activo/inactivo)
 * - Historial de cambios
 */

require_once '../conexion.php';
require_once '../utils/validaciones.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Login.php");
    exit();
}

// Generar token CSRF
$csrf_token = generarCSRF();

$mensaje = '';
$tipo_mensaje = '';
$usuario_cargo = $_SESSION['usuario_cargo'] ?? 'Usuario';

// ==================== PROCESAR ACCIONES ====================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    $token_recibido = $_POST['csrf_token'] ?? '';
    if (!validarCSRF($token_recibido)) {
        $tipo_mensaje = 'error';
        $mensaje = '❌ Error de seguridad: token inválido. Por favor, recarga la página.';
    } else {
        $action = $_POST['action'] ?? '';
        $validador = new Validador();
        
        // AGREGAR EMPLEADO
        if ($action === 'add') {
            $nombres = Validador::sanitizar($_POST['nombres'] ?? '', 'string');
            $apellido1 = Validador::sanitizar($_POST['apellido1'] ?? '', 'string');
            $apellido2 = Validador::sanitizar($_POST['apellido2'] ?? '', 'string');
            $rfc = strtoupper(Validador::sanitizar($_POST['rfc'] ?? '', 'string'));
            $nomina = intval($_POST['nomina'] ?? 0);
            $fecha_ingreso = Validador::sanitizar($_POST['fecha_ingreso'] ?? '', 'string');
            $puesto = Validador::sanitizar($_POST['puesto'] ?? '', 'string');
            $departamento = Validador::sanitizar($_POST['departamento'] ?? '', 'string');
            $sueldo = floatval($_POST['sueldo'] ?? 0);
            $telefono = Validador::sanitizar($_POST['telefono'] ?? '', 'string');
            $email = Validador::sanitizar($_POST['email'] ?? '', 'email');
            $licencia = Validador::sanitizar($_POST['licencia'] ?? '', 'string');
            $direccion = Validador::sanitizar($_POST['direccion'] ?? '', 'string');
            $estado = 'activo';
            
            // Validaciones
            $validador->validarNombre($nombres, 'nombres', 2, 50, true);
            $validador->validarNombre($apellido1, 'apellido1', 2, 50, true);
            if (!empty($apellido2)) {
                $validador->validarNombre($apellido2, 'apellido2', 2, 50, false);
            }
            $validador->requerido($rfc, 'rfc', 'El RFC es requerido');
            $validador->longitud($rfc, 'rfc', 12, 13);
            if (!preg_match('/^[A-Z&Ñ]{3,4}\d{6}[A-V1-9][A-Z1-9][0-9A]$/', $rfc)) {
                $validador->agregarError('rfc', 'RFC inválido. Por favor verifica el formato.');
            }
            $validador->validarNumero($nomina, 'nomina', 1, 999999);
            $validador->requerido($fecha_ingreso, 'fecha_ingreso', 'La fecha de ingreso es requerida');
            $validador->requerido($puesto, 'puesto', 'El puesto es requerido');
            $validador->requerido($departamento, 'departamento', 'El departamento es requerido');
            $validador->validarNumero($sueldo, 'sueldo', 0, 999999);
            if (!empty($telefono)) {
                $validador->validarTelefono($telefono, 'telefono', false);
            }
            if (!empty($email)) {
                $validador->validarEmail($email, 'email', false);
            }
            
            if (!$validador->tieneErrores()) {
                $query = "INSERT INTO empleados 
                         (Nombres, Apellido1, Apellido2, RFC, Nomina, Fecha_Ingreso, Puesto, 
                          departamento, Sueldo, telefono, email, licencia, direccion, estado) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssissdssssss", $nombres, $apellido1, $apellido2, $rfc, $nomina, 
                                 $fecha_ingreso, $puesto, $departamento, $sueldo, $telefono, 
                                 $email, $licencia, $direccion, $estado);
                
                if ($stmt->execute()) {
                    $tipo_mensaje = 'success';
                    $mensaje = '✅ Empleado agregado exitosamente';
                    
                    // Registrar en historial
                    $empleado_id = $stmt->insert_id;
                    $usuario_id = $_SESSION['usuario_id'];
                    $accion = "Empleado creado: $nombres $apellido1";
                    
                    $log_query = "INSERT INTO historial_empleados (empleado_id, usuario_id, accion, fecha) 
                                 VALUES (?, ?, ?, NOW())";
                    $log_stmt = $conn->prepare($log_query);
                    $log_stmt->bind_param("iis", $empleado_id, $usuario_id, $accion);
                    $log_stmt->execute();
                    $stmt->close();
                } else {
                    $tipo_mensaje = 'error';
                    $mensaje = '❌ Error al agregar empleado: ' . $stmt->error;
                    $stmt->close();
                }
            } else {
                $tipo_mensaje = 'error';
                $mensaje = '❌ Errores de validación: ' . $validador->obtenerErroresString(', ');
            }
        }
        
        // EDITAR EMPLEADO
        elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $nombres = $conn->real_escape_string(trim($_POST['nombres']));
        $apellido1 = $conn->real_escape_string(trim($_POST['apellido1']));
        $apellido2 = $conn->real_escape_string(trim($_POST['apellido2']));
        $rfc = strtoupper($conn->real_escape_string(trim($_POST['rfc'])));
        $nomina = intval($_POST['nomina']);
        $fecha_ingreso = $conn->real_escape_string($_POST['fecha_ingreso']);
        $puesto = $conn->real_escape_string($_POST['puesto']);
        $departamento = $conn->real_escape_string($_POST['departamento']);
        $sueldo = floatval($_POST['sueldo']);
        $telefono = $conn->real_escape_string(trim($_POST['telefono']));
        $email = $conn->real_escape_string(trim($_POST['email']));
        $licencia = $conn->real_escape_string(trim($_POST['licencia']));
        $direccion = $conn->real_escape_string(trim($_POST['direccion']));
        
        $query = "UPDATE empleados SET 
                 Nombres=?, Apellido1=?, Apellido2=?, RFC=?, Nomina=?, Fecha_Ingreso=?, 
                 Puesto=?, departamento=?, Sueldo=?, telefono=?, email=?, licencia=?, direccion=?
                 WHERE ID_Empleado=?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssissdsssssi", $nombres, $apellido1, $apellido2, $rfc, $nomina, 
                         $fecha_ingreso, $puesto, $departamento, $sueldo, $telefono, 
                         $email, $licencia, $direccion, $id);
        
        if ($stmt->execute()) {
            $tipo_mensaje = 'success';
            $mensaje = '✅ Empleado actualizado exitosamente';
            
            // Registrar en historial
            $usuario_id = $_SESSION['usuario_id'];
            $accion = "Empleado actualizado: $nombres $apellido1";
            
            $log_query = "INSERT INTO historial_empleados (empleado_id, usuario_id, accion, fecha) 
                         VALUES (?, ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("iis", $id, $usuario_id, $accion);
            $log_stmt->execute();
        } else {
            $tipo_mensaje = 'error';
            $mensaje = '❌ Error al actualizar empleado';
        }
    }
    
    // ELIMINAR EMPLEADO (cambiar a inactivo)
    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        $query = "UPDATE empleados SET estado='inactivo', fecha_baja=NOW() WHERE ID_Empleado=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $tipo_mensaje = 'success';
            $mensaje = '✅ Empleado dado de baja exitosamente';
            
            // Registrar en historial
            $usuario_id = $_SESSION['usuario_id'];
            $accion = "Empleado dado de baja";
            
            $log_query = "INSERT INTO historial_empleados (empleado_id, usuario_id, accion, fecha) 
                         VALUES (?, ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("iis", $id, $usuario_id, $accion);
            $log_stmt->execute();
        } else {
            $tipo_mensaje = 'error';
            $mensaje = '❌ Error al dar de baja al empleado';
        }
    }
    
    // REACTIVAR EMPLEADO
    elseif ($action === 'activate') {
            if ($id <= 0) {
                $tipo_mensaje = 'error';
                $mensaje = '❌ ID de empleado inválido';
            } else {
                $query = "UPDATE empleados SET estado='activo', fecha_baja=NULL WHERE ID_Empleado=?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $tipo_mensaje = 'success';
                    $mensaje = '✅ Empleado reactivado exitosamente';
                    $stmt->close();
                } else {
                    $tipo_mensaje = 'error';
                    $mensaje = '❌ Error al reactivar empleado: ' . $stmt->error;
                    $stmt->close();
                }
            }
        }
    }
}

// ==================== FILTROS Y BÚSQUEDA ====================

$busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string(trim($_GET['busqueda'])) : '';
$filtro_puesto = isset($_GET['puesto']) ? $conn->real_escape_string($_GET['puesto']) : '';
$filtro_estado = isset($_GET['estado']) ? $conn->real_escape_string($_GET['estado']) : 'activo';
$filtro_departamento = isset($_GET['departamento']) ? $conn->real_escape_string($_GET['departamento']) : '';

// ==================== PAGINACIÓN ====================

$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// ==================== CONSULTA CON FILTROS ====================

$where_conditions = ["1=1"];
$params = [];
$types = "";

if (!empty($busqueda)) {
    $where_conditions[] = "(Nombres LIKE ? OR Apellido1 LIKE ? OR Apellido2 LIKE ? OR RFC LIKE ? OR email LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param]);
    $types .= "sssss";
}

if (!empty($filtro_puesto)) {
    $where_conditions[] = "Puesto = ?";
    $params[] = $filtro_puesto;
    $types .= "s";
}

if (!empty($filtro_estado)) {
    $where_conditions[] = "estado = ?";
    $params[] = $filtro_estado;
    $types .= "s";
}

if (!empty($filtro_departamento)) {
    $where_conditions[] = "departamento = ?";
    $params[] = $filtro_departamento;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Contar total de registros
$count_query = "SELECT COUNT(*) as total FROM empleados WHERE $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_registros = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener empleados con paginación
$query = "SELECT * FROM empleados WHERE $where_clause ORDER BY ID_Empleado DESC LIMIT ? OFFSET ?";
$params[] = $registros_por_pagina;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$empleados = [];
while ($row = $result->fetch_assoc()) {
        $empleados[] = $row;
    }

// ==================== ESTADÍSTICAS ====================

$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                AVG(Sueldo) as sueldo_promedio,
                SUM(Sueldo) as nomina_total
                FROM empleados";
$stats = $conn->query($stats_query)->fetch_assoc();

// Obtener puestos únicos para filtro
$puestos_query = "SELECT DISTINCT Puesto FROM empleados WHERE Puesto IS NOT NULL ORDER BY Puesto";
$puestos_result = $conn->query($puestos_query);
$puestos = [];
while ($row = $puestos_result->fetch_assoc()) {
    $puestos[] = $row['Puesto'];
}

// Obtener departamentos únicos
$dept_query = "SELECT DISTINCT departamento FROM empleados WHERE departamento IS NOT NULL AND departamento != '' ORDER BY departamento";
$dept_result = $conn->query($dept_query);
$departamentos = [];
while ($row = $dept_result->fetch_assoc()) {
    $departamentos[] = $row['departamento'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - DBACK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/Empleados.css">
</head>
<body>
    <div class="main-container">
        <a href="MenuAdmin.PHP" class="back-button">
            <i class="fas fa-arrow-left"></i> Volver al Menú
        </a>
        
        <div class="header-section">
            <h1><i class="fas fa-users"></i> Gestión de Empleados</h1>
            <p class="text-muted">Sistema completo para administrar tu personal</p>
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
                <div class="label">Total Empleados</div>
            </div>
            <div class="stat-card success">
                <div class="number"><?php echo $stats['activos']; ?></div>
                <div class="label">Activos</div>
            </div>
            <div class="stat-card danger">
                <div class="number"><?php echo $stats['inactivos']; ?></div>
                <div class="label">Inactivos</div>
            </div>
            <div class="stat-card warning">
                <div class="number">$<?php echo number_format($stats['sueldo_promedio'], 2); ?></div>
                <div class="label">Sueldo Promedio</div>
            </div>
            <div class="stat-card">
                <div class="number">$<?php echo number_format($stats['nomina_total'], 2); ?></div>
                <div class="label">Nómina Total</div>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="filters-section">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="busqueda" class="form-control" 
                           placeholder="🔍 Buscar empleado..." 
                           value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <div class="col-md-2">
                    <select name="puesto" class="form-select">
                        <option value="">Todos los puestos</option>
                        <?php foreach ($puestos as $puesto): ?>
                        <option value="<?php echo htmlspecialchars($puesto); ?>" 
                                <?php echo $filtro_puesto == $puesto ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($puesto); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="departamento" class="form-select">
                        <option value="">Todos los departamentos</option>
                        <?php foreach ($departamentos as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>" 
                                <?php echo $filtro_departamento == $dept ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" <?php echo $filtro_estado == 'activo' ? 'selected' : ''; ?>>Activos</option>
                        <option value="inactivo" <?php echo $filtro_estado == 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="Empleados.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Botones de Acción -->
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-success" onclick="abrirModalAgregar()">
                    <i class="fas fa-plus"></i> Nuevo Empleado
                </button>
                <button class="btn btn-primary" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
            </div>
            <div class="text-muted">
                Mostrando <?php echo count($empleados); ?> de <?php echo $total_registros; ?> empleados
            </div>
        </div>
        
        <!-- Tabla de Empleados -->
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>RFC</th>
                        <th>Puesto</th>
                        <th>Departamento</th>
                        <th>Sueldo</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($empleados)): ?>
                        <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron empleados</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($empleados as $emp): ?>
                        <tr>
                            <td><?php echo $emp['ID_Empleado']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($emp['Nombres'] . ' ' . $emp['Apellido1'] . ' ' . $emp['Apellido2']); ?></strong>
                            </td>
                            <td><code><?php echo htmlspecialchars($emp['RFC']); ?></code></td>
                            <td><?php echo htmlspecialchars($emp['Puesto']); ?></td>
                            <td><?php echo htmlspecialchars($emp['departamento'] ?? 'N/A'); ?></td>
                            <td><strong>$<?php echo number_format($emp['Sueldo'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($emp['telefono']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $emp['estado'] == 'activo' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($emp['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editarEmpleado(<?php echo json_encode($emp); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($emp['estado'] == 'activo'): ?>
                                <button class="btn btn-sm btn-danger" onclick="eliminarEmpleado(<?php echo $emp['ID_Empleado']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php else: ?>
                                <button class="btn btn-sm btn-success" onclick="reactivarEmpleado(<?php echo $emp['ID_Empleado']; ?>)">
                                    <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-info" onclick="verDetalle(<?php echo $emp['ID_Empleado']; ?>)">
                                    <i class="fas fa-eye"></i>
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
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&puesto=<?php echo urlencode($filtro_puesto); ?>&estado=<?php echo urlencode($filtro_estado); ?>">
                        Anterior
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>&puesto=<?php echo urlencode($filtro_puesto); ?>&estado=<?php echo urlencode($filtro_estado); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagina_actual < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&puesto=<?php echo urlencode($filtro_puesto); ?>&estado=<?php echo urlencode($filtro_estado); ?>">
                        Siguiente
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
</div>

    <!-- Modal Agregar/Editar Empleado -->
    <div class="modal fade" id="empleadoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
    <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Agregar Empleado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="empleadoForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="empleadoId">
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nombres *</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Apellido Paterno *</label>
                                <input type="text" name="apellido1" id="apellido1" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Apellido Materno *</label>
                                <input type="text" name="apellido2" id="apellido2" class="form-control" required>
            </div>
            </div>
            
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">RFC *</label>
                                <input type="text" name="rfc" id="rfc" class="form-control" maxlength="13" 
                                       pattern="[A-ZÑ&]{3,4}\d{6}[A-V1-9][A-Z1-9][0-9A]" required>
                                <small class="text-muted">13 caracteres</small>
            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nómina *</label>
                                <input type="number" name="nomina" id="nomina" class="form-control" required>
            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fecha de Ingreso *</label>
                                <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control" required>
            </div>
            </div>
            
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Puesto *</label>
                                <select name="puesto" id="puesto" class="form-select" required>
                    <option value="">Seleccionar...</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Conductor">Conductor</option>
                                    <option value="Operador">Operador</option>
                                    <option value="Contador">Contador</option>
                                    <option value="Recursos Humanos">Recursos Humanos</option>
                    <option value="IT">IT</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Ventas">Ventas</option>
                                    <option value="Supervisor">Supervisor</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Departamento *</label>
                                <select name="departamento" id="departamento" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Operaciones">Operaciones</option>
                                    <option value="Administración">Administración</option>
                                    <option value="Finanzas">Finanzas</option>
                                    <option value="Recursos Humanos">Recursos Humanos</option>
                                    <option value="Tecnología">Tecnología</option>
                                    <option value="Comercial">Comercial</option>
                </select>
            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sueldo *</label>
                                <input type="number" name="sueldo" id="sueldo" class="form-control" step="0.01" min="0" required>
                            </div>
            </div>
            
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono *</label>
                                <input type="tel" name="telefono" id="telefono" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control">
                            </div>
            </div>
            
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Licencia</label>
                                <input type="text" name="licencia" id="licencia" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" id="direccion" class="form-control">
                            </div>
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
        const modal = new bootstrap.Modal(document.getElementById('empleadoModal'));
        
        function abrirModalAgregar() {
            document.getElementById('modalTitle').textContent = 'Agregar Empleado';
            document.getElementById('formAction').value = 'add';
            document.getElementById('empleadoForm').reset();
            modal.show();
        }
        
        function editarEmpleado(empleado) {
            document.getElementById('modalTitle').textContent = 'Editar Empleado';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('empleadoId').value = empleado.ID_Empleado;
            document.getElementById('nombres').value = empleado.Nombres;
            document.getElementById('apellido1').value = empleado.Apellido1;
            document.getElementById('apellido2').value = empleado.Apellido2;
            document.getElementById('rfc').value = empleado.RFC;
            document.getElementById('nomina').value = empleado.Nomina;
            document.getElementById('fecha_ingreso').value = empleado.Fecha_Ingreso;
            document.getElementById('puesto').value = empleado.Puesto;
            document.getElementById('departamento').value = empleado.departamento || '';
            document.getElementById('sueldo').value = empleado.Sueldo;
            document.getElementById('telefono').value = empleado.telefono;
            document.getElementById('email').value = empleado.email || '';
            document.getElementById('licencia').value = empleado.licencia || '';
            document.getElementById('direccion').value = empleado.direccion || '';
            modal.show();
        }
        
        function eliminarEmpleado(id) {
            if (confirm('¿Estás seguro de dar de baja a este empleado?')) {
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
        
        function reactivarEmpleado(id) {
            if (confirm('¿Estás seguro de reactivar a este empleado?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="activate">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function verDetalle(id) {
            alert('Ver detalle del empleado #' + id);
            // Aquí puedes agregar lógica para mostrar detalles completos
        }
        
        function exportarExcel() {
            window.location.href = 'exportar-empleados.php';
        }
        
        // Validación de RFC en tiempo real
        document.getElementById('rfc').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
</script>
</body>
</html>
