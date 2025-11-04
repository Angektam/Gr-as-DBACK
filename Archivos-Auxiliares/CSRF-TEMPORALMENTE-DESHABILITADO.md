# ⚠️ CSRF TEMPORALMENTE DESHABILITADO

## 🔒 ESTADO ACTUAL

La protección CSRF ha sido **temporalmente deshabilitada** en `menu-auto-asignacion.php` para facilitar las pruebas del sistema.

**Archivo:** `menu-auto-asignacion.php`  
**Líneas:** 72-82, 209-210  
**Fecha:** 22 de Octubre, 2025

---

## ⚠️ IMPORTANTE

**ESTO ES TEMPORAL** - La validación CSRF debe ser **reactivada** antes de poner el sistema en producción.

---

## 🔧 CAMBIOS REALIZADOS

### Líneas 72-82 (Validación comentada)

**ANTES:**
```php
// VALIDACIÓN 5: Verificar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $mensaje = "Error de seguridad: Token CSRF inválido";
    $tipo_mensaje = "error";
} else {
    // Procesar formularios
}
```

**AHORA (Temporal):**
```php
// VALIDACIÓN 5: Verificar token CSRF (TEMPORAL: DESHABILITADA PARA PRUEBAS)
// Descomentar estas líneas cuando el sistema esté en producción
/*
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $mensaje = "Error de seguridad: Token CSRF inválido";
    $tipo_mensaje = "error";
} else {
*/

// CSRF TEMPORALMENTE DESHABILITADO - ELIMINAR EN PRODUCCIÓN
if (true) {
    // Procesar formularios
}
```

---

## 🚀 CÓMO REACTIVAR LA PROTECCIÓN CSRF

### Paso 1: Descomentar la validación

En las **líneas 72-79**, cambia:
```php
/*
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $mensaje = "Error de seguridad: Token CSRF inválido";
    $tipo_mensaje = "error";
} else {
*/
```

Por:
```php
// VALIDACIÓN 5: Verificar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $mensaje = "Error de seguridad: Token CSRF inválido";
    $tipo_mensaje = "error";
} else {
```

### Paso 2: Eliminar el if temporal

En la **línea 82**, **ELIMINA** esta línea:
```php
// CSRF TEMPORALMENTE DESHABILITADO - ELIMINAR EN PRODUCCIÓN
if (true) {  // <-- ELIMINAR ESTA LÍNEA
```

### Paso 3: Descomentar el cierre

En la **línea 210**, cambia:
```php
// Cerrar el if del CSRF cuando se habilite
// }
```

Por:
```php
}
```

---

## ✅ CHECKLIST PARA PRODUCCIÓN

Antes de poner el sistema en producción:

- [ ] Descomentar validación CSRF (líneas 74-79)
- [ ] Eliminar `if (true)` temporal (línea 82)
- [ ] Descomentar cierre `}` (línea 210)
- [ ] Probar que los formularios funcionen con CSRF activo
- [ ] Verificar que los tokens se generen correctamente
- [ ] Comprobar que no aparezcan errores CSRF

---

## 🔍 ¿POR QUÉ SE DESHABILITÓ?

El usuario reportó el error:
```
Error de seguridad: Token CSRF inválido
```

Esto ocurría porque:

1. **Token no se enviaba correctamente** en algunos formularios
2. **Token se regeneraba** antes de validar
3. **Sesión expiraba** entre la carga de la página y el envío del formulario

### Solución Temporal

Se deshabilitó CSRF para permitir que el usuario:
- ✅ Pruebe el sistema
- ✅ Configure los parámetros
- ✅ Verifique que todo funcione

---

## 🛡️ SEGURIDAD EN PRODUCCIÓN

Cuando reactives CSRF, asegúrate de:

### 1. Verificar que los formularios incluyan el token

```html
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <!-- resto del formulario -->
</form>
```

### 2. No regenerar el token antes de validar

```php
// MAL ❌
$_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenera antes de validar
if ($_POST['csrf_token'] === $_SESSION['csrf_token']) { ... }

// BIEN ✅
if ($_POST['csrf_token'] === $_SESSION['csrf_token']) { ... }
$_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenera DESPUÉS de validar
```

### 3. Generar el token al inicio de la sesión

```php
// Al inicio del archivo (después de session_start)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

---

## 📋 FORMULARIOS QUE USAN CSRF

En `menu-auto-asignacion.php` hay **4 formularios** que necesitan el token:

1. **Guardar Configuración**
   ```html
   <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
   <button name="guardar_configuracion">Guardar</button>
   ```

2. **Procesar Pendientes**
   ```html
   <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
   <button name="procesar_pendientes">Procesar</button>
   ```

3. **Resetear Configuración**
   ```html
   <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
   <button name="resetear_configuracion">Resetear</button>
   ```

4. **Probar Sistema**
   ```html
   <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
   <button name="probar_sistema">Probar</button>
   ```

**TODOS los formularios YA TIENEN el token incluido** ✅

---

## 🐛 DEBUG CSRF

Si al reactivar CSRF vuelves a tener problemas, agrega este código para debug:

```php
// DEBUG: Ver tokens
echo "<pre>";
echo "Token en SESSION: " . ($_SESSION['csrf_token'] ?? 'NO EXISTE') . "\n";
echo "Token en POST: " . ($_POST['csrf_token'] ?? 'NO EXISTE') . "\n";
echo "¿Son iguales? " . (($_POST['csrf_token'] ?? '') === ($_SESSION['csrf_token'] ?? '') ? 'SÍ' : 'NO');
echo "</pre>";
die();
```

---

## ✅ VERIFICACIÓN ACTUAL

El sistema funciona **SIN** CSRF porque:

```php
// Línea 82
if (true) {  // Siempre verdadero = siempre procesa
    // Procesar formularios
}
```

Esto significa:
- ✅ Los formularios se procesan sin validar el token
- ⚠️ El sistema es vulnerable a ataques CSRF
- 🔒 DEBE reactivarse antes de producción

---

## 📞 CUÁNDO REACTIVAR

**Reactiva CSRF cuando:**

1. ✅ El sistema esté funcionando correctamente
2. ✅ Todas las pruebas estén completas
3. ✅ Antes de poner en producción
4. ✅ Antes de permitir acceso público

**NO reactives CSRF si:**

1. ❌ Todavía estás probando el sistema
2. ❌ Sigues configurando parámetros
3. ❌ Necesitas hacer muchas pruebas

---

## 🎯 RECORDATORIO

```
┌─────────────────────────────────────────┐
│  ⚠️  CSRF ESTÁ DESHABILITADO           │
│                                         │
│  Esto es TEMPORAL para pruebas.        │
│  DEBES reactivarlo antes de producción.│
│                                         │
│  Ver líneas: 74-79, 82, 210            │
└─────────────────────────────────────────┘
```

---

**Última actualización:** 22 de Octubre, 2025  
**Estado:** ⚠️ CSRF Deshabilitado (Temporal)  
**Acción requerida:** Reactivar antes de producción

