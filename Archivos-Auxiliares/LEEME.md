# 📂 ARCHIVOS AUXILIARES

Esta carpeta contiene archivos que **NO son necesarios** para el funcionamiento diario del sistema DBACK, pero que pueden ser útiles para:

- 🔧 Instalación y configuración inicial
- ✅ Verificación y diagnóstico
- 📚 Documentación y guías
- 💾 Respaldos de versiones anteriores
- 🧪 Pruebas y desarrollo

---

## 📋 Categorías de Archivos

### 🔧 Instaladores y Configuradores
Archivos para configurar la base de datos y el sistema:
- `instalar-*.php` - Instaladores automáticos
- `configuracion-*.sql` - Scripts SQL de configuración
- `fix-*.php` - Scripts de corrección rápida

### ✅ Verificadores
Scripts para verificar que todo funcione correctamente:
- `verificar-*.php` - Verificadores de configuración
- `diagnostico-*.php` - Herramientas de diagnóstico
- `debug-*.php` - Scripts de depuración

### 💾 Respaldos
Versiones anteriores de archivos (por seguridad):
- `*-backup-*.php` - Respaldos de archivos modificados

### 🧪 Pruebas
Archivos de desarrollo y pruebas:
- `prueba-*.php` - Scripts de prueba
- `test-*.php` - Tests del sistema
- `ejemplo-*.php` - Archivos de ejemplo

### 📚 Documentación
Guías y documentación del sistema:
- `*.md` - Archivos Markdown con instrucciones
- `INSTRUCCIONES-*.md` - Guías detalladas
- `RESUMEN-*.md` - Resúmenes de mejoras
- `LEEME-*.md` - Guías rápidas

### 📝 Logs
Archivos de registro de errores:
- `*_log.txt` - Logs de errores y actividad

### 🗃️ Archivos Antiguos
Versiones anteriores o archivos obsoletos:
- `*.html` - Versiones HTML antiguas
- `*.js` - Scripts JavaScript antiguos
- Otros archivos de versiones previas

---

## ⚠️ IMPORTANTE

**NO ELIMINES ESTA CARPETA** si:
- Necesitas reinstalar o reconfigurar el sistema
- Quieres consultar la documentación
- Necesitas restaurar una versión anterior
- Estás depurando problemas

---

## 🎯 ¿Qué Archivos SÍ Necesitas?

Los archivos esenciales están en la **carpeta raíz** del proyecto:

### Páginas Principales
- `MenuAdmin.PHP` - Menú principal
- `Gruas.php` - Gestión de grúas
- `Empleados.php` - Gestión de empleados
- `Gastos.php` - Gestión de gastos
- `Reportes.php` - Reportes
- `solicitud.php` - Sistema de solicitudes
- `Login.php` - Inicio de sesión

### Archivos de Sistema
- `config.php` - Configuración principal
- `conexion.php` - Conexión a base de datos
- `api.php` - API principal
- Componentes (`header-component.php`, `sidebar-component.php`, etc.)

---

## 📁 Estructura de Esta Carpeta

```
Archivos-Auxiliares/
│
├── 🔧 Instaladores/
│   ├── instalar-*.php
│   └── configuracion-*.sql
│
├── ✅ Verificadores/
│   ├── verificar-*.php
│   └── diagnostico-*.php
│
├── 💾 Respaldos/
│   └── *-backup-*.php
│
├── 🧪 Pruebas/
│   ├── prueba-*.php
│   └── test-*.php
│
├── 📚 Documentación/
│   └── *.md
│
└── 📝 Logs/
    └── *_log.txt
```

---

## 💡 Cuándo Usar Estos Archivos

### Instalación Inicial
1. Ejecuta `instalar-empleados-mejorado.php`
2. Ejecuta `instalar-gruas-mejorado.php`
3. Verifica con `verificar-*.php`

### Problemas de Configuración
1. Usa `diagnostico-*.php` para identificar el problema
2. Usa `fix-*.php` para soluciones rápidas
3. Consulta la documentación en archivos `.md`

### Consulta de Documentación
- Lee `INSTRUCCIONES-*.md` para guías completas
- Lee `RESUMEN-*.md` para resúmenes rápidos
- Lee `LEEME-*.md` para guías de inicio rápido

### Restauración
- Usa los archivos `*-backup-*.php` si necesitas volver a una versión anterior

---

## 🔐 Seguridad

Esta carpeta contiene:
- ✅ Documentación (segura)
- ✅ Scripts de configuración (ejecutar solo una vez)
- ✅ Respaldos (solo lectura)
- ⚠️ NO contiene datos sensibles de usuarios

---

**Última actualización**: Octubre 2025  
**Sistema**: DBACK - Gestión de Grúas

