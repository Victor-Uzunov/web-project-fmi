<?php
// app/index.php - Main application logic

require_once __DIR__ . '/config.php';        // Includes session_start(), getDbConnection(), and DEPARTMENTS
require_once __DIR__ . '/auth.php';          // Includes authentication functions
require_once __DIR__ . '/course_manager.php'; // Includes course management functions

$message = ''; // For displaying success/error messages

// --- Handle Logout ---
if (isset($_POST['logout'])) {
    logoutUser(); // This will redirect to login.php
}

// --- Check Authentication ---
if (!isLoggedIn()) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// --- Course Management Logic (only for logged-in users) ---
$current_user_id = $_SESSION['user_id'];

// --- Handle Form Submissions (POST requests) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate common fields for add/update operations
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $credits = (int)($_POST['credits'] ?? 0);
    $department = trim($_POST['department'] ?? ''); // Department now comes from a select

    // Get prerequisite IDs, ensuring they are integers and filter out invalid ones
    $prerequisite_ids = isset($_POST['prerequisites']) ? (array)$_POST['prerequisites'] : [];
    $prerequisite_ids = array_map('intval', $prerequisite_ids);
    $prerequisite_ids = array_filter($prerequisite_ids, function($id) { return $id > 0; });


    if (isset($_POST["add_course"])) {
        // Validation for adding a course
        if (empty($course_code) || empty($course_name) || empty($credits)) {
            $message = "Please fill in all required course fields (Code, Name, Credits).";
        } else if ($credits < 1) {
            $message = "Credits must be a positive number.";
        } else {
            $response = addCourse($current_user_id, $course_code, $course_name, $credits, $department, $prerequisite_ids);
            $message = $response['message'];
        }
    } elseif (isset($_POST["update_course"])) {
        $course_id = (int)($_POST['course_id'] ?? 0); // Get the ID of the course being updated

        // Validation for updating a course
        if ($course_id <= 0 || empty($course_code) || empty($course_name) || empty($credits)) {
            $message = "Invalid input for course update. Please fill all required fields.";
        } else if ($credits < 1) {
            $message = "Credits must be a positive number.";
        } else {
            $response = updateCourse($course_id, $current_user_id, $course_code, $course_name, $credits, $department, $prerequisite_ids);
            $message = $response['message'];
        }
    } elseif (isset($_POST["delete_course"])) {
        $course_id = (int)($_POST['course_id'] ?? 0); // Get the ID of the course to delete
        if ($course_id <= 0) {
            $message = "Invalid course ID for deletion.";
        } else {
            $response = deleteCourse($course_id, $current_user_id);
            $message = $response['message'];
        }
    }
}

// --- Handle Search and Filter (GET requests) ---
$search_name = trim($_GET['search_name'] ?? '');
$filter_department = trim($_GET['filter_department'] ?? '');
$prereq_search_term = trim($_GET['prereq_search_term'] ?? ''); // Used for AJAX/JS filtering of prerequisites

// --- Fetch Data for Display and Forms ---
// Fetch all courses for the current user, applying search/filter
$courses = getAllCoursesForUser($current_user_id, $search_name, $filter_department);

// Fetch all available courses that can be selected as prerequisites
// This list will be passed to both the add course form and the edit course modal.
// This list is NOT filtered by the JavaScript search term, as JS handles that client-side.
$all_available_courses = getAllAvailableCoursesForPrerequisites($current_user_id);


// --- Template Rendering ---
// Set variables for the main layout and content templates
$title = "My Courses";

// This is the main content template that will be included inside layout.php
$content_template_path = __DIR__ . '/templates/dashboard.php';

// Prepare variables to be available in the templates.
// These variables will be in the scope when dashboard.php (and its included partials) is rendered.
$message_for_form = $message;               // Message to display in the forms (add/update/delete success/failure)
$courses_to_display = $courses;             // Data for the course list table
$prerequisite_options = $all_available_courses; // Options for prerequisite dropdowns in forms (for both add and edit forms)
$departments_enum = DEPARTMENTS;            // The array of allowed department names

$current_search_name = $search_name;        // Pass back to the search form for sticky input
$current_filter_department = $filter_department; // Pass back to the filter form for sticky selection

// Finally, render the main layout, which in turn includes the dashboard content.
include __DIR__ . '/templates/layout.php';

?>
