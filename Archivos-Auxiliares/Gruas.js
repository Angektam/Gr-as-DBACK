// script.js - Gestión de Grúas

document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let cranes = [];
    let currentPage = 1;
    const itemsPerPage = 10;
    
    // Elementos del DOM
    const addCraneBtn = document.getElementById('addCraneBtn');
    const craneModal = document.getElementById('craneModal');
    const craneForm = document.getElementById('craneForm');
    const detailsModal = document.getElementById('detailsModal');
    const maintenanceModal = document.getElementById('maintenanceModal');
    const closeButtons = document.querySelectorAll('.close');
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const editFromDetails = document.getElementById('editFromDetails');
    const scheduleMaintenanceBtn = document.getElementById('scheduleMaintenanceBtn');
    const addMaintenanceBtn = document.getElementById('addMaintenanceBtn');
    const maintenanceForm = document.getElementById('maintenanceForm');
    
    // Inicialización
    init();
    
    // Funciones principales
    function init() {
        // Cargar datos iniciales (simulados)
        loadSampleData();
        
        // Configurar event listeners
        setupEventListeners();
        
        // Renderizar la tabla
        renderCranesTable();
        
        // Actualizar estadísticas
        updateStats();
    }
    
    function loadSampleData() {
        // Datos de ejemplo (en un sistema real, estos vendrían de una API)
        cranes = [
            {
                id: 'GRU001',
                model: 'Terex CTT 181',
                type: 'torre',
                capacity: 8,
                manufacturer: 'Terex',
                year: 2020,
                status: 'available',
                location: 'Almacén Central',
                nextMaintenance: '2023-12-15',
                notes: 'Nueva, en perfecto estado',
                maintenanceHistory: [],
                operationsHistory: []
            },
            {
                id: 'GRU002',
                model: 'Liebherr LTM 1050',
                type: 'movil',
                capacity: 50,
                manufacturer: 'Liebherr',
                year: 2018,
                status: 'in-use',
                location: 'Obra Calle Principal',
                nextMaintenance: '2023-11-20',
                notes: 'Requiere revisión de sistema hidráulico',
                maintenanceHistory: [],
                operationsHistory: []
            },
            {
                id: 'GRU003',
                model: 'Manitowoc 999',
                type: 'telescopica',
                capacity: 250,
                manufacturer: 'Manitowoc',
                year: 2019,
                status: 'maintenance',
                location: 'Taller de Mantenimiento',
                nextMaintenance: '2023-10-30',
                notes: 'En reparación de motor',
                maintenanceHistory: [],
                operationsHistory: []
            }
        ];
    }
    
    function setupEventListeners() {
        // Modal para añadir/editar grúas
        if (addCraneBtn) {
            addCraneBtn.addEventListener('click', () => {
                craneForm.reset();
                document.getElementById('modalTitle').textContent = 'Añadir Grúa';
                document.getElementById('craneId').value = '';
                craneModal.style.display = 'block';
            });
        }
        
        // Formulario de grúa
        if (craneForm) {
            craneForm.addEventListener('submit', handleCraneFormSubmit);
        }
        
        // Cerrar modales
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal');
                if (modal) modal.style.display = 'none';
            });
        });
        
        // Cerrar modales al hacer clic fuera
        window.addEventListener('click', (event) => {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
        
        // Filtros y búsqueda
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                currentPage = 1;
                renderCranesTable();
            });
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                currentPage = 1;
                renderCranesTable();
            });
        }
        
        if (typeFilter) {
            typeFilter.addEventListener('change', () => {
                currentPage = 1;
                renderCranesTable();
            });
        }
        
        // Botón editar desde detalles
        if (editFromDetails) {
            editFromDetails.addEventListener('click', () => {
                const craneId = document.getElementById('detail-id').textContent;
                editCrane(craneId);
                detailsModal.style.display = 'none';
            });
        }
        
        // Programar mantenimiento
        if (scheduleMaintenanceBtn) {
            scheduleMaintenanceBtn.addEventListener('click', () => {
                const craneId = document.getElementById('detail-id').textContent;
                document.getElementById('maintenanceCraneId').value = craneId;
                maintenanceModal.style.display = 'block';
                detailsModal.style.display = 'none';
            });
        }
        
        // Añadir mantenimiento desde historial
        if (addMaintenanceBtn) {
            addMaintenanceBtn.addEventListener('click', () => {
                const craneId = document.getElementById('detail-id').textContent;
                document.getElementById('maintenanceCraneId').value = craneId;
                maintenanceModal.style.display = 'block';
            });
        }
        
        // Formulario de mantenimiento
        if (maintenanceForm) {
            maintenanceForm.addEventListener('submit', handleMaintenanceFormSubmit);
        }
        
        // Tabs en modal de detalles
        const tabs = document.querySelectorAll('.tabs li');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.getAttribute('data-tab');
                switchTab(tabName);
            });
        });
    }
    
    function handleCraneFormSubmit(e) {
        e.preventDefault();
        
        const id = document.getElementById('craneId').value;
        const model = document.getElementById('model').value;
        const type = document.getElementById('type').value;
        const capacity = parseFloat(document.getElementById('capacity').value);
        const manufacturer = document.getElementById('manufacturer').value;
        const year = parseInt(document.getElementById('year').value);
        const status = document.getElementById('status').value;
        const location = document.getElementById('location').value;
        const nextMaintenance = document.getElementById('nextMaintenance').value;
        const notes = document.getElementById('notes').value;
        
        const craneData = {
            model,
            type,
            capacity,
            manufacturer,
            year,
            status,
            location,
            nextMaintenance,
            notes,
            maintenanceHistory: [],
            operationsHistory: []
        };
        
        if (id) {
            // Editar grúa existente
            const index = cranes.findIndex(c => c.id === id);
            if (index !== -1) {
                craneData.id = id;
                craneData.maintenanceHistory = cranes[index].maintenanceHistory;
                craneData.operationsHistory = cranes[index].operationsHistory;
                cranes[index] = craneData;
            }
        } else {
            // Añadir nueva grúa
            craneData.id = 'GRU' + (cranes.length + 1).toString().padStart(3, '0');
            cranes.push(craneData);
        }
        
        craneModal.style.display = 'none';
        renderCranesTable();
        updateStats();
    }
    
    function handleMaintenanceFormSubmit(e) {
        e.preventDefault();
        
        const craneId = document.getElementById('maintenanceCraneId').value;
        const maintenanceType = document.getElementById('maintenanceType').value;
        const maintenanceDate = document.getElementById('maintenanceDate').value;
        const technicianName = document.getElementById('technicianName').value;
        const maintenanceCost = parseFloat(document.getElementById('maintenanceCost').value) || 0;
        const maintenanceDetails = document.getElementById('maintenanceDetails').value;
        const nextMaintenanceUpdate = document.getElementById('nextMaintenanceUpdate').value;
        
        const maintenanceRecord = {
            id: 'MNT' + Date.now().toString(),
            type: maintenanceType,
            date: maintenanceDate,
            technician: technicianName,
            cost: maintenanceCost,
            details: maintenanceDetails
        };
        
        const craneIndex = cranes.findIndex(c => c.id === craneId);
        if (craneIndex !== -1) {
            cranes[craneIndex].maintenanceHistory.push(maintenanceRecord);
            cranes[craneIndex].nextMaintenance = nextMaintenanceUpdate;
            
            if (maintenanceType === 'correctivo') {
                cranes[craneIndex].status = 'maintenance';
            }
        }
        
        maintenanceModal.style.display = 'none';
        renderCranesTable();
        updateStats();
        
        // Si veníamos del modal de detalles, actualizamos el historial
        if (document.getElementById('detail-id')?.textContent === craneId) {
            renderMaintenanceHistory(craneId);
        }
    }
    
    function renderCranesTable() {
        const tableBody = document.getElementById('cranesList');
        if (!tableBody) return;
        
        // Aplicar filtros
        const filteredCranes = filterCranes();
        
        // Paginación
        const totalPages = Math.ceil(filteredCranes.length / itemsPerPage);
        const paginatedCranes = filteredCranes.slice(
            (currentPage - 1) * itemsPerPage,
            currentPage * itemsPerPage
        );
        
        // Limpiar tabla
        tableBody.innerHTML = '';
        
        // Llenar tabla
        paginatedCranes.forEach(crane => {
            const row = document.createElement('tr');
            
            // Mapear estado a clase CSS
            let statusClass;
            switch (crane.status) {
                case 'available': statusClass = 'status-available'; break;
                case 'in-use': statusClass = 'status-in-use'; break;
                case 'maintenance': statusClass = 'status-maintenance'; break;
                default: statusClass = '';
            }
            
            // Mapear tipo a nombre legible
            let typeName;
            switch (crane.type) {
                case 'torre': typeName = 'Grúa Torre'; break;
                case 'movil': typeName = 'Grúa Móvil'; break;
                case 'telescopica': typeName = 'Grúa Telescópica'; break;
                case 'portuaria': typeName = 'Grúa Portuaria'; break;
                default: typeName = crane.type;
            }
            
            row.innerHTML = `
                <td>${crane.id}</td>
                <td>${crane.model}</td>
                <td>${typeName}</td>
                <td>${crane.capacity}</td>
                <td>${crane.location}</td>
                <td><span class="status-badge ${statusClass}">${getStatusText(crane.status)}</span></td>
                <td>${formatDate(crane.nextMaintenance)}</td>
                <td class="actions">
                    <button class="view-btn" data-id="${crane.id}">Ver</button>
                    <button class="edit-btn" data-id="${crane.id}">Editar</button>
                    <button class="delete-btn" data-id="${crane.id}">Eliminar</button>
                </td>
            `;
            
            tableBody.appendChild(row);
        });
        
        // Configurar event listeners para los botones de acción
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => viewCrane(btn.getAttribute('data-id')));
        });
        
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => editCrane(btn.getAttribute('data-id')));
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => deleteCrane(btn.getAttribute('data-id')));
        });
        
        // Renderizar paginación
        renderPagination(totalPages);
    }
    
    function filterCranes() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilterValue = statusFilter.value;
        const typeFilterValue = typeFilter.value;
        
        return cranes.filter(crane => {
            // Filtrar por búsqueda
            const matchesSearch = 
                crane.id.toLowerCase().includes(searchTerm) ||
                crane.model.toLowerCase().includes(searchTerm);
            
            // Filtrar por estado
            const matchesStatus = 
                statusFilterValue === 'all' || 
                crane.status === statusFilterValue ||
                (statusFilterValue === 'in-use' && crane.status === 'in-use');
            
            // Filtrar por tipo
            const matchesType = 
                typeFilterValue === 'all' || 
                crane.type === typeFilterValue;
            
            return matchesSearch && matchesStatus && matchesType;
        });
    }
    
    function renderPagination(totalPages) {
        const pagination = document.getElementById('pagination');
        if (!pagination) return;
        
        pagination.innerHTML = '';
        
        if (totalPages <= 1) return;
        
        // Botón Anterior
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Anterior';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderCranesTable();
            }
        });
        pagination.appendChild(prevBtn);
        
        // Números de página
        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = currentPage === i ? 'active' : '';
            pageBtn.addEventListener('click', () => {
                currentPage = i;
                renderCranesTable();
            });
            pagination.appendChild(pageBtn);
        }
        
        // Botón Siguiente
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Siguiente';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderCranesTable();
            }
        });
        pagination.appendChild(nextBtn);
    }
    
    function updateStats() {
        document.getElementById('totalCranes').textContent = cranes.length;
        document.getElementById('availableCranes').textContent = cranes.filter(c => c.status === 'available').length;
        document.getElementById('inUseCranes').textContent = cranes.filter(c => c.status === 'in-use').length;
        document.getElementById('maintenanceCranes').textContent = cranes.filter(c => c.status === 'maintenance').length;
    }
    
    function viewCrane(craneId) {
        const crane = cranes.find(c => c.id === craneId);
        if (!crane) return;
        
        // Mostrar información básica
        document.getElementById('detail-id').textContent = crane.id;
        document.getElementById('detail-model').textContent = crane.model;
        document.getElementById('detail-type').textContent = getTypeText(crane.type);
        document.getElementById('detail-capacity').textContent = `${crane.capacity} ton`;
        document.getElementById('detail-manufacturer').textContent = crane.manufacturer;
        document.getElementById('detail-year').textContent = crane.year;
        document.getElementById('detail-status').textContent = getStatusText(crane.status);
        document.getElementById('detail-location').textContent = crane.location;
        document.getElementById('detail-nextMaintenance').textContent = formatDate(crane.nextMaintenance);
        document.getElementById('detail-notes').textContent = crane.notes;
        
        // Mostrar historial de mantenimiento
        renderMaintenanceHistory(craneId);
        
        // Mostrar historial de operaciones
        renderOperationsHistory(craneId);
        
        // Activar primera pestaña
        switchTab('info');
        
        // Mostrar modal
        detailsModal.style.display = 'block';
    }
    
    function editCrane(craneId) {
        const crane = cranes.find(c => c.id === craneId);
        if (!crane) return;
        
        document.getElementById('modalTitle').textContent = 'Editar Grúa';
        document.getElementById('craneId').value = crane.id;
        document.getElementById('model').value = crane.model;
        document.getElementById('type').value = crane.type;
        document.getElementById('capacity').value = crane.capacity;
        document.getElementById('manufacturer').value = crane.manufacturer;
        document.getElementById('year').value = crane.year;
        document.getElementById('status').value = crane.status;
        document.getElementById('location').value = crane.location;
        document.getElementById('nextMaintenance').value = crane.nextMaintenance;
        document.getElementById('notes').value = crane.notes;
        
        craneModal.style.display = 'block';
    }
    
    function deleteCrane(craneId) {
        if (confirm('¿Estás seguro de que deseas eliminar esta grúa? Esta acción no se puede deshacer.')) {
            cranes = cranes.filter(c => c.id !== craneId);
            renderCranesTable();
            updateStats();
        }
    }
    
    function renderMaintenanceHistory(craneId) {
        const crane = cranes.find(c => c.id === craneId);
        if (!crane) return;
        
        const maintenanceLog = document.getElementById('maintenanceLog');
        if (!maintenanceLog) return;
        
        maintenanceLog.innerHTML = '';
        
        if (crane.maintenanceHistory.length === 0) {
            maintenanceLog.innerHTML = '<p>No hay registros de mantenimiento.</p>';
            return;
        }
        
        const table = document.createElement('table');
        table.innerHTML = `
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Técnico</th>
                    <th>Costo</th>
                    <th>Detalles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;
        
        const tbody = table.querySelector('tbody');
        
        crane.maintenanceHistory.forEach(record => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${formatDate(record.date)}</td>
                <td>${getMaintenanceTypeText(record.type)}</td>
                <td>${record.technician}</td>
                <td>$${record.cost.toFixed(2)}</td>
                <td>${record.details}</td>
                <td>
                    <button class="delete-maintenance-btn" data-id="${record.id}">Eliminar</button>
                </td>
            `;
            tbody.appendChild(row);
        });
        
        maintenanceLog.appendChild(table);
        
        // Event listeners para botones de eliminar mantenimiento
        document.querySelectorAll('.delete-maintenance-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const recordId = this.getAttribute('data-id');
                deleteMaintenanceRecord(craneId, recordId);
            });
        });
    }
    
    function renderOperationsHistory(craneId) {
        const crane = cranes.find(c => c.id === craneId);
        if (!crane) return;
        
        const operationsLog = document.getElementById('operationsLog');
        if (!operationsLog) return;
        
        operationsLog.innerHTML = '';
        
        if (crane.operationsHistory.length === 0) {
            operationsLog.innerHTML = '<p>No hay registros de operaciones.</p>';
            return;
        }
        
        // Similar a renderMaintenanceHistory pero para operaciones
        // (Implementar según necesidades específicas)
    }
    
    function deleteMaintenanceRecord(craneId, recordId) {
        if (confirm('¿Estás seguro de que deseas eliminar este registro de mantenimiento?')) {
            const craneIndex = cranes.findIndex(c => c.id === craneId);
            if (craneIndex !== -1) {
                cranes[craneIndex].maintenanceHistory = cranes[craneIndex].maintenanceHistory.filter(
                    record => record.id !== recordId
                );
                
                // Si estamos viendo los detalles de esta grúa, actualizar la vista
                if (document.getElementById('detail-id')?.textContent === craneId) {
                    renderMaintenanceHistory(craneId);
                }
            }
        }
    }
    
    function switchTab(tabName) {
        // Ocultar todos los contenidos de pestañas
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Desactivar todas las pestañas
        document.querySelectorAll('.tabs li').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Activar la pestaña seleccionada
        const tab = document.querySelector(`.tabs li[data-tab="${tabName}"]`);
        if (tab) {
            tab.classList.add('active');
        }
        
        // Mostrar el contenido correspondiente
        const content = document.getElementById(tabName);
        if (content) {
            content.classList.add('active');
        }
    }
    
    // Funciones auxiliares
    function getStatusText(status) {
        switch (status) {
            case 'available': return 'Disponible';
            case 'in-use': return 'En operación';
            case 'maintenance': return 'En mantenimiento';
            default: return status;
        }
    }
    
    function getTypeText(type) {
        switch (type) {
            case 'torre': return 'Grúa Torre';
            case 'movil': return 'Grúa Móvil';
            case 'telescopica': return 'Grúa Telescópica';
            case 'portuaria': return 'Grúa Portuaria';
            default: return type;
        }
    }
    
    function getMaintenanceTypeText(type) {
        switch (type) {
            case 'preventivo': return 'Preventivo';
            case 'correctivo': return 'Correctivo';
            case 'revision': return 'Revisión Rutinaria';
            default: return type;
        }
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'No programado';
        
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('es-ES', options);
    }
});