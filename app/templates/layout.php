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
                    <a href="index.php" class="text-indigo-600 hover:text-indigo-800 mr-4">My Courses</a>
                    <a href="all_courses.php" class="text-indigo-600 hover:text-indigo-800 mr-4">All Courses</a>
                    <a href="graph.php" class="text-indigo-600 hover:text-indigo-800 mr-4">Course Graph</a>
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

        <main>
            <?php include $content_template_path; // This variable holds the path to the main content template ?>
        </main>
    </div>

    <!-- Include JavaScript for modal functionality -->
    <script src="js/script.js"></script>
</body>
</html>
