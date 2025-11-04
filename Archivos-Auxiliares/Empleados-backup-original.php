<?php
// Configuración de la página
$page_title = 'Gestión de Empleados - Grúas DBACK';
$additional_css = ['./CSS/Empleados.css'];

session_start();

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "5211";
$dbname = "dback";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_employee') {
        $Nombres = $conn->real_escape_string($_POST['Nombres']);
        $Apellido1 = $conn->real_escape_string($_POST['Apellido1']);
        $Apellido2 = $conn->real_escape_string($_POST['Apellido2']);
        $RFC = $conn->real_escape_string($_POST['RFC']);
        $Nomina = intval($_POST['Nomina']);
        $Fecha_Ingreso = $conn->real_escape_string($_POST['Fecha_Ingreso']);
        $Puesto = $conn->real_escape_string($_POST['Puesto']);
        $Sueldo = floatval($_POST['Sueldo']);
        $telefono = $conn->real_escape_string($_POST['telefono']);
        $email = $conn->real_escape_string($_POST['email']);
        $licencia = $conn->real_escape_string($_POST['licencia']);
        
        $sql = "INSERT INTO empleados (Nombres, Apellido1, Apellido2, RFC, Nomina, Fecha_Ingreso, Puesto, Sueldo, telefono, email, licencia) 
                VALUES ('$Nombres', '$Apellido1', '$Apellido2', '$RFC', $Nomina, '$Fecha_Ingreso', '$Puesto', $Sueldo, '$telefono', '$email', '$licencia')";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Empleado agregado exitosamente";
        } else {
            $error_message = "Error al agregar empleado: " . $conn->error;
        }
    }
}

// Obtener empleados
$sql = "SELECT * FROM empleados ORDER BY ID_Empleado DESC";
$result = $conn->query($sql);
$empleados = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $empleados[] = $row;
    }
}
?>

<?php include 'header-component.php'; ?>

<div class="container">
    <header>
        <a href="MenuAdmin.PHP" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Volver al Menú
        </a>
        
        <h1>Gestión de Empleados</h1>
        <p>Administra la información de tus empleados de manera eficiente</p>
    </header>
    
    <!-- Mensajes -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <div class="content">
        <!-- Botón para agregar empleado -->
        <div class="controls">
            <button id="addEmployeeBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Añadir Empleado
            </button>
        </div>
        
        <!-- Tabla de empleados -->
        <div class="employees-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombres</th>
                        <th>Apellido 1</th>
                        <th>Apellido 2</th>
                        <th>RFC</th>
                        <th>Puesto</th>
                        <th>Sueldo</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($empleados)): ?>
                        <tr>
                            <td colspan="10" class="no-data">
                                <i class="fas fa-users"></i>
                                No hay empleados registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($empleados as $empleado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($empleado['ID_Empleado']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Nombres'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Apellido1'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Apellido2'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['RFC'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Puesto'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($empleado['Sueldo'] ?? 0, 2); ?></td>
                                <td><?php echo htmlspecialchars($empleado['telefono'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['email'] ?? 'N/A'); ?></td>
                                <td class="actions">
                                    <button class="btn btn-sm btn-warning edit-btn" data-id="<?php echo $empleado['ID_Empleado']; ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $empleado['ID_Empleado']; ?>">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar empleado -->
<div id="employeeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Añadir Empleado</h2>
        <form id="employeeForm" method="POST">
            <input type="hidden" name="action" value="add_employee">
            
            <div class="form-group">
                <label for="Nombres">Nombres:</label>
                <input type="text" id="Nombres" name="Nombres" required>
            </div>
            
            <div class="form-group">
                <label for="Apellido1">Apellido 1:</label>
                <input type="text" id="Apellido1" name="Apellido1" required>
            </div>
            
            <div class="form-group">
                <label for="Apellido2">Apellido 2:</label>
                <input type="text" id="Apellido2" name="Apellido2" required>
            </div>
            
            <div class="form-group">
                <label for="RFC">RFC:</label>
                <input type="text" id="RFC" name="RFC" required>
            </div>
            
            <div class="form-group">
                <label for="Nomina">Nómina:</label>
                <input type="number" id="Nomina" name="Nomina" required>
            </div>
            
            <div class="form-group">
                <label for="Fecha_Ingreso">Fecha de Ingreso:</label>
                <input type="date" id="Fecha_Ingreso" name="Fecha_Ingreso" required>
            </div>
            
            <div class="form-group">
                <label for="Puesto">Puesto:</label>
                <select id="Puesto" name="Puesto" required>
                    <option value="">Seleccionar...</option>
                    <option value="IT">IT</option>
                    <option value="Recursos Humanos">Recursos Humanos</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Ventas">Ventas</option>
                    <option value="Contador">Contador</option>
                    <option value="Conductor">Conductor</option>
                    <option value="Administrador">Administrador</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="Sueldo">Sueldo:</label>
                <input type="number" id="Sueldo" name="Sueldo" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email">
            </div>
            
            <div class="form-group">
                <label for="licencia">Licencia:</label>
                <input type="text" id="licencia" name="licencia">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid transparent;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-error {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.controls {
    margin-bottom: 20px;
    text-align: right;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.employees-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.no-data {
    text-align: center;
    color: #6c757d;
    font-style: italic;
}

.actions {
    white-space: nowrap;
}

.actions .btn {
    margin-right: 5px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
    position: relative;
}

.close {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-actions {
    text-align: right;
    margin-top: 20px;
}

.form-actions .btn {
    margin-left: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('employeeModal');
    const addBtn = document.getElementById('addEmployeeBtn');
    const closeBtn = document.querySelector('.close');
    
    // Mostrar modal
    addBtn.addEventListener('click', function() {
        modal.style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Añadir Empleado';
        document.getElementById('employeeForm').reset();
    });
    
    // Cerrar modal
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Manejar botones de eliminar
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que quieres eliminar este empleado?')) {
                // Aquí puedes agregar la lógica para eliminar
                alert('Funcionalidad de eliminación pendiente de implementar');
            }
        });
    });
    
    // Manejar botones de editar
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Aquí puedes agregar la lógica para editar
            alert('Funcionalidad de edición pendiente de implementar');
        });
    });
});

function closeModal() {
    document.getElementById('employeeModal').style.display = 'none';
}
</script>

<?php include 'footer-component.php'; ?>

<?php
$conn->close();
?>