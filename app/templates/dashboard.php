<?php
// app/templates/dashboard.php

// Variables expected from index.php:
// $message_for_form
// $courses_to_display
// $prerequisite_options
// $departments_enum
// $current_search_name
// $current_filter_department

// Include the add course form template
// Pass necessary variables to it
$message = $message_for_form;
$prerequisites_options = $prerequisite_options; // for add form's prereq dropdown
$departments_enum = $departments_enum; // for add form's department dropdown
include __DIR__ . '/add_course_form.php';

// --- Course Search and Filter Form ---
?>
<div class="mb-10 p-6 bg-yellow-50 rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-yellow-700 mb-4">Search & Filter Courses</h2>
    <form action="index.php" method="get" class="space-y-4 md:flex md:space-y-0 md:space-x-4">
        <div class="flex-1">
            <label for="search_name" class="block text-sm font-medium text-gray-700">Search by Name/Code:</label>
            <input type="text" id="search_name" name="search_name"
                   value="<?php echo htmlspecialchars($current_search_name); ?>"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
        </div>
        <div class="flex-1">
            <label for="filter_department" class="block text-sm font-medium text-gray-700">Filter by Department:</label>
            <select id="filter_department" name="filter_department"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                <option value="">All Departments</option>
                <?php foreach ($departments_enum as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept); ?>"
                        <?php echo ($current_filter_department === $dept) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:self-end">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Apply Filters
            </button>
            <?php if (!empty($current_search_name) || !empty($current_filter_department)): ?>
                <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 ml-2">
                    Clear Filters
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php
// Include the course list template
// Pass necessary variables to it
$courses = $courses_to_display;
$message = $message_for_form; // Re-use message variable name expected by list template
include __DIR__ . '/course_list.php';

// Include the edit course modal template
// Pass necessary variables to it
$prerequisites_options = $prerequisite_options; // for edit modal's prereq dropdown
$departments_enum = $departments_enum; // for edit modal's department dropdown
include __DIR__ . '/modals/edit_course_modal.php';

?>
