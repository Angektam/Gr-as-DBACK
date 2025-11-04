# 🚛 SISTEMA DE GESTIÓN DE GRÚAS - VERSIÓN MEJORADA

## 📋 Resumen de Mejoras

El sistema de gestión de grúas ha sido completamente renovado con las siguientes mejoras:

### ✨ Características Principales

1. **Diseño Moderno y Profesional**
   - Interfaz con gradientes y colores atractivos
   - Diseño responsive (adaptable a móviles)
   - Animaciones suaves y transiciones
   - Íconos de Font Awesome

2. **Estadísticas en Tiempo Real**
   - Total de grúas
   - Grúas activas
   - Grúas en mantenimiento
   - Grúas inactivas
   - Tarjetas con colores diferenciados

3. **Búsqueda y Filtros Avanzados**
   - Búsqueda por placa, marca o modelo
   - Filtro por tipo de grúa
   - Filtro por estado
   - Resultados en tiempo real

4. **CRUD Completo**
   - ✅ Crear nuevas grúas
   - ✅ Editar grúas existentes
   - ✅ Eliminar grúas
   - ✅ Ver detalles completos

5. **Paginación**
   - 10 registros por página
   - Navegación entre páginas
   - Contador de resultados

6. **Validaciones**
   - Validación de campos obligatorios
   - Placa única (no duplicados)
   - Conversión automática de placa a mayúsculas
   - Confirmación antes de eliminar

7. **Gestión de Sesiones Mejorada**
   - Sesión centralizada en config.php
   - Sin duplicación de session_start()
   - Verificación de autenticación

---

## 🚀 INSTALACIÓN

### Opción 1: Instalador Automático (RECOMENDADO)

1. Abre tu navegador y accede a:
   ```
   http://localhost/instalar-gruas-mejorado.php
   ```

2. El instalador automáticamente:
   - ✅ Verificará la conexión a la base de datos
   - ✅ Creará la tabla `gruas` si no existe
   - ✅ Agregará columnas faltantes si la tabla ya existe
   - ✅ Creará la tabla `mantenimiento_gruas` para futuro uso
   - ✅ Agregará datos de ejemplo (opcional)
   - ✅ Te mostrará un reporte detallado

3. Haz clic en "Ir a Gestión de Grúas" para comenzar a usar el sistema.

### Opción 2: Instalación Manual con SQL

1. Abre phpMyAdmin
2. Selecciona la base de datos `dback`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido del archivo `configuracion-gruas-mejorado.sql`
5. Haz clic en "Continuar"

---

## 📁 Archivos del Sistema

### Archivos Principales

- **Gruas.php** - Interfaz principal del sistema de grúas
- **conexion.php** - Conexión centralizada a la base de datos
- **config.php** - Configuración y gestión de sesiones

### Archivos de Instalación

- **instalar-gruas-mejorado.php** - Instalador automático
- **configuracion-gruas-mejorado.sql** - Script SQL manual

### Archivos de Respaldo

- **Gruas-backup-original.php** - Respaldo del sistema anterior

---

## 💾 Estructura de la Base de Datos

### Tabla: `gruas`

```sql
CREATE TABLE gruas (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Placa VARCHAR(10) NOT NULL UNIQUE,
    Marca VARCHAR(100) NOT NULL,
    Modelo VARCHAR(100) NOT NULL,
    Tipo ENUM('Plataforma', 'Arrastre', 'Remolque', 'Grúa') DEFAULT 'Plataforma',
    Estado ENUM('Activa', 'Mantenimiento', 'Inactiva') DEFAULT 'Activa',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_placa (Placa),
    INDEX idx_estado (Estado),
    INDEX idx_tipo (Tipo)
);
```

### Tabla: `mantenimiento_gruas` (Para futuro)

```sql
CREATE TABLE mantenimiento_gruas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grua_id INT NOT NULL,
    tipo_mantenimiento ENUM('Preventivo', 'Correctivo', 'Revisión') NOT NULL,
    fecha_mantenimiento DATE NOT NULL,
    tecnico_responsable VARCHAR(100),
    costo DECIMAL(10,2) DEFAULT 0.00,
    detalles TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grua_id) REFERENCES gruas(ID) ON DELETE CASCADE
);
```

