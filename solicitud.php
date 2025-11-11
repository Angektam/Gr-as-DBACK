<?php
// Incluir sistema de validaciones
require_once 'utils/validaciones.php';
require_once 'conexion.php';

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión: " . ($conn->connect_error ?? "No se pudo establecer conexión"));
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar token CSRF
$csrf_token = generarCSRF();

// Mostrar mensaje de éxito tras PRG
if (isset($_GET['enviado']) && $_GET['enviado'] === '1') {
    $success_message = "Solicitud enviada con éxito. Nos pondremos en contacto contigo pronto.";
}

// Procesar el formulario si se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar token CSRF
    $token_recibido = $_POST['csrf_token'] ?? '';
    if (!validarCSRF($token_recibido)) {
        $error_message = "Error de seguridad: token inválido. Por favor, recarga la página e intenta nuevamente.";
    } else {
        // Crear instancia del validador
        $validador = new Validador();
        
        // Validar y sanitizar datos
        $nombre = Validador::sanitizar($_POST['nombre'] ?? '', 'string');
        $telefono = Validador::sanitizar($_POST['telefono'] ?? '', 'string');
        $email = Validador::sanitizar($_POST['email'] ?? '', 'email');
        $ubicacion_origen = Validador::sanitizar($_POST['ubicacion_origen'] ?? '', 'string');
        $ubicacion_destino = Validador::sanitizar($_POST['ubicacion_destino'] ?? '', 'string');
        $vehiculo = Validador::sanitizar($_POST['vehiculo'] ?? '', 'string');
        $marca = Validador::sanitizar($_POST['marca'] ?? '', 'string');
        $modelo = Validador::sanitizar($_POST['modelo'] ?? '', 'string');
        $tipo_servicio = Validador::sanitizar($_POST['tipo_servicio'] ?? '', 'string');
        $descripcion = Validador::sanitizar($_POST['descripcion'] ?? '', 'string');
        $urgencia = Validador::sanitizar($_POST['urgencia'] ?? 'normal', 'string');
        $distancia = Validador::sanitizar($_POST['distancia'] ?? '0', 'string');
        
        // Aplicar validaciones
        $validador->validarNombre($nombre, 'nombre', 2, 50, true);
        $validador->validarTelefono($telefono, 'telefono', true);
        if (!empty($email)) {
            $validador->validarEmail($email, 'email', false);
        }
        $validador->requerido($ubicacion_origen, 'ubicacion_origen', 'La ubicación de origen es requerida');
        $validador->longitud($ubicacion_origen, 'ubicacion_origen', 5, 200);
        $validador->requerido($ubicacion_destino, 'ubicacion_destino', 'La ubicación de destino es requerida');
        $validador->longitud($ubicacion_destino, 'ubicacion_destino', 5, 200);
        $validador->requerido($vehiculo, 'vehiculo', 'El tipo de vehículo es requerido');
        $validador->longitud($marca, 'marca', 1, 50);
        $validador->longitud($modelo, 'modelo', 1, 50);
        $validador->requerido($tipo_servicio, 'tipo_servicio', 'El tipo de servicio es requerido');
        $validador->longitud($descripcion, 'descripcion', 10, 500);
        
        // Validar valores permitidos (alineados al ENUM de la BD)
        $vehiculos_validos = ['automovil', 'camioneta', 'motocicleta', 'camion'];
        if (!in_array(strtolower($vehiculo), $vehiculos_validos, true)) {
            $validador->agregarError('vehiculo', 'Tipo de vehículo no válido');
        }
        
        $tipos_servicio_validos = ['remolque', 'bateria', 'gasolina', 'llanta', 'arranque', 'otro'];
        if (!in_array(strtolower($tipo_servicio), $tipos_servicio_validos, true)) {
            $validador->agregarError('tipo_servicio', 'Tipo de servicio no válido');
        }
        
        $urgencias_validas = ['normal', 'urgente', 'emergencia'];
        if (!in_array(strtolower($urgencia), $urgencias_validas, true)) {
            $urgencia = 'normal';
        }
        
        // Validar costo
        $costo_raw = $_POST['costo'] ?? '0';
        $costo_clean = preg_replace('/[^0-9.]/', '', $costo_raw);
        $costo = floatval($costo_clean);
        $validador->validarNumero($costo, 'costo', 0, 999999);
        
        // Validar distancia
        $distancia_clean = preg_replace('/[^0-9.]/', '', $distancia);
        $distancia_km = floatval($distancia_clean);
        $validador->validarNumero($distancia_km, 'distancia', 0, 9999);
        
        // Validar archivo si se subió
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $validador->validarArchivo($_FILES['foto'], 'foto', 5, ['jpg', 'jpeg', 'png', 'gif']);
        }
        
        // Validar consentimiento
        $consentimiento = isset($_POST['consentimiento']) && $_POST['consentimiento'] == '1' ? 1 : 0;
        if ($consentimiento != 1) {
            $validador->agregarError('consentimiento', 'Debes aceptar el consentimiento de datos');
        }
        
        // Si hay errores, mostrarlos
        if ($validador->tieneErrores()) {
            $error_message = $validador->obtenerErroresString();
    } else {
            // Sanitizar datos adicionales
            $metodo_pago = Validador::sanitizar($_POST['metodo_pago_seleccionado'] ?? 'Efectivo', 'string');
            $paypal_order_id = Validador::sanitizar($_POST['paypal_order_id'] ?? '', 'string');
            $paypal_status = Validador::sanitizar($_POST['paypal_status'] ?? '', 'string');
            $paypal_email = Validador::sanitizar($_POST['paypal_email'] ?? '', 'email');
            $paypal_name = Validador::sanitizar($_POST['paypal_name'] ?? '', 'string');
        
        // Procesar la foto del vehículo
        $foto_nombre = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = "uploads/";
            
            // Crear directorio si no existe
            if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
            }
            
                // Generar nombre único para evitar conflictos
                $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $foto_nombre = uniqid('foto_', true) . '.' . $extension;
                $upload_path = $upload_dir . $foto_nombre;
                
                // Mover el archivo
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $error_message = "Error al subir la foto. Por favor, intenta nuevamente.";
                }
        }
        
            // Preparar datos para la tabla 'solicitudes'
        $nombre_completo = $nombre;
            $ubicacion_final = $ubicacion_origen;
            // Coordenadas (si existen campos ocultos de origen)
            $origen_lat = isset($_POST['origen_lat']) ? trim($_POST['origen_lat']) : '';
            $origen_lng = isset($_POST['origen_lng']) ? trim($_POST['origen_lng']) : '';
            $coordenadas = ($origen_lat !== '' && $origen_lng !== '') ? ($origen_lat . ',' . $origen_lng) : null;
        $tipo_vehiculo = $vehiculo;
        $marca_vehiculo = $marca;
        $modelo_vehiculo = $modelo;
            // Incluir destino como parte de la descripción si se proporcionó
            $descripcion_problema = $descripcion . (!empty($ubicacion_destino) ? (" | Destino: " . $ubicacion_destino) : "");
        $consentimiento_datos = $consentimiento;
        $ip_cliente = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $costo_estimado = $costo;

            // Usar prepared statements (alineado a columnas reales de la tabla 'solicitudes')
            $stmt = $conn->prepare("INSERT INTO solicitudes (
                nombre_completo, telefono, email, ubicacion, coordenadas, tipo_vehiculo, marca_vehiculo, modelo_vehiculo,
                foto_vehiculo, tipo_servicio, descripcion_problema, urgencia, distancia_km, costo_estimado,
                consentimiento_datos, ip_cliente, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param("ssssssssssssddiss",
                    $nombre_completo, $telefono, $email, $ubicacion_final, $coordenadas,
                    $tipo_vehiculo, $marca_vehiculo, $modelo_vehiculo, $foto_nombre,
                    $tipo_servicio, $descripcion_problema, $urgencia, $distancia_km, $costo_estimado,
                    $consentimiento_datos, $ip_cliente, $user_agent
                );
        
                if ($stmt->execute()) {
                    $stmt->close();
            // Redirigir (PRG) para limpiar el formulario y evitar reenvíos
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?enviado=1");
            exit;
        } else {
                    $error_message = "Error al guardar la solicitud. Por favor, intenta nuevamente.";
                    $stmt->close();
                }
            } else {
                $error_message = "Error al preparar la consulta: " . $conn->error;
            }
        }
    }
    
    // Cerrar conexión
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Solicita nuestro servicio de grúas 24/7. Asistencia rápida y profesional para todo tipo de vehículos.">
    <title>Solicitar Servicio de Grúa | Grúas DBACK</title>
    <link rel="stylesheet" href="CSS/Solicitud_ARCO.css">
    <link rel="stylesheet" href="js/validaciones.css">
    <link rel="stylesheet" href="CSS/chatbot.css">
    <script src="js/validaciones.js" defer></script>
