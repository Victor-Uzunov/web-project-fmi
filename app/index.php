<?php
// app/index.php - Main application logic

require_once __DIR__ . '/config.php';        // Includes session_start(), getDbConnection(), DEPARTMENTS, SYSTEM_USERNAME, getUserIdByUsername
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
$system_user_id = getUserIdByUsername(SYSTEM_USERNAME); // Get the ID of the global system user

// Ensure system user exists, otherwise global courses cannot be managed/displayed properly
if ($system_user_id === null) {
    error_log("CRITICAL ERROR: 'system' user not found in the database. Global courses will not function correctly.");
    // You might want to display a user-friendly error message here as well.
}


// --- Handle Form Submissions (POST requests) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate common fields for add/update operations
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $credits = (int)($_POST['credits'] ?? 0);
    $department = trim($_POST['department'] ?? '');

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
            // Courses are added by the current logged-in user
            $response = addCourse($current_user_id, $course_code, $course_name, $credits, $department, $prerequisite_ids);
            $message = $response['message'];
        }
    } elseif (isset($_POST["update_course"])) {
        $course_id = (int)($_POST['course_id'] ?? 0); // Get the ID of the course being updated
        // Determine the actual owner of the course being updated
        // This is important to ensure only owner can update.
        // We fetch the course to ensure the current user (or system user for global courses) has permission.
        $target_course_owner_id = null;
        $conn_check_owner = getDbConnection(); // Use a new connection for a quick check
        $stmt_check_owner = $conn_check_owner->prepare("SELECT user_id FROM courses WHERE id = ?");
        if ($stmt_check_owner) {
            $stmt_check_owner->bind_param("i", $course_id);
            $stmt_check_owner->execute();
            $result_check_owner = $stmt_check_owner->get_result();
            if ($row_check_owner = $result_check_owner->fetch_assoc()) {
                $target_course_owner_id = $row_check_owner['user_id'];
            }
            $stmt_check_owner->close();
        }
        $conn_check_owner->close();

        // Only allow update if current user is the owner OR is the system user and updating a global course
        if ($target_course_owner_id !== null && ($target_course_owner_id === $current_user_id || ($target_course_owner_id === $system_user_id && $current_user_id === $system_user_id))) {
            // Validation for updating a course
            if ($course_id <= 0 || empty($course_code) || empty($course_name) || empty($credits)) {
                $message = "Invalid input for course update. Please fill all required fields.";
            } else if ($credits < 1) {
                $message = "Credits must be a positive number.";
            } else {
                // Pass the *actual owner's ID* to updateCourse, not the current_user_id if it's a global course
                // This ensures the WHERE clause in updateCourse correctly finds the record
                $response = updateCourse($course_id, $target_course_owner_id, $course_code, $course_name, $credits, $department, $prerequisite_ids);
                $message = $response['message'];
            }
        } else {
            $message = "You do not have permission to update this course.";
        }
    } elseif (isset($_POST["delete_course"])) {
        $course_id = (int)($_POST['course_id'] ?? 0); // Get the ID of the course to delete

        $target_course_owner_id = null;
        $conn_check_owner = getDbConnection();
        $stmt_check_owner = $conn_check_owner->prepare("SELECT user_id FROM courses WHERE id = ?");
        if ($stmt_check_owner) {
            $stmt_check_owner->bind_param("i", $course_id);
            $stmt_check_owner->execute();
            $result_check_owner = $stmt_check_owner->get_result();
            if ($row_check_owner = $result_check_owner->fetch_assoc()) {
                $target_course_owner_id = $row_check_owner['user_id'];
            }
            $stmt_check_owner->close();
        }
        $conn_check_owner->close();

        // Only allow deletion if current user is the owner OR is the system user and deleting a global course
        if ($target_course_owner_id !== null && ($target_course_owner_id === $current_user_id || ($target_course_owner_id === $system_user_id && $current_user_id === $system_user_id))) {
            if ($course_id <= 0) {
                $message = "Invalid course ID for deletion.";
            } else {
                // Pass the *actual owner's ID* to deleteCourse
                $response = deleteCourse($course_id, $target_course_owner_id);
                $message = $response['message'];
            }
        } else {
            $message = "You do not have permission to delete this course.";
        }
    }
}

// --- Handle Search and Filter (GET requests) ---
$search_name = trim($_GET['search_name'] ?? '');
$filter_department = trim($_GET['filter_department'] ?? '');
$prereq_search_term = trim($_GET['prereq_search_term'] ?? ''); // Used for AJAX/JS filtering of prerequisites

// --- Fetch Data for Display and Forms ---
// Pass both current user ID and system user ID to fetch all relevant courses
$courses = getAllCoursesForUser($current_user_id, $system_user_id, $search_name, $filter_department);

// Fetch all available courses (current user's and global) that can be selected as prerequisites
$all_available_courses = getAllAvailableCoursesForPrerequisites($current_user_id, $system_user_id);


// --- Template Rendering ---
// Set variables for the main layout and content templates
$title = "My Courses";

// This is the main content template that will be included inside layout.php
$content_template_path = __DIR__ . '/templates/dashboard.php';

// Prepare variables to be available in the templates.
$message_for_form = $message;               // Message to display in the forms (add/update/delete success/failure)
$courses_to_display = $courses;             // Data for the course list table
$prerequisite_options = $all_available_courses; // Options for prerequisite dropdowns in forms
$departments_enum = DEPARTMENTS;            // The array of allowed department names
$current_search_name = $search_name;        // Pass back to the search form for sticky input
$current_filter_department = $filter_department; // Pass back to the filter form for sticky selection

// Finally, render the main layout, which in turn includes the dashboard content.
include __DIR__ . '/templates/layout.php';

?>
