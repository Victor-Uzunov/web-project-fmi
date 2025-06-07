<?php?>
<div class="p-6 bg-green-50 rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-green-700 mb-4">My Existing Courses</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 shadow-md rounded-lg overflow-hidden">
            <thead class="bg-green-100">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider rounded-tl-lg">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Credits</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider rounded-tr-lg">Department</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $course): ?>
                        <tr class='hover:bg-gray-50'>
                            <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'><?php echo htmlspecialchars($course['id']); ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'><?php echo htmlspecialchars($course['credits']); ?></td>
                            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-700'><?php echo htmlspecialchars($course['department']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan='5' class='px-6 py-4 text-sm text-gray-500 text-center'>No courses found for this user.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
