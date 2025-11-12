// File: JavaScript/gestionar_inscriptos.js
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
    const cargarComisiones = (cursoId) => {
        if (!filtroComision) return;

        // Limpiar y añadir la opción por defecto.
        filtroComision.innerHTML = '<option value="">Comisión</option>';

        // Si no hay un curso seleccionado, el filtro de comisión permanece deshabilitado.
        if (!cursoId) {
            filtroComision.disabled = true;
            return;
        }

        // Si se selecciona un curso, se habilita el filtro y se puebla con las comisiones fijas.
        filtroComision.disabled = false;
        const comisionesFijas = ['A', 'B', 'C', 'D', 'E', 'F'];

        comisionesFijas.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            filtroComision.appendChild(opt);
        });
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
                <td class="acciones">
                    <!-- Botón editar: redirige a tu PHP -->
                    <button class="btn-accion editar" data-id="${r.ID_Inscripcion}" title="Editar" onclick="event.preventDefault();">
                        <i class="fas fa-edit"></i>
                    </button>
                    <!-- Botón eliminar: envía form oculto -->
                    <button class="btn-accion eliminar" data-id="${r.ID_Inscripcion}" title="Eliminar" onclick="event.preventDefault();">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>`;

        }).join('');
        resultadosContainer.innerHTML = `
            <table id="results-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Alumno</th><th>CUIL</th><th>Curso</th><th>Comisión</th>
                        <th>Cuatrimestre</th><th>Año</th><th>Estado</th><th>Acciones</th>

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
            
            // Ordenar los resultados por ID_Inscripcion de forma ascendente
            data.sort((a, b) => parseInt(a.ID_Inscripcion) - parseInt(b.ID_Inscripcion));

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
        if (filtroComision) { 
            filtroComision.innerHTML = '<option value="">Comisión</option>'; 
            filtroComision.value = '';
            filtroComision.disabled = true; 
        }
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

    // -------------------------------------------------------
    // 10. OVERLAY DE EDICIÓN Y MODAL DE ELIMINACIÓN (NUEVA IMPLEMENTACIÓN)
    // -------------------------------------------------------

    const editModal = document.getElementById('edit-modal');
    const editModalBody = document.getElementById('edit-modal-body');
    const closeEditModalBtn = document.getElementById('close-edit-modal');

    // Función para cerrar el modal de edición
    const cerrarModalEdicion = () => {
        if (editModal) {
            editModal.classList.remove('active');
            setTimeout(() => {
                editModal.style.display = 'none';
                if (editModalBody) editModalBody.innerHTML = ''; // Limpiar contenido
            }, 300);
        }
    };

    if (editModal) {
        closeEditModalBtn.addEventListener('click', cerrarModalEdicion);
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                cerrarModalEdicion();
            }
        });
    }
    
    // Cargar y mostrar el formulario de edición en el modal
    const cargarFormularioEnModal = async (url, idInscripcion) => {
        if (!editModal || !editModalBody) return;

        editModalBody.innerHTML = '<p class="loading">Cargando formulario...</p>';
        editModal.style.display = 'flex';
        setTimeout(() => editModal.classList.add('active'), 10);

        try {
            const response = await fetch(`${url}?ID_Inscripcion=${idInscripcion}`);
            if (!response.ok) throw new Error(`Error al cargar el formulario: ${response.statusText}`);
            
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const formContainer = doc.querySelector('.edit-form-container');

            if (!formContainer) throw new Error('No se pudo encontrar el contenedor del formulario en la respuesta.');

            editModalBody.innerHTML = '';
            editModalBody.appendChild(formContainer);

            const form = editModalBody.querySelector('form');
            if (form) {
                const formActions = form.querySelector('.form-actions');
                const submitButton = form.querySelector('button[type="submit"]');

                // 1. Estilizar el botón de submit existente
                if (submitButton) {
                    submitButton.classList.add('btn-modal-action', 'submit');
                }

                // 2. Crear y añadir el botón de cancelar
                if (formActions) {
                    // Quitar el link de cancelar original si existe
                    const oldCancelLink = formActions.querySelector('a.btn-cancel');
                    if (oldCancelLink) oldCancelLink.remove();
                    
                    const cancelButton = document.createElement('button');
                    cancelButton.type = 'button';
                    cancelButton.textContent = 'Cancelar';
                    cancelButton.className = 'btn-modal-action';
                    cancelButton.addEventListener('click', cerrarModalEdicion);
                    
                    // Insertar antes del botón de guardar para que quede a la izquierda
                    formActions.insertBefore(cancelButton, submitButton);
                }

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(form);
                    const originalButtonText = submitButton.innerHTML;
                    submitButton.innerHTML = 'Guardando...';
                    submitButton.disabled = true;

                    try {
                        const postResponse = await fetch(form.action, {
                            method: 'POST',
                            body: formData
                        });

                        if (postResponse.ok || (postResponse.redirected && postResponse.url.includes('gestionar_inscriptos.php'))) {
                            cerrarModalEdicion();
                            fetchInscriptos({ all: '1' }); // Recargar la tabla
                        } else {
                           const errorText = await postResponse.text();
                           throw new Error(`La actualización falló: ${errorText}`);
                        }

                    } catch (error) {
                        console.error('Error al enviar el formulario:', error);
                        let errorMsg = form.querySelector('.form-error-msg');
                        if (!errorMsg) {
                            errorMsg = document.createElement('p');
                            errorMsg.className = 'form-error-msg';
                            errorMsg.style.color = 'red';
                            errorMsg.style.marginTop = '10px';
                            formActions.insertAdjacentElement('afterend', errorMsg);
                        }
                        errorMsg.textContent = 'Error al guardar. Por favor, intente de nuevo.';
                    } finally {
                        if(submitButton) {
                            submitButton.innerHTML = originalButtonText;
                            submitButton.disabled = false;
                        }
                    }
                });
            }

        } catch (error) {
            console.error('Error en cargarFormularioEnModal:', error);
            editModalBody.innerHTML = `<p class="no-results error-message">${error.message}</p>`;
        }
    };


    // Crear y mostrar overlay de edición con dos opciones
    function abrirOverlayEdicion(idInscripcion) {
        // Eliminar overlays previos si existen
        const existingOverlay = document.querySelector('.overlay-edicion');
        if (existingOverlay) existingOverlay.remove();

        const overlay = document.createElement('div');
        overlay.className = 'overlay-edicion';
        overlay.innerHTML = `
            <div class="overlay-edicion-content">
                <button class="close-btn" title="Cerrar">&times;</button>
                <h2>¿Qué deseas editar?</h2>
                <div class="opciones-edicion">
                    <div class="opcion-editar" data-tipo="alumno">
                        <i class="fas fa-user"></i>
                        <p>Editar datos del estudiante</p>
                    </div>
                    <div class="opcion-editar" data-tipo="inscripcion">
                        <i class="fas fa-book-open"></i>
                        <p>Editar datos de la inscripción</p>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        const close = () => {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 300);
        };

        setTimeout(() => overlay.classList.add('active'), 10);

        overlay.querySelector('.close-btn').addEventListener('click', close);
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                close();
            }
        });

        overlay.querySelectorAll('.opcion-editar').forEach(opt => {
            opt.addEventListener('click', () => {
                const tipo = opt.dataset.tipo;
                let url;
                if (tipo === 'inscripcion') {
                    url = 'editar_inscripto.php';
                } else if (tipo === 'alumno') {
                    url = 'editar_alumno.php';
                }
                
                if (url) {
                    cargarFormularioEnModal(url, idInscripcion);
                }
                close();
            });
        });
    }

    // Crear y mostrar overlay de confirmación de eliminación
    function abrirModalEliminar(idInscripcion, nombreEstudiante) {
        // Eliminar modales previos si existen
        const existingModal = document.querySelector('.modal-advertencia');
        if (existingModal) existingModal.remove();

        const contenido = `
            <div class="icono-advertencia">⚠️</div>
            <p class="advertencia-titulo"><strong>Advertencia: estás por eliminar una inscripción.</strong></p>
            <p>¿Está seguro de que desea eliminar la inscripción del estudiante <strong>${escapeHTML(nombreEstudiante)}</strong>? Esta acción no se podrá deshacer.</p>
            <div class="botones-confirmacion">
                <button type="button" class="btn-confirmar-eliminar">Confirmar eliminación</button>
                <button type="button" class="btn-cancelar-eliminar">Cancelar</button>
            </div>
        `;
        
        const modal = document.createElement('div');
        modal.className = 'modal-advertencia';
        modal.innerHTML = `
            <div class="modal-contenido-advertencia">
                <span class="cerrar-advertencia">&times;</span>
                ${contenido}
            </div>
        `;
        document.body.appendChild(modal);

        const cerrarModal = () => modal.remove();
        modal.querySelector('.cerrar-advertencia').onclick = cerrarModal;
        modal.querySelector('.btn-cancelar-eliminar').onclick = cerrarModal;

        modal.querySelector('.btn-confirmar-eliminar').onclick = async () => {
            try {
                const formData = new FormData();
                formData.append('ID_Inscripcion', idInscripcion);

                const response = await fetch('eliminar_inscripto.php', {
                    method: 'POST',
                    body: formData
                });

                // Asumimos que una respuesta OK significa que se eliminó.
                if (response.ok) {
                    // Podríamos añadir una notificación de éxito aquí.
                    fetchInscriptos({ all: '1' }); // Recargar la tabla para ver los cambios
                } else {
                    // Intentar leer un mensaje de error del cuerpo de la respuesta
                    const result = await response.text();
                    throw new Error(result || 'Error al eliminar.');
                }
            } catch (error) {
                console.error('Error de conexión al intentar eliminar:', error);
                // Podríamos mostrar un modal de error más amigable
                alert(`Error: ${error.message}`);
            } finally {
                cerrarModal();
            }
        };
    }

    // -------------------------------------------------------
    // 11. LÓGICA PARA FORMULARIO MULTI-STEP
    // -------------------------------------------------------
    const initMultiStepForm = () => {
        const multiStepContainer = document.querySelector('.multistep-form-container');
        if (!multiStepContainer) return;

        const progressBar = multiStepContainer.querySelector('.progress-bar');
        const steps = multiStepContainer.querySelectorAll('.form-step');
        const formStep1 = multiStepContainer.querySelector('#form-step-1');
        const formStep2 = multiStepContainer.querySelector('#form-step-2');
        const successMessage = multiStepContainer.querySelector('#inscripcion-exitosa-mensaje');

        let currentStep; // Se inicializará después
        // Usaremos sessionStorage para guardar los datos del alumno entre pasos

        const updateStepUI = () => {
            steps.forEach(step => step.classList.remove('active'));
            if (multiStepContainer.querySelector(`#step-${currentStep}`)) {
                multiStepContainer.querySelector(`#step-${currentStep}`).classList.add('active');
            }

            progressBar.querySelectorAll('.progress-step').forEach(stepEl => {
                const stepNum = parseInt(stepEl.dataset.step, 10);
                if (stepNum <= currentStep) {
                    stepEl.classList.add('active');
                } else {
                    stepEl.classList.remove('active');
                }
            });
        };

        const resetToStep1 = () => {
            currentStep = 1;
            formStep1.reset();
            formStep2.reset();
            sessionStorage.removeItem('alumnoData'); // Limpiar datos temporales
            sessionStorage.removeItem('currentFormStep'); // Limpiar el paso actual
            multiStepContainer.querySelector('#mensaje-step-1').style.display = 'none';
            multiStepContainer.querySelector('#mensaje-step-2').style.display = 'none';
            updateStepUI();
        };

        const restoreFormState = () => {
            const savedStep = sessionStorage.getItem('currentFormStep');
            const savedData = sessionStorage.getItem('alumnoData');

            if (savedStep === '2' && savedData) {
                currentStep = 2;
                const alumnoData = JSON.parse(savedData);
                // Rellenar el formulario del paso 1 con los datos guardados
                Object.keys(alumnoData).forEach(key => {
                    const input = formStep1.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = alumnoData[key];
                    }
                });
            } else {
                currentStep = 1;
            }
            updateStepUI();
        };

        const showStepMessage = (step, message, isError = true) => {
            const msgElement = multiStepContainer.querySelector(`#mensaje-step-${step}`);
            msgElement.textContent = message;
            msgElement.style.color = isError ? '#dc3545' : '#155724';
            msgElement.style.display = 'block';
        };

        // --- Event Listeners ---

        // PASO 1: Registrar y Continuar
        formStep1.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(formStep1);
            const alumnoData = Object.fromEntries(formData.entries());

            // Validaciones básicas en el cliente
            if (!/^[0-9]{10,11}$/.test(alumnoData.ID_Cuil_Alumno)) {
                showStepMessage(1, 'El CUIL debe tener 11 dígitos numéricos.');
                return;
            }
            if (!/^[0-9]{7,8}$/.test(alumnoData.DNI_Alumno)) {
                showStepMessage(1, 'El DNI debe tener entre 7 y 8 dígitos.');
                return;
            }

            // Guardar en sessionStorage y pasar al siguiente paso
            sessionStorage.setItem('alumnoData', JSON.stringify(alumnoData));
            currentStep = 2;
            sessionStorage.setItem('currentFormStep', '2');
            updateStepUI();
            multiStepContainer.querySelector('#mensaje-step-1').style.display = 'none';
        });

        // PASO 2: Finalizar Inscripción
        formStep2.addEventListener('submit', async (e) => {
            e.preventDefault();
            const alumnoDataString = sessionStorage.getItem('alumnoData');
            if (!alumnoDataString) {
                showStepMessage(2, 'Error: No se encontraron los datos del estudiante. Por favor, vuelva al paso 1.');
                return;
            }

            const alumnoData = JSON.parse(alumnoDataString);
            const inscripcionData = new FormData(formStep2);

            // Combinar datos del alumno y de la inscripción
            for (const key in alumnoData) {
                inscripcionData.append(key, alumnoData[key]);
            }

            const btn = formStep2.querySelector('.btn-finalizar');
            btn.disabled = true;
            btn.textContent = 'Finalizando...';
            try {
                const response = await fetch('acciones/registrar_inscripcion_completa.php', {
                    method: 'POST',
                    body: inscripcionData
                });
                const result = await response.json();

                if (result.success) {
                    successMessage.style.display = 'flex';
                    setTimeout(() => {
                        successMessage.style.display = 'none'; // Ocultar el mensaje antes de resetear
                        resetToStep1();
                        fetchInscriptos({ all: '1' });
                    }, 3000);
                } else {
                    showStepMessage(2, result.message || 'Error al finalizar la inscripción.');
                }
            } catch (error) {
                showStepMessage(2, 'Error de conexión. Intente de nuevo.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Finalizar Inscripción';
            }
        });

        // Botones de Cancelar
        multiStepContainer.querySelectorAll('.btn-cancelar-paso').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = document.createElement('div');
                modal.className = 'modal-advertencia';
                modal.innerHTML = `
                    <div class="modal-contenido-advertencia">
                        <span class="cerrar-advertencia">&times;</span>
                        <div class="icono-advertencia">⚠️</div>
                        <p class="advertencia-titulo"><strong>¿Seguro que quiere cancelar?</strong></p>
                        <p>Perderá todos los cambios realizados.</p>
                        <div class="botones-confirmacion">
                            <button class="btn-confirmar-eliminar">Sí, cancelar</button>
                            <button class="btn-cancelar-eliminar">No, continuar</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);

                const closeModal = () => modal.remove();
                modal.querySelector('.cerrar-advertencia').onclick = closeModal;
                modal.querySelector('.btn-cancelar-eliminar').onclick = closeModal;
                
                modal.querySelector('.btn-confirmar-eliminar').onclick = async () => {
                    resetToStep1(); // Ahora solo necesita resetear el formulario y sessionStorage
                    closeModal();
                };
            });
        });
    };
    
    initMultiStepForm(); // Llama a la función principal

    // Delegación de eventos para la tabla de resultados (Editar y Eliminar)
    if (resultadosContainer) {
        resultadosContainer.addEventListener('click', (e) => {
            const targetElement = e.target;
            
            const editarBtn = targetElement.closest('.btn-accion.editar');
            if (editarBtn) {
                e.preventDefault();
                const id = editarBtn.dataset.id;
                abrirOverlayEdicion(id);
            }

            const eliminarBtn = targetElement.closest('.btn-accion.eliminar');
            if (eliminarBtn) {
                e.preventDefault(); 
                const id = eliminarBtn.dataset.id;
                
                const row = eliminarBtn.closest('tr');
                const studentName = row ? row.cells[1].textContent.trim() : 'este registro';

                abrirModalEliminar(id, studentName);
            }
        });
    }
});