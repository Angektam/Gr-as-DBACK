<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Sistema de Grúas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .installer-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .step {
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .step.success {
            background: #d4edda;
            border-left: 5px solid #28a745;
        }
        .step.error {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
        }
        .step.warning {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <h1 class="text-center mb-4">
            <i class="fas fa-truck"></i> Instalador de Sistema de Grúas
        </h1>
        <p class="text-center text-muted mb-4">
            Este instalador verificará y configurará la base de datos para el sistema de grúas
        </p>
        
        <?php
        require_once 'conexion.php';
        
        $pasos_completados = 0;
        $pasos_totales = 4;
        $errores = [];
        
        echo "<div class='progress mb-4'>
                <div class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 0%' id='progreso'>
                    0%
                </div>
              </div>";
        
        // PASO 1: Verificar conexión
        echo "<div class='step'>";
        echo "<h5><i class='fas fa-database'></i> Paso 1: Verificando conexión a la base de datos...</h5>";
        if ($conn->connect_error) {
            echo "<p class='text-danger'>❌ Error de conexión: " . $conn->connect_error . "</p>";
            $errores[] = "No se pudo conectar a la base de datos";
        } else {
            echo "<p class='text-success'>✅ Conexión exitosa</p>";
            $pasos_completados++;
        }
        echo "</div>";
        
        // PASO 2: Verificar tabla gruas
        echo "<div class='step'>";
        echo "<h5><i class='fas fa-table'></i> Paso 2: Verificando tabla gruas...</h5>";
        $result = $conn->query("SHOW TABLES LIKE 'gruas'");
        if ($result->num_rows > 0) {
            echo "<p class='text-success'>✅ La tabla 'gruas' existe</p>";
            $pasos_completados++;
            
            // Verificar columnas
            $columns = $conn->query("SHOW COLUMNS FROM gruas");
            $columnas_existentes = [];
            while ($col = $columns->fetch_assoc()) {
                $columnas_existentes[] = $col['Field'];
            }
            
            echo "<p class='text-muted'>Columnas encontradas: " . implode(', ', $columnas_existentes) . "</p>";
            
            // Agregar columnas faltantes
            $columnas_necesarias = [
                'ID' => "INT AUTO_INCREMENT PRIMARY KEY",
                'Placa' => "VARCHAR(10) NOT NULL",
                'Marca' => "VARCHAR(100) NOT NULL",
                'Modelo' => "VARCHAR(100) NOT NULL",
                'Tipo' => "ENUM('Plataforma', 'Arrastre', 'Remolque', 'Grúa') DEFAULT 'Plataforma'",
                'Estado' => "ENUM('Activa', 'Mantenimiento', 'Inactiva') DEFAULT 'Activa'",
                'fecha_registro' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
            ];
            
            foreach ($columnas_necesarias as $columna => $definicion) {
                if (!in_array($columna, $columnas_existentes) && $columna != 'ID') {
                    $sql = "ALTER TABLE gruas ADD COLUMN $columna $definicion";
                    if ($conn->query($sql)) {
                        echo "<p class='text-info'>✅ Columna '$columna' agregada</p>";
                    } else {
                        echo "<p class='text-warning'>⚠️ No se pudo agregar '$columna': " . $conn->error . "</p>";
                    }
                }
            }
            
        } else {
            echo "<p class='text-warning'>⚠️ La tabla 'gruas' no existe. Creando...</p>";
            
            $sql_create = "CREATE TABLE gruas (
                ID INT AUTO_INCREMENT PRIMARY KEY,
                Placa VARCHAR(10) NOT NULL UNIQUE,
                Marca VARCHAR(100) NOT NULL,
                Modelo VARCHAR(100) NOT NULL,
                Tipo ENUM('Plataforma', 'Arrastre', 'Remolque', 'Grúa') DEFAULT 'Plataforma',
                Estado ENUM('Activa', 'Mantenimiento', 'Inactiva') DEFAULT 'Activa',
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_placa (Placa),
                INDEX idx_estado (Estado),
                INDEX idx_tipo (Tipo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($conn->query($sql_create)) {
                echo "<p class='text-success'>✅ Tabla 'gruas' creada exitosamente</p>";
                $pasos_completados++;
            } else {
                echo "<p class='text-danger'>❌ Error al crear tabla: " . $conn->error . "</p>";
                $errores[] = "No se pudo crear la tabla gruas";
            }
        }
        echo "</div>";
        
        // PASO 3: Verificar datos
        echo "<div class='step'>";
        echo "<h5><i class='fas fa-check-circle'></i> Paso 3: Verificando datos...</h5>";
        $count = $conn->query("SELECT COUNT(*) as total FROM gruas")->fetch_assoc()['total'];
        echo "<p>Total de grúas registradas: <strong>$count</strong></p>";
        
        if ($count == 0) {
            echo "<p class='text-warning'>⚠️ No hay grúas registradas. ¿Desea agregar datos de ejemplo?</p>";
            
            $sql_ejemplo = "INSERT INTO gruas (Placa, Marca, Modelo, Tipo, Estado) VALUES
                ('ABC123', 'Ford', 'F-350', 'Plataforma', 'Activa'),
                ('XYZ456', 'Chevrolet', 'Silverado 3500', 'Arrastre', 'Activa'),
                ('DEF789', 'Dodge', 'Ram 5500', 'Remolque', 'Mantenimiento')";
            
            if ($conn->multi_query($sql_ejemplo)) {
                echo "<p class='text-success'>✅ Datos de ejemplo agregados</p>";
                while ($conn->next_result()) {;} // Limpiar resultados
            }
        } else {
            echo "<p class='text-success'>✅ Datos verificados</p>";
        }
        $pasos_completados++;
        echo "</div>";
        
        // PASO 4: Verificar tabla de mantenimiento
        echo "<div class='step'>";
        echo "<h5><i class='fas fa-wrench'></i> Paso 4: Verificando tabla de mantenimiento...</h5>";
        $result = $conn->query("SHOW TABLES LIKE 'mantenimiento_gruas'");
        if ($result->num_rows > 0) {
            echo "<p class='text-success'>✅ La tabla 'mantenimiento_gruas' existe</p>";
            $pasos_completados++;
        } else {
            echo "<p class='text-warning'>⚠️ Creando tabla 'mantenimiento_gruas'...</p>";
            
            $sql_mantenimiento = "CREATE TABLE mantenimiento_gruas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                grua_id INT NOT NULL,
                tipo_mantenimiento ENUM('Preventivo', 'Correctivo', 'Revisión') NOT NULL,
                fecha_mantenimiento DATE NOT NULL,
                tecnico_responsable VARCHAR(100),
                costo DECIMAL(10,2) DEFAULT 0.00,
                detalles TEXT,
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (grua_id) REFERENCES gruas(ID) ON DELETE CASCADE,
                INDEX idx_grua (grua_id),
                INDEX idx_fecha (fecha_mantenimiento)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($conn->query($sql_mantenimiento)) {
                echo "<p class='text-success'>✅ Tabla 'mantenimiento_gruas' creada exitosamente</p>";
                $pasos_completados++;
            } else {
                echo "<p class='text-warning'>⚠️ Error al crear tabla de mantenimiento: " . $conn->error . "</p>";
            }
        }
        echo "</div>";
        
        // Resumen final
        $porcentaje = ($pasos_completados / $pasos_totales) * 100;
        echo "<script>
                document.getElementById('progreso').style.width = '$porcentaje%';
                document.getElementById('progreso').textContent = Math.round($porcentaje) + '%';
              </script>";
        
        echo "<div class='step " . (count($errores) == 0 ? 'success' : 'warning') . "'>";
        echo "<h4><i class='fas fa-flag-checkered'></i> Resumen de Instalación</h4>";
        echo "<p>Pasos completados: <strong>$pasos_completados de $pasos_totales</strong></p>";
        
        if (count($errores) > 0) {
            echo "<div class='alert alert-warning'>";
            echo "<strong>Advertencias:</strong><ul>";
            foreach ($errores as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul></div>";
        } else {
            echo "<div class='alert alert-success'>";
            echo "<strong>✅ ¡Instalación completada exitosamente!</strong><br>";
            echo "El sistema de grúas está listo para usar.";
            echo "</div>";
        }
        
        echo "<div class='text-center mt-4'>";
        echo "<a href='Gruas.php' class='btn btn-primary btn-lg'>";
        echo "<i class='fas fa-truck'></i> Ir a Gestión de Grúas";
        echo "</a>";
        echo "</div>";
        
        echo "</div>";
        
        $conn->close();
        ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