---

## 🎯 Cómo Usar el Sistema

### Agregar una Grúa

1. Haz clic en el botón "Nueva Grúa"
2. Completa el formulario:
   - Placa (única, se convierte a mayúsculas)
   - Marca
   - Modelo
   - Tipo (Plataforma, Arrastre, Remolque, Grúa)
   - Estado (Activa, Mantenimiento, Inactiva)
3. Haz clic en "Guardar"

### Buscar Grúas

1. Usa el campo de búsqueda para filtrar por placa, marca o modelo
2. Usa los filtros desplegables para filtrar por tipo y estado
3. Haz clic en "Filtrar"

### Editar una Grúa

1. Encuentra la grúa en la tabla
2. Haz clic en el botón amarillo "✏️" (Editar)
3. Modifica los datos
4. Haz clic en "Guardar"

### Eliminar una Grúa

1. Encuentra la grúa en la tabla
2. Haz clic en el botón rojo "🗑️" (Eliminar)
3. Confirma la eliminación

### Navegación

- Usa la paginación en la parte inferior para navegar entre páginas
- Cada página muestra 10 grúas
- El contador indica cuántas grúas se están mostrando del total

---

## 🎨 Características de Diseño

### Colores por Estado

- **Verde** 🟢 - Grúas activas
- **Amarillo** 🟡 - Grúas en mantenimiento
- **Rojo** 🔴 - Grúas inactivas
- **Morado** 🟣 - Total de grúas

### Elementos Interactivos

- Botones con efecto hover (elevación)
- Tablas con filas resaltables
- Modales con animaciones
- Formularios con validación visual
- Badges de estado con colores

---

## 🔧 Solución de Problemas

### Error: "session_start() already active"

✅ **Solucionado**: El nuevo sistema usa sesión centralizada en `config.php`

### Error: "Unknown column"

1. Ejecuta `instalar-gruas-mejorado.php`
2. O ejecuta el SQL en phpMyAdmin

### Error: "Duplicate entry for key 'Placa'"

- Las placas deben ser únicas
- Verifica que no exista otra grúa con la misma placa

### Error: "Connection failed"

- Verifica que XAMPP esté ejecutándose
- Verifica las credenciales en `config.php`

---

## 📊 Estadísticas del Sistema

El dashboard muestra en tiempo real:

- **Total de Grúas**: Todas las grúas registradas
- **Grúas Activas**: Grúas disponibles para servicio
- **En Mantenimiento**: Grúas temporalmente fuera de servicio
- **Inactivas**: Grúas dadas de baja o fuera de circulación

---

## 🔐 Seguridad

- ✅ Validación de sesión en cada página
- ✅ Escape de caracteres en consultas SQL
- ✅ Prepared statements para prevenir SQL injection
- ✅ Validación de datos en el servidor
- ✅ htmlspecialchars() para prevenir XSS

---

## 🚀 Funcionalidades Futuras (Planeadas)

- [ ] Historial de mantenimiento por grúa
- [ ] Asignación de conductores
- [ ] Seguimiento GPS
- [ ] Alertas de mantenimiento preventivo
- [ ] Reportes en PDF/Excel
- [ ] Gráficos de uso y disponibilidad
- [ ] Calendario de mantenimientos

---

## 📞 Soporte

Si encuentras algún problema:

1. Verifica que XAMPP esté ejecutándose
2. Ejecuta `instalar-gruas-mejorado.php`
3. Revisa los logs de error en `error_log.txt`
4. Verifica la consola del navegador (F12)

---

## ✅ Checklist de Instalación

- [ ] XAMPP instalado y ejecutándose
- [ ] Base de datos `dback` creada
- [ ] Archivo `config.php` configurado
- [ ] Ejecutado `instalar-gruas-mejorado.php`
- [ ] Sistema accesible en `http://localhost/Gruas.php`
- [ ] Sesión iniciada correctamente

---

**¡Sistema listo para usar!** 🎉