</head>
<body data-chatbot-form-url="solicitud.php" data-chatbot-phone="529992592882" data-chatbot-whatsapp="526688253351">
    <header>
        <nav class="navbar" aria-label="Navegación principal">
            <div class="nav-content">
                <a href="index.php" class="navbar-brand">
                    <img src="Elementos/LogoDBACK.png" alt="Logo DBACK" width="50" height="50">
                    <h1>Grúas DBACK</h1>
                </a>
                
                <div class="nav-links">
                    <a href="index.php" class="cta-button">Inicio</a>
                    <a href="tel:+526688253351" class="cta-button accent">Llamar ahora</a>
                </div>
            </div>
        </nav>   
    </header>

    <main>
        <?php if (isset($success_message)): ?>
        <div class="success-message">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <section class="formulario" aria-labelledby="form-title">
            <h2 id="form-title">Solicitar Servicio de Grúa</h2>
            <p class="form-description">Complete el formulario y nos pondremos en contacto lo antes posible.</p>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="servicioForm" enctype="multipart/form-data" novalidate>
                <!-- Token CSRF para seguridad -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <!-- Coordenadas ocultas para cálculo de distancia -->
                <input type="hidden" id="origen_lat" name="origen_lat">
                <input type="hidden" id="origen_lng" name="origen_lng">
                <input type="hidden" id="destino_lat" name="destino_lat">
                <input type="hidden" id="destino_lng" name="destino_lng">
                <!-- Información de contacto -->
                <fieldset>
                    <legend>Información de contacto</legend>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre completo:</label>
                        <input type="text" id="nombre" name="nombre" required 
                               pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]{3,50}"
                               placeholder="Ej: Juan Pérez"
                               title="Ingrese un nombre válido (solo letras y espacios, mínimo 3 caracteres)"
                               aria-required="true"
                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                        <div id="nombre-error" class="error-message" role="alert">Por favor ingrese un nombre válido (mínimo 3 caracteres, solo letras y espacios)</div>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono de contacto:</label>
                        <input type="tel" id="telefono" name="telefono" required 
                               pattern="(\d{10}|\d{3}-\d{3}-\d{4})"
                               placeholder="Ej: 6681234567 o 668-123-4567"
                               title="Formato requerido: XXXXXXXXXX o XXX-XXX-XXXX"
                               aria-required="true"
                               value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                        <div id="telefono-error" class="error-message" role="alert">Por favor ingrese un teléfono válido (10 dígitos o formato XXX-XXX-XXXX)</div>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo electrónico:</label>
                        <input type="email" id="email" name="email" 
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                               placeholder="Ej: juan@ejemplo.com"
                               title="Ingrese un correo electrónico válido"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <div id="email-error" class="error-message" role="alert">Por favor ingrese un correo electrónico válido</div>
                    </div>
                </fieldset>

                <!-- Sección de ubicaciones -->
                <fieldset>
                    <legend>Ubicaciones</legend>
                    
                    <div class="location-section">
                        <h3>Ubicación de Recogida</h3>
                        <div class="form-group">
                            <label for="ubicacion_origen">Ubicación actual del vehículo:</label>
                            <div class="location-input-container">
                                <input type="text" id="ubicacion_origen" name="ubicacion_origen" required 
                                       minlength="5"
                                       placeholder="Dirección o punto de referencia" 
                                       list="ubicaciones_origen"
                                       title="Ingrese una ubicación válida (mínimo 5 caracteres)"
                                       aria-required="true"
                                       value="<?php echo isset($_POST['ubicacion_origen']) ? htmlspecialchars($_POST['ubicacion_origen']) : ''; ?>">
                                <button type="button" id="obtenerUbicacionOrigen" class="location-button" aria-label="Obtener mi ubicación actual">
                                    <img src="https://cdn-icons-png.flaticon.com/512/535/535137.png" alt="Ubicación" width="20" height="20">
                                </button>
                            </div>
                            <div id="ubicacion_origen-error" class="error-message" role="alert">Por favor ingrese una ubicación válida (mínimo 5 caracteres)</div>
                            <datalist id="ubicaciones_origen"></datalist>
                            <div id="map_origen" style="height: 300px; width: 100%; margin-top: 10px;"></div>
                        </div>
                    </div>

<!-- Hoja de estilos de Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

<div class="location-section">
    <h3>Ubicación de Entrega</h3>
    <div class="form-group">
        <label for="ubicacion_destino">¿A dónde necesita llevar el vehículo?</label>
        <div class="location-input-container">
            <input type="text" id="ubicacion_destino" name="ubicacion_destino" required 
                   minlength="5"
                   placeholder="Dirección o punto de referencia" 
                   list="ubicaciones_destino"
                   title="Ingrese una ubicación válida (mínimo 5 caracteres)"
                   aria-required="true"
                   value="<?php echo isset($_POST['ubicacion_destino']) ? htmlspecialchars($_POST['ubicacion_destino']) : ''; ?>">
            <button type="button" id="obtenerUbicacionDestino" class="location-button" aria-label="Obtener mi ubicación actual">
                <img src="https://cdn-icons-png.flaticon.com/512/535/535137.png" alt="Ubicación" width="20" height="20">
            </button>
        </div>
        <div id="ubicacion_destino-error" class="error-message" role="alert" style="display: none;">Por favor ingrese una ubicación válida (mínimo 5 caracteres)</div>
        <datalist id="ubicaciones_destino"></datalist>
    </div>
    <div id="map" style="height: 400px; width: 100%; margin-top: 10px;"></div>
</div>

