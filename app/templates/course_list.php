<?php
// app/templates/course_list.php

// $courses_to_display variable is passed from index.php
// $message_for_form variable is passed from index.php for displaying update success/failure
?>
<div class="p-6 bg-green-50 rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-green-700 mb-4">My Existing Courses</h2>
    <?php if (!empty($message_for_form)): ?>
        <p class="<?php echo strpos($message_for_form, 'successfully') !== false ? 'text-green-600' : 'text-red-600'; ?> font-semibold mb-4"><?php echo htmlspecialchars($message_for_form); ?></p>
    <?php endif; ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 shadow-md rounded-lg overflow-hidden">
            <thead class="bg-green-100">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider rounded-tl-lg">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Credits</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Department</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Prerequisites</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider rounded-tr-lg">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($courses_to_display)): ?>
                    <?php foreach ($courses_to_display as $course): ?>
                        <tr class='hover:bg-gray-50'>
                            <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'><?php echo htmlspecialchars($course['id']); ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'><?php echo htmlspecialchars($course['course_code']); ?></td>
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
                            <td class='px-6 py-4 whitespace-nowrap text-right text-sm font-medium'>
                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($course)); ?>)"
                                        class="text-indigo-600 hover:text-indigo-900 focus:outline-none focus:underline mr-4">
                                    Edit
                                </button>
                                <button onclick="deleteCourse(<?php echo htmlspecialchars($course['id']); ?>, '<?php echo htmlspecialchars($course['course_name']); ?>')"
                                        class="text-red-600 hover:text-red-900 focus:outline-none focus:underline">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan='7' class='px-6 py-4 text-sm text-gray-500 text-center'>No courses found for this user.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
