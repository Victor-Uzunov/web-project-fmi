<?php
// app/templates/layout.php

// Corrected path to config.php - it's in the parent directory of 'templates'
require_once __DIR__ . '/../config.php';
// Corrected path to auth.php - also in the parent directory of 'templates'
require_once __DIR__ . '/../auth.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'University Course Manager'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="p-8">
    <div class="container mx-auto max-w-7xl bg-white rounded-lg shadow-xl p-8">
        <header class="flex justify-between items-center mb-8 rounded-md bg-indigo-100 p-4">
            <h1 class="text-4xl font-bold text-indigo-700">University Course Manager</h1>
            <?php if (isLoggedIn()): // Use the helper function ?>
                <div class="text-lg text-gray-700 flex items-center">
                    <span>Welcome, <span class="font-semibold text-indigo-800"><?php echo htmlspecialchars($_SESSION['username']); ?></span>!</span>
                    <form action="index.php" method="post" class="ml-4">
                        <button type="submit" name="logout"
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Logout
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </header>

        <nav class="mb-8 bg-white rounded-lg shadow-sm p-4">
            <div class="flex space-x-4">
                <?php
                // Get the current page name
                $current_page = basename($_SERVER['PHP_SELF']);
                
                // Determine which view we're in and what the alternative view should be
                $is_courses_view = in_array($current_page, ['index.php', 'all_courses.php']);
                $is_graph_view = in_array($current_page, ['my_courses_graph.php', 'all_courses_graph.php']);
                
                if ($is_courses_view) {
                    $current_view = $current_page === 'index.php' ? 'My Courses' : 'All Courses';
                    $alternative_view = $current_page === 'index.php' ? 'All Courses' : 'My Courses';
                    $alternative_link = $current_page === 'index.php' ? 'all_courses.php' : 'index.php';
                    $graph_link = $current_page === 'index.php' ? 'my_courses_graph.php' : 'all_courses_graph.php';
                } else if ($is_graph_view) {
                    $current_view = $current_page === 'my_courses_graph.php' ? 'My Courses Graph' : 'All Courses Graph';
                    $alternative_view = $current_page === 'my_courses_graph.php' ? 'All Courses Graph' : 'My Courses Graph';
                    $alternative_link = $current_page === 'my_courses_graph.php' ? 'all_courses_graph.php' : 'my_courses_graph.php';
                    $courses_link = $current_page === 'my_courses_graph.php' ? 'index.php' : 'all_courses.php';
                }
                ?>

                <?php if ($is_courses_view): ?>
                    <a href="<?php echo $alternative_link; ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Switch to <?php echo $alternative_view; ?>
                    </a>
                    <a href="<?php echo $graph_link; ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Show Graph
                    </a>
                <?php else: ?>
                    <a href="<?php echo $courses_link; ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Switch to <?php echo $current_page === 'my_courses_graph.php' ? 'My Courses' : 'All Courses'; ?>
                    </a>
                    <a href="<?php echo $alternative_link; ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Switch to <?php echo $alternative_view; ?>
                    </a>
                <?php endif; ?>
            </div>
        </nav>

        <main>
            <?php include $content_template_path; // This variable holds the path to the main content template ?>
        </main>
    </div>

    <!-- Include JavaScript for modal functionality -->
    <script src="js/script.js"></script>
    <script>
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('[id$="Dropdown"]');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(event.target) && !event.target.matches('button')) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        // Toggle dropdown visibility
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const allDropdowns = document.querySelectorAll('[id$="Dropdown"]');
            
            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== dropdownId) {
                    d.classList.add('hidden');
                }
            });
            
            // Toggle the clicked dropdown
            dropdown.classList.toggle('hidden');
        }
    </script>
</body>
</html>
