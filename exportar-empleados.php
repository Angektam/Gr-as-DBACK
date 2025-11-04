<?php
/**
 * EXPORTAR EMPLEADOS A EXCEL
 * Genera un archivo Excel con la lista completa de empleados
 */

require_once 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    die('Acceso no autorizado');
}

// Configurar headers para Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="empleados_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Obtener empleados
$query = "SELECT 
            ID_Empleado,
            Nombres,
            Apellido1,
            Apellido2,
            RFC,
            Nomina,
            Fecha_Ingreso,
            Puesto,
            departamento,
            Sueldo,
            telefono,
            email,
            licencia,
            direccion,
            estado,
            TIMESTAMPDIFF(YEAR, Fecha_Ingreso, CURDATE()) as antiguedad_anos
          FROM empleados 
          ORDER BY ID_Empleado DESC";

$result = $conn->query($query);

// Generar HTML para Excel
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background-color: #667eea;
            color: white;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #000;
        }
        td {
            padding: 8px;
            border: 1px solid #000;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .header-section {
            margin-bottom: 20px;
        }
        .total {
            font-weight: bold;
            background-color: #ffeb3b;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <h1>REPORTE DE EMPLEADOS - DBACK</h1>
        <p><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        <p><strong>Generado por:</strong> <?php echo $_SESSION['usuario_nombre'] ?? 'Sistema'; ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombres</th>
                <th>Apellido Paterno</th>
                <th>Apellido Materno</th>
                <th>RFC</th>
                <th>Nómina</th>
                <th>Fecha Ingreso</th>
                <th>Antigüedad (años)</th>
                <th>Puesto</th>
                <th>Departamento</th>
                <th>Sueldo</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Licencia</th>
                <th>Dirección</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_empleados = 0;
            $total_nomina = 0;
            $activos = 0;
            
            while ($emp = $result->fetch_assoc()): 
                $total_empleados++;
                $total_nomina += $emp['Sueldo'];
                if ($emp['estado'] == 'activo') $activos++;
            ?>
            <tr>
                <td><?php echo $emp['ID_Empleado']; ?></td>
                <td><?php echo htmlspecialchars($emp['Nombres']); ?></td>
                <td><?php echo htmlspecialchars($emp['Apellido1']); ?></td>
                <td><?php echo htmlspecialchars($emp['Apellido2']); ?></td>
                <td><?php echo htmlspecialchars($emp['RFC']); ?></td>
                <td><?php echo $emp['Nomina']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($emp['Fecha_Ingreso'])); ?></td>
                <td><?php echo $emp['antiguedad_anos']; ?></td>
                <td><?php echo htmlspecialchars($emp['Puesto']); ?></td>
                <td><?php echo htmlspecialchars($emp['departamento'] ?? 'N/A'); ?></td>
                <td>$<?php echo number_format($emp['Sueldo'], 2); ?></td>
                <td><?php echo htmlspecialchars($emp['telefono']); ?></td>
                <td><?php echo htmlspecialchars($emp['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($emp['licencia'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($emp['direccion'] ?? ''); ?></td>
                <td><?php echo strtoupper($emp['estado']); ?></td>
            </tr>
            <?php endwhile; ?>
            
            <!-- Fila de totales -->
            <tr class="total">
                <td colspan="10"><strong>TOTALES</strong></td>
                <td><strong>$<?php echo number_format($total_nomina, 2); ?></strong></td>
                <td colspan="5"></td>
            </tr>
            <tr class="total">
                <td colspan="10"><strong>Total de Empleados</strong></td>
                <td colspan="6"><strong><?php echo $total_empleados; ?></strong></td>
            </tr>
            <tr class="total">
                <td colspan="10"><strong>Empleados Activos</strong></td>
                <td colspan="6"><strong><?php echo $activos; ?></strong></td>
            </tr>
            <tr class="total">
                <td colspan="10"><strong>Sueldo Promedio</strong></td>
                <td colspan="6"><strong>$<?php echo number_format($total_nomina / $total_empleados, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
<?php
$conn->close();
?>

