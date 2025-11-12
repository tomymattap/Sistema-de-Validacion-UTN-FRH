document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-main');
    const tableBody = document.querySelector('#tabla-cursos tbody');
    const form = document.querySelector('.filter-form');
    let debounceTimeout;

    // Función para resaltar el texto
    const highlight = (text, term) => {
        if (!term.trim() || !text) return text;
        const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    };

    // Función para escapar caracteres de RegExp
    const escapeRegExp = (string) => {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    };

    // Función para renderizar la tabla
    const renderTable = (cursos, searchTerm) => {
        tableBody.innerHTML = ''; // Limpiar tabla
        if (!cursos || cursos.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">No se encontraron cursos.</td></tr>';
            return;
        }

        cursos.forEach(curso => {
            const row = tableBody.insertRow();
            row.innerHTML = `
                <td>${highlight(String(curso.ID_Curso), searchTerm)}</td>
                <td>${highlight(curso.Nombre_Curso, searchTerm)}</td>
                <td>${highlight(curso.Categoria, searchTerm)}</td>
                <td>${highlight(curso.Tipo, searchTerm)}</td>
                <td class="actions">
                    <a href="editar_curso.php?id=${curso.ID_Curso}" class="btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                    <a href="confirmar_eliminar_curso.php?id=${curso.ID_Curso}" class="btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                </td>
            `;
        });
    };

    // Función para buscar cursos
    const searchCourses = async () => {
        const searchTerm = searchInput.value;
        
        try {
            // Usar la ruta relativa correcta desde la ubicación del PHP que incluye el JS
            const response = await fetch(`../API/search_cursos.php?search=${encodeURIComponent(searchTerm)}`);
            if (!response.ok) {
                throw new Error(`Error en la respuesta del servidor: ${response.statusText}`);
            }
            const cursos = await response.json();
            renderTable(cursos, searchTerm);
        } catch (error) {
            console.error('Error al buscar cursos:', error);
            tableBody.innerHTML = '<tr><td colspan="5" class="error-message">No se pudieron cargar los cursos. Intente nuevamente.</td></tr>';
        }
    };

    // Event listener para el input de búsqueda con debounce
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(searchCourses, 300); // Espera 300ms antes de buscar
    });

    // Prevenir el envío tradicional del formulario
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        searchCourses(); // Realizar búsqueda al presionar Enter o el botón
    });
    
    // Si el campo de búsqueda está vacío al cargar, no hacemos nada,
    // para que se muestre la tabla ordenada por el servidor.
    // Si se limpia la búsqueda, recargamos para obtener el orden original.
    if (searchInput.value === '') {
        document.querySelector('#reset-btn').addEventListener('click', () => window.location.href = 'gestionar_cursos.php');
    }
});
