<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../course_manager.php';


if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$system_user_id = getUserIdByUsername(SYSTEM_USERNAME);

if ($system_user_id === null) {
    die("System user not found. Please contact support.");
}


$courses = getAllCoursesForUser($current_user_id, $system_user_id);


header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="courses_export.csv"');


$output = fopen('php://output', 'w');


fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));


fputcsv($output, ['course_code', 'course_name', 'credits', 'department', 'dependencies']);


foreach ($courses as $course) {
    $dependencies = '';
    if (!empty($course['prerequisites'])) {
        $dependencies = implode(',', $course['prerequisites']);
    }

    fputcsv($output, [
        $course['course_code'],
        $course['course_name'],
        $course['credits'],
        $course['department'],
        $dependencies
    ]);
}


fclose($output);
exit();
