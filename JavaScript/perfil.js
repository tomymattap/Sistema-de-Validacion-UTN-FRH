document.addEventListener('DOMContentLoaded', () => {
    const editButtons = document.querySelectorAll('.edit-btn');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const field = button.previousElementSibling;
            const isEditing = field.isContentEditable;

            if (isEditing) {
                field.contentEditable = false;
                button.innerHTML = '<i class="fas fa-pencil-alt"></i>';
                // Aquí se podría agregar una llamada para guardar los cambios
            } else {
                field.contentEditable = true;
                field.focus();
                button.innerHTML = '<i class="fas fa-save"></i>';
            }
        });
    });
});
