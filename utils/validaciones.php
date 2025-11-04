<?php
/**
 * Sistema de Validaciones - DBACK
 * Validaciones del lado del servidor (PHP)
 */

class Validador {
    private $errores = [];
    
    // Patrones de validación
    const PATRON_EMAIL = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    const PATRON_TELEFONO = '/^[\d\s\-\+\(\)]{10,15}$/';
    const PATRON_TELEFONO_MX = '/^[\d\s\-]{10}$|^[\+]?52[\d\s\-]{10}$/';
    const PATRON_NOMBRE = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/';
    const PATRON_ALFANUMERICO = '/^[a-zA-Z0-9\s\-_]{1,100}$/';
    const PATRON_SOLO_NUMEROS = '/^\d+$/';
    const PATRON_DECIMAL = '/^\d+(\.\d{1,2})?$/';
    const PATRON_COORDENADAS = '/^-?\d+\.?\d*,\s*-?\d+\.?\d*$/';
    
    /**
     * Validar email
     */
    public function validarEmail($email, $campo = 'email', $requerido = true) {
        if ($requerido && empty($email)) {
            $this->agregarError($campo, 'El email es requerido');
            return false;
        }
        
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->agregarError($campo, 'El formato del email no es válido');
                return false;
            }
            if (strlen($email) > 100) {
                $this->agregarError($campo, 'El email no puede tener más de 100 caracteres');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validar teléfono
     */
    public function validarTelefono($telefono, $campo = 'telefono', $requerido = true) {
        if ($requerido && empty($telefono)) {
            $this->agregarError($campo, 'El teléfono es requerido');
            return false;
        }
        
        if (!empty($telefono)) {
            $telefonoLimpio = preg_replace('/\s/', '', $telefono);
            if (!preg_match(self::PATRON_TELEFONO_MX, $telefonoLimpio)) {
                $this->agregarError($campo, 'El formato del teléfono no es válido');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validar nombre
     */
    public function validarNombre($nombre, $campo = 'nombre', $minLength = 2, $maxLength = 50, $requerido = true) {
        if ($requerido && empty($nombre)) {
            $this->agregarError($campo, 'El nombre es requerido');
            return false;
        }
        
        if (!empty($nombre)) {
            $nombre = trim($nombre);
            if (strlen($nombre) < $minLength) {
                $this->agregarError($campo, "El nombre debe tener al menos {$minLength} caracteres");
                return false;
            }
            if (strlen($nombre) > $maxLength) {
                $this->agregarError($campo, "El nombre no puede tener más de {$maxLength} caracteres");
                return false;
            }
            if (!preg_match(self::PATRON_NOMBRE, $nombre)) {
                $this->agregarError($campo, 'El nombre solo puede contener letras y espacios');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validar campo requerido
     */
    public function requerido($valor, $campo, $mensaje = null) {
        if (empty($valor) || (is_string($valor) && trim($valor) === '')) {
            $this->agregarError($campo, $mensaje ?: "{$campo} es requerido");
            return false;
        }
        return true;
    }
    
    /**
     * Validar longitud
     */
    public function longitud($valor, $campo, $min, $max) {
        $longitud = strlen($valor);
        if ($longitud < $min || $longitud > $max) {
            $this->agregarError($campo, "Debe tener entre {$min} y {$max} caracteres");
            return false;
        }
        return true;
    }
    
    /**
     * Validar número
     */
    public function validarNumero($valor, $campo, $min = null, $max = null) {
        if (!is_numeric($valor)) {
            $this->agregarError($campo, 'Debe ser un número válido');
            return false;
        }
        
        $num = floatval($valor);
        if ($min !== null && $num < $min) {
            $this->agregarError($campo, "El valor mínimo es {$min}");
            return false;
        }
        if ($max !== null && $num > $max) {
            $this->agregarError($campo, "El valor máximo es {$max}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar archivo subido
     */
    public function validarArchivo($archivo, $campo, $maxSizeMB = 5, $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf']) {
        if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return true; // Archivo opcional
        }
        
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $this->agregarError($campo, 'Error al subir el archivo');
            return false;
        }
        
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;
        if ($archivo['size'] > $maxSizeBytes) {
            $this->agregarError($campo, "El archivo no puede ser mayor a {$maxSizeMB}MB");
            return false;
        }
        
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $tiposPermitidos)) {
            $this->agregarError($campo, 'Tipo de archivo no permitido');
            return false;
        }
        
        // Validar tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);
        
        $mimesPermitidos = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf'
        ];
        
        if (isset($mimesPermitidos[$extension]) && $mimeType !== $mimesPermitidos[$extension]) {
            $this->agregarError($campo, 'El tipo de archivo no coincide con la extensión');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar coordenadas
     */
    public function validarCoordenadas($coordenadas, $campo = 'coordenadas') {
        if (empty($coordenadas)) {
            return true; // Opcional
        }
        if (!preg_match(self::PATRON_COORDENADAS, $coordenadas)) {
            $this->agregarError($campo, 'Formato de coordenadas inválido');
            return false;
        }
        return true;
    }
    
    /**
     * Sanitizar entrada
     */
    public static function sanitizar($dato, $tipo = 'string') {
        if (is_null($dato)) {
            return null;
        }
        
        switch ($tipo) {
            case 'string':
                return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
            case 'int':
                return intval($dato);
            case 'float':
                return floatval($dato);
            case 'email':
                return filter_var(trim($dato), FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var(trim($dato), FILTER_SANITIZE_URL);
            default:
                return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Agregar error
     */
    public function agregarError($campo, $mensaje) {
        if (!isset($this->errores[$campo])) {
            $this->errores[$campo] = [];
        }
        $this->errores[$campo][] = $mensaje;
    }
    
    /**
     * Limpiar errores
     */
    public function limpiarErrores() {
        $this->errores = [];
    }
    
    /**
     * Obtener errores
     */
    public function obtenerErrores() {
        return $this->errores;
    }
    
    /**
     * Verificar si hay errores
     */
    public function tieneErrores() {
        return count($this->errores) > 0;
    }
    
    /**
     * Obtener primer error de un campo
     */
    public function obtenerError($campo) {
        return isset($this->errores[$campo]) ? $this->errores[$campo][0] : null;
    }
    
    /**
     * Obtener todos los errores como string
     */
    public function obtenerErroresString($separador = '<br>') {
        $mensajes = [];
        foreach ($this->errores as $campo => $errores) {
            $mensajes = array_merge($mensajes, $errores);
        }
        return implode($separador, $mensajes);
    }
}

/**
 * Función helper para validar token CSRF
 */
function validarCSRF($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Función helper para generar token CSRF
 */
function generarCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Función helper para limpiar datos de entrada
 */
function limpiarDatos($conn, $dato) {
    if (is_null($dato)) {
        return null;
    }
    return $conn->real_escape_string(trim($dato));
}

/**
 * Función helper para validar y sanitizar POST
 */
function obtenerPOST($clave, $tipo = 'string', $requerido = false) {
    if (!isset($_POST[$clave])) {
        if ($requerido) {
            throw new Exception("Campo requerido: {$clave}");
        }
        return null;
    }
    
    return Validador::sanitizar($_POST[$clave], $tipo);
}

/**
 * Función helper para validar y sanitizar GET
 */
function obtenerGET($clave, $tipo = 'string', $requerido = false) {
    if (!isset($_GET[$clave])) {
        if ($requerido) {
            throw new Exception("Campo requerido: {$clave}");
        }
        return null;
    }
    
    return Validador::sanitizar($_GET[$clave], $tipo);
}

?>

