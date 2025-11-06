document.addEventListener('DOMContentLoaded', () => {
    const certificateForm = document.getElementById('certificate-form');
    const qrCodeContainer = document.getElementById('qr-code');
    const certificateCodeSpan = document.getElementById('certificate-code');
    const courseCompletedSelect = document.getElementById('course-completed');

    // Dummy data for courses, in a real application this would be fetched from the server
    const courses = [
        { id: 1, name: 'Instalación de Paneles Solares' },
        { id: 2, name: 'Instalación de Cámaras de Seguridad y Alarmas' },
        { id: 3, name: 'Electricidad Industrial' }
    ];

    // Populate course options
    courses.forEach(course => {
        const option = document.createElement('option');
        option.value = course.name;
        option.textContent = course.name;
        courseCompletedSelect.appendChild(option);
    });

    certificateForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const studentName = document.getElementById('student-name').value;
        const studentDni = document.getElementById('student-dni').value;
        const courseCompleted = document.getElementById('course-completed').value;

        // Generate certificate code
        const certificateCode = 'UTN-FRH-' + Date.now();
        certificateCodeSpan.textContent = certificateCode;

        // Generate QR code
        qrCodeContainer.innerHTML = '';
        new QRCode(qrCodeContainer, {
            text: `Certificado: ${certificateCode}\nAlumno: ${studentName}\nDNI: ${studentDni}\nCurso: ${courseCompleted}`,
            width: 128,
            height: 128,
        });
    });
});