<?php
// app/course_manager.php

require_once __DIR__ . '/config.php'; // For getDbConnection()

/**
 * Adds a new course to the database for a specific user.
 *
 * @param int    $user_id       The ID of the user creating the course.
 * @param string $course_code   The unique code for the course (per user).
 * @param string $course_name   The name of the course.
 * @param int    $credits       The number of credits for the course.
 * @param string $department    The department the course belongs to.
 * @return array An associative array with 'success' (bool) and 'message' (string).
 */
function addCourse($user_id, $course_code, $course_name, $credits, $department) {
    $conn = getDbConnection();
    $conn->begin_transaction(); // Start transaction for atomicity

    try {
        // 1. Insert the new course
        $stmt = $conn->prepare("INSERT INTO courses (user_id, course_code, course_name, credits, department) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("issis", $user_id, $course_code, $course_name, $credits, $department);
        if (!$stmt->execute()) {
            // Check for unique constraint violation (user_id, course_code)
            if ($conn->errno == 1062) {
                throw new Exception("A course with code '{$course_code}' already exists for your account.");
            }
            throw new Exception("Error adding course: " . $stmt->error);
        }
        $stmt->close();

        $conn->commit(); // Commit transaction
        $conn->close();
        return ['success' => true, 'message' => "New course '{$course_name}' added successfully!"];

    } catch (Exception $e) {
        $conn->rollback(); // Rollback on error
        $conn->close();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Fetches all courses for a specific user.
 *
 * @param int $user_id The ID of the user.
 * @return array An array of course data.
 */
function getAllCoursesForUser($user_id) {
    $conn = getDbConnection();
    $courses = [];

    // Fetch main course details
    $sql = "SELECT id, course_code, course_name, credits, department FROM courses WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $courses;
}

?>
