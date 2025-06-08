<?php
// app/graph.php - Course Dependency Graph Visualization

require_once __DIR__ . '/config.php';        // Includes session_start() and getDbConnection()
require_once __DIR__ . '/auth.php';          // Includes authentication functions
// No need for course_manager.php directly here, as graph_view.php will fetch data via API

// --- Check Authentication ---
if (!isLoggedIn()) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// --- Template Rendering ---
// Set variables for the main layout
$title = "Course Dependency Graph";

// This is the main content template that will be included inside layout.php
$content_template_path = __DIR__ . '/templates/graph_view.php';

// Finally, render the main layout.
include __DIR__ . '/templates/layout.php';

?>
