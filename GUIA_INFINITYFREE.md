# 🚀 Guía Paso a Paso: Subir DBACK a InfinityFree

## 📋 Requisitos Previos

- ✅ Cuenta en InfinityFree (gratis en https://www.infinityfree.com/)
- ✅ Cliente FTP (FileZilla - gratuito) o acceso al panel de control
- ✅ Acceso a la base de datos MySQL

---

## PASO 1: Crear Cuenta en InfinityFree

1. **Ve a**: https://www.infinityfree.com/
2. **Haz clic en** "Sign Up" (Registrarse)
3. **Completa el formulario**:
   - Email
   - Contraseña (guárdala segura)
   - Usuario
4. **Verifica tu email** (revisa tu bandeja de entrada)
5. **Inicia sesión** en tu cuenta

---

## PASO 2: Crear un Sitio Web

1. **En el panel de InfinityFree**, haz clic en **"Add Website"**
2. **Elige un subdominio**:
   - Ejemplo: `dback-gruas.infinityfreeapp.com`
   - O usa tu propio dominio si lo tienes
3. **Haz clic en "Submit"** (Enviar)
4. **Espera 5-10 minutos** a que se active el sitio
5. **Anota tus credenciales**:
   - **FTP Host**: `ftpupload.net`
   - **FTP Usuario**: (te lo darán en el panel)
   - **FTP Contraseña**: (la que configuraste)
   - **Puerto FTP**: `21`

---

## PASO 3: Crear la Base de Datos MySQL

1. **En el panel de InfinityFree**, ve a **"MySQL Databases"**
2. **Crea una nueva base de datos**:
   - Nombre: `dback` (o el que prefieras)
   - Haz clic en **"Create"**
3. **Anota los datos**:
   - **Nombre de BD**: `epiz_xxxxx_dback` (InfinityFree agrega prefijo)
   - **Usuario**: `epiz_xxxxx` (tu usuario de BD)
   - **Contraseña**: (la que configuraste)
   - **Host**: `sqlxxx.infinityfree.com` (tu servidor MySQL)
   - **Puerto**: `3306`

**⚠️ IMPORTANTE**: Guarda estos datos, los necesitarás para configurar `config.php`

---

## PASO 4: Importar la Base de Datos

### Opción A: Por phpMyAdmin (Recomendado)

1. **En el panel**, haz clic en **"phpMyAdmin"**
2. **Selecciona tu base de datos** (ej: `epiz_xxxxx_dback`)
3. **Haz clic en la pestaña "Importar"**
4. **Selecciona archivo**: `database/DBACKBD.sql`
5. **Haz clic en "Continuar"**
6. **Espera** a que termine la importación

### Opción B: Por línea de comandos (si tienes acceso SSH)

```bash
mysql -h sqlxxx.infinityfree.com -u epiz_xxxxx -p epiz_xxxxx_dback < DBACKBD.sql
```

---

## PASO 5: Descargar FileZilla (Cliente FTP)

1. **Descarga FileZilla**: https://filezilla-project.org/download.php?type=client
2. **Instálalo** en tu computadora
3. **Ábrelo**

---

## PASO 6: Conectar por FTP a InfinityFree

1. **En FileZilla**, en la parte superior:
   - **Host**: `ftpupload.net`
   - **Usuario**: (tu usuario FTP de InfinityFree)
   - **Contraseña**: (tu contraseña FTP)
   - **Puerto**: `21`
   - Haz clic en **"Conexión rápida"**

2. **Si conecta exitosamente**, verás:
   - **Lado izquierdo**: Archivos de tu computadora
   - **Lado derecho**: Archivos del servidor (InfinityFree)

---

## PASO 7: Subir Archivos al Servidor

1. **En el lado derecho** (servidor), navega a:
   - `htdocs/` (esta es la carpeta pública)

2. **En el lado izquierdo** (tu PC), navega a tu proyecto:
   - `C:\Users\angek\OneDrive\Documentos\UNIVERSIDAD\Diaana\DBACK-main\`

3. **Selecciona TODOS los archivos** (Ctrl+A) excepto:
   - ❌ `config.php` (lo crearemos en el servidor)
   - ❌ `.git/` (carpeta de Git)
   - ❌ `.gitignore`
   - ❌ `uploads/` (si tiene contenido sensible)

4. **Arrastra y suelta** todos los archivos desde el lado izquierdo al lado derecho

5. **Espera** a que termine la subida (puede tardar varios minutos)

---

## PASO 8: Crear el archivo config.php en el servidor

### Opción A: Crear directamente en el servidor

1. **En el panel de InfinityFree**, ve a **"File Manager"**
2. **Navega a** `htdocs/`
3. **Crea un nuevo archivo** llamado `config.php`
4. **Edítalo** y pega este contenido (ajusta los datos):

```php
<?php
/**
 * Configuración para InfinityFree
 */

// Configuración de la base de datos de InfinityFree
define('DB_HOST', 'sqlxxx.infinityfree.com'); // Tu host MySQL
define('DB_USER', 'epiz_xxxxx'); // Tu usuario de BD
define('DB_PASS', 'tu_contraseña'); // Tu contraseña de BD
define('DB_NAME', 'epiz_xxxxx_dback'); // Tu nombre de BD completo

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Grúas DBACK');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'production');

// URL base de la aplicación
define('APP_URL', 'https://dback-gruas.infinityfreeapp.com'); // Tu URL
define('APP_PATH', '/');

// Configuración de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // HTTPS requerido
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);

