document.addEventListener('DOMContentLoaded', function() {
    const cursoItems = document.querySelectorAll('.curso-item');
    const estadoPendiente = document.getElementById('estado-pendiente');
    const estadoAceptado = document.getElementById('estado-aceptado');
    const estadoFinalizado = document.getElementById('estado-finalizado');

    cursoItems.forEach(item => {
        const header = item.querySelector('.curso-header');
        header.addEventListener('click', function() {
            const clickedItem = this.parentElement;
            const status = clickedItem.dataset.estado;

            // Toggle active class for accordion
            if (clickedItem.classList.contains('active')) {
                clickedItem.classList.remove('active');
                // Reset status highlight if the active item is closed
                resetStatusHighlight();
            } else {
                // Close any other open item
                cursoItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                });
                // Open the clicked one
                clickedItem.classList.add('active');
                // Highlight the status for the newly opened item
                updateStatusHighlight(status);
            }
        });
    });

    function updateStatusHighlight(status) {
        resetStatusHighlight();
        if (status === 'pendiente') {
            estadoPendiente.classList.add('highlight');
        } else if (status === 'aceptado') {
            estadoAceptado.classList.add('highlight');
        } else if (status === 'finalizado') {
            estadoFinalizado.classList.add('highlight');
        }
    }

    function resetStatusHighlight() {
        estadoPendiente.classList.remove('highlight');
        estadoAceptado.classList.remove('highlight');
        estadoFinalizado.classList.remove('highlight');
    }
});