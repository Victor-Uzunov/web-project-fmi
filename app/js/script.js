// app/js/script.js

// Function to open the edit course modal and populate its fields
function openEditModal(course) {
    console.log('Opening edit modal for course:', course);
    const modal = document.getElementById('editCourseModal');
    console.log('Modal element:', modal);
    
    const courseCodeField = document.getElementById('edit_course_code');
    const oldCourseCodeField = document.getElementById('edit_old_course_code');
    const courseNameField = document.getElementById('edit_course_name');
    const creditsField = document.getElementById('edit_credits');
    const departmentSelect = document.getElementById('edit_department');
    const prerequisiteSearchInput = document.getElementById('edit_prereq_search');
    const prerequisiteSelect = document.getElementById('edit_prerequisites');

    courseIdField.value = course.id;
    courseCodeField.value = course.course_code;
    courseNameField.value = course.course_name;
    creditsField.value = course.credits;
    departmentSelect.value = course.department;

    for (let i = 0; i < prerequisiteSelect.options.length; i++) {
        prerequisiteSelect.options[i].selected = false;
    }

    if (course.prerequisites && Array.isArray(course.prerequisites) && course.prerequisites.length > 0) {
        const currentPrerequisiteIds = course.prerequisites.map(p => String(p.id));

        for (let i = 0; i < prerequisiteSelect.options.length; i++) {
            if (currentPrerequisiteCodes.includes(prerequisiteSelect.options[i].value)) {
                prerequisiteSelect.options[i].selected = true;
            }
        }
    }

    prerequisiteSearchInput.value = '';
    filterSelectOptions('edit_prerequisites', 'edit_prereq_search');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    const modal = document.getElementById('editCourseModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function deleteCourse(courseId, courseName) {
    if (confirm(`Are you sure you want to delete the course "${courseName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php';

        const inputCode = document.createElement('input');
        inputCode.type = 'hidden';
        inputCode.name = 'course_code';
        inputCode.value = courseCode;
        form.appendChild(inputCode);

        const inputDelete = document.createElement('input');
        inputDelete.type = 'hidden';
        inputDelete.name = 'delete_course';
        inputDelete.value = 'true';
        form.appendChild(inputDelete);

        document.body.appendChild(form);
        form.submit();
    }
}

function filterSelectOptions(selectId, searchInputId) {
    const select = document.getElementById(selectId);
    const searchTerm = document.getElementById(searchInputId).value.toLowerCase();

    for (let i = 0; i < select.options.length; i++) {
        const option = select.options[i];
        const optionText = option.textContent.toLowerCase();
        
        if (option.disabled && optionText.includes('no other courses available')) {
            option.style.display = '';
        } else if (optionText.includes(searchTerm)) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const addPrereqSearch = document.getElementById('prereq_search');
    if (addPrereqSearch) {
        addPrereqSearch.addEventListener('keyup', () => {
            filterSelectOptions('prerequisites', 'prereq_search');
        });
    }

    const editPrereqSearch = document.getElementById('edit_prereq_search');
    if (editPrereqSearch) {
        editPrereqSearch.addEventListener('keyup', () => {
            filterSelectOptions('edit_prerequisites', 'edit_prereq_search');
        });
    }

    const modal = document.getElementById('editCourseModal');
    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeEditModal();
            }
        });
    }
});
