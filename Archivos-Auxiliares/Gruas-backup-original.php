<?php
session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: Login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grúas</title>
        <link rel="stylesheet" href="./CSS/MenuAdmin.CSS">
    <!-- Añadir Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
 <style>
        /* Estilos CSS (se mantienen igual) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background-color: #f5f5f5;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 20px;
        }

        header {
            margin-bottom: 30px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #3498db;
            text-decoration: none;
            margin-bottom: 15px;
        }

        .back-button svg {
            color: currentColor;
        }

        /* Dashboard */
        .dashboard {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stat-card h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }

        /* Action Button */
        .action-button-container {
            margin-bottom: 20px;
        }

        .main-action-button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .main-action-button:hover {
            background-color: #2980b9;
        }

        /* Filter Controls */
        .filter-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            min-width: 250px;
        }

        .search-box button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        .filters {
            display: flex;
            gap: 10px;
        }

        .filters select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: white;
        }

        /* Table */
        .cranes-list {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #7f8c8d;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.Activa {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.Mantenimiento {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.Inactiva {
            background-color: #f8d7da;
            color: #721c24;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .actions button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .view-btn {
            background-color: #17a2b8;
            color: white;
        }

        .edit-btn {
            background-color: #ffc107;
            color: #212529;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background-color: white;
            cursor: pointer;
            border-radius: 4px;
        }

        .pagination button.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            position: relative;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group textarea {
            min-height: 100px;
        }

        .full-width {
            grid-column: span 2;
        }

        #saveBtn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 20px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            list-style: none;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tabs li {
            padding: 10px 20px;
            cursor: pointer;
            position: relative;
        }

        .tabs li.active {
            color: #3498db;
        }

        .tabs li.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #3498db;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            .filter-controls {
                flex-direction: column;
            }
            .search-box {
                width: 100%;
            }
            .search-box input {
                flex-grow: 1;
                min-width: auto;
            }
            .filters {
                width: 100%;
            }
            .filters select {
                flex-grow: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Barra lateral -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar_header">
            <i class="fas fa-crane sidebar_icon"></i>
            <span class="sidebar_text">Grúas DBACK</span>
        </div>

       
    <ul class="sidebar_list" role="menubar">
      <li class="sidebar_element" role="menuitem">
        <a href="MenuAdmin.PHP" class="sidebar_link">
          <i class="bi bi-house sidebar_icon"></i>
          <span class="sidebar_text">Inicio</span>
        </a>
      </li>
      
      <li class="sidebar_element" role="menuitem">
        <a href="Gruas.php" class="sidebar_link">
          <i class="bi bi-truck sidebar_icon"></i>
          <span class="sidebar_text">Grúas</span>
        </a>
      </li>
      
      <li class="sidebar_element" role="menuitem">
        <a href="Gastos.php" class="sidebar_link">
          <i class="bi bi-cash-coin sidebar_icon"></i>
          <span class="sidebar_text">Gastos</span>
        </a>
      </li>
      
      <li class="sidebar_element" role="menuitem">
        <a href="Empleados.php" class="sidebar_link">
          <i class="bi bi-people sidebar_icon"></i>
          <span class="sidebar_text">Empleados</span>
        </a>
      </li>

      <li class="sidebar_element active" role="menuitem" aria-current="page">
        <a href="procesar-solicitud.php" class="sidebar_link">
          <i class="bi bi-clipboard2-check sidebar_icon"></i>
          <span class="sidebar_text">Panel de Solicitud</span>
        </a>
      </li>
    </ul>
<div class="sidebar_footer">
    <div class="sidebar_element" role="contentinfo">
        <i class="fas fa-user-circle sidebar_icon" aria-hidden="true"></i>
        <div>
            <div class="sidebar_text sidebar_title"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></div>
            <div class="sidebar_text sidebar_info"><?php echo htmlspecialchars($_SESSION['usuario_cargo']); ?></div>
        </div>
    </div>
</div>
    </aside>

    <div class="main-content">
        <div class="container">
           <header class="admin-header">
    <nav aria-label="Navegación administrativa">
        <a href="MenuAdmin.PHP" class="back-button" aria-label="Volver al menú administrativo">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            <span>Volver al Menú</span>
        </a>
    </nav>
</header>

<style>
    .admin-header {
        background-color: #f8f9fa;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .back-button:hover, .back-button:focus {
        background-color: #e9ecef;
        color: #0056b3;
    }
    
    .back-button svg {
        transition: transform 0.2s ease;
    }
    
    .back-button:hover svg {
        transform: translateX(-2px);
    }
</style>
            <header>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div>
                        <h1>Gestión de Grúas</h1>
                        <p>Sistema de administración y seguimiento de flota de grúas</p>
                    </div>
                </div>
            </header>

            <div class="dashboard">
                <div class="stat-card">
                    <h3>Total de Grúas</h3>
                    <div class="number" id="totalCranes">0</div>
                </div>
                <div class="stat-card">
                    <h3>Grúas Activas</h3>
                    <div class="number" id="availableCranes">0</div>
                </div>
                <div class="stat-card">
                    <h3>En Mantenimiento</h3>
                    <div class="number" id="maintenanceCranes">0</div>
                </div>
                <div class="stat-card">
                    <h3>Inactivas</h3>
                    <div class="number" id="inactiveCranes">0</div>
                </div>
            </div>
            
            <!-- Botón para añadir grúas -->
            <div class="action-button-container">
                <button id="addCraneBtn" class="main-action-button">
                    <i class="fas fa-plus"></i>
                    Añadir Nueva Grúa
                </button>
            </div>
            
            <div class="filter-controls">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Buscar por placa o modelo...">
                    <button id="searchBtn">Buscar</button>
                </div>
                
                <div class="filters">
                    <select id="statusFilter">
                        <option value="all">Todos los estados</option>
                        <option value="Activa">Activa</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Inactiva">Inactiva</option>
                    </select>
                    <select id="typeFilter">
                        <option value="all">Todos los tipos</option>
                        <option value="Plataforma">Plataforma</option>
                        <option value="Arrastre">Arrastre</option>
                        <option value="Remolque">Remolque</option>
                    </select>
                </div>
            </div>
            
            <div class="cranes-list">
                <table id="cranesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cranesList">
                        <!-- Las grúas se cargarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
            
            <div class="pagination" id="pagination">
                <!-- Botones de paginación se generarán aquí -->
            </div>
        </div>
        
        <!-- Modal para añadir/editar grúa -->
        <div id="craneModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalTitle">Añadir Grúa</h2>
                <form id="craneForm">
                    <input type="hidden" id="craneId">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="placa">Placa:</label>
                            <input type="text" id="placa" required maxlength="7">
                        </div>
                        <div class="form-group">
                            <label for="marca">Marca:</label>
                            <input type="text" id="marca" required>
                        </div>
                        <div class="form-group">
                            <label for="modelo">Modelo:</label>
                            <input type="text" id="modelo" required>
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo:</label>
                            <select id="tipo" required>
                                <option value="">Seleccionar...</option>
                                <option value="Plataforma">Plataforma</option>
                                <option value="Arrastre">Arrastre</option>
                                <option value="Remolque">Remolque</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado:</label>
                            <select id="estado" required>
                                <option value="">Seleccionar...</option>
                                <option value="Activa">Activa</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Inactiva">Inactiva</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" id="saveBtn">Guardar</button>
                </form>
            </div>
        </div>
        
        <!-- Modal para ver detalles de la grúa -->
        <div id="detailsModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="detailsTitle">Detalles de Grúa</h2>
                
                <div class="tab-container">
                    <ul class="tabs">
                        <li class="active" data-tab="info">Información</li>
                        <li data-tab="maintenance">Historial de Mantenimiento</li>
                    </ul>
                </div>
                
                <div id="info" class="tab-content active">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>ID:</label>
                            <p id="detail-id"></p>
                        </div>
                        <div class="form-group">
                            <label>Placa:</label>
                            <p id="detail-placa"></p>
                        </div>
                        <div class="form-group">
                            <label>Marca:</label>
                            <p id="detail-marca"></p>
                        </div>
                        <div class="form-group">
                            <label>Modelo:</label>
                            <p id="detail-modelo"></p>
                        </div>
                        <div class="form-group">
                            <label>Tipo:</label>
                            <p id="detail-tipo"></p>
                        </div>
                        <div class="form-group">
                            <label>Estado actual:</label>
                            <p id="detail-estado"></p>
                        </div>
                    </div>
                    
                    <div class="actions">
                        <button id="editFromDetails" class="edit-btn">Editar</button>
                    </div>
                </div>
                
                <div id="maintenance" class="tab-content">
                    <button id="addMaintenanceBtn">Registrar Mantenimiento</button>
                    
                    <div class="maintenance-log" id="maintenanceLog">
                        <!-- Historial de mantenimiento se cargará aquí -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para programar mantenimiento -->
        <div id="maintenanceModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Programar Mantenimiento</h2>
                <form id="maintenanceForm">
                    <input type="hidden" id="maintenanceCraneId">
                    <div class="form-group">
                        <label for="maintenanceType">Tipo de Mantenimiento:</label>
                        <select id="maintenanceType" required>
                            <option value="">Seleccionar...</option>
                            <option value="preventivo">Preventivo</option>
                            <option value="correctivo">Correctivo</option>
                            <option value="revision">Revisión Rutinaria</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="maintenanceDate">Fecha:</label>
                        <input type="date" id="maintenanceDate" required>
                    </div>
                    <div class="form-group">
                        <label for="technicianName">Técnico Responsable:</label>
                        <input type="text" id="technicianName" required>
                    </div>
                    <div class="form-group">
                        <label for="maintenanceCost">Costo:</label>
                        <input type="number" id="maintenanceCost" min="0" step="0.01">
                    </div>
                    <div class="form-group full-width">
                        <label for="maintenanceDetails">Detalles del Mantenimiento:</label>
                        <textarea id="maintenanceDetails" required></textarea>
                    </div>
                    <button type="submit" id="saveMaintenanceBtn">Guardar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables para paginación
        const recordsPerPage = 10;
        let currentPage = 1;
        let gruas = [];

        // Referencias a elementos DOM
        const cranesList = document.getElementById('cranesList');
        const pagination = document.getElementById('pagination');
        const craneModal = document.getElementById('craneModal');
        const detailsModal = document.getElementById('detailsModal');
        const maintenanceModal = document.getElementById('maintenanceModal');
        const closeButtons = document.querySelectorAll('.close');
        const addCraneBtn = document.getElementById('addCraneBtn');
        const searchBtn = document.getElementById('searchBtn');
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const typeFilter = document.getElementById('typeFilter');
        const craneForm = document.getElementById('craneForm');
        const maintenanceForm = document.getElementById('maintenanceForm');
        const modalTitle = document.getElementById('modalTitle');
        const detailsTitle = document.getElementById('detailsTitle');
        const editFromDetailsBtn = document.getElementById('editFromDetails');
        const addMaintenanceBtn = document.getElementById('addMaintenanceBtn');
        const tabs = document.querySelectorAll('.tabs li');
        const tabContents = document.querySelectorAll('.tab-content');
        const totalCranesElement = document.getElementById('totalCranes');
        const availableCranesElement = document.getElementById('availableCranes');
        const maintenanceCranesElement = document.getElementById('maintenanceCranes');
        const inactiveCranesElement = document.getElementById('inactiveCranes');

        // Función para cargar las grúas desde el servidor
        async function fetchGruas() {
            try {
                const estado = statusFilter.value;
                const tipo = typeFilter.value;
                const busqueda = searchInput.value;
                
                const response = await fetch(`api.php?action=getGruas&estado=${estado}&tipo=${tipo}&busqueda=${encodeURIComponent(busqueda)}`);
                if (!response.ok) {
                    throw new Error('Error al obtener las grúas');
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                
                gruas = data.gruas || [];
                displayGruas(currentPage);
                updateStats();
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar las grúas: ' + error.message);
            }
        }

        // Función para actualizar las estadísticas
        async function updateStats() {
            try {
                const response = await fetch('api.php?action=getStats');
                if (!response.ok) {
                    throw new Error('Error al obtener estadísticas');
                }
                
                const stats = await response.json();
                if (stats.error) {
                    throw new Error(stats.error);
                }
                
                totalCranesElement.textContent = stats.total;
                availableCranesElement.textContent = stats.activas;
                maintenanceCranesElement.textContent = stats.mantenimiento;
                inactiveCranesElement.textContent = stats.inactivas;
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar estadísticas: ' + error.message);
            }
        }

        // Función para mostrar grúas en la tabla
        function displayGruas(page = 1) {
            cranesList.innerHTML = '';
            
            const start = (page - 1) * recordsPerPage;
            const end = start + recordsPerPage;
            const paginatedGruas = gruas.slice(start, end);
            
            if (paginatedGruas.length === 0) {
                cranesList.innerHTML = '<tr><td colspan="7" style="text-align: center">No se encontraron grúas</td></tr>';
                setupPagination(gruas);
                return;
            }
            
            paginatedGruas.forEach(grua => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${grua.ID}</td>
                    <td>${grua.Placa}</td>
                    <td>${grua.Marca}</td>
                    <td>${grua.Modelo}</td>
                    <td>${grua.Tipo}</td>
                    <td><span class="status-badge ${grua.Estado}">${grua.Estado}</span></td>
                    <td class="actions">
                        <button class="view-btn" data-id="${grua.ID}">Ver</button>
                        <button class="edit-btn" data-id="${grua.ID}">Editar</button>
                        <button class="delete-btn" data-id="${grua.ID}">Eliminar</button>
                    </td>
                `;
                cranesList.appendChild(row);
            });
            
            setupPagination(gruas);
            setupActionButtons();
        }

        // Función para configurar la paginación
        function setupPagination(gruas) {
            pagination.innerHTML = '';
            const pageCount = Math.ceil(gruas.length / recordsPerPage);
            
            if (pageCount <= 1) return;
            
            for (let i = 1; i <= pageCount; i++) {
                const button = document.createElement('button');
                button.textContent = i;
                if (i === currentPage) {
                    button.classList.add('active');
                }
                
                button.addEventListener('click', () => {
                    currentPage = i;
                    displayGruas(currentPage);
                });
                
                pagination.appendChild(button);
            }
        }

        // Función para configurar los botones de acción
        function setupActionButtons() {
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', () => viewGrua(btn.dataset.id));
            });
            
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', () => editGrua(btn.dataset.id));
            });
            
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', () => deleteGrua(btn.dataset.id));
            });
        }

        // Función para ver los detalles de una grúa
        async function viewGrua(id) {
            try {
                const response = await fetch(`api.php?action=getGrua&id=${id}`);
                if (!response.ok) {
                    throw new Error('Error al obtener la grúa');
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.grua) {
                    showGruaDetails(data.grua);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar la grúa: ' + error.message);
            }
        }

        // Función para mostrar los detalles de una grúa
        function showGruaDetails(grua) {
            detailsTitle.textContent = `Grúa ${grua.Placa}`;
            
            // Llenar información básica
            document.getElementById('detail-id').textContent = grua.ID;
            document.getElementById('detail-placa').textContent = grua.Placa;
            document.getElementById('detail-marca').textContent = grua.Marca;
            document.getElementById('detail-modelo').textContent = grua.Modelo;
            document.getElementById('detail-tipo').textContent = grua.Tipo;
            document.getElementById('detail-estado').textContent = grua.Estado;
            
            // Configurar botón de editar
            editFromDetailsBtn.dataset.id = grua.ID;
            
            // Mostrar modal
            detailsModal.style.display = 'block';
        }

        // Función para editar una grúa
        async function editGrua(id) {
            try {
                const response = await fetch(`api.php?action=getGrua&id=${id}`);
                if (!response.ok) {
                    throw new Error('Error al obtener la grúa');
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.grua) {
                    showEditForm(data.grua);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar la grúa: ' + error.message);
            }
        }

        // Función para mostrar el formulario de edición
        function showEditForm(grua) {
            modalTitle.textContent = `Editar Grúa ${grua.Placa}`;
            
            // Llenar formulario
            document.getElementById('craneId').value = grua.ID;
            document.getElementById('placa').value = grua.Placa;
            document.getElementById('marca').value = grua.Marca;
            document.getElementById('modelo').value = grua.Modelo;
            document.getElementById('tipo').value = grua.Tipo;
            document.getElementById('estado').value = grua.Estado;
            
            // Mostrar modal
            craneModal.style.display = 'block';
        }

        // Función para eliminar una grúa
        async function deleteGrua(id) {
            if (!confirm('¿Estás seguro de que deseas eliminar esta grúa?')) {
                return;
            }
            
            try {
                const response = await fetch(`api.php?action=deleteGrua&id=${id}`);
                if (!response.ok) {
                    throw new Error('Error al eliminar la grúa');
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.success) {
                    fetchGruas();
                    alert('Grúa eliminada correctamente');
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar la grúa: ' + error.message);
            }
        }

        // Función para guardar una grúa (añadir o actualizar)
        async function saveGrua(e) {
            e.preventDefault();
            
            const id = document.getElementById('craneId').value;
            const placa = document.getElementById('placa').value;
            const marca = document.getElementById('marca').value;
            const modelo = document.getElementById('modelo').value;
            const tipo = document.getElementById('tipo').value;
            const estado = document.getElementById('estado').value;
            
            try {
                const formData = new FormData();
                if (id) formData.append('id', id);
                formData.append('placa', placa);
                formData.append('marca', marca);
                formData.append('modelo', modelo);
                formData.append('tipo', tipo);
                formData.append('estado', estado);
                
                const response = await fetch('api.php?action=saveGrua', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Error al guardar la grúa');
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.success) {
                    craneModal.style.display = 'none';
                    fetchGruas();
                    alert('Grúa guardada correctamente');
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar la grúa: ' + error.message);
            }
        }

        // Función para guardar un mantenimiento
        async function saveMaintenance(e) {
            e.preventDefault();
            
            const craneId = document.getElementById('maintenanceCraneId').value;
            const tipo = document.getElementById('maintenanceType').value;
            const fecha = document.getElementById('maintenanceDate').value;
            const tecnico = document.getElementById('technicianName').value;
            const costo = document.getElementById('maintenanceCost').value;
            const detalles = document.getElementById('maintenanceDetails').value;
            
            try {
                const formData = new FormData();
                formData.append('gruaId', craneId);
                formData.append('tipo', tipo);
                formData.append('fecha', fecha);
                formData.append('tecnico', tecnico);
                formData.append('costo', costo);
                formData.append('detalles', detalles);
                
                const response = await fetch('api.php?action=saveMantenimiento', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Error al guardar el mantenimiento');
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.success) {
                    maintenanceModal.style.display = 'none';
                    alert('Mantenimiento registrado correctamente');
                    // Recargar historial de mantenimiento
                    loadMaintenanceHistory(craneId);
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar el mantenimiento: ' + error.message);
            }
        }

        // Función para cargar el historial de mantenimiento
        async function loadMaintenanceHistory(gruaId) {
            try {
                const response = await fetch(`api.php?action=getMantenimientos&gruaId=${gruaId}`);
                if (!response.ok) {
                    throw new Error('Error al obtener el historial de mantenimiento');
                }
                
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                
                displayMaintenanceHistory(data.mantenimientos || []);
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar el historial de mantenimiento: ' + error.message);
            }
        }

        // Función para mostrar el historial de mantenimiento
        function displayMaintenanceHistory(mantenimientos) {
            const maintenanceLog = document.getElementById('maintenanceLog');
            maintenanceLog.innerHTML = '';
            
            if (mantenimientos.length === 0) {
                maintenanceLog.innerHTML = '<p>No hay registros de mantenimiento</p>';
                return;
            }
            
            const table = document.createElement('table');
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Técnico</th>
                        <th>Costo</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    ${mantenimientos.map(m => `
                        <tr>
                            <td>${m.Tipo}</td>
                            <td>${m.Fecha}</td>
                            <td>${m.Tecnico}</td>
                            <td>${m.Costo ? '$' + m.Costo : 'N/A'}</td>
                            <td>${m.Detalles}</td>
                        </tr>
                    `).join('')}
                </tbody>
            `;
            
            maintenanceLog.appendChild(table);
        }

        // Función para configurar las pestañas
        function setupTabs() {
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remover clase active de todas las pestañas y contenidos
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Agregar clase active a la pestaña clickeada
                    tab.classList.add('active');
                    
                    // Mostrar el contenido correspondiente
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                    
                    // Si es la pestaña de mantenimiento, cargar el historial
                    if (tabId === 'maintenance') {
                        const gruaId = editFromDetailsBtn.dataset.id;
                        if (gruaId) {
                            loadMaintenanceHistory(gruaId);
                        }
                    }
                });
            });
        }

        // Event Listeners
        window.addEventListener('load', () => {
            fetchGruas();
            setupTabs();
        });

        // Botón para añadir nueva grúa
        addCraneBtn.addEventListener('click', () => {
            modalTitle.textContent = 'Añadir Nueva Grúa';
            craneForm.reset();
            document.getElementById('craneId').value = '';
            craneModal.style.display = 'block';
        });

        // Botón de búsqueda
        searchBtn.addEventListener('click', () => {
            currentPage = 1;
            fetchGruas();
        });

        // Buscar al presionar Enter
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                currentPage = 1;
                fetchGruas();
            }
        });

        // Filtrar al cambiar los selectores
        statusFilter.addEventListener('change', () => {
            currentPage = 1;
            fetchGruas();
        });

        typeFilter.addEventListener('change', () => {
            currentPage = 1;
            fetchGruas();
        });

        // Cerrar modales
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                craneModal.style.display = 'none';
                detailsModal.style.display = 'none';
                maintenanceModal.style.display = 'none';
            });
        });

        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', (e) => {
            if (e.target === craneModal) {
                craneModal.style.display = 'none';
            }
            if (e.target === detailsModal) {
                detailsModal.style.display = 'none';
            }
            if (e.target === maintenanceModal) {
                maintenanceModal.style.display = 'none';
            }
        });

        // Formulario de grúa
        craneForm.addEventListener('submit', saveGrua);

        // Botón de editar desde detalles
        editFromDetailsBtn.addEventListener('click', () => {
            const id = editFromDetailsBtn.dataset.id;
            if (id) {
                detailsModal.style.display = 'none';
                editGrua(id);
            }
        });

        // Botón para añadir mantenimiento
        addMaintenanceBtn.addEventListener('click', () => {
            const gruaId = editFromDetailsBtn.dataset.id;
            if (gruaId) {
                document.getElementById('maintenanceCraneId').value = gruaId;
                document.getElementById('maintenanceDate').valueAsDate = new Date();
                maintenanceForm.reset();
                maintenanceModal.style.display = 'block';
            }
        });

        // Formulario de mantenimiento
        maintenanceForm.addEventListener('submit', saveMaintenance);
    </script>
</body>
</html>