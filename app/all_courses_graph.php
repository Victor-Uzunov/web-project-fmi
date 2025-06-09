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
$system_user_id = getUserIdByUsername(SYSTEM_USERNAME);

if ($system_user_id === null) {
    die("System user not found. Please contact support.");
}

$title = "All Courses Graph";
$content_template_path = __DIR__ . '/templates/all_courses_graph.php';

include __DIR__ . '/templates/layout.php';
?> 