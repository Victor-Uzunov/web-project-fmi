<?php
// app/templates/modals/edit_course_modal.php
// This modal expects $prerequisite_options and $departments_enum to be available from the parent scope.
?>
<script>
    console.log('Edit modal template loaded');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Edit modal element exists:', !!document.getElementById('editCourseModal'));
    });
</script>

<div id="editCourseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-indigo-700">Edit Course</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 text-3xl font-bold" onclick="closeEditModal()">
                &times;
            </button>
        </div>

        <form action="index.php" method="post" id="editCourseForm" class="space-y-4">
            <input type="hidden" name="old_course_code" id="edit_old_course_code">
            <input type="hidden" name="course_code" id="edit_course_code">

            <div>
                <label for="edit_course_code" class="block text-sm font-medium text-gray-700">Course Code:</label>
                <input type="text" id="edit_course_code" name="course_code" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="edit_course_name" class="block text-sm font-medium text-gray-700">Course Name:</label>
                <input type="text" id="edit_course_name" name="course_name" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="edit_credits" class="block text-sm font-medium text-gray-700">Credits:</label>
                <input type="number" id="edit_credits" name="credits" required min="1"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="edit_department" class="block text-sm font-medium text-gray-700">Department:</label>
                <select id="edit_department" name="department" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <?php foreach ($departments_enum as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="edit_prereq_search" class="block text-sm font-medium text-gray-700">Search Prerequisites:</label>
                <input type="text" id="edit_prereq_search" placeholder="Type to search courses..."
                       class="mb-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                <label for="edit_prerequisites" class="block text-sm font-medium text-gray-700">Prerequisites (Ctrl/Cmd + click to select multiple):</label>
                <select id="edit_prerequisites" name="prerequisites[]" multiple
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-32 overflow-y-auto">
                    <?php if (!empty($prerequisite_options)): ?>
                        <?php foreach ($prerequisite_options as $option): ?>
                            <option value="<?php echo htmlspecialchars($option['course_code']); ?>">
                                <?php echo htmlspecialchars($option['course_code'] . ' - ' . $option['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option disabled>No other courses available as prerequisites.</option>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" name="update_course"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Update Course
            </button>
        </form>
    </div>
</div>
