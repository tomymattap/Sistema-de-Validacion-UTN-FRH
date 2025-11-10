document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnMostrarTodos = document.getElementById('btnMostrarTodos');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const buscador = document.getElementById('buscadorLive');
    const resultadosContainer = document.getElementById('resultados');

    // Selects / inputs de filtros
    const filtroCurso = document.getElementById('filtroCurso');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroAnio = document.getElementById('filtroAnio');
    const filtroCuatrimestre = document.getElementById('filtroCuatrimestre');

    // Debounce
    let debounceTimeout = null;
    const DEBOUNCE_MS = 300;

    // Utilidades
    const escapeRegExp = (string) => {
        return String(string).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    };

    const escapeHTML = (str) => {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    const highlight = (text, term) => {
        if (!term || !String(text)) return escapeHTML(text);
        const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
        return escapeHTML(text).replace(regex, '<mark>$1</mark>');
    };

    // Construye parámetros según filtros actuales
    const getFiltros = () => {
        const params = {};
        const q = buscador.value.trim();
        if (q) params.search = q;

        if (filtroCurso && filtroCurso.value) params.curso = filtroCurso.value;
        if (filtroEstado && filtroEstado.value) params.estado = filtroEstado.value;
        if (filtroAnio && filtroAnio.value) params.anio = filtroAnio.value;
        if (filtroCuatrimestre && filtroCuatrimestre.value) params.cuatr = filtroCuatrimestre.value;

        return params;
    };

    // Construye query string desde objeto params
    const buildQuery = (params) => {
        return new URLSearchParams(params).toString();
    };

    // Render de resultados en tabla
    const renderResultados = (inscriptos, searchTerm = '') => {
        if (!inscriptos || inscriptos.length === 0) {
            resultadosContainer.innerHTML = '<p class="no-results">No se encontraron inscripciones.</p>';
            return;
        }

        const rows = inscriptos.map(r => {
            const status = r.Estado_Cursada || '';
            const statusClass = `status-${escapeHTML(status.toLowerCase().replace(/\s+/g, '-'))}`;

            return `
                <tr>
                    <td>${escapeHTML(r.ID_Inscripcion)}</td>
                    <td>${highlight(r.Apellido_Alumno + ', ' + r.Nombre_Alumno, searchTerm)}</td>
                    <td>${highlight(r.ID_Cuil_Alumno, searchTerm)}</td>
                    <td>${highlight(r.Nombre_Curso, searchTerm)}</td>
                    <td>${escapeHTML(r.Cuatrimestre)}</td>
                    <td>${escapeHTML(r.Anio)}</td>
                    <td><span class="status-badge ${statusClass}">${escapeHTML(status)}</span></td>
                </tr>
            `;
        }).join('');

        resultadosContainer.innerHTML = `
            <table id="results-table" class="table-results">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alumno</th>
                        <th>CUIL</th>
                        <th>Curso</th>
                        <th>Cuatrimestre</th>
                        <th>Año</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        `;
    };

    // Fetch principal con manejo de errores
    const fetchInscriptos = async (params = {}) => {
        const query = buildQuery(params);
        try {
            resultadosContainer.innerHTML = '<p class="loading">Cargando...</p>';
            const res = await fetch(`../API/search_inscriptos.php?${query}`, { cache: 'no-store' });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            renderResultados(data, params.search || '');
        } catch (err) {
            console.error('Error al obtener inscriptos:', err);
            resultadosContainer.innerHTML = '<p class="no-results error-message">Error al cargar los datos. Revisa la consola.</p>';
        }
    };

    // EVENTOS

    // 1) Botón Filtrar: recoge filtros y envía
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', (e) => {
            e.preventDefault();
            const filtros = getFiltros();
            if (Object.keys(filtros).length === 0) {
                resultadosContainer.innerHTML = '<p class="no-results">Por favor, seleccione al menos un filtro o escriba en el buscador.</p>';
                return;
            }
            fetchInscriptos(filtros);
        });
    }

    // 2) Mostrar todos
    if (btnMostrarTodos) {
        btnMostrarTodos.addEventListener('click', (e) => {
            e.preventDefault();
            // envia all=1 para que el backend devuelva todos
            fetchInscriptos({ all: '1' });
        });
    }

    // 3) Limpiar filtros
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', (e) => {
            e.preventDefault();
            if (filtroCurso) filtroCurso.value = '';
            if (filtroEstado) filtroEstado.value = '';
            if (filtroAnio) filtroAnio.value = '';
            if (filtroCuatrimestre) filtroCuatrimestre.value = '';
            if (buscador) buscador.value = '';
            resultadosContainer.innerHTML = '';
        });
    }

    // 4) Live search con debounce: también incluye filtros si están seleccionados
    if (buscador) {
        buscador.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                const filtros = getFiltros();
                // Si hay algo para buscar (search o cualquier filtro), hacemos fetch
                if (filtros.search || filtros.curso || filtros.estado || filtros.anio || filtros.cuatr) {
                    fetchInscriptos(filtros);
                } else {
                    resultadosContainer.innerHTML = '';
                }
            }, DEBOUNCE_MS);
        });
    }

    // 5) Si los selects de filtro cambian, aplicamos búsqueda automática (opcional)
    [filtroCurso, filtroEstado, filtroAnio, filtroCuatrimestre].forEach(selectEl => {
        if (!selectEl) return;
        selectEl.addEventListener('change', () => {
            // pequeño debounce para evitar múltiples requests rápidos
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                const filtros = getFiltros();
                // Si no hay ningún filtro ni búsqueda no hacemos nada
                if (Object.keys(filtros).length > 0) {
                    fetchInscriptos(filtros);
                } else {
                    resultadosContainer.innerHTML = '';
                }
            }, DEBOUNCE_MS);
        });
    });

    // 6) Cargar listado inicial opcional: por defecto no cargamos todo automáticamente.
    // Si querés que al cargar muestre todos: descomentar la siguiente línea:
    // fetchInscriptos({ all: '1' });
});