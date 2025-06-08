<?php
// app/index.php - Main application logic

require_once __DIR__ . '/config.php';        // Includes session_start() and getDbConnection()
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

// Handle form submissions for both adding and updating courses
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate common fields for both add and update operations
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $credits = (int)($_POST['credits'] ?? 0);
    $department = trim($_POST['department'] ?? '');
    
    // Get prerequisite IDs, ensuring they are integers and filter out invalid ones
    // This array will be empty if no prerequisites were selected.
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
            // Call the addCourse function from course_manager.php
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
            // Call the updateCourse function from course_manager.php
            $response = updateCourse($course_id, $current_user_id, $course_code, $course_name, $credits, $department, $prerequisite_ids);
            $message = $response['message'];
        }
    }
}

// --- Fetch Data for Display and Forms ---
// Fetch all courses for the current user, including their prerequisites for the list
$courses = getAllCoursesForUser($current_user_id);

// Fetch all available courses that can be selected as prerequisites
// This list will be passed to both the add course form and the edit course modal
$all_available_courses = getAllAvailableCoursesForPrerequisites($current_user_id);


// --- Template Rendering ---
// Set variables for the main layout and content templates
$title = "My Courses";

// This is the main content template that will be included inside layout.php
// It acts as a "dashboard" that combines the add form and the course list.
$content_template_path = __DIR__ . '/templates/dashboard.php';

// Prepare variables to be available in the templates.
// These variables will be in the scope when dashboard.php (and its included partials) is rendered.
$message_for_form = $message; // Message to display in the forms (add/update success/failure)
$courses_to_display = $courses; // Data for the course list table
$prerequisite_options = $all_available_courses; // Options for prerequisite dropdowns in forms (for both add and edit forms)

// Finally, render the main layout, which in turn includes the dashboard content.
include __DIR__ . '/templates/layout.php';

?>
