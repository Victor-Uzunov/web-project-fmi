<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/course_manager.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];

// --- Template Rendering ---
$title = "My Courses Graph";
$content_template_path = __DIR__ . '/templates/my_courses_graph.php';

// Render the main layout
include __DIR__ . '/templates/layout.php';
?> 