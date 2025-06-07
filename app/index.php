<?php
// app/index.php - Main application logic

require_once __DIR__ . '/config.php'; // Includes session_start() and getDbConnection()
require_once __DIR__ . '/auth.php';   // Includes authentication functions

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
$conn = getDbConnection();
$current_user_id = $_SESSION['user_id'];

// Handle form submission to add a new course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_course"])) {
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $credits = (int)$_POST['credits'];
    $department = trim($_POST['department']);

    if (empty($course_code) || empty($course_name) || empty($credits)) {
        $message = "Please fill in all required course fields (Code, Name, Credits).";
    } else if ($credits < 1) {
        $message = "Credits must be a positive number.";
    } else {
        // Prepare an SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO courses (user_id, course_code, course_name, credits, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $current_user_id, $course_code, $course_name, $credits, $department); // "issis" -> integer, string, string, integer, string

        if ($stmt->execute()) {
            $message = "New course added successfully!";
        } else {
            // Check for duplicate entry error specifically (e.g., if course_code was unique per user)
            if ($conn->errno == 1062) { // MySQL error code for duplicate entry
                $message = "Error: A course with this code already exists for you.";
            } else {
                $message = "Error adding course: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Fetch and display existing courses for the current user
$courses = [];
$sql = "SELECT id, course_code, course_name, credits, department FROM courses WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
$stmt->close();
$conn->close();

// --- Template Rendering ---
// Set variables for the layout and content templates
$title = "My Courses";
$content_template = __DIR__ . '/templates/add_course_form.php'; // The form template
$message_for_form = $message; // Pass the message to the form template

// We need to include the course list separately or as part of the content_template
// For simplicity, we'll just include it directly after the form in the layout context.
// Alternatively, you could have a single "dashboard" template that includes both.

// This is the order of inclusion: layout.php includes add_course_form.php and then course_list.php
ob_start(); // Start output buffering
include __DIR__ . '/templates/add_course_form.php'; // Include the form
include __DIR__ . '/templates/course_list.php';    // Include the list
$dashboard_content = ob_get_clean(); // Get buffered content and clear buffer

// Now include the main layout, passing the combined content
$content_template = 'data:text/html;base64,' . base64_encode($dashboard_content); // A trick to pass raw HTML as a template path for simple inclusion

// Finally, render the main layout
include __DIR__ . '/templates/layout.php';

?>
