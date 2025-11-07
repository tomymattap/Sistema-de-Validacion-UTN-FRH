document.addEventListener('DOMContentLoaded', () => {
    // Lógica de validación del formulario
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