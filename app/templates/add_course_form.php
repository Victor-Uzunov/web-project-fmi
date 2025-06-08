<?php
// app/templates/add_course_form.php

// This file expects the $message variable and $prerequisites_options, $departments_enum to be available from the parent scope.
?>
<div class="mb-10 p-6 bg-blue-50 rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-blue-700 mb-4">Add New Course</h2>
    <?php if (!empty($message)): ?>
        <p class="<?php echo strpos($message, 'successfully') !== false ? 'text-green-600' : 'text-red-600'; ?> font-semibold mb-4"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form action="index.php" method="post" class="space-y-4">
        <div>
            <label for="course_code" class="block text-sm font-medium text-gray-700">Course Code:</label>
            <input type="text" id="course_code" name="course_code" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="course_name" class="block text-sm font-medium text-gray-700">Course Name:</label>
            <input type="text" id="course_name" name="course_name" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="credits" class="block text-sm font-medium text-gray-700">Credits:</label>
            <input type="number" id="credits" name="credits" required min="1"
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="department" class="block text-sm font-medium text-gray-700">Department:</label>
            <select id="department" name="department" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php foreach ($departments_enum as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="prereq_search" class="block text-sm font-medium text-gray-700">Search Prerequisites:</label>
            <input type="text" id="prereq_search" placeholder="Type to search courses..."
                   class="mb-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

            <label for="prerequisites" class="block text-sm font-medium text-gray-700">Prerequisites (Ctrl/Cmd + click to select multiple):</label>
            <select id="prerequisites" name="prerequisites[]" multiple
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm h-32 overflow-y-auto">
                <?php if (!empty($prerequisites_options)): ?>
                    <?php foreach ($prerequisites_options as $option): ?>
                        <option value="<?php echo htmlspecialchars($option['id']); ?>">
                            <?php echo htmlspecialchars($option['course_code'] . ' - ' . $option['course_name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option disabled>No other courses available as prerequisites.</option>
                <?php endif; ?>
            </select>
        </div>
        <button type="submit" name="add_course"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Add Course
        </button>
    </form>
</div>
