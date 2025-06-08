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
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 50%, rgba(236, 72, 153, 0.1) 100%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: -1;
        }
        .header-gradient {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 50%, #EC4899 100%);
            background-size: 200% 200%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .container {
            position: relative;
            z-index: 1;
        }
        .container::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.1) 0%, rgba(79, 70, 229, 0) 70%);
            border-radius: 50%;
            z-index: -1;
        }
        .container::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: -20px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.1) 0%, rgba(236, 72, 153, 0) 70%);
            border-radius: 50%;
            z-index: -1;
        }
        .decorative-circle {
            position: fixed;
            border-radius: 50%;
            opacity: 0.1;
            z-index: -1;
        }
        .circle-1 {
            top: 10%;
            left: 5%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #4F46E5 0%, transparent 70%);
        }
        .circle-2 {
            bottom: 10%;
            right: 5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #EC4899 0%, transparent 70%);
        }
        .circle-3 {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #7C3AED 0%, transparent 70%);
        }
    </style>
</head>
<body class="p-8">
    <!-- Decorative circles -->
    <div class="decorative-circle circle-1"></div>
    <div class="decorative-circle circle-2"></div>
    <div class="decorative-circle circle-3"></div>

    <div class="container mx-auto max-w-7xl bg-white rounded-lg shadow-xl p-8">
        <header class="flex justify-between items-center mb-8 rounded-md header-gradient p-6">
            <h1 class="text-4xl font-bold text-white">University Course Manager</h1>
            <?php if (isLoggedIn()): ?>
                <div class="text-lg text-white flex items-center">
                    <span>Welcome, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>!</span>
                    <form action="index.php" method="post" class="ml-4">
                        <button type="submit" name="logout"
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
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
                
                // Determine which view we're in
                $is_courses_view = in_array($current_page, ['index.php', 'all_courses.php']);
                $is_graph_view = in_array($current_page, ['my_courses_graph.php', 'all_courses_graph.php']);
                
                // Set up the alternative view links
                if ($is_courses_view) {
                    $alternative_link = $current_page === 'index.php' ? 'all_courses.php' : 'index.php';
                    $alternative_view = $current_page === 'index.php' ? 'All Courses' : 'My Courses';
                    $graph_link = $current_page === 'index.php' ? 'my_courses_graph.php' : 'all_courses_graph.php';
                } else {
                    $courses_link = $current_page === 'my_courses_graph.php' ? 'index.php' : 'all_courses.php';
                    $alternative_link = $current_page === 'my_courses_graph.php' ? 'all_courses_graph.php' : 'my_courses_graph.php';
                    $alternative_view = $current_page === 'my_courses_graph.php' ? 'All Courses Graph' : 'My Courses Graph';
                }
                ?>

                <?php if ($is_courses_view): ?>
                    <a href="<?php echo $alternative_link; ?>" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Switch to <?php echo $alternative_view; ?>
                    </a>
                    <a href="<?php echo $graph_link; ?>" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Show Graph
                    </a>
                <?php else: ?>
                    <a href="<?php echo $courses_link; ?>" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Switch to <?php echo $current_page === 'my_courses_graph.php' ? 'My Courses' : 'All Courses'; ?>
                    </a>
                    <a href="<?php echo $alternative_link; ?>" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Switch to <?php echo $alternative_view; ?>
                    </a>
                <?php endif; ?>
            </div>
        </nav>

        <main>
            <?php include $content_template_path; ?>
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
