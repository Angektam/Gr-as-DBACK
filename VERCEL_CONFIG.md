# ⚙️ Configuración de Vercel - Paso a Paso

## 📋 Configuración del Proyecto

### Root Directory
**Deja VACÍO** o usa **`./`** (punto y barra)

**¿Por qué?**
- Todos tus archivos están en la raíz del repositorio
- No hay subdirectorios que contengan el código fuente
- `index.html` está directamente en la raíz

---

## 🔧 Configuración Completa Recomendada

### En el formulario de Vercel:

1. **Framework Preset**: `Other` o `Vite` (si no aparece, elige "Other")
2. **Root Directory**: **DEJAR VACÍO** o `./`
3. **Build Command**: **DEJAR VACÍO** (no hay build)
4. **Output Directory**: **DEJAR VACÍO** o `./`
5. **Install Command**: **DEJAR VACÍO** (no hay dependencias npm)

---

## ✅ Configuración Final

```
Framework Preset: Other
Root Directory: (vacío)
Build Command: (vacío)
Output Directory: (vacío)
Install Command: (vacío)
```

---

## 🚀 Después de Configurar

1. Haz clic en **"Deploy"**
2. Espera 1-2 minutos
3. Tu sitio estará en línea en: `https://gr-as-dback.vercel.app`

---

## ⚠️ Recordatorio

Vercel solo desplegará:
- ✅ `index.html` (página principal)
- ✅ CSS, JavaScript, imágenes
- ❌ NO funcionarán los archivos PHP

Para el backend PHP, usa InfinityFree o Hostinger.

---

## 📝 Si Tienes Problemas

Si Vercel no detecta tu `index.html`:
1. Verifica que `index.html` esté en la raíz del repositorio
2. Verifica que el archivo `vercel.json` esté presente
3. Revisa los logs de build en Vercel

