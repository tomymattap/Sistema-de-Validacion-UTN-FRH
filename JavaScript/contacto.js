document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.querySelector('.contact-form');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const formFields = contactForm.querySelectorAll('.form-field');

    // Ocultar mensajes de notificación que puedan estar visibles
    const hideMessages = () => {
        successMessage.classList.remove('visible');
        errorMessage.classList.remove('visible');
    };

    contactForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Siempre prevenir el envío por defecto

        let allFieldsFilled = true;
        formFields.forEach(field => {
            if (field.value.trim() === '') {
                allFieldsFilled = false;
            }
        });

        if (allFieldsFilled) {
            // Si todos los campos están llenos, mostrar éxito
            hideMessages();
            successMessage.classList.add('visible');
            contactForm.reset();

            // Ocultar el mensaje de éxito después de 20 segundos
            setTimeout(hideMessages, 20000);
        } else {
            // Si algún campo está vacío, mostrar error
            hideMessages();
            errorMessage.classList.add('visible');

            // Ocultar el mensaje de error después de 5 segundos
            setTimeout(hideMessages, 5000);
        }
    });
});