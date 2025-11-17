document.addEventListener('DOMContentLoaded', () => {
    // ----- Menú Hamburguesa y Off-canvas -----
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const offCanvasMenu = document.getElementById('off-canvas-menu');
    const closeBtn = document.querySelector('.close-btn');

    if (hamburgerMenu && offCanvasMenu && closeBtn) {
        hamburgerMenu.addEventListener('click', () => {
            offCanvasMenu.classList.toggle('active');
        });

        closeBtn.addEventListener('click', () => {
            offCanvasMenu.classList.remove('active');
        });

        offCanvasMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', (e) => {

            // Si es el botón "Hola, Nombre", NO cerrar
            if (link.classList.contains('user-menu-toggle-mobile')) {
                return; 
            }

            // Para cualquier otro link (incluye links dentro del submenu)
            offCanvasMenu.classList.remove('active');
        });
});

    // ----- Botón de Volver Arriba -----
    const scrollToTopBtn = document.getElementById('scroll-to-top-btn');

    if (scrollToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });

        scrollToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // ----- Resaltar Opción de Menú Activa -----
    const highlightActiveLink = () => {
        const currentPath = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.main-nav a, .off-canvas-menu a');

        navLinks.forEach(link => {
            link.classList.remove('active');
            const linkPath = link.getAttribute('href').split('/').pop();
            // No marcar como activas las anclas vacías o los botones de submenú
            if (linkPath === currentPath && currentPath !== '' && link.getAttribute('href') !== '#') {
                link.classList.add('active');
            }
        });
    };

    highlightActiveLink(); // Ejecutar al cargar la página

    // ----- Lógica para desplegar el submenú en móvil -----
    const setupMobileSubmenu = (mobileMenuToggle) => {
        // Prevenir doble asignación de eventos
        if (mobileMenuToggle.dataset.menuInitialized) return;
        mobileMenuToggle.dataset.menuInitialized = 'true';

        mobileMenuToggle.addEventListener('click', (e) => {
            e.preventDefault();
            const submenu = mobileMenuToggle.nextElementSibling;
            if (submenu && submenu.classList.contains('submenu')) {
                submenu.classList.toggle('active');
                const icon = mobileMenuToggle.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                }
            }
        });
    };

    // Función para buscar y configurar el menú. Se puede llamar en cualquier momento.
    const initializeMobileSubmenu = () => {
        const mobileMenuToggle = document.querySelector('.user-menu-toggle-mobile');
        if (mobileMenuToggle) {
            setupMobileSubmenu(mobileMenuToggle);
        }
    };

    // 1. Ejecutar al cargar el DOM para los menús renderizados por el servidor (PHP)
    initializeMobileSubmenu();

    // 2. Usar MutationObserver para menús cargados dinámicamente (JS/AJAX)
    const mobileSessionSection = document.getElementById('mobile-session-section');
    if (mobileSessionSection) {
        const observer = new MutationObserver((mutationsList) => {
            // Cuando el contenido cambie, inicializar el submenú y resaltar el link activo.
            initializeMobileSubmenu();
            highlightActiveLink();
        });

        observer.observe(mobileSessionSection, { childList: true, subtree: true });
    }

    // ----- Lógica de Sesión de Usuario para Páginas Estáticas -----
    // Este bloque se encarga de mostrar "Iniciar Sesión" o el menú de usuario.
    const sessionControls = document.getElementById('session-controls');    
    const footerDynamicNav = document.getElementById('footer-dynamic-nav');

    // Solo ejecutar si los elementos existen (para no interferir con páginas de admin/estudiante que tienen su propia lógica)
    if (sessionControls && mobileSessionSection && footerDynamicNav) {

        // Determinar la ruta base correcta para el fetch
        const path = window.location.pathname;
        const fetchPath = path.includes('/HTML/') ? '../PHP/get_user_name.php' : 'PHP/get_user_name.php';
        const basePath = path.includes('/HTML/') ? '../' : '';

        fetch(fetchPath)
            .then(response => response.json())
            .then(data => {
                if (data.user_name) {
                    let desktopDropdownMenu, mobileSubmenu, footerMenu;
                    const userName = data.user_name;
                    const phpPath = `${basePath}PHP/`; // Asegura que siempre apunte a la carpeta PHP

                    const desktopMenu = `
                        <a href="#" class="btn-sesion user-menu-toggle">Hola, ${userName} <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-menu"><ul>`;

                    if (data.user_rol === 1) { // Admin
                        desktopDropdownMenu = `${desktopMenu}
                            <li><a href="${phpPath}ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="${phpPath}ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="${phpPath}ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="${phpPath}ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                            <li><a href="${phpPath}logout.php">Cerrar Sesión</a></li></ul></div>`;
                        mobileSubmenu = `<a href="#" class="user-menu-toggle-mobile">Hola, ${userName} <i class="fas fa-chevron-down"></i></a>
                            <ul class="submenu">
                                <li><a href="${phpPath}ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                                <li><a href="${phpPath}ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                                <li><a href="${phpPath}ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                <li><a href="${phpPath}ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                                <li><a href="${phpPath}logout.php">Cerrar Sesión</a></li>
                            </ul>`;
                        footerMenu = `<h4>Admin</h4><ul>
                            <br>
                            <li><a href="${phpPath}ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                            <br>
                            <li><a href="${phpPath}ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                            <br>
                            <li><a href="${phpPath}ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <br>
                            <li><a href="${phpPath}ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                        </ul>`;

                    } else if (data.user_rol === 2) { // Estudiante
                        desktopDropdownMenu = `${desktopMenu}
                            <li><a href="${phpPath}ALUMNO/perfil.php">Mi Perfil</a></li>
                            <li><a href="${phpPath}ALUMNO/inscripciones.php">Inscripciones</a></li>
                            <li><a href="${phpPath}ALUMNO/certificaciones.php">Certificaciones</a></li>
                            <li><a href="${phpPath}logout.php">Cerrar Sesión</a></li></ul></div>`;
                        mobileSubmenu = `<a href="#" class="user-menu-toggle-mobile">Hola, ${userName} <i class="fas fa-chevron-down"></i></a>
                            <ul class="submenu">
                                <li><a href="${phpPath}ALUMNO/perfil.php">Mi Perfil</a></li>
                                <li><a href="${phpPath}ALUMNO/inscripciones.php">Inscripciones</a></li>
                                <li><a href="${phpPath}ALUMNO/certificaciones.php">Certificaciones</a></li>
                                <li><a href="${phpPath}logout.php">Cerrar Sesión</a></li></ul>`;
                        footerMenu = `<h4>Estudiante</h4><ul>
                            <br>
                            <li><a href="${phpPath}ALUMNO/perfil.php">Mi Perfil</a></li>
                            <br>
                            <li><a href="${phpPath}ALUMNO/inscripciones.php">Inscripciones</a></li>
                            <br>
                            <li><a href="${phpPath}ALUMNO/certificaciones.php">Certificaciones</a></li></ul>`;
                    }

                    sessionControls.innerHTML = desktopDropdownMenu;
                    mobileSessionSection.innerHTML = mobileSubmenu;
                    footerDynamicNav.innerHTML = footerMenu;

                } else {
                    // Usuario no logueado (invitado)
                    const loginPath = `${basePath}PHP/inicio_sesion.php`;
                    sessionControls.innerHTML = `<a href="${loginPath}" class="btn-sesion">INICIAR SESIÓN</a>`;
                    mobileSessionSection.innerHTML = `<a href="${loginPath}">INICIAR SESIÓN</a>`;
                    footerDynamicNav.innerHTML = `<h4>Acceso</h4><ul><li><a href="${loginPath}">Iniciar Sesión</a></li></ul>`;
                }
            });
        }
    }
});