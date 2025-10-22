document.addEventListener('DOMContentLoaded', () => {
    // --- DATOS DE EJEMPLO (SIMULACIÓN DE BASE DE DATOS) ---
    const inscripciones = [
        {
            alumno: 'Juan Pérez',
            dni: '12345678',
            curso: 'Instalación de Paneles Solares',
            modalidad: 'Virtual',
            ano: 2023,
            cuatrimestre: 'Primer cuatrimestre',
            estadoCursada: 'Finalizado',
            fechaInicio: '2023-03-15',
            fechaFin: '2023-07-05',
            estadoAprobacion: 'Aprobada',
            docente: 'Carlos Rodriguez'
        },
        {
            alumno: 'Maria Garcia',
            dni: '87654321',
            curso: 'CCNA I',
            modalidad: 'Presencial',
            ano: 2023,
            cuatrimestre: 'Segundo cuatrimestre',
            estadoCursada: 'En curso',
            fechaInicio: '2023-08-10',
            fechaFin: '2023-12-01',
            estadoAprobacion: '-',
            docente: 'Ana Martinez'
        },
        {
            alumno: 'Pedro Martinez',
            dni: '11223344',
            curso: 'Programming Essentials en Python',
            modalidad: 'Virtual',
            ano: 2024,
            cuatrimestre: 'Primer cuatrimestre',
            estadoCursada: 'Pendiente',
            fechaInicio: '2024-03-20',
            fechaFin: '2024-07-15',
            estadoAprobacion: '-',
            docente: 'Luis Fernandez'
        }
    ];

    // --- ELEMENTOS DEL DOM ---
    const searchInput = document.getElementById('search-main');
    const filterBtn = document.getElementById('filter-btn');
    const showAllBtn = document.getElementById('show-all-btn');
    const clearBtn = document.getElementById('reset-btn');
    const tableBody = document.querySelector('#results-table tbody');

    // --- FUNCIÓN PARA RENDERIZAR LA TABLA ---
    function renderTable(data, searchTerm = '') {
        tableBody.innerHTML = '';
        if (data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="11">No se encontraron resultados.</td></tr>';
            return;
        }

        data.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${highlight(item.alumno, searchTerm)}</td>
                <td>${highlight(item.dni, searchTerm)}</td>
                <td>${item.curso}</td>
                <td>${item.modalidad}</td>
                <td>${item.ano}</td>
                <td>${item.cuatrimestre}</td>
                <td>${item.estadoCursada}</td>
                <td>${item.fechaInicio}</td>
                <td>${item.fechaFin}</td>
                <td>${item.estadoAprobacion}</td>
                <td>${highlight(item.docente, searchTerm)}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    // --- FUNCIÓN PARA RESALTAR COINCIDENCIAS ---
    function highlight(text, term) {
        if (!term) return text;
        const regex = new RegExp(`(${term})`, 'gi');
        return text.toString().replace(regex, '<mark>$1</mark>');
    }

    // --- FUNCIÓN PARA FILTRAR LOS DATOS ---
    function filterData() {
        const filterCurso = document.getElementById('filter-curso').value;
        const filterEstadoCursada = document.getElementById('filter-estado-cursada').value;
        const filterAno = document.getElementById('filter-ano').value;
        const filterCuatrimestre = document.getElementById('filter-cuatrimestre').value;
        const filterModalidad = document.getElementById('filter-modalidad').value;
        const filterAprobacion = document.getElementById('filter-aprobacion').value;
        const searchTerm = searchInput.value.toLowerCase();
        const filterFechaInicio = document.getElementById('filter-fecha-inicio').value;
        const filterFechaFin = document.getElementById('filter-fecha-fin').value;

        const filteredData = inscripciones.filter(item => {
            return (
                (filterCurso === '' || item.curso === filterCurso) &&
                (filterEstadoCursada === '' || item.estadoCursada === filterEstadoCursada) &&
                (filterAno === '' || item.ano.toString() === filterAno) &&
                (filterCuatrimestre === '' || item.cuatrimestre === filterCuatrimestre) &&
                (filterModalidad === '' || item.modalidad === filterModalidad) &&
                (filterAprobacion === '' || item.estadoAprobacion === filterAprobacion) &&
                (filterFechaInicio === '' || item.fechaInicio >= filterFechaInicio) &&
                (filterFechaFin === '' || item.fechaFin <= filterFechaFin) &&
                (searchTerm === '' || 
                    item.alumno.toLowerCase().includes(searchTerm) || 
                    item.dni.includes(searchTerm) || 
                    item.docente.toLowerCase().includes(searchTerm))
            );
        });

        renderTable(filteredData, searchTerm);
    }

    // --- EVENT LISTENERS ---
    filterBtn.addEventListener('click', filterData);
    searchInput.addEventListener('input', filterData);

    showAllBtn.addEventListener('click', () => {
        renderTable(inscripciones);
    });

    clearBtn.addEventListener('click', () => {
        document.getElementById('filter-curso').value = '';
        document.getElementById('filter-estado-cursada').value = '';
        document.getElementById('filter-ano').value = '';
        document.getElementById('filter-cuatrimestre').value = '';
        document.getElementById('filter-modalidad').value = '';
        document.getElementById('filter-aprobacion').value = '';
        searchInput.value = '';
        document.getElementById('filter-fecha-inicio').value = '';
        document.getElementById('filter-fecha-fin').value = '';
        tableBody.innerHTML = '<tr><td colspan="11">Por favor, realice una búsqueda para ver los resultados.</td></tr>';
    });

    // --- RENDERIZADO INICIAL ---
    tableBody.innerHTML = '<tr><td colspan="11">Por favor, realice una búsqueda para ver los resultados.</td></tr>';
});