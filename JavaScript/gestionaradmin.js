document.addEventListener('DOMContentLoaded', () => {
    const tablaAdminsBody = document.querySelector('.tabla-admins tbody');
    const btnAgregar = document.querySelector('.btn-agregar');
    const liveSearch = document.getElementById('liveSearch');
    let adminsData = []; // Caché para los datos de los administradores

    // Función para mostrar mensajes flotantes
    const mostrarMensaje = (mensaje, tipo = 'success') => {
        const container = document.createElement('div');
        container.className = `mensaje-flotante ${tipo}`;
        container.textContent = mensaje;
        document.body.appendChild(container);
        setTimeout(() => container.remove(), 3500);
    };

    // Función para renderizar la tabla
    const renderizarTabla = (admins) => {
        tablaAdminsBody.innerHTML = '';
        if (admins.length === 0) {
            tablaAdminsBody.innerHTML = '<tr><td colspan="7">No se encontraron administradores.</td></tr>';
            return;
        }

        const searchTerm = liveSearch.value.toLowerCase();
        admins.forEach(admin => {
            const row = tablaAdminsBody.insertRow();
            row.dataset.idAdmin = admin.ID_Admin;

            // Resaltar coincidencias
            const highlight = (text) => {
                if (!searchTerm) return text;
                const regex = new RegExp(`(${searchTerm})`, 'gi');
                return text.replace(regex, '<mark>$1</mark>');
            };

            row.innerHTML = `
                <td>${admin.ID_Admin}</td>
                <td>${highlight(String(admin.Legajo))}</td>
                <td>${highlight(admin.Nombre)}</td>
                <td>${highlight(admin.Apellido)}</td>
                <td>${admin.Email}</td>
                <td>${admin.ID_Rol == 1 ? 'Admin' : 'Otro'}</td>
                <td class="acciones-admin">
                    <button class="btn-editar" data-id="${admin.ID_Admin}" title="Editar">✎</button>
                    <button class="btn-eliminar" data-id="${admin.ID_Admin}" title="Eliminar">🗑️</button>
                </td>
            `;
        });
    };

    // Función para cargar administradores
    const cargarAdmins = async () => {
        try {
            const response = await fetch('acciones/obtener_todos_los_admins.php');
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            adminsData = await response.json();
            renderizarTabla(adminsData);
        } catch (error) {
            console.error('Error al cargar administradores:', error);
            tablaAdminsBody.innerHTML = '<tr><td colspan="7" class="error-message">No se pudieron cargar los administradores. Intente nuevamente.</td></tr>';
        }
    };

    // Buscador en vivo
    liveSearch.addEventListener('keyup', () => {
        const searchTerm = liveSearch.value.toLowerCase();
        const filteredAdmins = adminsData.filter(admin => 
            admin.Nombre.toLowerCase().includes(searchTerm) ||
            admin.Apellido.toLowerCase().includes(searchTerm) ||
            String(admin.Legajo).toLowerCase().includes(searchTerm)
        );
        renderizarTabla(filteredAdmins);
    });

    // Función para mostrar el modal (genérica)
    const mostrarModal = (titulo, contenidoHTML, onConfirm) => {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-contenido">
                <span class="cerrar">&times;</span>
                <h2>${titulo}</h2>
                ${contenidoHTML}
            </div>
        `;
        document.body.appendChild(modal);

        const cerrarModal = () => modal.remove();
        modal.querySelector('.cerrar').onclick = cerrarModal;
        modal.querySelector('.btn-cancelar').onclick = cerrarModal;

        if (onConfirm) {
            const form = modal.querySelector('form');
            if (form) { // Para modales con formulario
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    await onConfirm(form);
                });
            } else { // Para modales de confirmación
                modal.querySelector('.btn-confirmar').onclick = async () => {
                    await onConfirm();
                };
            }
        }
    };

    // Abrir modal para AGREGAR
    btnAgregar.addEventListener('click', () => {
        const contenido = `
            <form id="formAgregarAdmin">
                <div class="campo-form">
                    <label for="legajo">Legajo:</label>
                    <input type="number" id="legajo" name="legajo" required>
                </div>
                <div class="campo-form">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="campo-form">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
                <div class="campo-form">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <input type="hidden" name="rol" value="1">
                <div class="botones-form">
                    <button type="submit" class="btn-guardar">Guardar</button>
                    <button type="button" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        `;
        mostrarModal('Agregar Administrador', contenido, async (form) => {
            const formData = new FormData(form);
            try {
                const response = await fetch('acciones/agregar_admin.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (response.ok && result.success) {
                    mostrarMensaje('Administrador agregado correctamente.');
                    cargarAdmins();
                    document.querySelector('.modal').remove();
                } else {
                    mostrarMensaje(result.message || 'Error al agregar el administrador.', 'error');
                }
            } catch (error) {
                mostrarMensaje('Error de conexión al intentar agregar.', 'error');
            }
        });
    });

    // Manejar clics en EDITAR y ELIMINAR
    tablaAdminsBody.addEventListener('click', async (e) => {
        const button = e.target.closest('button');
        if (!button) return;

        const id = button.dataset.id;

        // EDITAR
        if (button.classList.contains('btn-editar')) {
            try {
                const response = await fetch(`acciones/obtener_admin.php?id=${id}`);
                const result = await response.json();
                if (!result.success) throw new Error(result.message);
                const admin = result.data;

                const contenido = `
                    <form id="formEditarAdmin">
                        <input type="hidden" name="id" value="${admin.ID_Admin}">
                        <div class="campo-form">
                            <label for="legajo">Legajo:</label>
                            <input type="text" id="legajo" name="legajo" value="${admin.Legajo}" disabled>
                        </div>
                        <div class="campo-form">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" value="${admin.Nombre}" required>
                        </div>
                        <div class="campo-form">
                            <label for="apellido">Apellido:</label>
                            <input type="text" id="apellido" name="apellido" value="${admin.Apellido}" required>
                        </div>
                        <div class="campo-form">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="${admin.Email}" required>
                        </div>
                        <div class="campo-form">
                            <label for="rol">Rol:</label>
                            <select id="rol" name="rol" required>
                                <option value="1" ${admin.ID_Rol == 1 ? 'selected' : ''}>Admin</option>
                                <option value="2" ${admin.ID_Rol == 2 ? 'selected' : ''}>Otro</option>
                            </select>
                        </div>
                        <div class="botones-form">
                            <button type="submit" class="btn-guardar">Guardar Cambios</button>
                            <button type="button" class="btn-cancelar">Cancelar</button>
                        </div>
                    </form>
                `;
                mostrarModal('Editar Administrador', contenido, async (form) => {
                    const formData = new FormData(form);
                    try {
                        const response = await fetch('acciones/editar_admin.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        if (response.ok && result.success) {
                            mostrarMensaje('Datos actualizados correctamente.');
                            cargarAdmins();
                            document.querySelector('.modal').remove();
                        } else {
                            mostrarMensaje(result.message || 'Error al actualizar.', 'error');
                        }
                    } catch (error) {
                        mostrarMensaje('Error de conexión al intentar actualizar.', 'error');
                    }
                });
            } catch (error) {
                mostrarMensaje(error.message || 'No se pudieron cargar los datos del administrador.', 'error');
            }
        }

        // ELIMINAR
        if (button.classList.contains('btn-eliminar')) {
            const idAdmin = button.dataset.id;
            const contenido = `
                <div class="modal-confirmacion-institucional">
                    <div class="icono-advertencia">⚠️</div>
                    <p class="advertencia-titulo"><strong>Advertencia: estás por eliminar un administrador.</strong></p>
                    <p>¿Está seguro de que desea eliminar al administrador con ID: <strong>${idAdmin}</strong>?</p>
                    <div class="botones-confirmacion">
                        <button type="button" class="btn-confirmar-eliminar">Confirmar eliminación</button>
                        <button type="button" class="btn-cancelar-eliminar">Cancelar</button>
                    </div>
                </div>
            `;
            
            const modal = document.createElement('div');
            modal.className = 'modal modal-advertencia';
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
                    const response = await fetch('acciones/eliminar_admin.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${idAdmin}`
                    });
                    const result = await response.json();
                    if (response.ok && result.success) {
                        mostrarMensaje('Administrador eliminado correctamente.');
                        cargarAdmins();
                    } else {
                        mostrarMensaje(result.message || 'Error al eliminar.', 'error');
                    }
                } catch (error) {
                    mostrarMensaje('Error de conexión al intentar eliminar.', 'error');
                } finally {
                    cerrarModal();
                }
            };
        }
    });

    // Carga inicial de datos
    cargarAdmins();
});