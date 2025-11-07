#!/bin/bash
# Script de despliegue automático para AWS Lightsail
# Ejecutar en la instancia de Lightsail después de conectarse

echo "🚀 Iniciando despliegue de DBACK en AWS Lightsail..."

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Actualizar sistema
echo -e "${YELLOW}📦 Actualizando sistema...${NC}"
sudo apt update && sudo apt upgrade -y

# 2. Instalar Apache
echo -e "${YELLOW}🌐 Instalando Apache...${NC}"
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2

# 3. Instalar MySQL
echo -e "${YELLOW}🗄️ Instalando MySQL...${NC}"
sudo apt install mysql-server -y
sudo systemctl enable mysql
sudo systemctl start mysql

# 4. Instalar PHP y extensiones
echo -e "${YELLOW}🐘 Instalando PHP y extensiones...${NC}"
sudo apt install php php-mysql php-mbstring php-xml php-curl php-zip php-gd php-json -y

# 5. Habilitar mod_rewrite
echo -e "${YELLOW}⚙️ Configurando Apache...${NC}"
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2

# 6. Crear directorio de uploads
echo -e "${YELLOW}📁 Creando directorios necesarios...${NC}"
sudo mkdir -p /var/www/html/uploads
sudo mkdir -p /var/www/html/test_uploads

# 7. Configurar permisos
echo -e "${YELLOW}🔐 Configurando permisos...${NC}"
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/uploads
sudo chmod -R 775 /var/www/html/test_uploads

# 8. Configurar PHP (aumentar límites)
echo -e "${YELLOW}⚙️ Configurando PHP...${NC}"
sudo sed -i 's/upload_max_filesize = .*/upload_max_filesize = 10M/' /etc/php/*/apache2/php.ini
sudo sed -i 's/post_max_size = .*/post_max_size = 10M/' /etc/php/*/apache2/php.ini
sudo sed -i 's/memory_limit = .*/memory_limit = 128M/' /etc/php/*/apache2/php.ini
sudo systemctl restart apache2

# 9. Configurar MySQL (crear base de datos)
echo -e "${YELLOW}🗄️ Configurando MySQL...${NC}"
echo "Por favor, ejecuta manualmente:"
echo "sudo mysql"
echo "CREATE DATABASE dback CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "CREATE USER 'dback_user'@'localhost' IDENTIFIED BY 'TU_CONTRASEÑA_SEGURA';"
echo "GRANT ALL PRIVILEGES ON dback.* TO 'dback_user'@'localhost';"
echo "FLUSH PRIVILEGES;"
echo "EXIT;"

# 10. Instrucciones finales
echo -e "${GREEN}✅ Instalación base completada!${NC}"
echo ""
echo "📝 Próximos pasos:"
echo "1. Clona tu repositorio:"
echo "   cd /var/www/html"
echo "   sudo git clone https://github.com/Angektam/Gr-as-DBACK.git ."
echo ""
echo "2. Configura config.php con tus credenciales de base de datos"
echo ""
echo "3. Importa tu base de datos:"
echo "   mysql -u dback_user -p dback < archivo.sql"
echo ""
echo "4. Ajusta permisos:"
echo "   sudo chown -R www-data:www-data /var/www/html"
echo "   sudo chmod -R 755 /var/www/html"
echo ""
echo "5. Configura el firewall en Lightsail (puertos 80, 443, 22)"
echo ""
echo -e "${GREEN}🎉 ¡Listo para desplegar!${NC}"