<!-- Scripts de Leaflet -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    // Mapa de destino (entrega)
    const map = L.map('map').setView([25.814960975032974, -108.97984572706956], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([25.814960975032974, -108.97984572706956], { draggable: true }).addTo(map);

    // Mapa de origen (recogida)
    const mapOrigen = L.map('map_origen').setView([25.814960975032974, -108.97984572706956], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapOrigen);
    const markerOrigen = L.marker([25.814960975032974, -108.97984572706956], { draggable: true }).addTo(mapOrigen);

    function reverseGeocode(lat, lon, isDestino = true) {
        const inputId = isDestino ? 'ubicacion_destino' : 'ubicacion_origen';
        const latId = isDestino ? 'destino_lat' : 'origen_lat';
        const lngId = isDestino ? 'destino_lng' : 'origen_lng';
        
        // Guardar coordenadas
        const latElement = document.getElementById(latId);
        const lngElement = document.getElementById(lngId);
        if (latElement && lngElement) {
            latElement.value = lat;
            lngElement.value = lon;
        }
        
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById(inputId).value = data.display_name;
                }
            });
    }

    function searchAddress(query, isDestino = true) {
        const datalistId = isDestino ? 'ubicaciones_destino' : 'ubicaciones_origen';
        const mapElement = isDestino ? map : mapOrigen;
        const markerElement = isDestino ? marker : markerOrigen;
        const latId = isDestino ? 'destino_lat' : 'origen_lat';
        const lngId = isDestino ? 'destino_lng' : 'origen_lng';
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`)
            .then(res => res.json())
            .then(data => {
                const datalist = document.getElementById(datalistId);
                datalist.innerHTML = '';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.display_name;
                    datalist.appendChild(option);
                });

                if (data[0]) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    mapElement.setView([lat, lon], 15);
                    markerElement.setLatLng([lat, lon]);
                    const latElement = document.getElementById(latId);
                    const lngElement = document.getElementById(lngId);
                    if (latElement && lngElement) {
                        latElement.value = lat;
                        lngElement.value = lon;
                    }
                    // Calcular distancia después de actualizar ubicación
                    setTimeout(() => calcularDistancia(), 1000);
                }
            });
    }

    document.getElementById('ubicacion_destino').addEventListener('input', function () {
        const query = this.value;
        if (query.length >= 5) {
            searchAddress(query, true);
        }
    });

    document.getElementById('ubicacion_origen').addEventListener('input', function () {
        const query = this.value;
        if (query.length >= 5) {
            searchAddress(query, false);
        }
    });

    document.getElementById('obtenerUbicacionDestino').addEventListener('click', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                map.setView([lat, lon], 15);
                marker.setLatLng([lat, lon]);
                reverseGeocode(lat, lon, true);
                // Calcular distancia después de obtener ubicación
                setTimeout(() => calcularDistancia(), 1000);
            }, () => {
                alert("No se pudo obtener tu ubicación.");
            });
        } else {
            alert("Geolocalización no soportada.");
        }
    });

    // Botón para origen
    document.getElementById('obtenerUbicacionOrigen').addEventListener('click', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                mapOrigen.setView([lat, lon], 15);
                markerOrigen.setLatLng([lat, lon]);
                reverseGeocode(lat, lon, false);
                // Calcular distancia después de obtener ubicación
                setTimeout(() => calcularDistancia(), 1000);
            }, () => {
                alert("No se pudo obtener tu ubicación.");
            });
        } else {
            alert("Geolocalización no soportada.");
        }
    });

    map.on('click', e => {
        marker.setLatLng(e.latlng);
        reverseGeocode(e.latlng.lat, e.latlng.lng, true);
        // Calcular distancia después de actualizar ubicación
        setTimeout(() => calcularDistancia(), 1000);
    });

    marker.on('dragend', () => {
        const pos = marker.getLatLng();
        reverseGeocode(pos.lat, pos.lng, true);
        // Calcular distancia después de actualizar ubicación
        setTimeout(() => calcularDistancia(), 1000);
    });
</script>






                <!-- Información del vehículo -->
                <fieldset>
                    <legend>Información del vehículo</legend>
                    
                    <div class="form-group">
                        <label for="vehiculo">Tipo de vehículo:</label>
                        <select id="vehiculo" name="vehiculo" required aria-required="true">
                            <option value="">Seleccione una opción</option>
                            <option value="automovil" <?php echo (isset($_POST['vehiculo']) && $_POST['vehiculo'] == 'automovil') ? 'selected' : ''; ?>>Automóvil</option>
                            <option value="camioneta" <?php echo (isset($_POST['vehiculo']) && $_POST['vehiculo'] == 'camioneta') ? 'selected' : ''; ?>>Camioneta</option>
                            <option value="motocicleta" <?php echo (isset($_POST['vehiculo']) && $_POST['vehiculo'] == 'motocicleta') ? 'selected' : ''; ?>>Motocicleta</option>
                            <option value="camion" <?php echo (isset($_POST['vehiculo']) && $_POST['vehiculo'] == 'camion') ? 'selected' : ''; ?>>Camión</option>
                        </select>
                        <div id="vehiculo-error" class="error-message" role="alert">Por favor seleccione un tipo de vehículo</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca">Marca del vehículo:</label>
                        <input type="text" id="marca" name="marca" required
                               minlength="2"
                               placeholder="Ej: Toyota, Ford, Nissan"
                               aria-required="true"
                               value="<?php echo isset($_POST['marca']) ? htmlspecialchars($_POST['marca']) : ''; ?>">
                        <div id="marca-error" class="error-message" role="alert">Por favor ingrese la marca del vehículo (mínimo 2 caracteres)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modelo">Modelo del vehículo:</label>
                        <input type="text" id="modelo" name="modelo" required
                               minlength="2"
                               placeholder="Ej: Corolla, F-150, Sentra"
                               aria-required="true"
                               value="<?php echo isset($_POST['modelo']) ? htmlspecialchars($_POST['modelo']) : ''; ?>">
                        <div id="modelo-error" class="error-message" role="alert">Por favor ingrese el modelo del vehículo (mínimo 2 caracteres)</div>
                    </div>

                    <div class="form-group">
                        <label for="foto">Foto del vehículo (opcional):</label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg, image/png">
                        <p><small>Formatos aceptados: JPG, PNG. Tamaño máximo: 5MB</small></p>
                        <div id="foto-error" class="error-message" role="alert">El archivo debe ser una imagen (JPG o PNG) y no exceder 5MB</div>
                    </div>
                </fieldset>

                <!-- Información del servicio -->
                <fieldset>
                    <legend>Detalles del servicio</legend>
                    
                    <div class="form-group">
                        <label for="tipo_servicio">Tipo de Servicio:</label>
                        <select id="tipo_servicio" name="tipo_servicio" required aria-required="true">
                            <option value="">Seleccione una opción</option>
                            <option value="remolque" <?php echo (isset($_POST['tipo_servicio']) && $_POST['tipo_servicio'] == 'remolque') ? 'selected' : ''; ?>>Remolque</option>
                            <option value="bateria" <?php echo (isset($_POST['tipo_servicio']) && $_POST['tipo_servicio'] == 'bateria') ? 'selected' : ''; ?>>Cambio de batería</option>
                            <option value="gasolina" <?php echo (isset($_POST['tipo_servicio']) && $_POST['tipo_servicio'] == 'gasolina') ? 'selected' : ''; ?>>Suministro de gasolina</option>
                            <option value="llanta" <?php echo (isset($_POST['tipo_servicio']) && $_POST['tipo_servicio'] == 'llanta') ? 'selected' : ''; ?>>Cambio de llanta</option>
                            <option value="arranque" <?php echo (isset($_POST['tipo_servicio']) && $_POST['tipo_servicio'] == 'arranque') ? 'selected' : ''; ?>>Servicio de arranque</option>
                            <option value="otro" <?php echo (isset($_POST['tipo_servicio']) && $_POST['tipo_servicio'] == 'otro') ? 'selected' : ''; ?>>Otro servicio</option>
                        </select>
                        <div id="tipo_servicio-error" class="error-message" role="alert">Por favor seleccione un tipo de servicio</div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción del problema:</label>
                        <textarea id="descripcion" name="descripcion" rows="4" 
                                  minlength="10" maxlength="500"
                                  placeholder="Describa brevemente la situación"
                                  title="La descripción debe tener entre 10 y 500 caracteres"><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                        <div id="descripcion-error" class="error-message" role="alert">La descripción debe tener entre 10 y 500 caracteres</div>
                    </div>

                    <div class="form-group">
                        <label for="urgencia">Nivel de urgencia:</label>
                        <select id="urgencia" name="urgencia" required aria-required="true">
                            <option value="normal" <?php echo (isset($_POST['urgencia']) && $_POST['urgencia'] == 'normal') ? 'selected' : ''; ?>>Normal</option>
                            <option value="urgente" <?php echo (isset($_POST['urgencia']) && $_POST['urgencia'] == 'urgente') ? 'selected' : ''; ?>>Urgente</option>
                            <option value="emergencia" <?php echo (isset($_POST['urgencia']) && $_POST['urgencia'] == 'emergencia') ? 'selected' : ''; ?>>Emergencia</option>
                        </select>
                    </div>
                </fieldset>
                
                <!-- Información de cálculo de distancia y costos -->
                <div class="info-container" aria-live="polite">
                    <div class="form-group">
                        <label for="distancia">Distancia estimada:</label>
                        <input type="text" id="distancia" name="distancia" readonly 
                               placeholder="Calculando..." value="<?php echo isset($_POST['distancia']) ? htmlspecialchars($_POST['distancia']) : ''; ?>" aria-readonly="true">
                    </div>
                    
                    <div class="form-group">
                        <label for="costo">Costo estimado:</label>
                        <input type="text" id="costo" name="costo" readonly 
                               placeholder="Calculando..." value="<?php echo isset($_POST['costo']) ? htmlspecialchars($_POST['costo']) : ''; ?>" aria-readonly="true">
                    </div>
                </div>
                
                <!-- Sección de resumen para pago -->
                <div class="summary-section" aria-live="polite">
                    <h3>Resumen de Solicitud</h3>
                    <div class="summary-row">
                        <span>Cliente:</span>
                        <span id="display-nombre"><?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '(Por completar)'; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Correo:</span>
                        <span id="display-email"><?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '(No especificado)'; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Distancia estimada:</span>
                        <span id="display-distancia"><?php echo isset($_POST['distancia']) ? htmlspecialchars($_POST['distancia']) : '0 km'; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Costo total estimado:</span>
                        <span id="display-costo"><?php echo isset($_POST['costo']) ? '$' . htmlspecialchars($_POST['costo']) . ' MXN' : '$0.00 MXN'; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Depósito requerido (20%):</span>
                        <span id="display-deposito"><?php echo isset($_POST['costo']) ? '$' . number_format(floatval(preg_replace('/[^0-9.]/', '', $_POST['costo'])) * 0.2, 2) . ' MXN' : '$0.00 MXN'; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Pago restante:</span>
                        <span id="display-restante"><?php echo isset($_POST['costo']) ? '$' . number_format(floatval(preg_replace('/[^0-9.]/', '', $_POST['costo'])) * 0.8, 2) . ' MXN' : '$0.00 MXN'; ?></span>
                    </div>
                </div>
                
                <!-- Información de pago -->
                <fieldset>
                    <legend>Opciones de pago</legend>
                    <p>Para asegurar su servicio, puede realizar un depósito del 20% del costo total estimado.</p>
                    <p>El monto restante se pagará al finalizar el servicio.</p>
                    
                    <!-- Selección de método de pago -->
                    <div class="payment-methods" role="radiogroup" aria-labelledby="payment-methods-label">
                        <h4 id="payment-methods-label">💳 Seleccione su método de pago preferido</h4>
                        <p class="payment-subtitle">Elija cómo desea realizar el depósito del 20% para asegurar su servicio</p>
                        
                        <div class="payment-methods-grid">
                        <div class="payment-method" tabindex="0" role="radio" aria-checked="true" onclick="selectPaymentMethod('efectivo')" onkeydown="handlePaymentMethodKey(event, 'efectivo')">
                            <input type="radio" name="metodo_pago" id="metodo_efectivo" value="efectivo" <?php echo (!isset($_POST['metodo_pago_seleccionado']) || $_POST['metodo_pago_seleccionado'] == 'efectivo') ? 'checked' : ''; ?>>
                                <div class="payment-method-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 4V2C7 1.45 7.45 1 8 1H16C16.55 1 17 1.45 17 2V4H20C20.55 4 21 4.45 21 5S20.55 6 20 6H19V19C19 20.1 18.1 21 17 21H7C5.9 21 5 20.1 5 19V6H4C3.45 6 3 5.55 3 5S3.45 4 4 4H7ZM9 3V4H15V3H9ZM7 6V19H17V6H7Z" fill="#2E7D32"/>
                                        <path d="M9 8H15V10H9V8ZM9 12H15V14H9V12Z" fill="#2E7D32"/>
                                    </svg>
                                </div>
                            <div class="payment-method-details">
                                    <h4 class="payment-method-title">💰 Efectivo</h4>
                                    <p class="payment-method-description">Pago directo al técnico</p>
                                    <span class="payment-method-badge">Sin comisiones</span>
                                </div>
                                <div class="payment-method-check">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" fill="currentColor"/>
                                    </svg>
                            </div>
                        </div>
                        
                        <div class="payment-method" tabindex="0" role="radio" aria-checked="false" onclick="selectPaymentMethod('paypal')" onkeydown="handlePaymentMethodKey(event, 'paypal')">
                            <input type="radio" name="metodo_pago" id="metodo_paypal" value="paypal" <?php echo (isset($_POST['metodo_pago_seleccionado']) && $_POST['metodo_pago_seleccionado'] == 'paypal') ? 'checked' : ''; ?>>
                                <div class="payment-method-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.543-.676c-.318-.35-.7-.65-1.135-.89-.48-.27-.99-.48-1.52-.63-.5-.14-1.03-.21-1.57-.21H9.95c-.524 0-.968.382-1.05.9L7.76 19.106h4.64c.524 0 .968-.382 1.05-.9l1.12-7.106h2.19c.524 0 .968-.382 1.05-.9l1.12-7.106z" fill="#0070BA"/>
                                    </svg>
                                </div>
                            <div class="payment-method-details">
                                    <h4 class="payment-method-title">💳 PayPal</h4>
                                    <p class="payment-method-description">Tarjeta de crédito/débito</p>
                                    <span class="payment-method-badge">Pago seguro</span>
                                </div>
                                <div class="payment-method-check">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" fill="currentColor"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="payment-method" tabindex="0" role="radio" aria-checked="false" onclick="selectPaymentMethod('transferencia')" onkeydown="handlePaymentMethodKey(event, 'transferencia')">
                                <input type="radio" name="metodo_pago" id="metodo_transferencia" value="transferencia" <?php echo (isset($_POST['metodo_pago_seleccionado']) && $_POST['metodo_pago_seleccionado'] == 'transferencia') ? 'checked' : ''; ?>>
                                <div class="payment-method-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="#1976D2"/>
                                    </svg>
                                </div>
                                <div class="payment-method-details">
                                    <h4 class="payment-method-title">🏦 Transferencia</h4>
                                    <p class="payment-method-description">Transferencia bancaria</p>
                                    <span class="payment-method-badge">Rápido</span>
                                </div>
                                <div class="payment-method-check">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" fill="currentColor"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="payment-method" tabindex="0" role="radio" aria-checked="false" onclick="selectPaymentMethod('oxxo')" onkeydown="handlePaymentMethodKey(event, 'oxxo')">
                                <input type="radio" name="metodo_pago" id="metodo_oxxo" value="oxxo" <?php echo (isset($_POST['metodo_pago_seleccionado']) && $_POST['metodo_pago_seleccionado'] == 'oxxo') ? 'checked' : ''; ?>>
                                <div class="payment-method-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V5H19V19ZM17 12H7V10H17V12ZM17 16H7V14H17V16ZM17 8H7V6H17V8Z" fill="#FF6B35"/>
                                    </svg>
                                </div>
                                <div class="payment-method-details">
                                    <h4 class="payment-method-title">🏪 OXXO</h4>
                                    <p class="payment-method-description">Pago en tienda</p>
                                    <span class="payment-method-badge">Conveniente</span>
                                </div>
                                <div class="payment-method-check">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" fill="currentColor"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenedor para efectivo -->
                    <div id="efectivo-container" class="payment-container" style="<?php echo (!isset($_POST['metodo_pago_seleccionado']) || $_POST['metodo_pago_seleccionado'] == 'efectivo') ? 'display:block;' : 'display:none;'; ?>">
                        <div class="payment-info-card">
                            <div class="payment-info-header">
                                <div class="payment-info-icon">💰</div>
                                <h4>Pago en Efectivo</h4>
                            </div>
                            <div class="payment-info-content">
                                <p class="payment-info-description">Pague directamente al técnico cuando llegue a su ubicación</p>
                                <div class="payment-amount">
                                    <span class="payment-label">Monto total:</span>
                                    <span class="payment-value" id="efectivo-total"><?php echo isset($_POST['costo']) ? '$' . htmlspecialchars($_POST['costo']) . ' MXN' : '$0.00 MXN'; ?></span>
                                </div>
                                <div class="payment-features">
                                    <div class="feature-item">✅ Sin comisiones adicionales</div>
                                    <div class="feature-item">✅ Pago seguro al recibir el servicio</div>
                                    <div class="feature-item">✅ Confirmación inmediata</div>
                                </div>
                                <div class="payment-note">
                                    <strong>📞 Nota:</strong> Un operador se pondrá en contacto para confirmar los detalles del servicio.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenedor del botón de PayPal -->
                    <div id="paypal-container" class="payment-container" style="<?php echo (isset($_POST['metodo_pago_seleccionado']) && $_POST['metodo_pago_seleccionado'] == 'paypal') ? 'display:block;' : 'display:none;'; ?>">
                        <div class="payment-info-card">
                            <div class="payment-info-header">
                                <div class="payment-info-icon">💳</div>
                                <h4>Pago con PayPal</h4>
                            </div>
                            <div class="payment-info-content">
                                <p class="payment-info-description">Pago seguro con tarjeta de crédito, débito o cuenta PayPal</p>
                                <div class="payment-amount">
                                    <span class="payment-label">Depósito (20%):</span>
                                    <span class="payment-value" id="paypal-deposito">$0.00 MXN</span>
                                </div>
                                <div class="payment-features">
                                    <div class="feature-item">🔒 Pago 100% seguro</div>
                                    <div class="feature-item">💳 Acepta todas las tarjetas</div>
                                    <div class="feature-item">⚡ Procesamiento inmediato</div>
                                </div>
                        <div id="paypal-button-container">
                            <?php if (isset($_POST['paypal_order_id']) && $_POST['paypal_order_id'] != ''): ?>
                            <div class="paypal-success">
                                        <h4>✅ ¡Pago completado con éxito!</h4>
                                <p>ID de transacción: <?php echo htmlspecialchars($_POST['paypal_order_id']); ?></p>
                                <p>Estado: <?php echo htmlspecialchars($_POST['paypal_status']); ?></p>
                            </div>
                            <?php else: ?>
                            <button id="custom-paypal-button" class="paypal-button" type="button" onclick="initiatePayPalPayment()">
                                <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" alt="PayPal Logo" width="20" height="20">
                                Pagar con PayPal
                            </button>
                            <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenedor para transferencia -->
                    <div id="transferencia-container" class="payment-container" style="display:none;">
                        <div class="payment-info-card">
                            <div class="payment-info-header">
                                <div class="payment-info-icon">🏦</div>
                                <h4>Transferencia Bancaria</h4>
                            </div>
                            <div class="payment-info-content">
                                <p class="payment-info-description">Realice una transferencia bancaria a nuestra cuenta</p>
                                <div class="payment-amount">
                                    <span class="payment-label">Depósito (20%):</span>
                                    <span class="payment-value" id="transferencia-deposito">$0.00 MXN</span>
                                </div>
                                <div class="bank-details">
                                    <h5>Datos bancarios:</h5>
                                    <div class="bank-info">
                                        <div class="bank-item">
                                            <span class="bank-label">Banco:</span>
                                            <span class="bank-value">BBVA Bancomer</span>
                                        </div>
                                        <div class="bank-item">
                                            <span class="bank-label">Cuenta:</span>
                                            <span class="bank-value">0123456789</span>
                                        </div>
                                        <div class="bank-item">
                                            <span class="bank-label">CLABE:</span>
                                            <span class="bank-value">012345678901234567</span>
                                        </div>
                                        <div class="bank-item">
                                            <span class="bank-label">Titular:</span>
                                            <span class="bank-value">Grúas DBACK S.A. de C.V.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="payment-features">
                                    <div class="feature-item">⚡ Procesamiento en 1-2 horas</div>
                                    <div class="feature-item">📱 Envíe comprobante por WhatsApp</div>
                                    <div class="feature-item">✅ Confirmación automática</div>
                                </div>
                                <div class="payment-note">
                                    <strong>📱 Importante:</strong> Envíe el comprobante de transferencia al WhatsApp 668-825-3351 para confirmar su pago.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenedor para OXXO -->
                    <div id="oxxo-container" class="payment-container" style="display:none;">
                        <div class="payment-info-card">
                            <div class="payment-info-header">
                                <div class="payment-info-icon">🏪</div>
                                <h4>Pago en OXXO</h4>
                            </div>
                            <div class="payment-info-content">
                                <p class="payment-info-description">Pague en cualquier tienda OXXO con la referencia generada</p>
                                <div class="payment-amount">
                                    <span class="payment-label">Depósito (20%):</span>
                                    <span class="payment-value" id="oxxo-deposito">$0.00 MXN</span>
                                </div>
                                <div class="oxxo-reference">
                                    <h5>Referencia de pago:</h5>
                                    <div class="reference-code" id="oxxo-reference-code">DBACK-2024-001234</div>
                                    <button class="copy-button" onclick="copyReference()">📋 Copiar referencia</button>
                                </div>
                                <div class="payment-features">
                                    <div class="feature-item">🏪 Pague en cualquier OXXO</div>
                                    <div class="feature-item">⏰ Válido por 24 horas</div>
                                    <div class="feature-item">📱 Reciba confirmación por SMS</div>
                                </div>
                                <div class="payment-note">
                                    <strong>📱 Importante:</strong> Guarde el comprobante de pago y envíelo por WhatsApp al 668-825-3351.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" id="paypal_order_id" name="paypal_order_id" value="<?php echo isset($_POST['paypal_order_id']) ? htmlspecialchars($_POST['paypal_order_id']) : ''; ?>">
                    <input type="hidden" id="paypal_status" name="paypal_status" value="<?php echo isset($_POST['paypal_status']) ? htmlspecialchars($_POST['paypal_status']) : ''; ?>">
                    <input type="hidden" id="paypal_email" name="paypal_email" value="<?php echo isset($_POST['paypal_email']) ? htmlspecialchars($_POST['paypal_email']) : ''; ?>">
                    <input type="hidden" id="paypal_name" name="paypal_name" value="<?php echo isset($_POST['paypal_name']) ? htmlspecialchars($_POST['paypal_name']) : ''; ?>">
                    <input type="hidden" id="metodo_pago_seleccionado" name="metodo_pago_seleccionado" value="<?php echo isset($_POST['metodo_pago_seleccionado']) ? htmlspecialchars($_POST['metodo_pago_seleccionado']) : 'efectivo'; ?>">
                </fieldset>

                <!-- Checkbox para confirmar consentimiento -->
                <div id="privacy-container" class="privacy-checkbox-container">
                    <input type="checkbox" id="consentimiento" name="consentimiento" required aria-required="true" <?php echo isset($_POST['consentimiento']) ? 'checked' : ''; ?>>
                    <label for="consentimiento"><span class="privacy-text">He leído y acepto la <span class="privacy-link" id="openConsentModal" tabindex="0" role="button">política de privacidad</span></span></label>
                    <div id="consentimiento-error" class="error-message" role="alert">Debe aceptar la política de privacidad para continuar</div>
                </div>
                
                <!-- Botones de acción -->
                <div class="action-buttons">
                    <button type="submit" class="cta-button" id="submit-button">
                        <span id="submit-text">Enviar Solicitud</span>
                        <span id="submit-spinner" style="display:none;">Procesando...</span>
                    </button>
                </div>
            </form>
            <p class="emergency-note">Para emergencias inmediatas, llame al <a href="tel:+526688253351" class="emergency-phone">668-825-3351</a></p>
        </section>

        <!-- Modal de política de privacidad -->
        <div id="consentModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="consentModalTitle" hidden>
            <div class="modal-container">
                <button class="close-modal" id="closeModal" aria-label="Cerrar modal">&times;</button>
                
                <div class="header-decoration">
                    <h1 id="consentModalTitle">Consentimiento de Datos Personales</h1>
                </div>
                
                <div class="modal-content">
                    <p>Por este medio, expreso y otorgo mi consentimiento a Grúas DBACK en la recopilación, almacenamiento y uso de mis datos personales, con los fines relacionados con la prestación y uso de los servicios prestados por Grúas DBACK, tales como solicitudes de asistencia, el seguimiento de vehículos atendidos y la emisión de recibos o facturas de pago correspondiente.</p>
                    
                    <h2>Datos personales recopilados</h2>
                    <ul>
                        <li>Nombre completo</li>
                        <li>Domicilio</li>
                        <li>Número de teléfono</li>
                        <li>Correo electrónico</li>
                        <li>Datos del vehículo</li>
                        <li>Información de la solicitud de servicio</li>
                        <li>Ubicación del servicio</li>
                        <li>Fecha y hora de la solicitud</li>
                    </ul>
                    
                    <h2>Tratamiento de datos</h2>
                    <p>La información recopilada será tratada en estricto apego a lo establecido por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares y será utilizada exclusivamente para los fines mencionados.</p>
                    
                    <h2>Medios de contacto de Grúas DBACK para ejercer mis derechos ARCO</h2>
                    <p>Para más información sobre los derechos ARCO, visita: <a href="https://www.gob.mx/cms/uploads/attachment/file/428335/DDP_Gu_a_derechos_ARCO_13Dic18.pdf" target="_blank">Guía Derechos ARCO</a></p>
                    <p>Correo electrónico: <a href="mailto:protecciondedatos@gruasdback.com">protecciondedatos@gruasdback.com</a></p>
                    <p>Teléfono: <a href="tel:6688132905">668 813 2905</a></p>
                    <p>Dirección: Manuel Castro Elizalde 895 SUR, Luis Donaldo Colosio, Colón, 81233 Los Mochis, Sin.</p>
                    
                    <h2>Consentimiento</h2>
                    <p>Al hacer clic en "Aceptar", confirmo que he leído y comprendido los términos de esta autorización y que otorgo mi consentimiento para el tratamiento de mis datos personales a Grúas DBACK.</p>
                    
                    <div class="button-container">
                        <button class="button" id="acceptConsent">Aceptar</button>
                        <button class="button button-reject" id="rejectConsent">Rechazar</button>
                    </div>
                </div>
                
                <div class="footer-decoration">
                    <p>Grúas DBACK - Documento de Consentimiento de Datos Personales</p>
                </div>
            </div>
        </div>
        
        <!-- Modal de notificación de rechazo -->
        <div id="rejectModal" class="modal-overlay" role="alertdialog" aria-modal="true" aria-labelledby="rejectModalTitle" hidden>
            <div class="small-modal-container">
                <h2 id="rejectModalTitle">Consentimiento Requerido</h2>
                <p>Debe aceptar la política de privacidad para utilizar nuestros servicios. 
                    No podemos procesar su solicitud sin su consentimiento para el tratamiento de datos personales.</p>
                <button class="notification-button" id="closeRejectModal">Entendido</button>
            </div>
        </div>
        
        <!-- Modal de éxito al enviar -->
        <div id="successModal" class="success-modal-overlay" role="alertdialog" aria-modal="true" aria-labelledby="successModalTitle" hidden>
            <div class="success-modal-container">
                <h2 id="successModalTitle">¡Solicitud Enviada!</h2>
                <p>Su solicitud se ha enviado con éxito.</p>
                <p>Nos pondremos en contacto con usted a la brevedad posible.</p>
                <button class="success-button" id="closeSuccessModal">Aceptar</button>
            </div>
        </div>
    </main>

    <div class="chatbot-wrapper" id="chatbot-window" role="dialog" aria-modal="false" aria-label="Asistente virtual de Grúas DBACK">
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <h3>Asistente DBACK</h3>
                <span>Agenda tu servicio en minutos</span>
            </div>
            <button class="chatbot-close" type="button" aria-label="Cerrar asistente">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chatbot-body" aria-live="polite"></div>
        <div class="chatbot-options-wrapper">
            <div class="chatbot-options chatbot-dynamic-options"></div>
        </div>
        <div class="chatbot-footer">
            <input type="text" class="chatbot-input" placeholder="Escribe tu respuesta aquí..." aria-label="Mensaje para el asistente">
            <button class="chatbot-send" type="button">
                Enviar
            </button>
        </div>
    </div>

    <button class="chatbot-toggle" type="button" aria-controls="chatbot-window" aria-expanded="false" aria-label="Abrir asistente virtual">
        <i class="fas fa-headset"></i>
        <div>
            Habla con un asesor
            <span class="chatbot-status">Respuesta inmediata 24/7</span>
        </div>
    </button>

    <script src="js/chatbot.js"></script>
    <script>
        // Variables globales
        let costoTotalServicio = <?php echo isset($_POST['costo']) ? floatval(preg_replace('/[^0-9.]/', '', $_POST['costo'])) : 0; ?>;
        let paypalButtonsInitialized = false;
        
        // Función para validar un campo
        function validarCampo(campoId, mensajeErrorId, validacionFn) {
            const campo = document.getElementById(campoId);
            const mensajeError = document.getElementById(mensajeErrorId);
            
            if (!validacionFn(campo)) {
                campo.classList.add('input-error');
                campo.classList.remove('input-success');
                campo.setAttribute('aria-invalid', 'true');
                mensajeError.style.display = 'block';
                return false;
            } else {
                campo.classList.remove('input-error');
                campo.classList.add('input-success');
                campo.setAttribute('aria-invalid', 'false');
                mensajeError.style.display = 'none';
                return true;
            }
        }
        
        // Funciones de validación específicas
        function validarNombre(campo) {
            const regex = /^[A-Za-záéíóúÁÉÍÓÚñÑ\s]{3,50}$/;
            return regex.test(campo.value);
        }
        
        function validarTelefono(campo) {
            const regex = /^(\d{10}|\d{3}-\d{3}-\d{4})$/;
            return regex.test(campo.value);
        }
        
        function validarEmail(campo) {
            if (!campo.value) return true; // Opcional
            const regex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
            return regex.test(campo.value);
        }
        
        function validarUbicacion(campo) {
            return campo.value.length >= 5;
        }
        
        function validarSelect(campo) {
            return campo.value !== "";
        }
        
        function validarTexto(campo) {
            return campo.value.length >= campo.minLength;
        }
        
        function validarDescripcion(campo) {
            return campo.value.length >= 10 && campo.value.length <= 500;
        }
        
        function validarFoto(campo) {
            if (!campo.files.length) return true; // Opcional
            
            const file = campo.files[0];
            const tiposPermitidos = ['image/jpeg', 'image/png'];
            const tamanoMaximo = 5 * 1024 * 1024; // 5MB
            
            if (!tiposPermitidos.includes(file.type)) {
                return false;
            }
            
            if (file.size > tamanoMaximo) {
                return false;
            }
            
            return true;
        }
        
        // Función para verificar si el formulario está completo (sin validar formato)
        function verificarFormularioCompleto() {
            const camposRequeridos = [
                'nombre', 'telefono', 'ubicacion_origen', 'ubicacion_destino',
                'vehiculo', 'marca', 'modelo', 'tipo_servicio', 'descripcion'
            ];
            
            // Verificar campos de texto
            for (let campoId of camposRequeridos) {
                const campo = document.getElementById(campoId);
                if (!campo || !campo.value.trim()) {
                    return false;
                }
            }
            
            // Verificar consentimiento
            const consentimiento = document.getElementById('consentimiento');
            if (!consentimiento || !consentimiento.checked) {
                return false;
            }
            
            return true;
        }
        
        // Función para actualizar el estado del botón de envío
        function actualizarEstadoBoton() {
            const botonEnviar = document.getElementById('submit-button');
            const formularioCompleto = verificarFormularioCompleto();
            
            if (formularioCompleto) {
                botonEnviar.disabled = false;
                botonEnviar.style.opacity = '1';
                botonEnviar.title = 'Formulario completo - Listo para enviar';
            } else {
                botonEnviar.disabled = true;
                botonEnviar.style.opacity = '0.6';
                botonEnviar.title = 'Complete todos los campos requeridos para enviar';
            }
        }
        
        // Función para validar todos los campos del formulario
        function validarFormulario() {
            let valido = true;
            
            // Validar información de contacto
            valido = validarCampo('nombre', 'nombre-error', validarNombre) && valido;
            valido = validarCampo('telefono', 'telefono-error', validarTelefono) && valido;
            valido = validarCampo('email', 'email-error', validarEmail) && valido;
            
            // Validar ubicaciones
            valido = validarCampo('ubicacion_origen', 'ubicacion_origen-error', validarUbicacion) && valido;
            valido = validarCampo('ubicacion_destino', 'ubicacion_destino-error', validarUbicacion) && valido;
            
            // Validar información del vehículo
            valido = validarCampo('vehiculo', 'vehiculo-error', validarSelect) && valido;
            valido = validarCampo('marca', 'marca-error', validarTexto) && valido;
            valido = validarCampo('modelo', 'modelo-error', validarTexto) && valido;
            valido = validarCampo('foto', 'foto-error', validarFoto) && valido;
            
            // Validar detalles del servicio
            valido = validarCampo('tipo_servicio', 'tipo_servicio-error', validarSelect) && valido;
            valido = validarCampo('descripcion', 'descripcion-error', validarDescripcion) && valido;
            
            // Validar consentimiento
            const consentimiento = document.getElementById('consentimiento');
            const consentimientoError = document.getElementById('consentimiento-error');
            
            if (!consentimiento.checked) {
                consentimientoError.style.display = 'block';
                valido = false;
            } else {
                consentimientoError.style.display = 'none';
            }
            
            // Validar método de pago si hay costo
            if (costoTotalServicio > 0) {
                const metodoPago = document.getElementById('metodo_pago_seleccionado').value;
                if (metodoPago === 'paypal' && !document.getElementById('paypal_order_id').value) {
                    alert('Por favor complete el pago con PayPal antes de enviar el formulario');
                    valido = false;
                }
            }
            
            return valido;
        }
        
        // Función para obtener la ubicación actual
        function obtenerUbicacionActual(destino = false) {
            const inputId = destino ? 'ubicacion_destino' : 'ubicacion_origen';
            const ubicacionInput = document.getElementById(inputId);
            const errorId = destino ? 'ubicacion_destino-error' : 'ubicacion_origen-error';
            const errorElement = document.getElementById(errorId);
            
            if (!navigator.geolocation) {
                errorElement.textContent = "La geolocalización no es soportada por tu navegador";
                errorElement.style.display = 'block';
                return;
            }
            
            ubicacionInput.placeholder = "Obteniendo ubicación...";
            ubicacionInput.disabled = true;
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const coordenadas = `${lat},${lng}`;
                    
                    // Simulamos la obtención de dirección
                    ubicacionInput.value = coordenadas;
                    validarCampo(inputId, errorId, validarUbicacion);
                    ubicacionInput.disabled = false;
                    
                    // Calcular distancia si ambas ubicaciones están completas
                    if (document.getElementById('ubicacion_origen').value && 
                        document.getElementById('ubicacion_destino').value) {
                        calcularDistancia();
                    }
                },
                function(error) {
                    console.error("Error al obtener la ubicación:", error);
                    ubicacionInput.placeholder = "Dirección o punto de referencia";
                    ubicacionInput.disabled = false;
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorElement.textContent = "Permiso denegado para acceder a la ubicación";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorElement.textContent = "La información de ubicación no está disponible";
                            break;
                        case error.TIMEOUT:
                            errorElement.textContent = "Tiempo de espera agotado al obtener la ubicación";
                            break;
                        default:
                            errorElement.textContent = "Error desconocido al obtener la ubicación";
                    }
                    
                    errorElement.style.display = 'block';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // Función para actualizar la información del usuario
        function actualizarInfoUsuario() {
            const nombre = document.getElementById('nombre').value || '(Por completar)';
            const email = document.getElementById('email').value || '(No especificado)';
            
            document.getElementById('display-nombre').textContent = nombre;
            document.getElementById('display-email').textContent = email;
            
            // Actualizar campos ocultos para el backend
            document.getElementById('paypal_name').value = nombre;
            document.getElementById('paypal_email').value = email;
        }

        // Función para actualizar la información de pago
        function actualizarInfoPago() {
            const distancia = document.getElementById('distancia').value;
            const deposito = (costoTotalServicio * 0.2).toFixed(2);
            const restante = (costoTotalServicio * 0.8).toFixed(2);
            
            document.getElementById('display-distancia').textContent = distancia;
            document.getElementById('display-costo').textContent = `${costoTotalServicio.toFixed(2)} MXN`;
            document.getElementById('display-deposito').textContent = `${deposito} MXN`;
            document.getElementById('display-restante').textContent = `${restante} MXN`;
            
            // Actualizar todos los montos de pago
            document.getElementById('efectivo-total').textContent = `$${costoTotalServicio.toFixed(2)} MXN`;
            document.getElementById('paypal-deposito').textContent = `$${deposito} MXN`;
            document.getElementById('transferencia-deposito').textContent = `$${deposito} MXN`;
            document.getElementById('oxxo-deposito').textContent = `$${deposito} MXN`;
        }

        // Función para calcular la distancia (haversine) para evitar falsos positivos cuando origen=destino
        function calcularDistancia() {
            const origen = document.getElementById('ubicacion_origen').value;
            const destino = document.getElementById('ubicacion_destino').value;
            
            console.log('Calculando distancia:', { origen, destino });
            
            if (!origen || !destino) {
                document.getElementById('distancia').value = "Esperando ubicaciones...";
                document.getElementById('costo').value = "Esperando ubicaciones...";
                return; // No calcular si falta alguna ubicación
            }

            const olat = parseFloat(document.getElementById('origen_lat').value || '0');
            const olng = parseFloat(document.getElementById('origen_lng').value || '0');
            const dlat = parseFloat(document.getElementById('destino_lat').value || '0');
            const dlng = parseFloat(document.getElementById('destino_lng').value || '0');

            console.log('Coordenadas:', { olat, olng, dlat, dlng });

            // Si contamos con coordenadas válidas, usar Haversine, si no, mantener cálculo simulado
            const tieneCoords = !isNaN(olat) && !isNaN(olng) && !isNaN(dlat) && !isNaN(dlng) && (olat !== 0 || olng !== 0 || dlat !== 0 || dlng !== 0);

            document.getElementById('distancia').value = "Calculando...";
            document.getElementById('costo').value = "Calculando...";

            const costoPorKilometro = 80; // 80 pesos por kilómetro

            setTimeout(() => {
                let distanciaKm;
                if (tieneCoords) {
                    // Haversine
                    const R = 6371; // km
                    const toRad = deg => deg * Math.PI / 180;
                    const dLat = toRad(dlat - olat);
                    const dLon = toRad(dlng - olng);
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(toRad(olat)) * Math.cos(toRad(dlat)) * Math.sin(dLon/2) * Math.sin(dLon/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    distanciaKm = R * c;

                    // Si origen y destino son prácticamente iguales (< 50 metros), distancia = 0
                    if (!isFinite(distanciaKm) || distanciaKm < 0.05) distanciaKm = 0;
                    
                    console.log('Distancia calculada con coordenadas:', distanciaKm);
                } else {
                    // Fallback simulado si no hay coords - calcular basado en longitud de texto
                    const longitudOrigen = origen.length;
                    const longitudDestino = destino.length;
                    const diferencia = Math.abs(longitudOrigen - longitudDestino);
                    distanciaKm = Math.max(1, diferencia * 0.5 + Math.random() * 10);
                    
                    console.log('Distancia calculada con fallback:', distanciaKm);
                }

                costoTotalServicio = distanciaKm * costoPorKilometro;

                document.getElementById('distancia').value = `${distanciaKm.toFixed(2)} km`;
                document.getElementById('costo').value = `${costoTotalServicio.toFixed(2)} MXN`;

                console.log('Resultado final:', { distanciaKm, costoTotalServicio });

                actualizarInfoPago();
                actualizarInfoUsuario();
            }, 500);
        }

        // Función para manejar teclado en métodos de pago
        function handlePaymentMethodKey(event, method) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectPaymentMethod(method);
            }
        }

        // Función para seleccionar método de pago
        function selectPaymentMethod(method) {
            // Actualizar radio buttons
            document.getElementById('metodo_efectivo').checked = (method === 'efectivo');
            document.getElementById('metodo_paypal').checked = (method === 'paypal');
            document.getElementById('metodo_transferencia').checked = (method === 'transferencia');
            document.getElementById('metodo_oxxo').checked = (method === 'oxxo');
            
            // Actualizar atributos ARIA
            document.querySelectorAll('[role="radio"]').forEach(el => {
                el.setAttribute('aria-checked', 'false');
            });
            
            const selectedMethod = document.querySelector(`.payment-method[onclick*="${method}"]`);
            if (selectedMethod) {
                selectedMethod.setAttribute('aria-checked', 'true');
            }
            
            // Mostrar/ocultar contenedores
            document.getElementById('efectivo-container').style.display = method === 'efectivo' ? 'block' : 'none';
            document.getElementById('paypal-container').style.display = method === 'paypal' ? 'block' : 'none';
            document.getElementById('transferencia-container').style.display = method === 'transferencia' ? 'block' : 'none';
            document.getElementById('oxxo-container').style.display = method === 'oxxo' ? 'block' : 'none';
            
            // Guardar el método seleccionado en el campo oculto
            document.getElementById('metodo_pago_seleccionado').value = method;
            
            // Actualizar montos de depósito para métodos que lo requieren
            if (method !== 'efectivo') {
                const deposito = (costoTotalServicio * 0.2).toFixed(2);
                document.getElementById('paypal-deposito').textContent = `$${deposito} MXN`;
                document.getElementById('transferencia-deposito').textContent = `$${deposito} MXN`;
                document.getElementById('oxxo-deposito').textContent = `$${deposito} MXN`;
            }
            
            // Generar nueva referencia para OXXO
            if (method === 'oxxo') {
                generateOXXOReference();
            }
            
            // Cargar PayPal si es necesario
            if (method === 'paypal' && costoTotalServicio > 0) {
                loadPayPalScript();
            }
        }

        // Función para cargar el SDK de PayPal
        function loadPayPalScript() {
            // Verificar si el script ya está cargado
            if (paypalButtonsInitialized) {
                return;
            }
            
            // Mostrar mensaje de carga
            document.getElementById('paypal-button-container').innerHTML = '<p>Cargando opciones de pago...</p>';
            
            // Cargar el SDK de PayPal
            const script = document.createElement('script');
            // IMPORTANTE: Reemplaza con tu Client ID real de PayPal
            script.src = 'https://www.paypal.com/sdk/js?client-id=AQtRyS9WsZAGYVSbDj_acQ426CavpCZTucraVmHBgae8R9nHwz6HGigDPOgPYRZNxSIJdJLCY0y9FtHT&currency=MXN';
            script.async = true;
            
            script.onerror = function() {
                document.getElementById('paypal-button-container').innerHTML = `
                    <div class="paypal-error">
                        <h4>Error al cargar PayPal</h4>
                        <p>No se pudo cargar el sistema de pagos. Por favor, recargue la página.</p>
                    </div>
                `;
            };
            
            script.onload = function() {
                setupPayPalButtons();
                paypalButtonsInitialized = true;
            };
            
            document.body.appendChild(script);
        }
        
        // Configurar los botones de PayPal
        function setupPayPalButtons() {
            // Limpiar el contenedor
            const buttonContainer = document.getElementById('paypal-button-container');
            buttonContainer.innerHTML = '';
            
            // Calcular el depósito (20% del costo total)
            const deposito = (costoTotalServicio * 0.2).toFixed(2);
            
            if (typeof paypal !== 'undefined') {
                paypal.Buttons({
                    style: {
                        color: 'blue',
                        shape: 'rect',
                        label: 'pay',
                        height: 40
                    },
                    
                    // Crear la orden
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                description: 'Depósito para servicio de grúa',
                                amount: {
                                    value: deposito,
                                    currency_code: 'MXN'
                                }
                            }],
                            application_context: {
                                shipping_preference: 'NO_SHIPPING'
                            }
                        });
                    },
                    
                    // Finalizar la transacción
                    onApprove: function(data, actions) {
                        return actions.order.capture().then(function(orderData) {
                            // Procesar la transacción completada
                            const transaction = orderData.purchase_units[0].payments.captures[0];
                            
                            // Actualizar campos ocultos del formulario
                            document.getElementById('paypal_order_id').value = data.orderID;
                            document.getElementById('paypal_status').value = transaction.status;
                            document.getElementById('paypal_email').value = orderData.payer.email_address;
                            document.getElementById('paypal_name').value = orderData.payer.name.given_name + ' ' + (orderData.payer.name.surname || '');
                            
                            // Mostrar mensaje de éxito
                            buttonContainer.innerHTML = `
                                <div class="paypal-success">
                                    <h4>¡Pago completado con éxito!</h4>
                                    <p>ID de transacción: ${data.orderID}</p>
                                    <p>Monto: $${deposito} MXN</p>
                                    <p>Estado: ${transaction.status}</p>
                                </div>
                            `;
                            
                            // Habilitar el botón de enviar formulario
                            document.getElementById('submit-button').disabled = false;
                        });
                    },
                    
                    // Manejar errores
                    onError: function(err) {
                        buttonContainer.innerHTML = `
                            <div class="paypal-error">
                                <h4>Error en el pago</h4>
                                <p>${err.message || 'Ocurrió un error al procesar el pago'}</p>
                                <p>Por favor, intente nuevamente.</p>
                            </div>
                        `;
                    }
                }).render('#paypal-button-container');
            } else {
                buttonContainer.innerHTML = `
                    <div class="paypal-error">
                        <h4>PayPal no disponible</h4>
                        <p>No se pudo cargar el sistema de pagos de PayPal.</p>
                    </div>
                `;
            }
        }

    // Sincronizar cambios en mapa de origen con el input
    mapOrigen.on('click', e => {
        markerOrigen.setLatLng(e.latlng);
        reverseGeocode(e.latlng.lat, e.latlng.lng, false);
        // Calcular distancia después de actualizar ubicación
        setTimeout(() => calcularDistancia(), 1000);
    });

    markerOrigen.on('dragend', () => {
        const pos = markerOrigen.getLatLng();
        reverseGeocode(pos.lat, pos.lng, false);
        // Calcular distancia después de actualizar ubicación
        setTimeout(() => calcularDistancia(), 1000);
    });

        // Función para generar referencia de OXXO
        function generateOXXOReference() {
            const timestamp = new Date().getTime();
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const reference = `DBACK-2024-${random}`;
            document.getElementById('oxxo-reference-code').textContent = reference;
        }
        
        // Función para copiar referencia al portapapeles
        function copyReference() {
            const referenceCode = document.getElementById('oxxo-reference-code').textContent;
            navigator.clipboard.writeText(referenceCode).then(() => {
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = '✅ Copiado!';
                button.style.backgroundColor = '#4CAF50';
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.backgroundColor = '';
                }, 2000);
            }).catch(() => {
                alert('No se pudo copiar la referencia. Código: ' + referenceCode);
            });
        }

    // Función para iniciar el pago con PayPal
        function initiatePayPalPayment() {
            // Verificar que hay un costo calculado
            if (costoTotalServicio <= 0) {
                alert('Por favor complete la información del servicio para calcular el costo antes de pagar.');
                return;
            }
            
            // Cargar el SDK de PayPal si no está cargado
            loadPayPalScript();
        }

        // Función para validar y enviar el formulario
        function validarYEnviarFormulario(event) {
            event.preventDefault();
            
            // Mostrar spinner de carga
            document.getElementById('submit-text').style.display = 'none';
            document.getElementById('submit-spinner').style.display = 'inline-block';
            document.getElementById('submit-button').disabled = true;
            
            // Validar todos los campos
            const formularioValido = validarFormulario();
            
            if (!formularioValido) {
                // Ocultar spinner y habilitar botón
                document.getElementById('submit-text').style.display = 'inline-block';
                document.getElementById('submit-spinner').style.display = 'none';
                document.getElementById('submit-button').disabled = false;
                
                // Desplazarse al primer error
                const primerError = document.querySelector('.input-error');
                if (primerError) {
                    primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    primerError.focus();
                }
                
                return false;
            }
            
            // Verificar consentimiento
            if (!document.getElementById('consentimiento').checked) {
                document.getElementById('rejectModal').hidden = false;
                
                // Ocultar spinner y habilitar botón
                document.getElementById('submit-text').style.display = 'inline-block';
                document.getElementById('submit-spinner').style.display = 'none';
                document.getElementById('submit-button').disabled = false;
                
                return false;
            }
            
            // Mostrar modal de éxito
            document.getElementById('successModal').hidden = false;
            
            // Enviar el formulario después de 2 segundos (simulación)
            setTimeout(() => {
                document.getElementById('servicioForm').submit();
            }, 2000);
            
            return true;
        }

        // Función para manejar el modal de consentimiento
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            
            if (show) {
                modal.hidden = false;
                modal.setAttribute('aria-hidden', 'false');
                // Enfocar el primer elemento interactivo del modal
                const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                if (focusable) focusable.focus();
                
                // Deshabilitar scroll del body
                document.body.style.overflow = 'hidden';
            } else {
                modal.hidden = true;
                modal.setAttribute('aria-hidden', 'true');
                
                // Habilitar scroll del body
                document.body.style.overflow = '';
            }
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Los event listeners de ubicación ya están definidos arriba
            
            // Calcular distancia cuando cambian las ubicaciones
            document.getElementById('ubicacion_origen').addEventListener('change', calcularDistancia);
            document.getElementById('ubicacion_destino').addEventListener('change', calcularDistancia);
            
            // También calcular cuando se escribe en los campos
            document.getElementById('ubicacion_origen').addEventListener('input', function() {
                if (this.value.length >= 5) {
                    setTimeout(() => calcularDistancia(), 2000);
                }
            });
            document.getElementById('ubicacion_destino').addEventListener('input', function() {
                if (this.value.length >= 5) {
                    setTimeout(() => calcularDistancia(), 2000);
                }
            });
            
            // Validación en tiempo real para campos de texto
            document.getElementById('nombre').addEventListener('blur', function() {
                validarCampo('nombre', 'nombre-error', validarNombre);
                actualizarInfoUsuario();
            });
            
            document.getElementById('telefono').addEventListener('blur', function() {
                validarCampo('telefono', 'telefono-error', validarTelefono);
            });
            
            document.getElementById('email').addEventListener('blur', function() {
                validarCampo('email', 'email-error', validarEmail);
                actualizarInfoUsuario();
            });
            
            document.getElementById('ubicacion_origen').addEventListener('blur', function() {
                validarCampo('ubicacion_origen', 'ubicacion_origen-error', validarUbicacion);
            });
            
            document.getElementById('ubicacion_destino').addEventListener('blur', function() {
                validarCampo('ubicacion_destino', 'ubicacion_destino-error', validarUbicacion);
            });
            
            document.getElementById('marca').addEventListener('blur', function() {
                validarCampo('marca', 'marca-error', validarTexto);
            });
            
            document.getElementById('modelo').addEventListener('blur', function() {
                validarCampo('modelo', 'modelo-error', validarTexto);
            });
            
            document.getElementById('descripcion').addEventListener('blur', function() {
                validarCampo('descripcion', 'descripcion-error', validarDescripcion);
            });
            
            document.getElementById('foto').addEventListener('change', function() {
                validarCampo('foto', 'foto-error', validarFoto);
            });
            
            // Validación para selects
            document.getElementById('vehiculo').addEventListener('change', function() {
                validarCampo('vehiculo', 'vehiculo-error', validarSelect);
            });
            
            document.getElementById('tipo_servicio').addEventListener('change', function() {
                validarCampo('tipo_servicio', 'tipo_servicio-error', validarSelect);
            });
            
            // Modal de consentimiento
            document.getElementById('openConsentModal').addEventListener('click', function(e) {
                e.preventDefault();
                toggleModal('consentModal', true);
            });
            
            document.getElementById('closeModal').addEventListener('click', function() {
                toggleModal('consentModal', false);
            });
            
            document.getElementById('acceptConsent').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('consentimiento').checked = true;
                toggleModal('consentModal', false);
            });
            
            document.getElementById('rejectConsent').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('consentimiento').checked = false;
                toggleModal('consentModal', false);
                toggleModal('rejectModal', true);
            });
            
            // Modal de rechazo
            document.getElementById('closeRejectModal').addEventListener('click', function() {
                toggleModal('rejectModal', false);
            });
            
            // Modal de éxito
            document.getElementById('closeSuccessModal').addEventListener('click', function() {
                toggleModal('successModal', false);
            });
            
            // Cerrar modales al hacer clic fuera
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        toggleModal(modal.id, false);
                    }
                });
            });
            
            // Cerrar modales con Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay').forEach(modal => {
                        if (!modal.hidden) {
                            toggleModal(modal.id, false);
                        }
                    });
                }
            });
            
            // Selección de método de pago por defecto
            selectPaymentMethod('efectivo');
            
            // Configurar el formulario
            document.getElementById('servicioForm').addEventListener('submit', validarYEnviarFormulario);
            
            // Agregar event listeners para actualizar el estado del botón en tiempo real
            const camposFormulario = [
                'nombre', 'telefono', 'email', 'ubicacion_origen', 'ubicacion_destino',
                'vehiculo', 'marca', 'modelo', 'tipo_servicio', 'descripcion', 'consentimiento'
            ];
            
            camposFormulario.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo) {
                    if (campo.type === 'checkbox') {
                        campo.addEventListener('change', actualizarEstadoBoton);
                    } else {
                        campo.addEventListener('input', actualizarEstadoBoton);
                        campo.addEventListener('change', actualizarEstadoBoton);
                    }
                }
            });
            
            // Inicializar el estado del botón
            actualizarEstadoBoton();
            
            // Inicializar costoTotalServicio si ya hay un valor en el formulario
            const costoInput = document.getElementById('costo');
            if (costoInput && costoInput.value) {
                costoTotalServicio = parseFloat(costoInput.value.replace(/[^0-9.]/g, ''));
                actualizarInfoPago();
            }
            
            // Intentar calcular distancia inicial si hay valores
            setTimeout(() => {
                calcularDistancia();
            }, 1000);
        });
    </script>
</body>
</html> 