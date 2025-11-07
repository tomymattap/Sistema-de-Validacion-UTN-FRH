document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM para filtros y resultados
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnMostrarTodos = document.getElementById('btnMostrarTodos');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const buscador = document.getElementById('buscadorLive');
    const resultadosContainer = document.getElementById('resultados');

    // --- Lógica de Búsqueda y Filtros ---

    const fetchInscriptos = async (params = {}) => {
        const query = new URLSearchParams(params).toString();
        try {
            const response = await fetch(`../API/search_inscriptos.php?${query}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            renderResultados(data);
        } catch (error) {
            console.error("Error al buscar inscriptos:", error);
            resultadosContainer.innerHTML = `<p class="no-results error-message">Error al cargar los datos. Verifique la consola para más detalles.</p>`;
        }
    };

    const renderResultados = (inscriptos) => {
        const searchTerm = buscador.value.trim();
        if (!inscriptos || inscriptos.length === 0) {
            resultadosContainer.innerHTML = '<p class="no-results">No se encontraron inscripciones que coincidan con los criterios de búsqueda.</p>';
            return;
        }

        const highlight = (text) => {
            if (!searchTerm || !text) return escapeHTML(text);
            const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
            return escapeHTML(text).replace(regex, '<mark>$1</mark>');
        };

        const tableHTML = `
            <table id="results-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alumno</th>
                        <th>CUIL</th>
                        <th>Curso</th>
                        <th>Periodo</th>
                        <th>Año</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    ${inscriptos.map(r => {
                        const statusClass = r.Estado_Cursada ? 'status-' + r.Estado_Cursada.toLowerCase().replace(/\s+/g, '-') : '';
                        return `
                            <tr>
                                <td>${r.ID_Inscripcion}</td>
                                <td>${highlight(r.Apellido_Alumno + ', ' + r.Nombre_Alumno)}</td>
                                <td>${highlight(r.ID_Cuil_Alumno)}</td>
                                <td>${highlight(r.Nombre_Curso)}</td>
                                <td>${escapeHTML(r.Cuatrimestre)}</td>
                                <td>${r.Anio}</td>
                                <td><span class="status-badge ${statusClass}">${escapeHTML(r.Estado_Cursada)}</span></td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;
        resultadosContainer.innerHTML = tableHTML;
    };

    const getFiltros = () => {
        const filtros = {};
        const q = buscador.value.trim();
        if (q) filtros.q = q;

        const curso = document.getElementById('filtroCurso').value;
        if (curso) filtros.curso = curso;

        const estado = document.getElementById('filtroEstado').value;
        if (estado) filtros.estado = estado;

        const anio = document.getElementById('filtroAnio').value;
        if (anio) filtros.anio = anio;

        const cuatr = document.getElementById('filtroCuatrimestre').value;
        if (cuatr) filtros.cuatr = cuatr;

        return filtros;
    };

    // Event Listeners para botones de filtro
    btnFiltrar.addEventListener('click', () => {
        const filtros = getFiltros();
        if (Object.keys(filtros).length > 0) {
            fetchInscriptos(filtros);
        } else {
            resultadosContainer.innerHTML = '<p class="no-results">Por favor, seleccione al menos un filtro para iniciar la búsqueda.</p>';
        }
    });

    btnMostrarTodos.addEventListener('click', () => fetchInscriptos({ all: '1' }));

    btnLimpiar.addEventListener('click', () => {
        document.getElementById('filtroCurso').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroAnio').value = '';
        document.getElementById('filtroCuatrimestre').value = '';
        buscador.value = '';
        resultadosContainer.innerHTML = '';
    });

    // Live search con debounce
    let debounceTimeout;
    buscador.addEventListener('input', () => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            const filtros = getFiltros();
            if (filtros.q) {
                fetchInscriptos(filtros);
            } else {
                resultadosContainer.innerHTML = '';
            }
        }, 300);
    });

    // --- Funciones de Utilidad ---
    const escapeRegExp = (string) => {
        return string.replace(/[.*+?^${}()|[\\]/g, '\\$&');
    };

    const escapeHTML = (str) => {\n        if (str === null || str === undefined) return \'\';\n        return str.toString()\n            .replace(/&/g, \'&amp;\')\n            .replace(/</g, \'&lt;\')\n            .replace(/>/g, \'&gt;\')\n            .replace(/\"/g, \'&quot;\')\n            .replace(/'/g, \'&#039;\');\n    };\n\n    // --- Lógica para Carga Manual de Inscripción ---\n    const formCargaManual = document.getElementById(\'formCargaManual\');\n    const mensajeCargaManual = document.getElementById(\'mensaje-carga-manual\');\n    const btnCancelarCarga = document.querySelector(\'.btn-cancelar-carga\');\n\n    if (formCargaManual) {\n        formCargaManual.addEventListener(\'submit\', async (e) => {\n            e.preventDefault();\n            mensajeCargaManual.style.display = \'none\';\n            mensajeCargaManual.className = \'mensaje-form\'; // Reset classes\n\n            const formData = new FormData(formCargaManual);\n            const cuil = formData.get(\'cuil\');\n            const email = formData.get(\'email\');\n\n            // Basic client-side validation\n            if (!/^[0-9]{11}$/.test(cuil)) {\n                mostrarMensajeForm(mensajeCargaManual, \'El CUIL debe tener 11 dígitos numéricos.\', \'error\');\n                return;\n            }\n            if (!/^[^\s@]+@[^\s@]+\\.[^\s@]+$/.test(email)) {\n                mostrarMensajeForm(mensajeCargaManual, \'Ingrese un formato de email válido.\', \'error\');\n                return;\n            }\n\n            try {\n                const response = await fetch(\'acciones/registrar_inscripcion.php\', {\n                    method: \'POST\',\n                    body: formData\n                });\n                const result = await response.json();\n\n                if (result.success) {\n                    mostrarMensajeForm(mensajeCargaManual, \'Inscripción registrada correctamente.\', \'success\');\n                    formCargaManual.reset(); // Limpiar formulario\n                    // Recargar la tabla de inscriptos si está visible\n                    if (document.getElementById(\'ver\').classList.contains(\'active\')) {\n                        fetchInscriptos({ all: \'1\' });\n                    }\n                } else {\n                    mostrarMensajeForm(mensajeCargaManual, result.message || \'No se pudo completar la inscripción. Verifique los datos.\', \'error\');\n                }\n            } catch (error) {\n                console.error(\'Error al registrar inscripción:\', error);\n                mostrarMensajeForm(mensajeCargaManual, \'Error de conexión al intentar registrar la inscripción.\', \'error\');\n            }\n        });\n\n        btnCancelarCarga.addEventListener(\'click\', () => {\n            formCargaManual.reset();\n            mensajeCargaManual.style.display = \'none\';\n        });\n    }\n\n    function mostrarMensajeForm(element, mensaje, tipo) {\n        element.textContent = mensaje;\n        element.classList.add(tipo);\n        element.style.display = \'block\';\n    }\n});