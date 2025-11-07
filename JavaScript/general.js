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
    }

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
    const currentPath = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.main-nav a, .off-canvas-menu a');

    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href').split('/').pop();
        if (linkPath === currentPath) {
            link.classList.add('active');
        }
    });

    // ----- Lógica para desplegar el submenú en móvil -----
    const setupMobileSubmenu = (mobileMenuToggle) => {
        mobileMenuToggle.addEventListener('click', (e) => {
            e.preventDefault();
            const submenu = mobileMenuToggle.nextElementSibling;
            submenu.classList.toggle('active');
            const icon = mobileMenuToggle.querySelector('i');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });
    };

    const mobileSessionSection = document.getElementById('mobile-session-section');
    if (mobileSessionSection) {
        const observer = new MutationObserver((mutationsList, observer) => {
            for (const mutation of mutationsList) {
                if (mutation.type === 'childList') {
                    const mobileMenuToggle = document.querySelector('.user-menu-toggle-mobile');
                    if (mobileMenuToggle) {
                        setupMobileSubmenu(mobileMenuToggle);
                        observer.disconnect(); // Detener el observador una vez que el elemento se ha encontrado y configurado
                    }
                }
            }
        });

        observer.observe(mobileSessionSection, { childList: true, subtree: true });
    }

    // --- SIMULACIÓN DE SESIÓN DE USUARIO ---
    // En una aplicación real, esta información vendría del servidor.
    // Cambia el valor de 'userRole' para probar los diferentes estados:
    // 'GUEST', 'ALUMNO', 'ADMIN'
    const user = {
        role: 'GUEST', // O 'ALUMNO', 'ADMIN'
        name: 'Juan Pérez'
    };

    const sessionControls = document.getElementById('session-controls');
    const footerDynamicNav = document.getElementById('footer-dynamic-nav');

    function updateUIForUserRole(user) {
        // Limpiar contenedores
        sessionControls.innerHTML = '';
        footerDynamicNav.innerHTML = '';

        const currentPage = window.location.pathname.split('/').pop();
        const loginPagePath = currentPage.includes('index.html') || currentPage === '' ? 'HTML/iniciosesion.php' : 'iniciosesion.php';

        if (user.role === 'GUEST') {
            // Header
            sessionControls.innerHTML = `<a href="${loginPagePath}" class="btn-sesion">Iniciar Sesión</a>`;
            
            // Footer
            footerDynamicNav.innerHTML = `
                <h4>Acceso</h4>
                <ul>
                <br>
                    <li><a href="${loginPagePath}">Iniciar Sesión</a></li>
                </ul>
            `;
        } else {
            let menuItems = '';
            let roleName = '';

            if (user.role === 'ADMIN') {
                roleName = 'ADMIN';
                menuItems = `
                    <li><a href="#gestionarinscriptos">Gesionar Inscriptos</a></li>
                    <li><a href="#gestionar-cursos">Gestionar Cursos</a></li>
                    <li><a href="#emitir-certificados">Emitir Certificados</a></li>
                    <li><a href="#gestionaradmins">Gestionar Administradores</a></li>
                `;
            } else if (user.role === 'ALUMNO') {
                roleName = 'ALUMNO';
                menuItems = `
                    <li><a href="#mi-perfil">Mi Perfil</a></li>
                    <li><a href="#certificados">Certificados</a></li>
                    <li><a href="#inscripciones">Inscripciones</a></li>
                `;
            }

            // Header
            sessionControls.innerHTML = `
                <div class="user-menu-toggle">
                    <span>Hola, ${user.name.split(' ')[0]}</span> | <strong>${roleName}</strong>
                </div>
                <div class="dropdown-menu">
                    <ul>${menuItems}</ul>
                </div>
            `;

            // Footer
            footerDynamicNav.innerHTML = `
                <h4>${roleName}</h4>
                <ul>${menuItems}</ul>
            `;
        }
    }

    // Inicializar la UI con el rol de usuario simulado
    updateUIForUserRole(user);
});