document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.main-nav a');
    const scrollToTopBtn = document.querySelector('.scroll-to-top-btn');
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const mobileNav = document.querySelector('.mobile-nav');

    // Simulación de estado de sesión (reemplazar con lógica real)

    // Ajustar rutas para la página de inicio de sesión
    const loginPagePath = currentPage.includes('index.html') ? 'HTML/iniciosesion.html' : 'iniciosesion.html';

    // Navegación activa
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop();
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });

    // Hamburger menu toggle
    if (hamburgerMenu && mobileNav) {
        hamburgerMenu.addEventListener('click', () => {
            hamburgerMenu.classList.toggle('active');
            mobileNav.classList.toggle('active');
        });
    }

    // Lógica del botón "Scroll to Top"
    if (scrollToTopBtn) {
        scrollToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

function initializeDropdown() {
    const userMenuToggle = document.querySelector('.user-menu-toggle');
    if (userMenuToggle) {
        const dropdownMenu = userMenuToggle.nextElementSibling;
        userMenuToggle.addEventListener('click', () => {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        window.addEventListener('click', (e) => {
            if (!userMenuToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.style.display = 'none';
            }
        });
    }
}