<?php
// Configuración de conexión
$servername = "localhost";
$username = "root";
$password = "5211";  // Cambia esto por tu contraseña real
$dbname = "dback";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Procesar el formulario si se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y sanitizar los datos del formulario
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $email = $conn->real_escape_string($_POST['email']);
    $ubicacion_origen = $conn->real_escape_string($_POST['ubicacion_origen']);
    $ubicacion_destino = $conn->real_escape_string($_POST['ubicacion_destino']);
    $vehiculo = $conn->real_escape_string($_POST['vehiculo']);
    $marca = $conn->real_escape_string($_POST['marca']);
    $modelo = $conn->real_escape_string($_POST['modelo']);
    $tipo_servicio = $conn->real_escape_string($_POST['tipo_servicio']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $urgencia = $conn->real_escape_string($_POST['urgencia']);
    $distancia = $conn->real_escape_string($_POST['distancia']);
    
    // Procesar el costo - CORRECCIÓN PRINCIPAL
    $costo_raw = $_POST['costo'];
    $costo_clean = preg_replace('/[^0-9.]/', '', $costo_raw); // Eliminar todo excepto números y punto decimal
    $costo = floatval($costo_clean); // Convertir a número
    
    // Validación adicional del costo
    if (!is_numeric($costo)) {
        $error_message = "El costo debe ser un valor numérico válido";
    } else {
        $metodo_pago = $conn->real_escape_string($_POST['metodo_pago_seleccionado']);
        $paypal_order_id = $conn->real_escape_string($_POST['paypal_order_id']);
        $paypal_status = $conn->real_escape_string($_POST['paypal_status']);
        $paypal_email = $conn->real_escape_string($_POST['paypal_email']);
        $paypal_name = $conn->real_escape_string($_POST['paypal_name']);
        $consentimiento = isset($_POST['consentimiento']) ? 1 : 0;
        
        // Procesar la foto del vehículo
        $foto_nombre = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_nombre = basename($_FILES['foto']['name']);
            $upload_dir = "uploads/";
            
            // Crear directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Mover el archivo al directorio de uploads
            move_uploaded_file($foto_tmp, $upload_dir . $foto_nombre);
        }
        
        // Insertar en la base de datos - $costo sin comillas porque es numérico
        $sql = "INSERT INTO solicitudes_servicio (
            nombre, telefono, email, ubicacion_origen, ubicacion_destino, 
            vehiculo, marca, modelo, foto_vehiculo, tipo_servicio, 
            descripcion, urgencia, distancia, costo, metodo_pago, 
            paypal_order_id, paypal_status, paypal_email, paypal_name, 
            consentimiento, fecha_solicitud
        ) VALUES (
            '$nombre', '$telefono', '$email', '$ubicacion_origen', '$ubicacion_destino', 
            '$vehiculo', '$marca', '$modelo', '$foto_nombre', '$tipo_servicio', 
            '$descripcion', '$urgencia', '$distancia', $costo, '$metodo_pago', 
            '$paypal_order_id', '$paypal_status', '$paypal_email', '$paypal_name', 
            $consentimiento, NOW()
        )";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Solicitud enviada con éxito. ID: " . $conn->insert_id;
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
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
</head>
<body>
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
                        </div>
                    </div>
                    
                    <!-- Hoja de estilos de Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

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
    const map = L.map('map').setView([25.814960975032974, -108.97984572706956], 13); // CDMX default

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([25.814960975032974, -108.97984572706956], { draggable: true }).addTo(map);

    function reverseGeocode(lat, lon) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('ubicacion_destino').value = data.display_name;
                }
            });
    }

    function searchAddress(query) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`)
            .then(res => res.json())
            .then(data => {
                const datalist = document.getElementById('ubicaciones_destino');
                datalist.innerHTML = '';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.display_name;
                    datalist.appendChild(option);
                });

                if (data[0]) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    map.setView([lat, lon], 15);
                    marker.setLatLng([lat, lon]);
                }
            });
    }

    document.getElementById('ubicacion_destino').addEventListener('input', function () {
        const query = this.value;
        if (query.length >= 5) {
            searchAddress(query);
        }
    });

    document.getElementById('obtenerUbicacionDestino').addEventListener('click', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                map.setView([lat, lon], 15);
                marker.setLatLng([lat, lon]);
                reverseGeocode(lat, lon);
            }, () => {
                alert("No se pudo obtener tu ubicación.");
            });
        } else {
            alert("Geolocalización no soportada.");
        }
    });

    map.on('click', e => {
        marker.setLatLng(e.latlng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    marker.on('dragend', () => {
        const pos = marker.getLatLng();
        reverseGeocode(pos.lat, pos.lng);
    });
</script>


<!-- Script de Leaflet -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>




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
                            <option value="bicicleta" <?php echo (isset($_POST['vehiculo']) && $_POST['vehiculo'] == 'bicicleta') ? 'selected' : ''; ?>>Bicicleta</option>
                        </select>
                        <div id="vehiculo-error" class="error-message" role="alert">Por favor seleccione un tipo de vehículo</div>
                    </div>
                    
                    <dclass="form-group">
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
                        <h4 id="payment-methods-label">Seleccione método de pago:</h4>
                        
                        <div class="payment-method" tabindex="0" role="radio" aria-checked="true" onclick="selectPaymentMethod('efectivo')" onkeydown="handlePaymentMethodKey(event, 'efectivo')">
                            <input type="radio" name="metodo_pago" id="metodo_efectivo" value="efectivo" <?php echo (!isset($_POST['metodo_pago_seleccionado']) || $_POST['metodo_pago_seleccionado'] == 'efectivo') ? 'checked' : ''; ?>>
                            <img src="https://cdn-icons-png.flaticon.com/512/639/639365.png" alt="Efectivo" class="payment-method-icon" width="40" height="40">
                            <div class="payment-method-details">
                                <h4 class="payment-method-title">Efectivo</h4>
                                <p class="payment-method-description">Pago en efectivo al momento de recibir el servicio</p>
                            </div>
                        </div>
                        
                        <div class="payment-method" tabindex="0" role="radio" aria-checked="false" onclick="selectPaymentMethod('paypal')" onkeydown="handlePaymentMethodKey(event, 'paypal')">
                            <input type="radio" name="metodo_pago" id="metodo_paypal" value="paypal" <?php echo (isset($_POST['metodo_pago_seleccionado']) && $_POST['metodo_pago_seleccionado'] == 'paypal') ? 'checked' : ''; ?>>
                            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" alt="PayPal" class="payment-method-icon" width="40" height="40">
                            <div class="payment-method-details">
                                <h4 class="payment-method-title">PayPal</h4>
                                <p class="payment-method-description">Pago seguro con tarjeta de crédito o cuenta PayPal</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenedor para efectivo -->
                    <div id="efectivo-container" class="payment-container" style="<?php echo (!isset($_POST['metodo_pago_seleccionado']) || $_POST['metodo_pago_seleccionado'] == 'efectivo') ? 'display:block;' : 'display:none;'; ?>">
                        <p>Ha seleccionado pago en efectivo.</p>
                        <p>El monto total de <strong id="efectivo-total"><?php echo isset($_POST['costo']) ? '$' . htmlspecialchars($_POST['costo']) . ' MXN' : '$0.00 MXN'; ?></strong> deberá ser pagado al finalizar el servicio.</p>
                        <p><strong>Nota:</strong> Al elegir este método, un operador se pondrá en contacto con usted para confirmar los detalles.</p>
                    </div>
                    
                    <!-- Contenedor del botón de PayPal -->
                    <div id="paypal-container" class="payment-container" style="<?php echo (isset($_POST['metodo_pago_seleccionado']) && $_POST['metodo_pago_seleccionado'] == 'paypal') ? 'display:block;' : 'display:none;'; ?>">
                        <div id="paypal-button-container">
                            <?php if (isset($_POST['paypal_order_id']) && $_POST['paypal_order_id'] != ''): ?>
                            <div class="paypal-success">
                                <h4>¡Pago completado con éxito!</h4>
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
            
            // Actualizar también el monto en efectivo
            document.getElementById('efectivo-total').textContent = `$${costoTotalServicio.toFixed(2)} MXN`;
        }

        // Función para calcular la distancia (simulada)
        function calcularDistancia() {
            const origen = document.getElementById('ubicacion_origen').value;
            const destino = document.getElementById('ubicacion_destino').value;
            
            if (!origen || !destino) {
                return; // No calcular si falta alguna ubicación
            }
            
            // Mostrar mensaje de cálculo
            document.getElementById('distancia').value = "Calculando...";
            document.getElementById('costo').value = "Calculando...";
            
            // En una implementación real, usarías la API de Google Maps
            // Esta es una simulación para el ejemplo
            setTimeout(() => {
                const distanciaKm = Math.random() * 50 + 5; // Entre 5 y 55 km
                const costoPorKilometro = 80; // 80 pesos por kilómetro
                costoTotalServicio = distanciaKm * costoPorKilometro;
                
                // Mostrar la distancia y el costo
                document.getElementById('distancia').value = `${distanciaKm.toFixed(2)} km`;
                document.getElementById('costo').value = `${costoTotalServicio.toFixed(2)} MXN`;
                
                // Actualizar la sección de pago
                actualizarInfoPago();
                
                // También actualizamos la información del usuario
                actualizarInfoUsuario();
            }, 1000);
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
            
            // Guardar el método seleccionado en el campo oculto
            document.getElementById('metodo_pago_seleccionado').value = method;
            
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
            // Botones de ubicación
            document.getElementById('obtenerUbicacionOrigen').addEventListener('click', function() {
                obtenerUbicacionActual(false); // Origen
            });
            
            document.getElementById('obtenerUbicacionDestino').addEventListener('click', function() {
                obtenerUbicacionActual(true); // Destino
            });
            
            // Calcular distancia cuando cambian las ubicaciones
            document.getElementById('ubicacion_origen').addEventListener('change', calcularDistancia);
            document.getElementById('ubicacion_destino').addEventListener('change', calcularDistancia);
            
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
            
            // Inicializar costoTotalServicio si ya hay un valor en el formulario
            const costoInput = document.getElementById('costo');
            if (costoInput && costoInput.value) {
                costoTotalServicio = parseFloat(preg_replace('/[^0-9.]/', '', costoInput.value));
                actualizarInfoPago();
            }
        });
    </script>
</body>
</html> 