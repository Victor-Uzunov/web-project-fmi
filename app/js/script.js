// app/js/script.js

// Function to open the edit course modal and populate its fields
function openEditModal(course) {
    const modal = document.getElementById('editCourseModal');
    const form = document.getElementById('editCourseForm');
    const prerequisiteSelect = document.getElementById('edit_prerequisites');

    // Populate form fields with existing course data
    document.getElementById('edit_course_id').value = course.id;
    document.getElementById('edit_course_code').value = course.course_code;
    document.getElementById('edit_course_name').value = course.course_name;
    document.getElementById('edit_credits').value = course.credits;
    document.getElementById('edit_department').value = course.department;

    // Clear previous selections in the prerequisites dropdown
    for (let i = 0; i < prerequisiteSelect.options.length; i++) {
        prerequisiteSelect.options[i].selected = false;
    }

    // Select current prerequisites for this course in the dropdown
    if (course.prerequisites && Array.isArray(course.prerequisites) && course.prerequisites.length > 0) {
        // Map current prerequisites to an array of their IDs (as strings for comparison with option values)
        const currentPrerequisiteIds = course.prerequisites.map(p => String(p.id));

        for (let i = 0; i < prerequisiteSelect.options.length; i++) {
            // If the option's value (prerequisite course ID) is in the currentPrerequisiteIds array, select it
            if (currentPrerequisiteIds.includes(prerequisiteSelect.options[i].value)) {
                prerequisiteSelect.options[i].selected = true;
            }
        }
    }

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

// Close modal when clicking outside (optional, but good UX)
document.addEventListener('DOMContentLoaded', () => {
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
