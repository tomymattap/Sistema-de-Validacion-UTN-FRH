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
});
