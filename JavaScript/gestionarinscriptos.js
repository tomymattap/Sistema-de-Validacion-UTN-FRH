// File: JavaScript/gestionarinscriptos.js
document.addEventListener('DOMContentLoaded', () => {

    // ---------------------------
    // 1. TABS (principal y secundarios)
    // ---------------------------
    document.querySelectorAll('.tabs-container .tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tabs-container .tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            const id = tab.dataset.tab;
            document.getElementById(id).classList.add('active');
        });
    });

    document.querySelectorAll('.tabs-secondary button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tabs-secondary button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.tab-panel-secondary').forEach(panel => panel.classList.remove('active'));
            const tab = btn.dataset.tab;
            const panelId = 'tab-' + tab;
            const panel = document.getElementById(panelId);
            if (panel) panel.classList.add('active');
        });
    });

    // ---------------------------
    // 2. ELEMENTOS & UTILIDADES
    // ---------------------------
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnMostrarTodos = document.getElementById('btnMostrarTodos');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const buscador = document.getElementById('buscadorLive');
    const resultadosContainer = document.getElementById('resultados');
    const filtroCurso = document.getElementById('filtroCurso');
    const filtroComision = document.getElementById('filtroComision');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroAnio = document.getElementById('filtroAnio');
    const filtroCuatrimestre = document.getElementById('filtroCuatrimestre');

    const overlayGuia = document.getElementById('overlayGuia');
    const btnGuiaArchivo = document.getElementById('btnGuiaArchivo');
    const cerrarGuia = document.getElementById('cerrarGuia');
    const btnCerrarGuia = document.getElementById('btnCerrarGuia');

    const dropzone = document.getElementById('dropzone');
    const archivoInput = document.getElementById('archivo');
    const previewContainer = document.getElementById('preview-container');
    const previewTable = document.getElementById('preview-table');
    const dzMessage = dropzone ? dropzone.querySelector('.dz-message') : null;
    const previewErrors = document.getElementById('preview-errors');
    const btnUploadConfirm = document.getElementById('btnUploadConfirm');
    const btnCancelarArchivo = document.getElementById('btnCancelarArchivo');
    const mensajeArchivo = document.getElementById('mensajeArchivo');

    let selectedFile = null;
    let previewRows = []; // array de arrays

    const DEBOUNCE_MS = 300;
    let debounceTimeout = null;

    const escapeHTML = (str) => {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>'"`]/g, (m) => ({
            '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;', '`':'&#96;'
        })[m]);
    };

    // ---------------------------
    // 3. FILTROS: obtener comisiones según curso
    // ---------------------------
    const cargarComisiones = async (cursoId) => {
        if (!filtroComision) return;
        filtroComision.innerHTML = '<option value="">Comisión</option>';
        filtroComision.disabled = true;
        if (!cursoId) return;
        try {
            const res = await fetch(`get_comisiones.php?curso_id=${encodeURIComponent(cursoId)}`);
            if (!res.ok) return;
            const coms = await res.json();
            coms.forEach(c => {
                const opt = document.createElement('option');
                // asumimos campo 'Comision' en respuesta
                opt.value = c.Comision;
                opt.textContent = c.Comision;
                filtroComision.appendChild(opt);
            });
            filtroComision.disabled = false;
        } catch (e) {
            console.error('Error cargando comisiones', e);
        }
    };

    if (filtroCurso) {
        filtroCurso.addEventListener('change', () => {
            cargarComisiones(filtroCurso.value);
            triggerAutoSearch();
        });
    }
    if (filtroComision) filtroComision.addEventListener('change', triggerAutoSearch);
    if (filtroEstado) filtroEstado.addEventListener('change', triggerAutoSearch);
    if (filtroAnio) filtroAnio.addEventListener('change', triggerAutoSearch);
    if (filtroCuatrimestre) filtroCuatrimestre.addEventListener('change', triggerAutoSearch);

    // ---------------------------
    // 4. Construcción de query y fetch
    // ---------------------------
    const getFiltros = () => {
        const params = {};
        const q = buscador ? buscador.value.trim() : '';
        if (q) params.search = q;

        if (filtroCurso && filtroCurso.value) params.curso = filtroCurso.value;
        if (filtroComision && filtroComision.value) params.comision = filtroComision.value;
        if (filtroEstado && filtroEstado.value) params.estado = filtroEstado.value;
        if (filtroAnio && filtroAnio.value) params.anio = filtroAnio.value;
        if (filtroCuatrimestre && filtroCuatrimestre.value) params.cuatr = filtroCuatrimestre.value;
        return params;
    };

    const buildQuery = (params) => new URLSearchParams(params).toString();

    const renderResultados = (inscriptos = [], searchTerm = '') => {
        if (!inscriptos || inscriptos.length === 0) {
            resultadosContainer.innerHTML = '<p class="no-results">No se encontraron inscripciones.</p>';
            return;
        }
        const rows = inscriptos.map(r => {
            const statusClass = `status-${(r.Estado_Cursada || '').toLowerCase().replace(/\s+/g, '-')}`;
            // Highlight solo si searchTerm existe
            const highlight = (text) => {
                if (!searchTerm) return escapeHTML(text || '');
                const re = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\\]/g,'\\$&')})`, 'gi');
                return escapeHTML(text || '').replace(re, '<mark>$1</mark>');
            };
            return `<tr>
                <td>${escapeHTML(r.ID_Inscripcion)}</td>
                <td>${highlight((r.Apellido_Alumno || '') + ', ' + (r.Nombre_Alumno || ''))}</td>
                <td>${highlight(r.ID_Cuil_Alumno || '')}</td>
                <td>${highlight(r.Nombre_Curso || '')}</td>
                <td>${escapeHTML(r.Comision || '')}</td>
                <td>${escapeHTML(r.Cuatrimestre || '')}</td>
                <td>${escapeHTML(r.Anio || '')}</td>
                <td><span class="status-badge ${statusClass}">${escapeHTML(r.Estado_Cursada || '')}</span></td>
            </tr>`;
        }).join('');
        resultadosContainer.innerHTML = `
            <table id="results-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Alumno</th><th>CUIL</th><th>Curso</th><th>Comisión</th>
                        <th>Cuatrimestre</th><th>Año</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`;
    };

    const fetchInscriptos = async (params = {}) => {
        resultadosContainer.innerHTML = '<p class="loading">Cargando...</p>';
        try {
            const q = buildQuery(params);
            const res = await fetch(`../API/search_inscriptos.php?${q}`, {cache: 'no-store'});
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            // Pasamos searchTerm para highlight (si existe)
            renderResultados(data, params.search || '');
        } catch (err) {
            console.error('Error fetching inscriptos', err);
            resultadosContainer.innerHTML = '<p class="no-results error-message">Error al cargar los datos.</p>';
        }
    };

    // ---------------------------
    // 5. EVENTOS FILTROS UI
    // ---------------------------
    function triggerAutoSearch() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            const filtros = getFiltros();
            // si hay texto de búsqueda o cualquier filtro, hacemos fetch
            if (Object.keys(filtros).length > 0) {
                fetchInscriptos(filtros);
            } else {
                resultadosContainer.innerHTML = '';
            }
        }, DEBOUNCE_MS);
    }

    if (buscador) {
        buscador.addEventListener('input', triggerAutoSearch);
    }

    if (btnFiltrar) btnFiltrar.addEventListener('click', (e) => {
        e.preventDefault();
        const filtros = getFiltros();
        if (Object.keys(filtros).length === 0) {
            resultadosContainer.innerHTML = '<p class="no-results">Por favor, seleccione al menos un filtro o escriba en el buscador.</p>';
            return;
        }
        fetchInscriptos(filtros);
    });

    if (btnMostrarTodos) btnMostrarTodos.addEventListener('click', (e) => {
        e.preventDefault();
        fetchInscriptos({ all: '1' });
    });

    if (btnLimpiar) btnLimpiar.addEventListener('click', (e) => {
        e.preventDefault();
        if (filtroCurso) filtroCurso.value = '';
        if (filtroComision) { filtroComision.innerHTML = '<option value="">Comisión</option>'; filtroComision.disabled = true; }
        if (filtroEstado) filtroEstado.value = '';
        if (filtroAnio) filtroAnio.value = '';
        if (filtroCuatrimestre) filtroCuatrimestre.value = '';
        if (buscador) buscador.value = '';
        resultadosContainer.innerHTML = '';
    });

    // ---------------------------
    // 6. OVERLAY GUIA
    // ---------------------------
    const toggleOverlay = (show) => {
        if (!overlayGuia) return;
        overlayGuia.classList.toggle('active', !!show);
        overlayGuia.setAttribute('aria-hidden', show ? 'false' : 'true');
    };
    if (btnGuiaArchivo) btnGuiaArchivo.addEventListener('click', () => toggleOverlay(true));
    if (cerrarGuia) cerrarGuia.addEventListener('click', () => toggleOverlay(false));
    if (btnCerrarGuia) btnCerrarGuia.addEventListener('click', () => toggleOverlay(false));
    if (overlayGuia) overlayGuia.addEventListener('click', (e) => { if (e.target === overlayGuia) toggleOverlay(false); });

    // ---------------------------
    // 7. DROPZONE & PREVIEW CSV
    // ---------------------------
    if (dropzone) {
        dropzone.addEventListener('click', () => archivoInput.click());
        dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dz-drag-hover'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dz-drag-hover'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dz-drag-hover');
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                archivoInput.files = e.dataTransfer.files;
                handleFileSelect({ target: archivoInput });
            }
        });
    }

    const resetUpload = () => {
        selectedFile = null;
        if (archivoInput) archivoInput.value = '';
        if (previewTable) previewTable.innerHTML = '';
        if (previewContainer) previewContainer.style.display = 'none';
        if (dzMessage) dzMessage.textContent = 'Arrastrá el archivo aquí o hacé clic para seleccionarlo';
        if (previewErrors) previewErrors.textContent = '';
        if (mensajeArchivo) { mensajeArchivo.textContent = ''; mensajeArchivo.style.color = ''; }
        previewRows = [];
    };

    const handleFileSelect = (event) => {
        const file = event.target.files && event.target.files[0];
        if (!file) { resetUpload(); return; }
        selectedFile = file;
        const name = file.name;
        const ext = name.split('.').pop().toLowerCase();
        if (dzMessage) dzMessage.textContent = `Archivo seleccionado: ${name}`;
        previewRows = [];
        previewErrors.textContent = '';

        if (ext !== 'csv') {
            previewErrors.textContent = 'Formato no soportado para vista previa. Subí un CSV (puedo integrar XLSX con PhpSpreadsheet).';
            if (previewContainer) previewContainer.style.display = 'none';
            return;
        }

        // Leer CSV y mostrar primeras 5 filas
        const reader = new FileReader();
        reader.onload = (e) => {
            const text = e.target.result.replace(/\r/g, '');
            const lines = text.split('\n').filter(l => l.trim() !== '');
            if (lines.length === 0) {
                previewErrors.textContent = 'El archivo está vacío o no tiene filas legibles.';
                previewContainer.style.display = 'none';
                return;
            }

            // Parse csv header and first 5 rows robustly with split by comma (nota: no soporta comillas complejas)
            const rows = [];
            for (let i = 0; i < Math.min(6, lines.length); i++) {
                // splitting CSV basic: si necesitás soportar comillas/commas internas usar un parser más robusto
                const cells = lines[i].split(',');
                rows.push(cells.map(c => c.trim()));
            }
            previewRows = rows;
            // construir tabla
            let html = '';
            html += '<thead><tr>';
            const header = previewRows.shift();
            header.forEach(h => html += `<th>${escapeHTML(h || '')}</th>`);
            html += '</tr></thead><tbody>';
            previewRows.forEach(r => {
                html += '<tr>';
                r.forEach(c => html += `<td>${escapeHTML(c || '')}</td>`);
                html += '</tr>';
            });
            html += '</tbody>';
            previewTable.innerHTML = html;
            previewContainer.style.display = 'block';
        };
        reader.readAsText(file, 'UTF-8');
    };

    if (archivoInput) archivoInput.addEventListener('change', handleFileSelect);

    if (btnCancelarArchivo) btnCancelarArchivo.addEventListener('click', (e) => { e.preventDefault(); resetUpload(); });

    // ---------------------------
    // 8. ENVIAR CSV AL BACKEND (confirmar)
    // ---------------------------
    if (btnUploadConfirm) {
        btnUploadConfirm.addEventListener('click', async (e) => {
            e.preventDefault();
            if (!selectedFile) {
                mensajeArchivo.textContent = '⚠️ Seleccioná un archivo antes de subir.';
                mensajeArchivo.style.color = '#b30000';
                return;
            }
            mensajeArchivo.textContent = '⏳ Subiendo y procesando...';
            mensajeArchivo.style.color = '#00455C';

            const formData = new FormData();
            formData.append('archivo', selectedFile);

            try {
                const res = await fetch('insertar_inscriptos_csv.php', { method: 'POST', body: formData });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const json = await res.json();
                if (json.error) {
                    mensajeArchivo.textContent = json.error;
                    mensajeArchivo.style.color = '#b30000';
                } else {
                    // resumen de carga
                    mensajeArchivo.textContent = json.mensaje || 'Carga finalizada correctamente.';
                    mensajeArchivo.style.color = '#0b6623';
                    // opcional: refrescar lista
                    if (json.inserted_count) {
                        fetchInscriptos({ all: '1' });
                    }
                    // limpiar
                    resetUpload();
                }
            } catch (err) {
                console.error(err);
                mensajeArchivo.textContent = '❌ Error al procesar el archivo. Revisa la consola.';
                mensajeArchivo.style.color = '#b30000';
            }
        });
    }

    // ---------------------------
    // 9. Inicial: no cargamos todo hasta que el usuario pida
    // ---------------------------
    // (si querés que cargue todo al entrar, descomentá:)
    // fetchInscriptos({ all: '1' });
});