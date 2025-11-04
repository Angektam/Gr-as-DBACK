# 🚀 Desplegar en Vercel - Guía Completa

## ⚠️ IMPORTANTE: Limitaciones de Vercel

**Vercel NO soporta PHP directamente**. Esto significa:

✅ **Funcionará en Vercel:**
- Página principal (`index.html`)
- Estilos CSS
- JavaScript
- Imágenes y recursos estáticos

❌ **NO funcionará en Vercel:**
- Archivos PHP (solicitud.php, Login.php, etc.)
- Base de datos MySQL
- Funcionalidades del backend
- Panel de administración

**Solución**: Usa Vercel para el frontend y otro hosting (InfinityFree, Hostinger) para el backend PHP.

---

## 📋 Opción 1: Desplegar solo Frontend en Vercel

### Paso 1: Crear cuenta en Vercel

1. Ve a: https://vercel.com/
2. Haz clic en **"Sign Up"**
3. Elige **"Continue with GitHub"** (recomendado)
4. Autoriza a Vercel para acceder a tu repositorio

### Paso 2: Conectar repositorio

1. En el dashboard de Vercel, haz clic en **"Add New..."** → **"Project"**
2. Importa tu repositorio: `Angektam/Gr-as-DBACK`
3. Vercel detectará automáticamente la configuración

### Paso 3: Configurar proyecto

**Configuración recomendada:**
- **Framework Preset**: Other
- **Root Directory**: `./` (raíz)
- **Build Command**: (dejar vacío - no hay build)
- **Output Directory**: `./` (raíz)
- **Install Command**: (dejar vacío)

### Paso 4: Variables de entorno (opcional)

Si necesitas configuraciones, puedes agregar variables de entorno:
- Click en **"Environment Variables"**
- Agrega variables si las necesitas

### Paso 5: Desplegar

1. Haz clic en **"Deploy"**
2. Espera 1-2 minutos
3. ¡Tu sitio estará en línea!

**URL**: `https://tu-proyecto.vercel.app`

---

## 📋 Opción 2: Desplegar Automáticamente (Recomendado)

### Configuración automática con GitHub

1. **Conecta tu repositorio** a Vercel (como en Opción 1)
2. **Vercel detectará automáticamente** los cambios en GitHub
3. **Cada push a `master`** desplegará automáticamente
4. **Preview deployments** para cada pull request

### Ventajas:
- ✅ Despliegue automático en cada cambio
- ✅ URLs de preview para testing
- ✅ Rollback fácil a versiones anteriores
- ✅ Integración perfecta con GitHub

---

## 🔧 Configuración Avanzada

El archivo `vercel.json` ya está configurado con:
- ✅ Rutas estáticas
- ✅ Headers de seguridad
- ✅ Redirecciones básicas

---

## 🌐 Arquitectura Híbrida (Recomendada)

### Frontend en Vercel + Backend PHP en otro hosting

**Frontend (Vercel):**
- `index.html` - Página principal
- CSS, JavaScript, imágenes
- URL: `https://dback.vercel.app`

**Backend (InfinityFree/Hostinger):**
- Todos los archivos PHP
- Base de datos MySQL
- API endpoints
- URL: `https://api.dback.infinityfreeapp.com` o dominio personalizado

**Configuración:**
1. Modifica `index.html` para apuntar a tu backend PHP
2. Cambia las rutas PHP por URLs del backend:
   ```html
   <!-- Antes -->
   <a href="solicitud.php">Solicitar Servicio</a>
   
   <!-- Después -->
   <a href="https://api.dback.infinityfreeapp.com/solicitud.php">Solicitar Servicio</a>
   ```

---

## 📝 Pasos Rápidos para Desplegar

1. **Ve a**: https://vercel.com/
2. **Click en**: "Add New..." → "Project"
3. **Importa**: Tu repositorio de GitHub
4. **Click en**: "Deploy"
5. **¡Listo!** Tu sitio estará en línea

---

## 🔍 Verificar el Despliegue

1. Visita tu URL: `https://tu-proyecto.vercel.app`
2. Verifica que `index.html` carga correctamente
3. Verifica que CSS y JavaScript funcionan
4. Verifica que las imágenes se ven

---

## ⚙️ Personalizar Dominio

1. En el dashboard de Vercel, ve a tu proyecto
2. Click en **"Settings"** → **"Domains"**
3. Agrega tu dominio personalizado
4. Configura los DNS según las instrucciones

---

## 🆘 Solución de Problemas

### Error: "Build failed"
- Verifica que `vercel.json` esté correcto
- Asegúrate de que `index.html` esté en la raíz

### Las imágenes no se ven
- Verifica las rutas en `index.html`
- Asegúrate de que la carpeta `Elementos/` esté en el repositorio

### CSS no carga
- Verifica que los archivos CSS estén en el repositorio
- Verifica las rutas en `index.html`

---

## 📊 Alternativas Completas (PHP + MySQL)

Si necesitas que TODO funcione (incluyendo PHP):

1. **InfinityFree** (Gratis) - https://www.infinityfree.com/
2. **Hostinger** ($3.99/mes) - https://www.hostinger.com/
3. **HostGator** ($99 MXN/mes) - https://www.hostgator.com.mx/

Ver `GUIA_INFINITYFREE.md` para instrucciones detalladas.

---

## 💡 Recomendación Final

**Para tu proyecto:**
- ✅ **Frontend estático** → Vercel (rápido, gratis, automático)
- ✅ **Backend PHP** → InfinityFree o Hostinger (soporta PHP + MySQL)

Esta arquitectura te da:
- Velocidad y CDN global de Vercel
- Funcionalidad completa del backend PHP
- Mejor rendimiento general

---

**¿Necesitas ayuda?** Revisa la documentación de Vercel: https://vercel.com/docs

