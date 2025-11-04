        </main>
    </div>

    <!-- Scripts comunes -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Scripts adicionales específicos de la página -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Script común para funcionalidad de la barra lateral -->
    <script>
        // Funcionalidad común para todas las páginas
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar navegación por teclado
            const sidebarElements = document.querySelectorAll('.sidebar_element[tabindex="0"]');
            
            sidebarElements.forEach(element => {
                element.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });
            
            // Resaltar elemento activo en la barra lateral
            const currentPage = window.location.pathname.split('/').pop();
            const sidebarLinks = document.querySelectorAll('.sidebar_link');
            
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.closest('.sidebar_element').classList.add('active');
                }
            });
        });
        
        // Función para mostrar secciones (compatible con páginas existentes)
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        }
        
        // Función para mostrar ayuda
        function showHelp() {
            alert('Sistema de Grúas DBACK\n\nNavega por el menú lateral para acceder a las diferentes funcionalidades del sistema.');
        }
    </script>
</body>
</html>
