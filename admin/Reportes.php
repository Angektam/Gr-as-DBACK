<?php
// Configuración de la página
$page_title = 'Reportes de Gastos - Grúas DBACK';
$additional_css = ['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'];
$additional_js = ['https://cdn.jsdelivr.net/npm/chart.js'];

session_start();
?>

<?php include '../components/header-component.php'; ?>

<style>
/* Estilos para la página de Reportes */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.header h1 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.filters {
    background: white;
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 150px;
}

.filter-group label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.form-control {
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease;
    border-left: 5px solid #667eea;
}

.summary-card:hover {
    transform: translateY(-5px);
}

.summary-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
}

.summary-card .value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #667eea;
    margin: 0;
}

.charts-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.chart-container {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.chart-container h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 1.3rem;
    font-weight: 600;
    text-align: center;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.chart-container canvas {
    max-height: 300px;
}

.table-container {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
}

.table-container h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 1.3rem;
    font-weight: 600;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 15px 12px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

tr:hover {
    background-color: #f8f9fa;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn:active {
    transform: translateY(0);
}

/* Animaciones */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.container > * {
    animation: fadeIn 0.6s ease-out;
}

.container > *:nth-child(2) {
    animation-delay: 0.1s;
}

.container > *:nth-child(3) {
    animation-delay: 0.2s;
}

.container > *:nth-child(4) {
    animation-delay: 0.3s;
}

.container > *:nth-child(5) {
    animation-delay: 0.4s;
}

/* Responsive */
@media (max-width: 768px) {
    .header {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .header h1 {
        font-size: 2rem;
    }
    
    .header-actions {
        justify-content: center;
    }
    
    .filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .charts-container {
        grid-template-columns: 1fr;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
    
    table {
        font-size: 0.8rem;
    }
    
    th, td {
        padding: 10px 8px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 10px;
    }
    
    .header {
        padding: 20px;
    }
    
    .header h1 {
        font-size: 1.5rem;
    }
    
    .btn {
        padding: 10px 15px;
        font-size: 0.8rem;
    }
}

/* Estados de carga */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #667eea;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Mejoras visuales adicionales */
.summary-card:nth-child(1) {
    border-left-color: #28a745;
}

.summary-card:nth-child(2) {
    border-left-color: #ffc107;
}

.summary-card:nth-child(3) {
    border-left-color: #dc3545;
}

.summary-card:nth-child(1) .value {
    color: #28a745;
}

.summary-card:nth-child(2) .value {
    color: #ffc107;
}

.summary-card:nth-child(3) .value {
    color: #dc3545;
}

/* Estilos para mensajes */
.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: none;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
}
</style>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-file-invoice-dollar"></i> Reportes de Gastos</h1>
        <div class="header-actions">
            <button class="btn btn-primary" id="exportPdf">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button class="btn btn-secondary" id="exportExcel">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters">
        <div class="filter-group">
            <label for="fecha_inicio">Fecha Inicio:</label>
            <input type="date" id="fecha_inicio" class="form-control">
        </div>
        <div class="filter-group">
            <label for="fecha_fin">Fecha Fin:</label>
            <input type="date" id="fecha_fin" class="form-control">
        </div>
        <div class="filter-group">
            <label for="categoria">Categoría:</label>
            <select id="categoria" class="form-control">
                <option value="">Todas las categorías</option>
                <option value="Reparacion">Reparación</option>
                <option value="Gasto_Oficina">Gasto Oficina</option>
                <option value="Gasolina">Gasolina</option>
            </select>
        </div>
        <button class="btn btn-primary" id="aplicarFiltros">
            <i class="fas fa-filter"></i> Aplicar Filtros
        </button>
    </div>

    <!-- Resumen -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3>Total Gastos</h3>
            <div class="value" id="totalGastos">$0.00</div>
        </div>
        <div class="summary-card">
            <h3>Gastos del Mes</h3>
            <div class="value" id="gastosMes">$0.00</div>
        </div>
        <div class="summary-card">
            <h3>Promedio Diario</h3>
            <div class="value" id="promedioDiario">$0.00</div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="charts-container">
        <div class="chart-container">
            <h3>Gastos por Categoría</h3>
            <canvas id="categoriaChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Gastos por Mes</h3>
            <canvas id="mesChart"></canvas>
        </div>
    </div>

    <!-- Tabla de gastos -->
    <div class="table-container">
        <h3>Detalle de Gastos</h3>
        <table id="gastosTable">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Grúa</th>
                </tr>
            </thead>
            <tbody>
                <!-- Los datos se cargarán dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar fechas por defecto
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    
    document.getElementById('fecha_inicio').valueAsDate = firstDay;
    document.getElementById('fecha_fin').valueAsDate = today;
    
    // Variables globales para los gráficos
    let categoriaChart = null;
    let mesChart = null;
    
    // Cargar datos iniciales
    cargarDatos();
    
    // Event listeners
    document.getElementById('aplicarFiltros').addEventListener('click', cargarDatos);
    document.getElementById('exportPdf').addEventListener('click', exportarPDF);
    document.getElementById('exportExcel').addEventListener('click', exportarExcel);
    
    // Función para cargar datos
    async function cargarDatos() {
        try {
            mostrarCarga(true);
            
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            const categoria = document.getElementById('categoria').value;
            
            // Simular carga de datos (en producción esto sería una llamada AJAX)
            const datos = await simularCargaDatos(fechaInicio, fechaFin, categoria);
            
            // Actualizar resumen
            actualizarResumen(datos.resumen);
            
            // Actualizar gráficos
            actualizarGraficos(datos.graficos);
            
            // Actualizar tabla
            actualizarTabla(datos.gastos);
            
        } catch (error) {
            console.error('Error al cargar datos:', error);
            mostrarMensaje('Error al cargar los datos', 'danger');
        } finally {
            mostrarCarga(false);
        }
    }
    
    // Simular carga de datos
    async function simularCargaDatos(fechaInicio, fechaFin, categoria) {
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({
                    resumen: {
                        total: 125000.50,
                        mes: 45000.25,
                        promedio: 1500.75
                    },
                    graficos: {
                        categoria: [
                            { nombre: 'Reparación', valor: 45000 },
                            { nombre: 'Gasolina', valor: 35000 },
                            { nombre: 'Gasto Oficina', valor: 15000 }
                        ],
                        mes: [
                            { mes: 'Enero', valor: 25000 },
                            { mes: 'Febrero', valor: 30000 },
                            { mes: 'Marzo', valor: 35000 },
                            { mes: 'Abril', valor: 40000 },
                            { mes: 'Mayo', valor: 45000 },
                            { mes: 'Junio', valor: 50000 }
                        ]
                    },
                    gastos: [
                        { fecha: '2025-10-15', categoria: 'Reparación', descripcion: 'Cambio de aceite', monto: 2500, grua: 'ABC-123' },
                        { fecha: '2025-10-14', categoria: 'Gasolina', descripcion: 'Carga de combustible', monto: 1500, grua: 'XYZ-456' },
                        { fecha: '2025-10-13', categoria: 'Gasto Oficina', descripcion: 'Material de oficina', monto: 800, grua: 'DEF-789' },
                        { fecha: '2025-10-12', categoria: 'Reparación', descripcion: 'Reparación de frenos', monto: 3200, grua: 'GHI-012' },
                        { fecha: '2025-10-11', categoria: 'Gasolina', descripcion: 'Carga de combustible', monto: 1800, grua: 'JKL-345' }
                    ]
                });
            }, 1000);
        });
    }
    
    // Actualizar resumen
    function actualizarResumen(resumen) {
        document.getElementById('totalGastos').textContent = `$${resumen.total.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
        document.getElementById('gastosMes').textContent = `$${resumen.mes.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
        document.getElementById('promedioDiario').textContent = `$${resumen.promedio.toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
    }
    
    // Actualizar gráficos
    function actualizarGraficos(datos) {
        // Gráfico de categorías
        const categoriaCtx = document.getElementById('categoriaChart').getContext('2d');
        if (categoriaChart) {
            categoriaChart.destroy();
        }
        
        categoriaChart = new Chart(categoriaCtx, {
            type: 'doughnut',
            data: {
                labels: datos.categoria.map(item => item.nombre),
                datasets: [{
                    data: datos.categoria.map(item => item.valor),
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb',
                        '#f5576c',
                        '#4facfe'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${context.label}: $${context.raw.toLocaleString('es-MX')} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Gráfico mensual
        const mesCtx = document.getElementById('mesChart').getContext('2d');
        if (mesChart) {
            mesChart.destroy();
        }
        
        mesChart = new Chart(mesCtx, {
            type: 'line',
            data: {
                labels: datos.mes.map(item => item.mes),
                datasets: [{
                    label: 'Gastos Mensuales',
                    data: datos.mes.map(item => item.valor),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#667eea',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return `Gastos: $${context.raw.toLocaleString('es-MX')}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return `$${value.toLocaleString('es-MX')}`;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Actualizar tabla
    function actualizarTabla(gastos) {
        const tbody = document.querySelector('#gastosTable tbody');
        tbody.innerHTML = '';
        
        gastos.forEach(gasto => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${formatearFecha(gasto.fecha)}</td>
                <td><span class="badge badge-${getCategoriaColor(gasto.categoria)}">${gasto.categoria}</span></td>
                <td>${gasto.descripcion}</td>
                <td>$${gasto.monto.toLocaleString('es-MX', { minimumFractionDigits: 2 })}</td>
                <td>${gasto.grua}</td>
            `;
            tbody.appendChild(row);
        });
    }
    
    // Funciones auxiliares
    function formatearFecha(fecha) {
        return new Date(fecha).toLocaleDateString('es-MX');
    }
    
    function getCategoriaColor(categoria) {
        const colores = {
            'Reparación': 'primary',
            'Gasolina': 'success',
            'Gasto Oficina': 'warning'
        };
        return colores[categoria] || 'secondary';
    }
    
    function mostrarCarga(mostrar) {
        const container = document.querySelector('.container');
        if (mostrar) {
            container.classList.add('loading');
        } else {
            container.classList.remove('loading');
        }
    }
    
    function mostrarMensaje(mensaje, tipo) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${mensaje}
        `;
        
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    function exportarPDF() {
        mostrarMensaje('Funcionalidad de exportación PDF en desarrollo', 'info');
    }
    
    function exportarExcel() {
        mostrarMensaje('Funcionalidad de exportación Excel en desarrollo', 'info');
    }
});
</script>

<style>
/* Estilos adicionales para badges */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-primary {
    background-color: #667eea;
    color: white;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}
</style>

<?php include '../components/footer-component.php'; ?>