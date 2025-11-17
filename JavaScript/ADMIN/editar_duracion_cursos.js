document.addEventListener('DOMContentLoaded', function() {
    // Resaltar fila al modificar una fecha
    document.querySelectorAll('.date-input').forEach(input => {
        input.addEventListener('change', function() {
            this.closest('tr').classList.add('modified');
        });
    });

    // --- VALIDACIÓN DE FECHAS EN EL LADO DEL CLIENTE ---
    const form = document.querySelector('form[action="confirmar_modif_fechas.php"]');
    if (form) {
        form.addEventListener('submit', function(event) {
            // Prevenir envíos múltiples si ya hay un error visible
            if (document.querySelector('.floating-error-banner.show')) return;

            let errores = [];
            const errorContainer = document.getElementById('error-container');
            errorContainer.classList.remove('show');
            errorContainer.innerHTML = '';
            const hoy = new Date().toISOString().split('T')[0]; // Formato YYYY-MM-DD

            // Limpiar errores de resaltado previos
            document.querySelectorAll('#tabla-fechas tbody tr.error').forEach(fila => {
                fila.classList.remove('error');
            });

            document.querySelectorAll('#tabla-fechas tbody tr').forEach(fila => {
                const nombreCurso = fila.querySelector('input[type="hidden"]').value;
                const inputInicio = fila.querySelector('input[name*="[inicio]"]');
                const inputFin = fila.querySelector('input[name*="[fin]"]');

                const inicioNuevo = inputInicio.value;
                const finNuevo = inputFin.value;
                const inicioOriginal = inputInicio.getAttribute('data-original-value');

                let inicioModificado = (inicioNuevo !== inicioOriginal);

                // 1. Validar si la fecha de inicio fue modificada y es anterior a hoy.
                if (inicioModificado && inicioNuevo && inicioNuevo < hoy) {
                    errores.push(`- Para el curso '${nombreCurso}', la nueva fecha de inicio no puede ser anterior a la fecha actual.`);
                }

                // 2. Si se define una fecha de fin, la de inicio es obligatoria.
                // Esta validación se aplica siempre, no solo en cambios, para mantener la coherencia.
                if (finNuevo && !inicioNuevo) {
                    errores.push(`- Para el curso '${nombreCurso}', si se define una fecha de fin, la fecha de inicio es obligatoria.`);
                }

                // 3. La fecha de fin no puede ser anterior a la de inicio.
                if (inicioNuevo && finNuevo && finNuevo < inicioNuevo) {
                    errores.push(`- Para el curso '${nombreCurso}', la fecha de fin no puede ser anterior a su fecha de inicio.`);
                }
            });

            if (errores.length > 0) {
                // Prevenir el envío del formulario
                event.preventDefault();

                // Construir el mensaje de error en HTML
                let errorHTML = `
                    <div class="confirmacion-header">
                        <div style="display: flex; align-items: center;">
                            <i class="fas fa-times-circle" style="color: var(--color-secundario-4); margin-right: 10px;"></i>
                            <h3>Error de validación</h3>
                        </div>
                        <button type="button" class="close-banner-btn" aria-label="Cerrar">&times;</button>
                    </div>
                    <p>Se encontraron los siguientes errores:</p>
                    <ul style="text-align: left; margin-left: 20px; list-style-type: disc;">
                        ${errores.map(e => `<li>${e.substring(2)}</li>`).join('')}
                    </ul>
                    <p style="margin-top: 1rem;">Por favor, corrija los datos antes de continuar.</p>
                `;

                errorContainer.innerHTML = errorHTML;
                errorContainer.classList.add('show');

                // Cerrar el cartel al hacer clic en la 'x'
                errorContainer.querySelector('.close-banner-btn').addEventListener('click', () => {
                    errorContainer.classList.remove('show');
                });

                // Ocultar automáticamente después de 10 segundos
                setTimeout(() => { errorContainer.classList.remove('show'); }, 10000);
            }
        });
    }
});
