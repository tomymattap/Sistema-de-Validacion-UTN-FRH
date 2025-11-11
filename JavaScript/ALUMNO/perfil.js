document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profile-form');
    const editBtn = document.getElementById('edit-btn');
    const saveBtn = document.getElementById('save-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const messageContainer = document.getElementById('message-container');
    const editableFields = form.querySelectorAll('.edit-input');
    const displayFields = form.querySelectorAll('.display-value');

    /**
     * Activa o desactiva el modo de edición para todo el formulario.
     * @param {boolean} isEditing - True para activar el modo edición, false para desactivarlo.
     */
    function toggleFormEditMode(isEditing) {
        // Muestra/oculta los campos de texto y los inputs
        displayFields.forEach(span => span.style.display = isEditing ? 'none' : 'inline');
        editableFields.forEach(input => {
            input.style.display = isEditing ? 'inline-block' : 'none';
            input.disabled = !isEditing;
        });
        
        // Muestra/oculta los botones de acción
        editBtn.style.display = isEditing ? 'none' : 'inline-flex';
        saveBtn.style.display = isEditing ? 'inline-flex' : 'none';
        cancelBtn.style.display = isEditing ? 'inline-flex' : 'none';
        
        // Pone el foco en el primer campo editable
        if (isEditing) {
            editableFields[0].focus();
        }
    }

    // Evento para el botón "Editar"
    editBtn.addEventListener('click', () => {
        toggleFormEditMode(true);
    });

    // Evento para el botón "Cancelar"
    cancelBtn.addEventListener('click', () => {
        // Restaura los valores originales de los inputs desde los spans
        editableFields.forEach(input => {
            const fieldName = input.name;
            const displaySpan = document.querySelector(`[data-field="${fieldName}"] .display-value`);
            if (displaySpan) {
                input.value = displaySpan.textContent;
            }
        });
        toggleFormEditMode(false);
        showMessage('', true, true); // Oculta cualquier mensaje
    });

    // Evento para el envío del formulario (botón "Guardar Cambios")
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch('actualizar_perfil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(result => {
            showMessage(result.message, result.success);
            if (result.success) {
                // Actualiza los textos de los spans con los nuevos valores
                document.querySelector('[data-field="email"] .display-value').textContent = data.email;
                document.querySelector('[data-field="telefono"] .display-value').textContent = data.telefono;
                toggleFormEditMode(false);
            }
        })
        .catch(error => {
            showMessage('Ocurrió un error de conexión.', false);
            console.error('Error:', error);
        });
    });

    /**
     * Muestra un mensaje de feedback al usuario.
     * @param {string} message - El mensaje a mostrar.
     * @param {boolean} isSuccess - True si es un mensaje de éxito, false si es de error.
     * @param {boolean} [hide=false] - Si es true, oculta el contenedor de mensajes.
     */
    function showMessage(message, isSuccess) {
        messageContainer.textContent = message;
        messageContainer.className = isSuccess ? 'message-container success' : 'message-container error';
        messageContainer.style.display = 'block';

        // Oculta el mensaje después de 5 segundos
        setTimeout(() => { messageContainer.style.display = 'none'; }, 5000);
    }
});