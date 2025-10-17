document.addEventListener('DOMContentLoaded', () => {
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
        const loginPagePath = currentPage.includes('index.html') || currentPage === '' ? 'HTML/iniciosesion.html' : 'iniciosesion.html';

        if (user.role === 'GUEST') {
            // Header
            sessionControls.innerHTML = `<a href="${loginPagePath}" class="session-btn">Iniciar Sesión</a>`;
            
            // Footer
            footerDynamicNav.innerHTML = `
                <h4>Acceso</h4>
                <ul>
                    <li><a href="${loginPagePath}">Iniciar Sesión</a></li>
                </ul>
            `;
        } else {
            let menuItems = '';
            let footerItems = '';
            let roleName = '';

            if (user.role === 'ADMIN') {
                roleName = 'ADMIN';
                menuItems = `
                    <li><a href="#ver-inscriptos">Ver Inscriptos</a></li>
                    <li><a href="#gestionar-cursos">Gestionar Cursos</a></li>
                    <li><a href="#emitir-certificados">Emitir Certificados</a></li>
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


    // --- LÓGICA DE VALIDACIÓN DEL FORMULARIO ---
    const certificateInput = document.getElementById('certificate-code');
    const validateButton = document.getElementById('validate-btn');

    certificateInput.addEventListener('input', () => {
        if (certificateInput.value.trim() !== '') {
            validateButton.disabled = false;
        } else {
            validateButton.disabled = true;
        }
    });
});
