# 🌐 Guía de Hosting para DBACK - Sistema de Grúas

## 📋 Requisitos del Sistema

Tu proyecto necesita:
- **PHP 7.4 o superior** (recomendado PHP 8.0+)
- **MySQL 5.7 o superior**
- **Apache/Nginx** con soporte .htaccess
- **Espacio en disco**: ~100-500 MB (según archivos subidos)
- **Base de datos MySQL**: 1 base de datos
- **Extensiones PHP**: mysqli, json, session, GD (para imágenes)

---

## 🆓 OPCIONES GRATUITAS (Para pruebas y desarrollo)

### 1. **000WebHost** ⭐ Recomendado para empezar
- **URL**: https://www.000webhost.com/
- **Precio**: Gratis (con limitaciones)
- **Características**:
  - ✅ PHP 8.0+ soportado
  - ✅ MySQL gratuito
  - ✅ Sin anuncios en tu sitio
  - ✅ Panel de control fácil (cPanel)
  - ✅ 300 MB de espacio
  - ✅ 3 GB de ancho de banda
  - ⚠️ Subdominio: `tusitio.000webhostapp.com`
  - ⚠️ Limitado para sitios pequeños

**Ventajas**: Muy fácil de usar, sin anuncios
**Desventajas**: Limitado en recursos, puede ser lento

---

### 2. **InfinityFree** ⭐ Mejor opción gratuita
- **URL**: https://www.infinityfree.com/
- **Precio**: Gratis (ilimitado)
- **Características**:
  - ✅ PHP 8.1 soportado
  - ✅ MySQL ilimitado
  - ✅ Espacio ilimitado
  - ✅ Ancho de banda ilimitado
  - ✅ Panel de control (iPanel)
  - ✅ Dominio personalizado soportado
  - ⚠️ Subdominio: `tusitio.infinityfreeapp.com`

**Ventajas**: Recursos ilimitados, muy generoso
**Desventajas**: Puede tener restricciones de CPU

---

### 3. **AwardSpace**
- **URL**: https://www.awardspace.com/
- **Precio**: Gratis
- **Características**:
  - ✅ PHP 8.0+
  - ✅ MySQL
  - ✅ 1 GB de espacio
  - ✅ 5 GB de ancho de banda
  - ⚠️ Subdominio: `tusitio.webs.com`

**Ventajas**: Estable, sin anuncios
**Desventajas**: Limitado en recursos

---

## 💰 OPCIONES DE PAGO (Recomendado para producción)

