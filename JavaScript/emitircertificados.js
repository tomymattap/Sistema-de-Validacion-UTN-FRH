/*
    ARCHIVO: emitircertificados.js
    DESCRIPCIÓN: Script para la página de emisión de certificados del administrador.
    - Popula dinámicamente el selector de cursos.
    - Al enviar el formulario, genera un código de certificado y un código QR en el lado del cliente para previsualización.
    - NOTA: La generación final y el guardado se manejan en el backend (PHP)
*/

document.addEventListener('DOMContentLoaded', () => {
    // --- SELECCIÓN DE ELEMENTOS DEL DOM ---
    const certificateForm = document.getElementById('certificate-form');
    const qrCodeContainer = document.getElementById('qr-code');
    const certificateCodeSpan = document.getElementById('certificate-code');
    const courseCompletedSelect = document.getElementById('course-completed');

    // --- DATOS DE CURSOS (EJEMPLO) ---
    // En una aplicación real, estos datos se obtendrían del servidor (base de datos)
    const courses = [
        { id: 1, name: 'Instalación de Paneles Solares' },
        { id: 2, name: 'Instalación de Cámaras de Seguridad y Alarmas' },
        { id: 3, name: 'Electricidad Industrial' }
    ];

    /**
     * Rellena el menú desplegable de cursos con los datos disponibles.
     */
    courses.forEach(course => {
        const option = document.createElement('option');
        option.value = course.name;
        option.textContent = course.name;
        courseCompletedSelect.appendChild(option);
    });

    // --- EVENTO DE ENVÍO DEL FORMULARIO ---
    certificateForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Previene el envío tradicional del formulario.

        // Obtiene los valores de los campos del formulario.
        const studentName = document.getElementById('student-name').value;
        const studentDni = document.getElementById('student-dni').value;
        const courseCompleted = document.getElementById('course-completed').value;

        // --- GENERACIÓN DEL CÓDIGO DE CERTIFICADO (PARA PREVISUALIZACIÓN) ---
        // Se combina un prefijo con la fecha actual en milisegundos para crear un código único.
        const certificateCode = 'UTN-FRH-' + Date.now();
        certificateCodeSpan.textContent = certificateCode; // Muestra el código en la página.

        // --- GENERACIÓN DEL CÓDIGO QR (USANDO LA LIBRERÍA qrcode.js) ---
        qrCodeContainer.innerHTML = ''; // Limpia el contenedor de QR previo.
        new QRCode(qrCodeContainer, {
            // El texto que se codificará en el QR.
            text: `Certificado: ${certificateCode}\nAlumno: ${studentName}\nDNI: ${studentDni}\nCurso: ${courseCompleted}`,
            width: 128,
            height: 128,
        });
    });
});
