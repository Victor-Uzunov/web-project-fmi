<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$message = '';

if (isset($_POST['logout'])) {
    logoutUser();
}

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$conn = getDbConnection();
$current_user_id = $_SESSION['user_id'];

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
        $stmt = $conn->prepare("INSERT INTO courses (user_id, course_code, course_name, credits, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $current_user_id, $course_code, $course_name, $credits, $department); // "issis" -> integer, string, string, integer, string

        if ($stmt->execute()) {
            $message = "New course added successfully!";
        } else {
            if ($conn->errno == 1062) {
                $message = "Error: A course with this code already exists for you."; 
            } else {
                $message = "Error adding course: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

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

$title = "My Courses";
$content_template_path = __DIR__ . '/templates/dashboard.php';

include __DIR__ . '/templates/layout.php';

?>
