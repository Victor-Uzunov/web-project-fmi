<?php

require_once __DIR__ . '/config.php';        // Includes session_start() and getDbConnection()
require_once __DIR__ . '/auth.php';          // Includes authentication functions
require_once __DIR__ . '/course_manager.php'; // Includes simplified course management functions

$message = '';

if (isset($_POST['logout'])) {
    logoutUser();
}

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// --- Course Management Logic (only for logged-in users) ---
$current_user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_course"])) {
    // Sanitize and validate fields for adding a course
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $credits = (int)($_POST['credits'] ?? 0);
    $department = trim($_POST['department'] ?? '');

    if (empty($course_code) || empty($course_name) || empty($credits)) {
        $message = "Please fill in all required course fields (Code, Name, Credits).";
    } else if ($credits < 1) {
        $message = "Credits must be a positive number.";
    } else {
        // Call the simplified addCourse function from course_manager.php
        $response = addCourse($current_user_id, $course_code, $course_name, $credits, $department);
        $message = $response['message'];
    }
}

// --- Fetch Data for Display ---
// Fetch all courses for the current user (for the list)
$courses = getAllCoursesForUser($current_user_id);


// --- Template Rendering ---
// Set variables for the main layout and content templates
$title = "My Courses";

// This is the main content template that will be included inside layout.php
// It acts as a "dashboard" that combines the add form and the course list.
$content_template_path = __DIR__ . '/templates/dashboard.php';

// Prepare variables to be available in the templates.
// These variables will be in the scope when dashboard.php (and its included partials) is rendered.
$message_for_form = $message; // Message to display in the forms (add success/failure)
$courses_to_display = $courses; // Data for the course list table


// Finally, render the main layout, which in turn includes the dashboard content.
include __DIR__ . '/templates/layout.php';

?>