// Configuración de errores para producción
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Configuración de zona horaria
date_default_timezone_set('America/Mazatlan');

// Configuración de archivos subidos
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

/**
 * Función para conectar a la base de datos
 */
function get_database_connection() {
    static $connection = null;
    
    if ($connection === null) {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($connection->connect_error) {
            die('Error de conexión: ' . $connection->connect_error);
        }
        
        $connection->set_charset('utf8mb4');
    }
    
    return $connection;
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
```

5. **Guarda** el archivo

### Opción B: Crear localmente y subir

1. **Crea** `config-infinityfree.php` en tu proyecto local
2. **Cópialo** como `config.php` en el servidor vía FTP

---

## PASO 9: Configurar Permisos de Carpetas

1. **En el panel de InfinityFree**, ve a **"File Manager"**
2. **Navega a** la carpeta `uploads/`
3. **Haz clic derecho** → **"Change Permissions"**
4. **Establece permisos**: `755` o `777`
5. **Repite** para otras carpetas que necesiten escritura

---

## PASO 10: Probar el Sitio

1. **Abre tu navegador**
2. **Ve a**: `https://tu-subdominio.infinityfreeapp.com`
3. **Verifica**:
   - ✅ La página principal carga
   - ✅ El login funciona
   - ✅ Puedes crear solicitudes
   - ✅ Las imágenes se ven correctamente

---

## PASO 11: Configurar SSL (HTTPS)

InfinityFree incluye SSL gratis:

1. **En el panel**, ve a **"SSL"** o **"Security"**
2. **Habilita SSL** (Let's Encrypt)
3. **Espera** unos minutos a que se active
4. **Verifica** que tu sitio cargue con `https://`

---

## 🔧 Solución de Problemas Comunes

### Error: "No se puede conectar a la base de datos"
- ✅ Verifica que el host, usuario, contraseña y nombre de BD sean correctos
- ✅ Asegúrate de usar el nombre completo de la BD (con prefijo `epiz_xxxxx_`)
- ✅ Verifica que la BD esté creada y activa

### Error: "404 Not Found"
- ✅ Verifica que los archivos estén en `htdocs/`
- ✅ Verifica que `index.html` esté en la raíz de `htdocs/`

### Error: "Permission denied" al subir archivos
- ✅ Verifica los permisos de la carpeta `uploads/` (debe ser 755 o 777)
- ✅ Verifica que el usuario FTP tenga permisos de escritura

### Las imágenes no se ven
- ✅ Verifica las rutas de las imágenes
- ✅ Verifica que la carpeta `Elementos/` esté subida
- ✅ Verifica los permisos de la carpeta

### Error de sesión
- ✅ Verifica que `session_start()` esté en los archivos correctos
- ✅ Verifica que las cookies estén habilitadas en el navegador

---

## 📝 Checklist Final

- [ ] Cuenta creada en InfinityFree
- [ ] Sitio web creado
- [ ] Base de datos MySQL creada
- [ ] Base de datos importada (DBACKBD.sql)
- [ ] Archivos subidos por FTP
- [ ] `config.php` creado con credenciales correctas
- [ ] Permisos de `uploads/` configurados (755)
- [ ] SSL/HTTPS habilitado
- [ ] Sitio probado y funcionando

---

## 🎉 ¡Listo!

Tu sitio debería estar funcionando en:
**https://tu-subdominio.infinityfreeapp.com**

---

## 📞 Soporte

Si tienes problemas:
- **Panel de InfinityFree**: https://panel.infinityfree.com/
- **Documentación**: https://forum.infinityfree.com/
- **Foros de ayuda**: https://forum.infinityfree.com/

---

**Nota**: InfinityFree es gratuito pero tiene algunas limitaciones. Para producción profesional, considera migrar a un hosting de pago como Hostinger después de probar.

