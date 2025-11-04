<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación - Sistema de Grúas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 30px; }
        .container { max-width: 800px; background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .check-item { padding: 15px; margin: 10px 0; border-radius: 10px; background: #f8f9fa; }
        .check-item.success { background: #d4edda; border-left: 5px solid #28a745; }
        .check-item.error { background: #f8d7da; border-left: 5px solid #dc3545; }
        .check-item.warning { background: #fff3cd; border-left: 5px solid #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">🔍 Verificación del Sistema de Grúas</h1>
        
        <?php
        require_once 'conexion.php';
        
        $checks = [];
        
        // 1. Verificar conexión
        if ($conn->connect_error) {
            $checks[] = ['status' => 'error', 'title' => 'Conexión a DB', 'msg' => 'Error: ' . $conn->connect_error];
        } else {
            $checks[] = ['status' => 'success', 'title' => 'Conexión a DB', 'msg' => 'Conectado exitosamente'];
        }
        
        // 2. Verificar tabla gruas
        $result = $conn->query("SHOW TABLES LIKE 'gruas'");
        if ($result->num_rows > 0) {
            $checks[] = ['status' => 'success', 'title' => 'Tabla gruas', 'msg' => 'La tabla existe'];
            
            // Verificar columnas
            $columns = $conn->query("SHOW COLUMNS FROM gruas");
            $columnas = [];
            while ($col = $columns->fetch_assoc()) {
                $columnas[] = $col['Field'];
            }
            
            $columnas_requeridas = ['ID', 'Placa', 'Marca', 'Modelo', 'Tipo', 'Estado'];
            $faltantes = array_diff($columnas_requeridas, $columnas);
            
            if (empty($faltantes)) {
                $checks[] = ['status' => 'success', 'title' => 'Columnas', 'msg' => 'Todas las columnas necesarias existen: ' . implode(', ', $columnas)];
            } else {
                $checks[] = ['status' => 'error', 'title' => 'Columnas', 'msg' => 'Faltan columnas: ' . implode(', ', $faltantes)];
            }
        } else {
            $checks[] = ['status' => 'error', 'title' => 'Tabla gruas', 'msg' => 'La tabla NO existe. Ejecuta el instalador.'];
        }
        
        // 3. Verificar datos
        $count_result = $conn->query("SELECT COUNT(*) as total FROM gruas");
        if ($count_result) {
            $count = $count_result->fetch_assoc()['total'];
            if ($count > 0) {
                $checks[] = ['status' => 'success', 'title' => 'Datos', 'msg' => "Hay $count grúa(s) registrada(s)"];
            } else {
                $checks[] = ['status' => 'warning', 'title' => 'Datos', 'msg' => 'No hay grúas registradas. Puedes agregar desde el sistema.'];
            }
        }
        
        // 4. Verificar estadísticas
        $stats = $conn->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN Estado = 'Activa' THEN 1 ELSE 0 END) as activas,
            SUM(CASE WHEN Estado = 'Mantenimiento' THEN 1 ELSE 0 END) as mantenimiento,
            SUM(CASE WHEN Estado = 'Inactiva' THEN 1 ELSE 0 END) as inactivas
            FROM gruas")->fetch_assoc();
        
        if ($stats) {
            $checks[] = ['status' => 'success', 'title' => 'Estadísticas', 
                'msg' => "Total: {$stats['total']} | Activas: {$stats['activas']} | Mantenimiento: {$stats['mantenimiento']} | Inactivas: {$stats['inactivas']}"];
        }
        
        // 5. Verificar tabla mantenimiento
        $result = $conn->query("SHOW TABLES LIKE 'mantenimiento_gruas'");
        if ($result->num_rows > 0) {
            $checks[] = ['status' => 'success', 'title' => 'Tabla mantenimiento_gruas', 'msg' => 'La tabla existe (lista para futuras funcionalidades)'];
        } else {
            $checks[] = ['status' => 'warning', 'title' => 'Tabla mantenimiento_gruas', 'msg' => 'La tabla no existe (opcional)'];
        }
        
        // 6. Verificar archivo Gruas.php
        if (file_exists('Gruas.php')) {
            $checks[] = ['status' => 'success', 'title' => 'Archivo Gruas.php', 'msg' => 'El archivo existe'];
        } else {
            $checks[] = ['status' => 'error', 'title' => 'Archivo Gruas.php', 'msg' => 'El archivo NO existe'];
        }
        
        // 7. Verificar sesión
        if (isset($_SESSION)) {
            $checks[] = ['status' => 'success', 'title' => 'Sesión', 'msg' => 'Sesión iniciada correctamente'];
        } else {
            $checks[] = ['status' => 'warning', 'title' => 'Sesión', 'msg' => 'No hay sesión activa (necesitas iniciar sesión)'];
        }
        
        // Mostrar resultados
        $errores = 0;
        $advertencias = 0;
        $exitos = 0;
        
        foreach ($checks as $check) {
            $icon = $check['status'] == 'success' ? '✅' : 
                   ($check['status'] == 'error' ? '❌' : '⚠️');
            
            echo "<div class='check-item {$check['status']}'>";
            echo "<strong>$icon {$check['title']}</strong><br>";
            echo "<small>{$check['msg']}</small>";
            echo "</div>";
            
            if ($check['status'] == 'error') $errores++;
            elseif ($check['status'] == 'warning') $advertencias++;
            else $exitos++;
        }
        
        // Resumen
        echo "<div class='mt-4 p-4 rounded' style='background: #e9ecef;'>";
        echo "<h5>📊 Resumen de Verificación</h5>";
        echo "<p>✅ Exitosas: <strong>$exitos</strong></p>";
        echo "<p>⚠️ Advertencias: <strong>$advertencias</strong></p>";
        echo "<p>❌ Errores: <strong>$errores</strong></p>";
        
        if ($errores == 0 && $advertencias == 0) {
            echo "<div class='alert alert-success mt-3'>";
            echo "<strong>🎉 ¡Perfecto!</strong> El sistema está completamente funcional.";
            echo "</div>";
        } elseif ($errores > 0) {
            echo "<div class='alert alert-danger mt-3'>";
            echo "<strong>⚠️ Atención:</strong> Hay errores que deben corregirse. Ejecuta el instalador.";
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning mt-3'>";
            echo "<strong>✓ Funcional:</strong> El sistema funciona pero hay algunas advertencias menores.";
            echo "</div>";
        }
        echo "</div>";
        
        $conn->close();
        ?>
        
        <div class="text-center mt-4">
            <?php if ($errores > 0): ?>
                <a href="instalar-gruas-mejorado.php" class="btn btn-danger btn-lg me-2">
                    🔧 Ejecutar Instalador
                </a>
            <?php endif; ?>
            <a href="Gruas.php" class="btn btn-primary btn-lg">
                🚛 Ir a Gestión de Grúas
            </a>
        </div>
    </div>
</body>
</html>

