/*
    ARCHIVO: contacto.js
    DESCRIPCIÓN: Script para la página de contacto.
    - Gestiona el envío del formulario de contacto de forma asíncrona (usando fetch).
    - Muestra mensajes de éxito o error al usuario sin recargar la página.
    - Realiza la validación de campos antes del envío.
*/

document.addEventListener('DOMContentLoaded', () => {
    // --- SELECCIÓN DE ELEMENTOS DEL DOM ---
    const contactForm = document.querySelector('.contact-form'); // El formulario de contacto
    const successMessage = document.getElementById('success-message'); // Div para mensajes de éxito
    const errorMessage = document.getElementById('error-message');   // Div para mensajes de error

    /**
     * Oculta los mensajes de éxito y error eliminando la clase 'visible'.
     */
    const hideMessages = () => {
        successMessage.classList.remove('visible');
        errorMessage.classList.remove('visible');
    };

    // --- EVENTO DE ENVÍO DEL FORMULARIO ---
    contactForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Previene el envío tradicional del formulario que recargaría la página.

        hideMessages(); // Oculta mensajes previos antes de un nuevo envío.

        // --- VALIDACIÓN DEL LADO DEL CLIENTE ---
        let isValid = true;
        const requiredFields = contactForm.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
            }
        });

        if (!isValid) {
            errorMessage.textContent = "Por favor, complete todos los campos requeridos.";
            errorMessage.classList.add('visible');
            return; // Detiene la ejecución si el formulario no es válido.
        }

        // Recolecta los datos del formulario.
        const formData = new FormData(contactForm);

        // --- PETICIÓN FETCH PARA ENVIAR LOS DATOS ---
        fetch('../PHP/guardar_contacto.php', {      //o enviar.php
            method: 'POST',
            body: formData
        })
        .then(response => response.text()) // Convierte la respuesta del servidor a texto.
        .then(data => {
            console.log('Respuesta del servidor:', data); // Muestra la respuesta en consola para depuración.

            // Comprueba si la respuesta del servidor indica éxito.
            if (data.includes('✅')) { // El backend (guardar_contacto.php) debería devolver este emoji en caso de éxito.
                successMessage.textContent = "Mensaje enviado correctamente.";
                successMessage.classList.add('visible'); // Muestra el mensaje de éxito.
                contactForm.reset(); // Limpia los campos del formulario.
                setTimeout(hideMessages, 20000); // Oculta el mensaje de éxito después de 20 segundos.
            } else {
                // Si la respuesta no indica éxito.
                errorMessage.textContent = "Error al enviar el mensaje. Intente nuevamente.";
                errorMessage.classList.add('visible'); // Muestra el mensaje de error.
                setTimeout(hideMessages, 5000); // Oculta el mensaje después de 5 segundos.
            }
        })
        .catch(error => {
            // --- MANEJO DE ERRORES DE CONEXIÓN ---
            console.error('Error:', error); // Muestra el error en consola.
            errorMessage.textContent = "Error de conexión. Intente más tarde.";
            errorMessage.classList.add('visible'); // Muestra un error de conexión.
            setTimeout(hideMessages, 5000);
        });
    });
});