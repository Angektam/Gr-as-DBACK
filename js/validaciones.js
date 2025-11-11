/**
 * Sistema de Validaciones - DBACK
 * Validaciones del lado del cliente (JavaScript)
 */

// Expresiones regulares comunes
const PATTERNS = {
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    telefono: /^[\d\s\-\+\(\)]{10,15}$/,
    telefono_mx: /^[\d\s\-]{10}$|^[\+]?52[\d\s\-]{10}$/,
    nombre: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/,
    alfanumerico: /^[a-zA-Z0-9\s\-_]{1,100}$/,
    soloNumeros: /^\d+$/,
    decimal: /^\d+(\.\d{1,2})?$/,
    url: /^https?:\/\/.+/,
    coordenadas: /^-?\d+\.?\d*,\s*-?\d+\.?\d*$/
};

// Clase para manejar validaciones
class Validador {
    constructor() {
        this.errores = {};
    }

    /**
     * Validar email
     */
    validarEmail(email, campo = 'email') {
        if (!email || email.trim() === '') {
            this.agregarError(campo, 'El email es requerido');
            return false;
        }
        if (!PATTERNS.email.test(email)) {
            this.agregarError(campo, 'El formato del email no es válido');
            return false;
        }
        if (email.length > 100) {
            this.agregarError(campo, 'El email no puede tener más de 100 caracteres');
            return false;
        }
        return true;
    }

    /**
     * Validar teléfono
     */
    validarTelefono(telefono, campo = 'telefono') {
        if (!telefono || telefono.trim() === '') {
            this.agregarError(campo, 'El teléfono es requerido');
            return false;
        }
        const telefonoLimpio = telefono.replace(/\s/g, '');
        if (!PATTERNS.telefono_mx.test(telefonoLimpio)) {
            this.agregarError(campo, 'El formato del teléfono no es válido (ej: 9991234567 o +529991234567)');
            return false;
        }
        return true;
    }

    /**
     * Validar nombre
     */
    validarNombre(nombre, campo = 'nombre', minLength = 2, maxLength = 50) {
        if (!nombre || nombre.trim() === '') {
            this.agregarError(campo, 'El nombre es requerido');
            return false;
        }
        if (nombre.length < minLength) {
            this.agregarError(campo, `El nombre debe tener al menos ${minLength} caracteres`);
            return false;
        }
        if (nombre.length > maxLength) {
            this.agregarError(campo, `El nombre no puede tener más de ${maxLength} caracteres`);
            return false;
        }
        if (!PATTERNS.nombre.test(nombre)) {
            this.agregarError(campo, 'El nombre solo puede contener letras y espacios');
            return false;
        }
        return true;
    }

    /**
     * Validar campo requerido
     */
    requerido(valor, campo, mensaje = null) {
        if (!valor || valor.toString().trim() === '') {
            this.agregarError(campo, mensaje || `${campo} es requerido`);
            return false;
        }
        return true;
    }

    /**
     * Validar longitud
     */
    longitud(valor, campo, min, max) {
        if (valor && (valor.length < min || valor.length > max)) {
            this.agregarError(campo, `Debe tener entre ${min} y ${max} caracteres`);
            return false;
        }
        return true;
    }

    /**
     * Validar número
     */
    validarNumero(valor, campo, min = null, max = null) {
        if (valor === '' || valor === null || valor === undefined) {
            this.agregarError(campo, 'Debe ser un número válido');
            return false;
        }
        const num = parseFloat(valor);
        if (isNaN(num)) {
            this.agregarError(campo, 'Debe ser un número válido');
            return false;
        }
        if (min !== null && num < min) {
            this.agregarError(campo, `El valor mínimo es ${min}`);
            return false;
        }
        if (max !== null && num > max) {
            this.agregarError(campo, `El valor máximo es ${max}`);
            return false;
        }
        return true;
    }