### 1. **Hostinger** ⭐⭐⭐ MEJOR RELACIÓN CALIDAD-PRECIO
- **URL**: https://www.hostinger.com/
- **Precio**: Desde $2.99/mes (promoción)
- **Plan recomendado**: "Premium" ($3.99/mes)
- **Características**:
  - ✅ PHP 8.0+ (múltiples versiones)
  - ✅ MySQL ilimitado
  - ✅ 100 GB de espacio SSD
  - ✅ Ancho de banda ilimitado
  - ✅ Dominio gratis el primer año
  - ✅ SSL gratuito (Let's Encrypt)
  - ✅ Email profesional incluido
  - ✅ Panel hPanel (muy fácil)
  - ✅ Soporte 24/7 en español
  - ✅ Backup automático semanal

**Ventajas**: Excelente rendimiento, muy económico, fácil de usar
**Desventajas**: Precio puede subir después del primer año

**👉 RECOMENDACIÓN: Ideal para tu proyecto**

---

### 2. **IONOS (1&1)**
- **URL**: https://www.ionos.com/
- **Precio**: Desde $1/mes (primer mes), luego $6/mes
- **Características**:
  - ✅ PHP 8.0+
  - ✅ 10 bases de datos MySQL
  - ✅ 100 GB de espacio
  - ✅ Dominio gratis
  - ✅ SSL gratuito
  - ✅ Email profesional

**Ventajas**: Precio bajo inicial, confiable
**Desventajas**: Precio aumenta después del primer mes

---

### 3. **HostGator** (Para México)
- **URL**: https://www.hostgator.com.mx/
- **Precio**: Desde $99 MXN/mes (~$5.50 USD)
- **Características**:
  - ✅ PHP 8.0+
  - ✅ MySQL ilimitado
  - ✅ Espacio ilimitado
  - ✅ Ancho de banda ilimitado
  - ✅ Dominio gratis
  - ✅ SSL gratuito
  - ✅ Soporte en español
  - ✅ cPanel profesional

**Ventajas**: Muy popular en México, buen soporte
**Desventajas**: Precio un poco más alto

---

### 4. **AccuWeb Hosting**
- **URL**: https://www.accuwebhosting.com/
- **Precio**: Desde $3.99/mes
- **Características**:
  - ✅ PHP 7.4 - 8.1
  - ✅ MySQL ilimitado
  - ✅ 50 GB SSD
  - ✅ SSL gratuito
  - ✅ Backup diario

**Ventajas**: Buen rendimiento, precios estables
**Desventajas**: Menos conocido

---

## 🚀 RECOMENDACIÓN FINAL

### Para empezar (Pruebas/Desarrollo):
**👉 InfinityFree** - Gratis, recursos ilimitados, fácil de usar

### Para producción (Negocio real):
**👉 Hostinger Premium** - Mejor relación calidad-precio, excelente soporte

---

## 📝 Pasos para Montar tu Sitio

### Paso 1: Crear cuenta en el hosting elegido
1. Ve al sitio web del hosting
2. Selecciona un plan
3. Elige un dominio (o usa el subdominio gratuito)
4. Completa el registro

### Paso 2: Configurar la base de datos
1. Accede al panel de control (cPanel/hPanel)
2. Ve a "MySQL Databases" o "Bases de datos"
3. Crea una nueva base de datos (ej: `dback`)
4. Crea un usuario y contraseña
5. Asigna el usuario a la base de datos
6. Importa el archivo `database/DBACKBD.sql`

### Paso 3: Subir archivos
**Opción A: Por FTP**
- Descarga FileZilla (gratis)
- Usa las credenciales FTP del hosting
- Sube todos los archivos a la carpeta `public_html` o `www`

**Opción B: Por Git (si el hosting lo permite)**
- Conecta tu repositorio de GitHub
- Clona en el servidor

### Paso 4: Configurar archivos
1. Edita `config.php` con las credenciales de la base de datos:
```php
define('DB_HOST', 'localhost'); // o la IP del servidor
define('DB_USER', 'tu_usuario_db');
define('DB_PASS', 'tu_contraseña_db');
define('DB_NAME', 'dback');
```

2. Configura permisos:
   - `uploads/` → permisos 755 o 777

### Paso 5: Probar
1. Visita tu dominio
2. Prueba el login
3. Verifica que todo funcione

---

## 🔐 Seguridad Importante

1. **Cambia las contraseñas** por defecto
2. **Habilita SSL** (HTTPS) - muchos hosting lo dan gratis
3. **Mantén actualizado** PHP y MySQL
4. **Haz backups** regularmente
5. **No subas** `config.php` con credenciales reales a GitHub

---

## 📞 Soporte

Si tienes problemas:
- **Hostinger**: Soporte 24/7 en español por chat
- **InfinityFree**: Foros de la comunidad
- **000WebHost**: Soporte por tickets

---

## 💡 Consejos Adicionales

- **Empieza con hosting gratuito** para probar
- **Migra a pago** cuando el sitio esté en producción
- **Usa un dominio personalizado** para profesionalismo
- **Configura backups automáticos** si están disponibles
- **Monitorea el espacio** y rendimiento

---

**¿Necesitas ayuda con algún paso?** Revisa la documentación del hosting o contacta a su soporte.

