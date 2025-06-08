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

    console.log('Form fields:', {
        courseCodeField,
        oldCourseCodeField,
        courseNameField,
        creditsField,
        departmentSelect,
        prerequisiteSearchInput,
        prerequisiteSelect
    });

    // Populate form fields with existing course data
    oldCourseCodeField.value = course.course_code;
    courseCodeField.value = course.course_code;
    courseNameField.value = course.course_name;
    creditsField.value = course.credits;
    departmentSelect.value = course.department;

    // Clear previous selections in the prerequisites dropdown
    for (let i = 0; i < prerequisiteSelect.options.length; i++) {
        prerequisiteSelect.options[i].selected = false;
    }

    // Select current prerequisites for this course in the dropdown
    if (course.prerequisites && Array.isArray(course.prerequisites) && course.prerequisites.length > 0) {
        const currentPrerequisiteCodes = course.prerequisites.map(p => p.course_code);

        for (let i = 0; i < prerequisiteSelect.options.length; i++) {
            if (currentPrerequisiteCodes.includes(prerequisiteSelect.options[i].value)) {
                prerequisiteSelect.options[i].selected = true;
            }
        }
    }

    // Reset prerequisite search input and apply initial filter
    prerequisiteSearchInput.value = '';
    filterSelectOptions('edit_prerequisites', 'edit_prereq_search');

    // Display the modal
    console.log('Showing modal...');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    console.log('Modal classes after showing:', modal.classList);
}

// Function to close the edit course modal
function closeEditModal() {
    const modal = document.getElementById('editCourseModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Function to handle course deletion
function deleteCourse(courseCode, courseName) {
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

// Function to filter options in a select dropdown based on a search input
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

// Event listeners for prerequisite search inputs
document.addEventListener('DOMContentLoaded', () => {
    // For the add course form
    const addPrereqSearch = document.getElementById('prereq_search');
    if (addPrereqSearch) {
        addPrereqSearch.addEventListener('keyup', () => {
            filterSelectOptions('prerequisites', 'prereq_search');
        });
    }

    // For the edit course modal
    const editPrereqSearch = document.getElementById('edit_prereq_search');
    if (editPrereqSearch) {
        editPrereqSearch.addEventListener('keyup', () => {
            filterSelectOptions('edit_prerequisites', 'edit_prereq_search');
        });
    }

    // Close modal when clicking outside
    const modal = document.getElementById('editCourseModal');
    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeEditModal();
            }
        });
    }
});
