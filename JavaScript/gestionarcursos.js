document.addEventListener('DOMContentLoaded', () => {
    const courseForm = document.getElementById('course-form');
    const courseList = document.querySelector('.course-list');
    const formTitle = document.getElementById('form-title');
    const courseIdInput = document.getElementById('course-id');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');

    let courses = [
        { id: 1, name: 'Instalación de Paneles Solares', startDate: '2025-08-15', duration: 40, modality: 'Presencial', requirements: 'Conocimientos básicos de electricidad.', image: '' },
        { id: 2, name: 'Instalación de Cámaras de Seguridad y Alarmas', startDate: '2025-09-01', duration: 35, modality: 'Presencial', requirements: 'Ninguno.', image: '' },
        { id: 3, name: 'Electricidad Industrial', startDate: '2025-08-20', duration: 60, modality: 'Híbrida', requirements: 'Conocimientos de electricidad domiciliaria.', image: '' }
    ];

    function renderCourses() {
        courseList.innerHTML = '';
        courses.forEach(course => {
            const courseItem = document.createElement('div');
            courseItem.className = 'course-item';
            courseItem.innerHTML = `
                <div class="course-item-info">
                    <h3>${course.name}</h3>
                    <p>Inicio: ${course.startDate} - Duración: ${course.duration}hs - Modalidad: ${course.modality}</p>
                </div>
                <div class="course-item-actions">
                    <button class="edit-btn" data-id="${course.id}"><i class="fas fa-pencil-alt"></i></button>
                    <button class="delete-btn" data-id="${course.id}"><i class="fas fa-trash-alt"></i></button>
                </div>
            `;
            courseList.appendChild(courseItem);
        });
    }

    function resetForm() {
        formTitle.textContent = 'Agregar Curso';
        courseIdInput.value = '';
        courseForm.reset();
        cancelEditBtn.style.display = 'none';
    }

    courseList.addEventListener('click', (e) => {
        if (e.target.closest('.edit-btn')) {
            const id = e.target.closest('.edit-btn').dataset.id;
            const course = courses.find(c => c.id == id);
            if (course) {
                formTitle.textContent = 'Editar Curso';
                courseIdInput.value = course.id;
                document.getElementById('course-name').value = course.name;
                document.getElementById('course-start-date').value = course.startDate;
                document.getElementById('course-duration').value = course.duration;
                document.getElementById('course-modality').value = course.modality;
                document.getElementById('course-requirements').value = course.requirements;
                cancelEditBtn.style.display = 'inline-block';
            }
        }

        if (e.target.closest('.delete-btn')) {
            const id = e.target.closest('.delete-btn').dataset.id;
            courses = courses.filter(c => c.id != id);
            renderCourses();
        }
    });

    courseForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const id = courseIdInput.value;
        const newCourse = {
            id: id ? parseInt(id) : Date.now(),
            name: document.getElementById('course-name').value,
            startDate: document.getElementById('course-start-date').value,
            duration: document.getElementById('course-duration').value,
            modality: document.getElementById('course-modality').value,
            requirements: document.getElementById('course-requirements').value,
            image: ''
        };

        if (id) {
            courses = courses.map(c => c.id == id ? newCourse : c);
        } else {
            courses.push(newCourse);
        }

        renderCourses();
        resetForm();
    });

    cancelEditBtn.addEventListener('click', resetForm);

    renderCourses();
});