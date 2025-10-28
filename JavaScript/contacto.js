document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.querySelector('.contact-form');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const formFields = contactForm.querySelectorAll('.form-field');

    const hideMessages = () => {
        successMessage.classList.remove('visible');
        errorMessage.classList.remove('visible');
    };

    contactForm.addEventListener('submit', (event) => {
        event.preventDefault(); // prevenimos el envío normal

        hideMessages();

        // Verificar que todos los campos estén llenos
        let allFieldsFilled = true;
        formFields.forEach(field => {
            if (field.value.trim() === '') {
                allFieldsFilled = false;
            }
        });

        if (!allFieldsFilled) {
            // Mostrar error si faltan campos
            errorMessage.textContent = "Por favor, complete todos los campos.";
            errorMessage.classList.add('visible');
            setTimeout(hideMessages, 5000);
            return;
        }

        // Si todo está completo, enviamos el formulario con fetch()
        const formData = new FormData(contactForm);

        fetch('../PHP/enviar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log('Respuesta del servidor:', data);

            if (data.includes('✅')) {
                successMessage.textContent = "Mensaje enviado correctamente.";
                successMessage.classList.add('visible');
                contactForm.reset();
                setTimeout(hideMessages, 20000);
            } else {
                errorMessage.textContent = "Error al enviar el mensaje. Intente nuevamente.";
                errorMessage.classList.add('visible');
                setTimeout(hideMessages, 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorMessage.textContent = "Error de conexión. Intente más tarde.";
            errorMessage.classList.add('visible');
            setTimeout(hideMessages, 5000);
        });
    });
});
