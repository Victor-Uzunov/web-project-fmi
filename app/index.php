<?php
// app/index.php - Main application logic

require_once __DIR__ . '/config.php';    
require_once __DIR__ . '/auth.php';         
require_once __DIR__ . '/course_manager.php';

$message = '';


if (isset($_POST['logout'])) {
    logoutUser();
}


if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$system_user_id = getUserIdByUsername(SYSTEM_USERNAME);

if ($system_user_id === null) {
    error_log("CRITICAL ERROR: 'system' user not found in the database. Global courses will not function correctly.");
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $credits = (int)($_POST['credits'] ?? 0);
    $department = trim($_POST['department'] ?? '');

    $prerequisite_ids = isset($_POST['prerequisites']) ? (array)$_POST['prerequisites'] : [];
    $prerequisite_ids = array_map('intval', $prerequisite_ids);
    $prerequisite_ids = array_filter($prerequisite_ids, function($id) { return $id > 0; });


    if (isset($_POST["add_course"])) {
        if (empty($course_code) || empty($course_name) || empty($credits)) {
            $message = "Please fill in all required course fields (Code, Name, Credits).";
        } else if ($credits < 1) {
            $message = "Credits must be a positive number.";
        } else {
            $response = addCourse($current_user_id, $course_code, $course_name, $credits, $department, $prerequisite_ids);
            $message = $response['message'];
        }
    } elseif (isset($_POST["update_course"])) {
        $course_id = (int)($_POST['course_id'] ?? 0); 
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

        if ($target_course_owner_id !== null && ($target_course_owner_id === $current_user_id || ($target_course_owner_id === $system_user_id && $current_user_id === $system_user_id))) {
            if ($course_id <= 0 || empty($course_code) || empty($course_name) || empty($credits)) {
                $message = "Invalid input for course update. Please fill all required fields.";
            } else if ($credits < 1) {
                $message = "Credits must be a positive number.";
            } else {
                $response = updateCourse($course_id, $target_course_owner_id, $course_code, $course_name, $credits, $department, $prerequisite_ids);
                $message = $response['message'];
            }
        } else {
            $message = "You do not have permission to update this course.";
        }
    } elseif (isset($_POST["delete_course"])) {
        $course_id = (int)($_POST['course_id'] ?? 0);

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

        if ($target_course_owner_id !== null && ($target_course_owner_id === $current_user_id || ($target_course_owner_id === $system_user_id && $current_user_id === $system_user_id))) {
            if ($course_id <= 0) {
                $message = "Invalid course ID for deletion.";
            } else {
                $response = deleteCourse($course_id, $target_course_owner_id);
                $message = $response['message'];
            }
        } else {
            $message = "You do not have permission to delete this course.";
        }
    }
}


$search_name = trim($_GET['search_name'] ?? '');
$filter_department = trim($_GET['filter_department'] ?? '');
$prereq_search_term = trim($_GET['prereq_search_term'] ?? '');

$courses = getManuallyAddedCourses($current_user_id, $search_name, $filter_department);

$all_available_courses = getAllAvailableCoursesForPrerequisites($current_user_id, $system_user_id);

$title = "My Courses";

$content_template_path = __DIR__ . '/templates/dashboard.php';

$message_for_form = $message;
$courses_to_display = $courses;
$prerequisite_options = $all_available_courses;
$departments_enum = DEPARTMENTS;
$current_search_name = $search_name;
$current_filter_department = $filter_department;

include __DIR__ . '/templates/layout.php';

?>