    /**
     * Validar archivo
     */
    validarArchivo(archivo, campo, maxSizeMB = 5, tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf']) {
        if (!archivo || !archivo.files || archivo.files.length === 0) {
            return true; // Archivo opcional
        }

        const file = archivo.files[0];
        const maxSizeBytes = maxSizeMB * 1024 * 1024;

        // Validar tamaño
        if (file.size > maxSizeBytes) {
            this.agregarError(campo, `El archivo no puede ser mayor a ${maxSizeMB}MB`);
            return false;
        }

        // Validar tipo
        const extension = file.name.split('.').pop().toLowerCase();
        if (!tiposPermitidos.includes(extension)) {
            this.agregarError(campo, `Solo se permiten archivos: ${tiposPermitidos.join(', ')}`);
            return false;
        }

        return true;
    }

    /**
     * Validar coordenadas
     */
    validarCoordenadas(coordenadas, campo = 'coordenadas') {
        if (!coordenadas || coordenadas.trim() === '') {
            return true; // Coordenadas opcionales
        }
        if (!PATTERNS.coordenadas.test(coordenadas)) {
            this.agregarError(campo, 'Formato de coordenadas inválido (ej: 21.123, -89.456)');
            return false;
        }
        return true;
    }

    /**
     * Validar URL
     */
    validarURL(url, campo = 'url') {
        if (!url || url.trim() === '') {
            return true; // URL opcional
        }
        if (!PATTERNS.url.test(url)) {
            this.agregarError(campo, 'La URL no es válida');
            return false;
        }
        return true;
    }

    /**
     * Validar consentimiento
     */
    validarConsentimiento(checkbox, campo = 'consentimiento') {
        if (!checkbox || !checkbox.checked) {
            this.agregarError(campo, 'Debes aceptar los términos y condiciones');
            return false;
        }
        return true;
    }

    /**
     * Agregar error
     */
    agregarError(campo, mensaje) {
        if (!this.errores[campo]) {
            this.errores[campo] = [];
        }
        this.errores[campo].push(mensaje);
    }

    /**
     * Limpiar errores
     */
    limpiarErrores() {
        this.errores = {};
    }

    /**
     * Obtener errores
     */
    obtenerErrores() {
        return this.errores;
    }

    /**
     * Verificar si hay errores
     */
    tieneErrores() {
        return Object.keys(this.errores).length > 0;
    }

    /**
     * Mostrar errores en el formulario
     */
    mostrarErrores() {
        // Limpiar errores anteriores
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.campo-error').forEach(el => {
            el.classList.remove('campo-error');
        });

        // Mostrar nuevos errores
        for (const [campo, mensajes] of Object.entries(this.errores)) {
            const input = document.querySelector(`[name="${campo}"]`);
            if (input) {
                input.classList.add('campo-error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.style.marginTop = '0.25rem';
                errorDiv.textContent = mensajes[0]; // Mostrar solo el primer error
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
        }
    }
}

// Función para sanitizar entrada
function sanitizar(texto) {
    if (!texto) return '';
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

// Función para validar formulario completo
function validarFormulario(formulario, reglas = {}) {
    const validador = new Validador();
    const formData = new FormData(formulario);

    // Aplicar reglas de validación
    for (const [campo, regla] of Object.entries(reglas || {})) {
        const valor = formData.get(campo);
        const input = formulario.querySelector(`[name="${campo}"]`);

        if (regla.requerido && !validador.requerido(valor, campo)) {
            continue;
        }

        if (valor && regla.tipo) {
            switch (regla.tipo) {
                case 'email':
                    validador.validarEmail(valor, campo);
                    break;
                case 'telefono':
                    validador.validarTelefono(valor, campo);
                    break;
                case 'nombre':
                    validador.validarNombre(valor, campo, regla.min, regla.max);
                    break;
                case 'numero':
                    validador.validarNumero(valor, campo, regla.min, regla.max);
                    break;
                case 'longitud':
                    validador.longitud(valor, campo, regla.min, regla.max);
                    break;
                case 'archivo':
                    validador.validarArchivo(input, campo, regla.maxSize, regla.tipos);
                    break;
                case 'consentimiento':
                    validador.validarConsentimiento(input, campo);
                    break;
            }
        }
    }

    validador.mostrarErrores();
    return !validador.tieneErrores();
}

// Exportar para uso global
window.Validador = Validador;
window.validarFormulario = validarFormulario;
window.sanitizar = sanitizar;
window.PATTERNS = PATTERNS;

