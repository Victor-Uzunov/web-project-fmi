// app/js/script.js

// Function to open the edit course modal and populate its fields
function openEditModal(course) {
    const modal = document.getElementById('editCourseModal');
    
    const courseIdField = document.getElementById('edit_course_id');
    const courseCodeField = document.getElementById('edit_course_code');
    const courseNameField = document.getElementById('edit_course_name');
    const creditsField = document.getElementById('edit_credits');
    const departmentSelect = document.getElementById('edit_department'); // It's now a select
    const prerequisiteSearchInput = document.getElementById('edit_prereq_search'); // New search input for prerequisites
    const prerequisiteSelect = document.getElementById('edit_prerequisites');

    // Populate form fields with existing course data
    courseIdField.value = course.id;
    courseCodeField.value = course.course_code;
    courseNameField.value = course.course_name;
    creditsField.value = course.credits;
    departmentSelect.value = course.department; // Set the selected department

    // Clear previous selections in the prerequisites dropdown
    for (let i = 0; i < prerequisiteSelect.options.length; i++) {
        prerequisiteSelect.options[i].selected = false;
    }

    // Select current prerequisites for this course in the dropdown
    if (course.prerequisites && Array.isArray(course.prerequisites) && course.prerequisites.length > 0) {
        const currentPrerequisiteIds = course.prerequisites.map(p => String(p.id)); // Convert to string for comparison

        for (let i = 0; i < prerequisiteSelect.options.length; i++) {
            if (currentPrerequisiteIds.includes(prerequisiteSelect.options[i].value)) {
                prerequisiteSelect.options[i].selected = true;
            }
        }
    }

    // Reset prerequisite search input and apply initial filter
    prerequisiteSearchInput.value = '';
    filterSelectOptions('edit_prerequisites', 'edit_prereq_search'); // Apply filter to show all options initially

    // Display the modal
    modal.classList.remove('hidden');
    modal.classList.add('flex'); // Use flex for centering with Tailwind
}

// Function to close the edit course modal
function closeEditModal() {
    const modal = document.getElementById('editCourseModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Function to handle course deletion
function deleteCourse(courseId, courseName) {
    // Using a simple browser confirm for now as per "pop up dialog" request
    if (confirm(`Are you sure you want to delete the course "${courseName}"? This action cannot be undone.`)) {
        // Create a hidden form to send the POST request for deletion
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php'; // Submit to the same page that handles logic

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'course_id';
        inputId.value = courseId;
        form.appendChild(inputId);

        const inputDelete = document.createElement('input');
        inputDelete.type = 'hidden';
        inputDelete.name = 'delete_course';
        inputDelete.value = 'true'; // A flag to indicate delete action
        form.appendChild(inputDelete);

        document.body.appendChild(form); // Append the form to the body
        form.submit(); // Submit the form
    }
}

// Function to filter options in a select dropdown based on a search input
function filterSelectOptions(selectId, searchInputId) {
    const select = document.getElementById(selectId);
    const searchTerm = document.getElementById(searchInputId).value.toLowerCase();

    for (let i = 0; i < select.options.length; i++) {
        const option = select.options[i];
        const optionText = option.textContent.toLowerCase();
        
        // If the option is the disabled placeholder, always show it.
        // Otherwise, filter based on search term.
        if (option.disabled && optionText.includes('no other courses available')) {
            option.style.display = '';
        } else if (optionText.includes(searchTerm)) {
            option.style.display = ''; // Show
        } else {
            option.style.display = 'none'; // Hide
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

    // Close modal when clicking outside (optional, but good UX)
    const modal = document.getElementById('editCourseModal');
    if (modal) {
        modal.addEventListener('click', (event) => {
            // Check if the click was directly on the modal backdrop, not inside the modal content
            if (event.target === modal) {
                closeEditModal();
            }
        });
    }
});
