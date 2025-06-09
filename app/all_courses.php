<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/course_manager.php';


if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$system_user_id = getUserIdByUsername(SYSTEM_USERNAME);

if ($system_user_id === null) {
    die("System user not found. Please contact support.");
}


$search_name = trim($_GET['search_name'] ?? '');
$filter_department = trim($_GET['filter_department'] ?? '');



$courses = getAllCoursesForUser($current_user_id, $system_user_id, $search_name, $filter_department);


$title = "All Courses";
$content_template_path = __DIR__ . '/templates/all_courses.php';


$courses_to_display = $courses;
$departments_enum = DEPARTMENTS;
$current_search_name = $search_name;
$current_filter_department = $filter_department;
$system_user_id = $system_user_id;


include __DIR__ . '/templates/layout.php';
?>
