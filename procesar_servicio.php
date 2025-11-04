<?php
// Configuración de la base de datos

// Configuración de conexión
$servername = "localhost";
$username = "root";
$password = "5211";  // Cambia esto por tu contraseña real
$dbname = "dback";



// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");

// Procesar datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar datos
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $telefono = htmlspecialchars(trim($_POST['telefono']));
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : NULL;
    $ubicacion_origen = htmlspecialchars(trim($_POST['ubicacion_origen']));
    $ubicacion_destino = htmlspecialchars(trim($_POST['ubicacion_destino']));
    $vehiculo = htmlspecialchars(trim($_POST['vehiculo']));
    $marca = htmlspecialchars(trim($_POST['marca']));
    $modelo = htmlspecialchars(trim($_POST['modelo']));
    $tipo_servicio = htmlspecialchars(trim($_POST['tipo_servicio']));
    $descripcion = htmlspecialchars(trim($_POST['descripcion']));
    $urgencia = htmlspecialchars(trim($_POST['urgencia']));
    $distancia = htmlspecialchars(trim($_POST['distancia']));
    $costo = htmlspecialchars(trim($_POST['costo']));
    $metodo_pago = htmlspecialchars(trim($_POST['metodo_pago_seleccionado']));
    
    // Procesar datos de PayPal si aplica
    $paypal_order_id = isset($_POST['paypal_order_id']) ? htmlspecialchars(trim($_POST['paypal_order_id'])) : NULL;
    $paypal_status = isset($_POST['paypal_status']) ? htmlspecialchars(trim($_POST['paypal_status'])) : NULL;
    $paypal_email = isset($_POST['paypal_email']) ? htmlspecialchars(trim($_POST['paypal_email'])) : NULL;
    $paypal_name = isset($_POST['paypal_name']) ? htmlspecialchars(trim($_POST['paypal_name'])) : NULL;
    
    // Procesar archivo de foto si se subió
    $foto_nombre = NULL;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_nombre = 'vehiculo_' . time() . '.' . $extension;
        $directorio_destino = 'uploads/vehiculos/';
        
        // Crear directorio si no existe
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }
        
        $ruta_destino = $directorio_destino . $foto_nombre;
        
        // Mover el archivo subido
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            $foto_nombre = NULL; // Si falla, no guardamos la referencia
        }
    }
    
    // Calcular depósito y pago restante
    $costo_float = (float) str_replace(['MXN', '$', ','], '', $costo);
    $deposito = $metodo_pago === 'paypal' ? $costo_float * 0.2 : 0;
    $pago_restante = $costo_float - $deposito;
    
    // Insertar datos en la base de datos
    $sql = "INSERT INTO solicitudes_servicio (
        nombre_cliente, 
        telefono, 
        email, 
        ubicacion_origen, 
        ubicacion_destino, 
        tipo_vehiculo, 
        marca_vehiculo, 
        modelo_vehiculo, 
        foto_vehiculo, 
        tipo_servicio, 
        descripcion_problema, 
        nivel_urgencia, 
        distancia_estimada, 
        costo_estimado, 
        metodo_pago, 
        deposito, 
        pago_restante, 
        paypal_order_id, 
        paypal_status, 
        paypal_email, 
        paypal_name, 
        fecha_solicitud, 
        estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pendiente')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssssdssdssss", 
        $nombre, 
        $telefono, 
        $email, 
        $ubicacion_origen, 
        $ubicacion_destino, 
        $vehiculo, 
        $marca, 
        $modelo, 
        $foto_nombre, 
        $tipo_servicio, 
        $descripcion, 
        $urgencia, 
        $distancia, 
        $costo_float, 
        $metodo_pago, 
        $deposito, 
        $pago_restante, 
        $paypal_order_id, 
        $paypal_status, 
        $paypal_email, 
        $paypal_name
    );
    
    if ($stmt->execute()) {
        // Enviar correo de confirmación
        enviarCorreoConfirmacion($nombre, $email, $telefono, $tipo_servicio, $costo_float, $deposito);
        
        // Redirigir a página de éxito
        header("Location: solicitud_exitosa.html");
        exit();
    } else {
        // Redirigir a página de error
        header("Location: error_solicitud.html");
        exit();
    }
    
  //  $stmt->close();
}

$conn->close();

// Función para enviar correo de confirmación
function enviarCorreoConfirmacion($nombre, $email, $telefono, $tipo_servicio, $costo, $deposito) {
    if ($email) {
        $asunto = "Confirmación de solicitud de servicio - Grúas DBACK";
        $mensaje = "
        <html>
        <head>
            <title>Confirmación de solicitud</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 0.8em; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Grúas DBACK</h2>
                <p>Confirmación de solicitud de servicio</p>
            </div>
            
            <div class='content'>
                <p>Hola $nombre,</p>
                <p>Hemos recibido tu solicitud de servicio de <strong>$tipo_servicio</strong>.</p>
                <p><strong>Detalles de tu solicitud:</strong></p>
                <ul>
                    <li>Teléfono de contacto: $telefono</li>
                    <li>Tipo de servicio: $tipo_servicio</li>
                    <li>Costo estimado: $" . number_format($costo, 2) . " MXN</li>
                    <li>Depósito: $" . number_format($deposito, 2) . " MXN</li>
                </ul>
                <p>Nos pondremos en contacto contigo a la brevedad para confirmar los detalles.</p>
                <p>Para cualquier duda, puedes responder a este correo o llamarnos al 668-825-3351.</p>
            </div>
            
            <div class='footer'>
                <p>© " . date('Y') . " Grúas DBACK. Todos los derechos reservados.</p>
                <p><a href='https://www.gruasdback.com'>www.gruasdback.com</a></p>
            </div>
        </body>
        </html>
        ";
        
        // Cabeceras para correo HTML
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Grúas DBACK <no-reply@gruasdback.com>\r\n";
        $headers .= "Reply-To: contacto@gruasdback.com\r\n";
        
        // Enviar el correo
        mail($email, $asunto, $mensaje, $headers);
    }
}
?>