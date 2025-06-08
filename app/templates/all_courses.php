<?php
// app/templates/all_courses.php

// Variables expected from all_courses.php:
// $courses_to_display
// $departments_enum
// $current_search_name
// $current_filter_department
?>

<div class="mb-10 p-6 bg-yellow-50 rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-yellow-700 mb-4">Search & Filter Courses</h2>
    <form action="all_courses.php" method="get" class="space-y-4 md:flex md:space-y-0 md:space-x-4">
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
                <a href="all_courses.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 ml-2">
                    Clear Filters
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="p-6 bg-green-50 rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-green-700 mb-4">All Available Courses</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 shadow-md rounded-lg overflow-hidden">
            <thead class="bg-green-100">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider rounded-tl-lg">Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Credits</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Department</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Prerequisites</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($courses_to_display)): ?>
                    <?php foreach ($courses_to_display as $course): ?>
                        <tr class='hover:bg-gray-50'>
                            <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'><?php echo htmlspecialchars($course['credits']); ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'><?php echo htmlspecialchars($course['department']); ?></td>
                            <td class='px-6 py-4 text-sm text-gray-700'>
                                <?php if (!empty($course['prerequisites'])): ?>
                                    <ul class="list-disc list-inside">
                                        <?php foreach ($course['prerequisites'] as $prereq): ?>
                                            <li><?php echo htmlspecialchars($prereq['course_code'] . ' - ' . $prereq['course_name']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    None
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan='5' class='px-6 py-4 text-sm text-gray-500 text-center'>No courses found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div> 