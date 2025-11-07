# 🚀 Guía de Despliegue en AWS - Sistema DBACK

## 📋 Opciones de Despliegue en AWS

Para este proyecto (PHP + MySQL), tienes varias opciones:

### 1. **AWS Lightsail** (Recomendado - Más Fácil) ⭐
- ✅ Configuración rápida (5-10 minutos)
- ✅ Incluye LAMP stack preconfigurado
- ✅ Precio fijo desde $3.50/mes
- ✅ Ideal para proyectos pequeños/medianos

### 2. **AWS Elastic Beanstalk**
- ✅ Escalable automáticamente
- ✅ Gestión de versiones
- ✅ Más complejo de configurar
- ✅ Mejor para producción

### 3. **AWS EC2**
- ✅ Control total
- ✅ Más flexible
- ⚠️ Requiere más conocimiento técnico

---

## 🌟 Opción 1: AWS Lightsail (Recomendada)

### Paso 1: Crear Cuenta en AWS

1. Ve a [aws.amazon.com](https://aws.amazon.com)
2. Haz clic en "Crear una cuenta de AWS"
3. Completa el registro (requiere tarjeta de crédito, pero hay tier gratuito)

### Paso 2: Crear Instancia en Lightsail

1. **Accede a Lightsail:**
   - Ve a [lightsail.aws.amazon.com](https://lightsail.aws.amazon.com)
   - Inicia sesión con tu cuenta AWS

2. **Crear Instancia:**
   - Haz clic en "Crear instancia"
   - **Plataforma:** Linux/Unix
   - **Imagen:** Ubuntu 22.04 LTS
   - **Plan:** 
     - **$3.50/mes** (512 MB RAM, 1 vCPU) - Para pruebas
     - **$5/mes** (1 GB RAM, 1 vCPU) - Recomendado
     - **$10/mes** (2 GB RAM, 1 vCPU) - Para producción
   - **Nombre:** `dback-server`
   - Haz clic en "Crear instancia"

### Paso 3: Conectar a la Instancia

1. **Usando el Navegador (Más Fácil):**
   - En Lightsail, haz clic en tu instancia
   - Haz clic en "Conectar usando SSH"
   - Se abrirá una terminal en el navegador

2. **Usando SSH (Windows):**
   - Descarga PuTTY o usa PowerShell
   - En Lightsail, ve a "Account" → "SSH keys"
   - Descarga la clave privada
   - Conecta usando:
   ```bash
   ssh -i ruta/a/tu/clave.pem ubuntu@IP_DE_TU_INSTANCIA
   ```

### Paso 4: Instalar LAMP Stack

Ejecuta estos comandos en la terminal:

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache
sudo apt install apache2 -y

# Instalar MySQL
sudo apt install mysql-server -y

# Instalar PHP y extensiones
sudo apt install php php-mysql php-mbstring php-xml php-curl php-zip php-gd -y

# Instalar phpMyAdmin (opcional, para gestionar BD)
sudo apt install phpmyadmin -y
# Durante la instalación:
# - Selecciona "apache2"
# - Selecciona "Yes" para configurar con dbconfig-common
# - Establece una contraseña para phpMyAdmin

# Habilitar mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Paso 5: Configurar Base de Datos

```bash
# Acceder a MySQL
sudo mysql

# En MySQL, ejecuta:
CREATE DATABASE dback CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dback_user'@'localhost' IDENTIFIED BY 'TU_CONTRASEÑA_SEGURA';
GRANT ALL PRIVILEGES ON dback.* TO 'dback_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Paso 6: Subir Archivos del Proyecto

**Opción A: Usando Git (Recomendado)**

```bash
# En la instancia, navega al directorio web
cd /var/www/html

# Clonar tu repositorio
sudo git clone https://github.com/Angektam/Gr-as-DBACK.git .

# O si ya tienes archivos, elimina el index.html por defecto
sudo rm index.html

# Dar permisos correctos
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/uploads
```

**Opción B: Usando FileZilla (FTP/SFTP)**

1. **Configurar SFTP en Lightsail:**
   - En Lightsail, ve a tu instancia
   - Pestaña "Networking"
   - Agrega regla: SSH (22) desde tu IP

2. **Conectar con FileZilla:**
   - Host: `IP_DE_TU_INSTANCIA`
   - Usuario: `ubuntu`
   - Contraseña: (usa la clave SSH)
   - Puerto: `22`
   - Protocolo: `SFTP`

3. **Subir archivos:**
   - Sube todos los archivos a `/var/www/html`

### Paso 7: Configurar config.php

```bash
# Editar config.php
sudo nano /var/www/html/config.php
```

Actualiza con los datos de tu base de datos:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'dback_user');
define('DB_PASS', 'TU_CONTRASEÑA_SEGURA');
define('DB_NAME', 'dback');
// ... resto de configuración
```

### Paso 8: Importar Base de Datos

```bash
# Si tienes un archivo .sql
mysql -u dback_user -p dback < ruta/a/tu/archivo.sql

# O usando phpMyAdmin
# Ve a: http://TU_IP/phpmyadmin
# Importa tu archivo .sql
```

### Paso 9: Configurar Permisos

```bash
# Crear directorio de uploads si no existe
sudo mkdir -p /var/www/html/uploads
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/uploads
```

### Paso 10: Configurar Dominio (Opcional)

1. **En Lightsail:**
   - Ve a "Networking"
   - Crea un "Static IP"
   - Asigna la IP estática a tu instancia

2. **Configurar DNS:**
   - En tu proveedor de dominio, crea un registro A
   - Apunta a la IP estática de Lightsail

3. **Configurar Apache:**
   ```bash
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```
   
   Agrega:
   ```apache
   <VirtualHost *:80>
       ServerName tudominio.com
       ServerAlias www.tudominio.com
       DocumentRoot /var/www/html
       
       <Directory /var/www/html>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

### Paso 11: Configurar Firewall

En Lightsail:
- Ve a "Networking"
- Agrega reglas:
  - HTTP (80)
  - HTTPS (443) - si usas SSL
  - SSH (22) - solo desde tu IP

### Paso 12: Instalar Certificado SSL (Opcional pero Recomendado)

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtener certificado
sudo certbot --apache -d tudominio.com -d www.tudominio.com

# Renovación automática
sudo certbot renew --dry-run
```

---

## 🔧 Opción 2: AWS Elastic Beanstalk

### Paso 1: Preparar Proyecto

1. **Crear archivo `.ebextensions/php.config`:**
   ```yaml
   option_settings:
     - namespace: aws:elasticbeanstalk:container:php
       option_name: document_root
       value: /
   ```

2. **Crear archivo `.ebextensions/db.config`:**
   ```yaml
   option_settings:
     - namespace: aws:rds:dbinstance
       option_name: DBName
       value: dback
   ```

### Paso 2: Instalar EB CLI

```bash
# Windows (PowerShell)
pip install awsebcli

# Verificar instalación
eb --version
```

### Paso 3: Inicializar Elastic Beanstalk

```bash
# En el directorio del proyecto
eb init

# Selecciona:
# - Región (ej: us-east-1)
# - Plataforma: PHP
# - Versión: PHP 8.2
# - SSH: Yes
```

### Paso 4: Crear Entorno

```bash
# Crear entorno
eb create dback-env

# O crear con base de datos RDS
eb create dback-env --database.engine mysql --database.username admin --database.password TU_PASSWORD
```

### Paso 5: Desplegar

```bash
# Desplegar código
eb deploy

# Abrir en navegador
eb open
```

### Paso 6: Configurar Variables de Entorno

```bash
# Configurar variables de entorno
eb setenv DB_HOST=tu-rds-endpoint DB_USER=admin DB_PASS=TU_PASSWORD DB_NAME=dback
```

---

## 📊 Comparación de Opciones

| Característica | Lightsail | Elastic Beanstalk | EC2 |
|---------------|-----------|-------------------|-----|
| Facilidad | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ |
| Precio/mes | $3.50+ | Variable | Variable |
| Escalabilidad | Manual | Automática | Manual |
| Tiempo setup | 10 min | 30 min | 1 hora+ |
| Ideal para | Proyectos pequeños | Producción | Control total |

---

## 🔐 Seguridad Post-Despliegue

1. **Cambiar contraseñas por defecto**
2. **Configurar firewall (solo puertos necesarios)**
3. **Instalar SSL (HTTPS)**
4. **Hacer backups regulares**
5. **Actualizar sistema regularmente:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

---

## 📝 Checklist de Despliegue

- [ ] Cuenta AWS creada
- [ ] Instancia Lightsail creada
- [ ] LAMP stack instalado
- [ ] Base de datos creada
- [ ] Archivos subidos
- [ ] config.php configurado
- [ ] Base de datos importada
- [ ] Permisos configurados
- [ ] Firewall configurado
- [ ] Dominio configurado (opcional)
- [ ] SSL instalado (opcional)
- [ ] Pruebas realizadas

---

## 🆘 Solución de Problemas

### Error 403 Forbidden
```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### Error de conexión a base de datos
- Verifica `config.php`
- Verifica que MySQL esté corriendo: `sudo systemctl status mysql`
- Verifica usuario y permisos en MySQL

### Archivos no se suben
```bash
sudo chmod -R 775 /var/www/html/uploads
sudo chown -R www-data:www-data /var/www/html/uploads
```

### PHP no funciona
```bash
sudo systemctl restart apache2
sudo a2enmod php8.2  # Ajusta la versión
```

---

## 💰 Costos Estimados

### Lightsail:
- **$3.50/mes** - 512 MB RAM (pruebas)
- **$5/mes** - 1 GB RAM (recomendado)
- **$10/mes** - 2 GB RAM (producción)
- **+$1/mes** - IP estática (opcional)

### Elastic Beanstalk:
- EC2: ~$10-20/mes
- RDS: ~$15-30/mes
- Total: ~$25-50/mes

---

## 📞 Recursos Adicionales

- [Documentación AWS Lightsail](https://docs.aws.amazon.com/lightsail/)
- [Documentación Elastic Beanstalk](https://docs.aws.amazon.com/elasticbeanstalk/)
- [AWS Free Tier](https://aws.amazon.com/free/)

---

**¿Necesitas ayuda con algún paso específico?** Puedo ayudarte a configurar cualquier parte del proceso.

