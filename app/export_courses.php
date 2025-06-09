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

// Get the export type from the request
$export_type = $_GET['type'] ?? '';

// Get courses based on export type
if ($export_type === 'all') {
    $courses = getAllCoursesForUser($current_user_id, $system_user_id);
} else {
    // Default to user's courses
    $courses = getManuallyAddedCourses($current_user_id);
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="courses_export.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV header
fputcsv($output, ['course_code', 'course_name', 'credits', 'department', 'dependencies']);

// Write course data
foreach ($courses as $course) {
    $dependencies = '';
    if (!empty($course['prerequisites'])) {
        $dependencies = implode(',', array_map(function($prereq) {
            return $prereq['course_code'];
        }, $course['prerequisites']));
    }
    
    fputcsv($output, [
        $course['course_code'],
        $course['course_name'],
        $course['credits'],
        $course['department'],
        $dependencies
    ]);
}

// Close the output stream
fclose($output);
exit(); 