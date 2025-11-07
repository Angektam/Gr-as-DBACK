<?php
// Incluir sistema de validaciones
require_once 'utils/validaciones.php';
require_once 'conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión: " . ($conn->connect_error ?? "No se pudo establecer conexión"));
}

// Generar token CSRF
$csrf_token = generarCSRF();

$connectionMessage = "";
$connectionStatus = true;
$userErrorMessage = "";
$passwordErrorMessage = "";
$lastUsername = "";

// Protección contra fuerza bruta
$intentos_maximos = 5;
$tiempo_bloqueo = 300; // 5 minutos
$ip_cliente = $_SERVER['REMOTE_ADDR'] ?? '';

// Verificar si la IP está bloqueada
$intentos_key = 'login_intentos_' . md5($ip_cliente);
$ultimo_intento_key = 'login_ultimo_intento_' . md5($ip_cliente);

if (isset($_SESSION[$intentos_key]) && $_SESSION[$intentos_key] >= $intentos_maximos) {
    $tiempo_espera = isset($_SESSION[$ultimo_intento_key]) ? time() - $_SESSION[$ultimo_intento_key] : 0;
    if ($tiempo_espera < $tiempo_bloqueo) {
        $tiempo_restante = $tiempo_bloqueo - $tiempo_espera;
        $minutos = floor($tiempo_restante / 60);
        $segundos = $tiempo_restante % 60;
        $connectionMessage = "<p style='color: red; text-align: center;'>Demasiados intentos fallidos. Por favor, espera {$minutos} minutos y {$segundos} segundos antes de intentar nuevamente.</p>";
        $connectionStatus = false;
    } else {
        // Resetear intentos después del tiempo de bloqueo
        unset($_SESSION[$intentos_key]);
        unset($_SESSION[$ultimo_intento_key]);
    }
}

if ($connectionStatus && isset($_POST['Login'])) {
    // Validar token CSRF
    $token_recibido = $_POST['csrf_token'] ?? '';
    if (!validarCSRF($token_recibido)) {
        $connectionMessage = "<p style='color: red; text-align: center;'>Error de seguridad: token inválido. Por favor, recarga la página.</p>";
        $connectionStatus = false;
    } else {
        // Crear instancia del validador
        $validador = new Validador();
        
        // Validar y sanitizar datos
        $usuario = Validador::sanitizar($_POST['IngresarUsuario'] ?? '', 'string');
        $clave = $_POST['IngresarContraseña'] ?? '';
        $lastUsername = $usuario;
        
        // Validaciones
        $validador->requerido($usuario, 'usuario', 'El usuario es requerido');
        $validador->longitud($usuario, 'usuario', 3, 50);
        $validador->requerido($clave, 'contraseña', 'La contraseña es requerida');
        $validador->longitud($clave, 'contraseña', 4, 100);
        
        if (!$validador->tieneErrores()) {
            // Verificar usuario en la tabla `usuarios`
            $stmt = $conn->prepare("SELECT ID_Usuario, Usuario, ROL, Contraseña FROM usuarios WHERE Usuario = ?");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $userResult = $stmt->get_result();

            if ($userResult->num_rows > 0) {
                $userData = $userResult->fetch_assoc();
                
                // Verificar contraseña (mejorar: usar password_verify si las contraseñas están hasheadas)
                // Por ahora, comparación directa (cambiar a hash en producción)
                if ($userData['Contraseña'] === $clave) {
                    // Login exitoso - resetear intentos
                    unset($_SESSION[$intentos_key]);
                    unset($_SESSION[$ultimo_intento_key]);
                    
                    // Regenerar ID de sesión por seguridad
                    session_regenerate_id(true);
                    
                    $_SESSION['usuario_id'] = $userData['ID_Usuario'];
                    $_SESSION['usuario_nombre'] = $userData['Usuario'];
                    $_SESSION['usuario_cargo'] = $userData['ROL'];
                    $_SESSION['usuario_usuario'] = $userData['Usuario'];
                    $_SESSION['login_time'] = time();

                    // Redireccionar a MenuAdmin
                    header("Location: admin/MenuAdmin.PHP");
                    exit();
                } else {
                    // Contraseña incorrecta - incrementar intentos
                    $intentos = isset($_SESSION[$intentos_key]) ? $_SESSION[$intentos_key] + 1 : 1;
                    $_SESSION[$intentos_key] = $intentos;
                    $_SESSION[$ultimo_intento_key] = time();
                    
                    $passwordErrorMessage = "Contraseña incorrecta. Intentos restantes: " . ($intentos_maximos - $intentos);
                }
            } else {
                // Usuario no existe - incrementar intentos (no revelar si el usuario existe por seguridad)
                $intentos = isset($_SESSION[$intentos_key]) ? $_SESSION[$intentos_key] + 1 : 1;
                $_SESSION[$intentos_key] = $intentos;
                $_SESSION[$ultimo_intento_key] = time();
                
                // No revelar si el usuario existe o no por seguridad
                $userErrorMessage = "Usuario o contraseña incorrectos. Intentos restantes: " . ($intentos_maximos - $intentos);
            }
            $stmt->close();
        } else {
            $errores = $validador->obtenerErrores();
            if (isset($errores['usuario'])) {
                $userErrorMessage = $errores['usuario'][0];
            }
            if (isset($errores['contraseña'])) {
                $passwordErrorMessage = $errores['contraseña'][0];
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grúas DBACK - Login</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 350px;
            padding: 30px;
            text-align: center;
        }
        
        .login-header {
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 50px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        h1 {
            color: #2c3e50;
            font-size: 24px;
        }
        
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
        
        input {
            width: 100%;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 14px;
        }
        
        button {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 12px 20px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #1a252f;
        }
        
        button:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        
        button i {
            margin-right: 8px;
        }
        
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            text-align: left;
        }
        
        .connection-message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-truck-pickup"></i>
            </div>
            <h1>Grúas D'BACK</h1>
        </div>
        
        <?php if (!empty($connectionMessage)): ?>
            <div class="connection-message <?php echo $connectionStatus ? 'success' : 'error'; ?>">
                <?php echo $connectionMessage; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <div class="input-group">
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                </div>
                <input type="text" name="IngresarUsuario" placeholder="Usuario" value="<?php echo htmlspecialchars($lastUsername); ?>" required>
                <?php if (!empty($userErrorMessage)): ?>
                    <div class="error-message"><?php echo $userErrorMessage; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="input-group">
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <input type="password" name="IngresarContraseña" placeholder="Contraseña" required>
                <?php if (!empty($passwordErrorMessage)): ?>
                    <div class="error-message"><?php echo $passwordErrorMessage; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="forgot-password">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>
            
            <button type="submit" name="Login" <?php echo $connectionStatus ? '' : 'disabled'; ?>>
                <i class="fas fa-sign-in-alt"></i>
                Iniciar Sesión
            </button>
        </form>
    </div>
</body>
</html>