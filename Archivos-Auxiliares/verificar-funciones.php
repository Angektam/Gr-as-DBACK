<?php
/**
 * Script de verificación de funciones disponibles
 */

echo "<h1>🔍 Verificación de Funciones Disponibles</h1>";
echo "<style>
body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}
.container{max-width:1200px;margin:0 auto;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.success{color:#28a745;background:#d4edda;padding:10px;border-radius:5px;margin:10px 0;}
.error{color:#dc3545;background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0;}
.info{color:#17a2b8;background:#d1ecf1;padding:10px;border-radius:5px;margin:10px 0;}
.warning{color:#856404;background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0;}
.btn{background:#007bff;color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;margin:5px;text-decoration:none;display:inline-block;}
.btn:hover{background:#0056b3;}
table{border-collapse:collapse;width:100%;margin:10px 0;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background-color:#f2f2f2;}
</style>";

echo "<div class='container'>";

// Incluir configuración crítica
require_once 'config-solicitud-critico.php';

echo "<h2>📋 Lista de Funciones Requeridas</h2>";

$funcionesRequeridas = [
    'validarEmailMejorado' => 'Validación de email',
    'validarTelefonoMejorado' => 'Validación de teléfono',
    'validarArchivoCritico' => 'Validación de archivos',
    'sanitizarEntradaCritico' => 'Sanitización de entrada',
    'generarNombreArchivoCritico' => 'Generación de nombres únicos',
    'crearDirectorioCritico' => 'Creación de directorios',
    'obtenerInfoClienteCritico' => 'Información del cliente',
    'registrarActividadCritica' => 'Registro de actividades',
    'verificarTamañoPOSTCritico' => 'Verificación de tamaño POST',
    'manejarErrorPOST' => 'Manejo de errores POST',
    'mostrarConfiguracionCritica' => 'Mostrar configuración'
];

echo "<table>";
echo "<tr><th>Función</th><th>Descripción</th><th>Estado</th></tr>";

$todasDisponibles = true;
foreach ($funcionesRequeridas as $funcion => $descripcion) {
    $disponible = function_exists($funcion);
    $estado = $disponible ? "✅ Disponible" : "❌ No disponible";
    $clase = $disponible ? "success" : "error";
    
    if (!$disponible) {
        $todasDisponibles = false;
    }
    
    echo "<tr>";
    echo "<td><strong>$funcion</strong></td>";
    echo "<td>$descripcion</td>";
    echo "<td class='$clase'>$estado</td>";
    echo "</tr>";
}
echo "</table>";

if ($todasDisponibles) {
    echo "<div class='success'>";
    echo "<h3>🎉 ¡Todas las funciones están disponibles!</h3>";
    echo "<p>El sistema está listo para funcionar correctamente.</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ Algunas funciones no están disponibles</h3>";
    echo "<p>Hay funciones faltantes que necesitan ser agregadas al archivo de configuración.</p>";
    echo "</div>";
}

echo "<h2>🧪 Pruebas de Funciones</h2>";

echo "<div class='info'>";
echo "<h3>1. Prueba de Validación de Email:</h3>";
if (function_exists('validarEmailMejorado')) {
    $emailsPrueba = [
        'test@example.com' => true,
        'invalid-email' => false,
        'user@domain.co.uk' => true,
        '' => false
    ];
    
    echo "<ul>";
    foreach ($emailsPrueba as $email => $esperado) {
        $resultado = validarEmailMejorado($email);
        $correcto = ($resultado === $esperado) ? "✅" : "❌";
        echo "<li>$correcto Email: '$email' - Resultado: " . ($resultado ? 'Válido' : 'Inválido') . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ Función no disponible</p>";
}
echo "</div>";

echo "<div class='info'>";
echo "<h3>2. Prueba de Validación de Teléfono:</h3>";
if (function_exists('validarTelefonoMejorado')) {
    $telefonosPrueba = [
        '1234567890' => true,
        '+52 123 456 7890' => true,
        '123' => false,
        '' => false,
        '(123) 456-7890' => true
    ];
    
    echo "<ul>";
    foreach ($telefonosPrueba as $telefono => $esperado) {
        $resultado = validarTelefonoMejorado($telefono);
        $correcto = ($resultado === $esperado) ? "✅" : "❌";
        echo "<li>$correcto Teléfono: '$telefono' - Resultado: " . ($resultado ? 'Válido' : 'Inválido') . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ Función no disponible</p>";
}
echo "</div>";

echo "<div class='info'>";
echo "<h3>3. Prueba de Sanitización:</h3>";
if (function_exists('sanitizarEntradaCritico')) {
    $entradasPrueba = [
        'Texto normal' => 'Texto normal',
        '<script>alert("xss")</script>' => '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
        'Texto con "comillas"' => 'Texto con &quot;comillas&quot;',
        '' => ''
    ];
    
    echo "<ul>";
    foreach ($entradasPrueba as $entrada => $esperado) {
        $resultado = sanitizarEntradaCritico($entrada);
        $correcto = ($resultado === $esperado) ? "✅" : "❌";
        echo "<li>$correcto Entrada: '$entrada' - Resultado: '$resultado'</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ Función no disponible</p>";
}
echo "</div>";

echo "<h2>🔧 Configuración PHP</h2>";
mostrarConfiguracionCritica();

echo "<h2>🚀 Enlaces de Prueba</h2>";
echo "<p><a href='solicitud.php' target='_blank' class='btn'>📝 Probar Formulario</a></p>";
echo "<p><a href='solicitud.php?debug=1' target='_blank' class='btn'>🔍 Ver Configuración</a></p>";

echo "<h2>✅ Estado del Sistema</h2>";
if ($todasDisponibles) {
    echo "<div class='success'>";
    echo "<p><strong>🎉 ¡Sistema completamente funcional!</strong></p>";
    echo "<p>Todas las funciones están disponibles y el formulario debería funcionar correctamente.</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<p><strong>⚠️ Sistema necesita corrección</strong></p>";
    echo "<p>Algunas funciones no están disponibles. Revisa el archivo de configuración.</p>";
    echo "</div>";
}

echo "</div>";
?>
