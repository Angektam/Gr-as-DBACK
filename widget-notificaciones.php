<!-- Widget de Notificaciones y Estado del Servicio -->
<!-- Incluir este archivo en las páginas donde se necesiten notificaciones -->

<style>
.notification-widget {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;
}

.notification-bell {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
    position: relative;
}

.notification-bell:hover {
    transform: scale(1.1);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.notification-panel {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    margin-top: 15px;
    max-height: 500px;
    overflow-y: auto;
    display: none;
    animation: slideDown 0.3s ease;
}

.notification-panel.show {
    display: block;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-item {
    padding: 15px;
    border-bottom: 1px solid #ecf0f1;
    transition: background 0.3s ease;
    cursor: pointer;
}

.notification-item:hover {
    background: #f8f9fa;
}

.notification-item.unread {
    background: #e8f5ff;
    border-left: 4px solid #3498db;
}

.notification-item .time {
    font-size: 0.85rem;
    color: #7f8c8d;
    margin-top: 5px;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

.notification-icon.info { background: #e8f5ff; color: #3498db; }
.notification-icon.success { background: #d4edda; color: #27ae60; }
.notification-icon.warning { background: #fff3cd; color: #f39c12; }
.notification-icon.danger { background: #f8d7da; color: #e74c3c; }

.alert-banner {
    position: fixed;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9998;
    max-width: 600px;
    width: 90%;
    animation: slideDown 0.5s ease;
}

.service-status-bar {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    padding: 10px 20px;
    text-align: center;
    font-weight: 500;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9997;
}

.service-status-bar.inactive {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.service-status-bar.warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.btn-mark-all-read {
    background: rgba(255,255,255,0.2);
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-mark-all-read:hover {
    background: rgba(255,255,255,0.3);
}

.no-notifications {
    padding: 40px;
    text-align: center;
    color: #7f8c8d;
}

.no-notifications i {
    font-size: 3rem;
    margin-bottom: 10px;
}
</style>

<!-- Barra de Estado del Servicio -->
<div id="serviceStatusBar" class="service-status-bar" style="display: none;">
    <i class="fas fa-info-circle"></i>
    <span id="serviceStatusMessage"></span>
</div>

<!-- Widget de Notificaciones -->
<div class="notification-widget">
    <div class="notification-bell" onclick="toggleNotifications()">
        <i class="fas fa-bell" style="font-size: 1.5rem;"></i>
        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
    </div>
    
    <div class="notification-panel" id="notificationPanel">
        <div class="notification-header">
            <span><i class="fas fa-bell"></i> Notificaciones</span>
            <button class="btn-mark-all-read" onclick="marcarTodasLeidas()">
                Marcar todas como leídas
            </button>
        </div>
        
        <div id="notificationList">
            <div class="no-notifications">
                <i class="fas fa-bell-slash"></i>
                <p>No hay notificaciones</p>
            </div>
        </div>
    </div>
</div>

<!-- Banner de Alertas -->
<div id="alertBanner" class="alert-banner" style="display: none;"></div>

<script>
let notificationsOpen = false;

// Toggle panel de notificaciones
function toggleNotifications() {
    const panel = document.getElementById('notificationPanel');
    notificationsOpen = !notificationsOpen;
    
    if (notificationsOpen) {
        panel.classList.add('show');
        cargarNotificaciones();
    } else {
        panel.classList.remove('show');
    }
}

// Cerrar panel al hacer clic fuera
document.addEventListener('click', function(event) {
    const widget = document.querySelector('.notification-widget');
    if (notificationsOpen && !widget.contains(event.target)) {
        document.getElementById('notificationPanel').classList.remove('show');
        notificationsOpen = false;
    }
});

// Cargar notificaciones
function cargarNotificaciones() {
    fetch('api-notificaciones.php?accion=obtener_notificaciones&limite=20')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificaciones(data.data.notificaciones);
                actualizarBadge(data.data.no_leidas);
            }
        })
        .catch(error => console.error('Error al cargar notificaciones:', error));
}

// Mostrar notificaciones en el panel
function mostrarNotificaciones(notificaciones) {
    const list = document.getElementById('notificationList');
    
    if (notificaciones.length === 0) {
        list.innerHTML = `
            <div class="no-notifications">
                <i class="fas fa-bell-slash"></i>
                <p>No hay notificaciones</p>
            </div>
        `;
        return;
    }
    
    list.innerHTML = notificaciones.map(notif => {
        const iconClass = getIconClass(notif.tipo);
        return `
            <div class="notification-item ${notif.leido == 0 ? 'unread' : ''}" 
                 onclick="marcarLeida(${notif.id})">
                <div style="display: flex; align-items: start;">
                    <div class="notification-icon ${notif.tipo}">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: ${notif.leido == 0 ? '600' : '400'};">
                            ${notif.mensaje}
                        </div>
                        <div class="time">
                            <i class="fas fa-clock"></i> ${notif.fecha_creacion_formateada}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Obtener icono según tipo
function getIconClass(tipo) {
    const icons = {
        'info': 'fa-info-circle',
        'success': 'fa-check-circle',
        'warning': 'fa-exclamation-triangle',
        'danger': 'fa-times-circle',
        'admin': 'fa-user-shield'
    };
    return icons[tipo] || 'fa-bell';
}

// Actualizar badge
function actualizarBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

// Marcar notificación como leída
function marcarLeida(notifId) {
    fetch('api-notificaciones.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `accion=marcar_leida&notificacion_id=${notifId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarNotificaciones();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Marcar todas como leídas
function marcarTodasLeidas() {
    fetch('api-notificaciones.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'accion=marcar_todas_leidas'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarNotificaciones();
            mostrarMensajeExito('Todas las notificaciones marcadas como leídas');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Verificar estado del servicio
function verificarEstadoServicio() {
    fetch('api-notificaciones.php?accion=obtener_alertas_sistema')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarBarraEstado(data.data.estado_servicio);
                mostrarAlertas(data.data.alertas);
            }
        })
        .catch(error => console.error('Error al verificar estado:', error));
}

// Mostrar barra de estado del servicio
function mostrarBarraEstado(estado) {
    const statusBar = document.getElementById('serviceStatusBar');
    const statusMessage = document.getElementById('serviceStatusMessage');
    
    if (!estado.servicio_activo) {
        statusBar.className = 'service-status-bar inactive';
        statusMessage.textContent = estado.mensaje_usuario;
        statusBar.style.display = 'block';
    } else if (estado.gruas_disponibles === 0) {
        statusBar.className = 'service-status-bar warning';
        statusMessage.textContent = estado.mensaje_usuario;
        statusBar.style.display = 'block';
    } else {
        statusBar.style.display = 'none';
    }
}

// Mostrar alertas
function mostrarAlertas(alertas) {
    if (alertas.length === 0) return;
    
    const alertBanner = document.getElementById('alertBanner');
    const alerta = alertas[0]; // Mostrar la primera alerta
    
    alertBanner.innerHTML = `
        <div class="alert alert-${alerta.tipo} alert-dismissible fade show" role="alert">
            <i class="fas ${alerta.icono}"></i>
            <strong>${alerta.titulo}:</strong> ${alerta.mensaje}
            <button type="button" class="btn-close" onclick="cerrarAlerta()"></button>
        </div>
    `;
    alertBanner.style.display = 'block';
    
    // Auto-cerrar después de 10 segundos
    setTimeout(() => {
        cerrarAlerta();
    }, 10000);
}

// Cerrar alerta
function cerrarAlerta() {
    document.getElementById('alertBanner').style.display = 'none';
}

// Mostrar mensaje de éxito temporal
function mostrarMensajeExito(mensaje) {
    const alertBanner = document.getElementById('alertBanner');
    alertBanner.innerHTML = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> ${mensaje}
        </div>
    `;
    alertBanner.style.display = 'block';
    
    setTimeout(() => {
        alertBanner.style.display = 'none';
    }, 3000);
}

// Inicializar widget
document.addEventListener('DOMContentLoaded', function() {
    // Cargar notificaciones iniciales
    cargarNotificaciones();
    
    // Verificar estado del servicio
    verificarEstadoServicio();
    
    // Actualizar cada 30 segundos
    setInterval(() => {
        cargarNotificaciones();
        verificarEstadoServicio();
    }, 30000);
});

// Reproducir sonido de notificación (opcional)
function playNotificationSound() {
    // Puedes agregar un archivo de audio aquí
    // const audio = new Audio('notification.mp3');
    // audio.play();
}
</script>

