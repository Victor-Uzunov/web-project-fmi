<?php
// app/course_manager.php

require_once __DIR__ . '/config.php'; // For getDbConnection()

/**
 * Adds a new course to the database for a specific user, including its prerequisites.
 *
 * @param int    $user_id       The ID of the user creating the course.
 * @param string $course_code   The unique code for the course.
 * @param string $course_name   The name of the course.
 * @param int    $credits       The number of credits for the course.
 * @param string $department    The department the course belongs to.
 * @param array  $prerequisite_ids An array of course IDs that are prerequisites.
 * @return array An associative array with 'success' (bool) and 'message' (string).
 */
function addCourse($user_id, $course_code, $course_name, $credits, $department, $prerequisite_ids = []) {
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
        $new_course_id = $conn->insert_id;
        $stmt->close();

        // 2. Add prerequisites if any
        if (!empty($prerequisite_ids)) {
            // Ensure no self-dependency (a course cannot be its own prerequisite)
            $prerequisite_ids = array_filter($prerequisite_ids, function($id) use ($new_course_id) {
                return $id != $new_course_id;
            });

            if (!empty($prerequisite_ids)) {
                $insert_prereq_sql = "INSERT INTO course_dependencies (course_id, prerequisite_course_id) VALUES (?, ?)";
                $stmt_prereq = $conn->prepare($insert_prereq_sql);
                if (!$stmt_prereq) {
                    throw new Exception("Prepare prerequisite failed: " . $conn->error);
                }

                foreach ($prerequisite_ids as $prereq_id) {
                    $stmt_prereq->bind_param("ii", $new_course_id, $prereq_id);
                    if (!$stmt_prereq->execute()) {
                        error_log("Failed to add prerequisite {$prereq_id} for course {$new_course_id}: " . $stmt_prereq->error);
                    }
                }
                $stmt_prereq->close();
            }
        }

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
 * Updates an existing course for a specific user, including its prerequisites.
 *
 * @param int    $course_id     The ID of the course to update.
 * @param int    $user_id       The ID of the user who owns the course.
 * @param string $course_code   The updated course code.
 * @param string $course_name   The updated course name.
 * @param int    $credits       The updated number of credits.
 * @param string $department    The updated department.
 * @param array  $prerequisite_ids An array of new prerequisite course IDs.
 * @return array An associative array with 'success' (bool) and 'message' (string).
 */
function updateCourse($course_id, $user_id, $course_code, $course_name, $credits, $department, $prerequisite_ids = []) {
    $conn = getDbConnection();
    $conn->begin_transaction(); // Start transaction

    try {
        // 1. Update course details
        $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, credits = ?, department = ? WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssisii", $course_code, $course_name, $credits, $department, $course_id, $user_id);
        if (!$stmt->execute()) {
             if ($conn->errno == 1062) { // Unique constraint violation (user_id, course_code)
                throw new Exception("A course with code '{$course_code}' already exists for your account.");
            }
            throw new Exception("Error updating course details: " . $stmt->error);
        }
        $stmt->close();

        // 2. Update prerequisites
        // Clear existing prerequisites for this course first
        $stmt_delete_prereqs = $conn->prepare("DELETE FROM course_dependencies WHERE course_id = ?");
        if (!$stmt_delete_prereqs) {
            throw new Exception("Prepare delete prerequisites failed: " . $conn->error);
        }
        $stmt_delete_prereqs->bind_param("i", $course_id);
        if (!$stmt_delete_prereqs->execute()) {
            throw new Exception("Error clearing existing prerequisites: " . $stmt_delete_prereqs->error);
        }
        $stmt_delete_prereqs->close();

        // Add new prerequisites if any
        if (!empty($prerequisite_ids)) {
            // Ensure no self-dependency
            $prerequisite_ids = array_filter($prerequisite_ids, function($id) use ($course_id) {
                return $id != $course_id;
            });

            if (!empty($prerequisite_ids)) {
                $insert_prereq_sql = "INSERT INTO course_dependencies (course_id, prerequisite_course_id) VALUES (?, ?)";
                $stmt_add_prereq = $conn->prepare($insert_prereq_sql);
                if (!$stmt_add_prereq) {
                    throw new Exception("Prepare add prerequisites failed: " . $conn->error);
                }

                foreach ($prerequisite_ids as $prereq_id) {
                    $stmt_add_prereq->bind_param("ii", $course_id, $prereq_id);
                    if (!$stmt_add_prereq->execute()) {
                         error_log("Failed to add prerequisite {$prereq_id} for course {$course_id}: " . $stmt_add_prereq->error);
                    }
                }
                $stmt_add_prereq->close();
            }
        }

        $conn->commit(); // Commit transaction
        $conn->close();
        return ['success' => true, 'message' => "Course '{$course_name}' updated successfully!"];

    } catch (Exception $e) {
        $conn->rollback(); // Rollback on error
        $conn->close();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Deletes a course from the database for a specific user.
 * Due to ON DELETE CASCADE on foreign keys, associated dependencies will also be removed.
 *
 * @param int $course_id The ID of the course to delete.
 * @param int $user_id   The ID of the user who owns the course.
 * @return array An associative array with 'success' (bool) and 'message' (string).
 */
function deleteCourse($course_id, $user_id) {
    $conn = getDbConnection();
    try {
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare delete failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $course_id, $user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                $conn->close();
                return ['success' => true, 'message' => "Course deleted successfully!"];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => "Course not found or you don't have permission to delete it."];
            }
        } else {
            throw new Exception("Error deleting course: " . $stmt->error);
        }
    } catch (Exception $e) {
        $conn->close();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


/**
 * Fetches a single course by its ID for a specific user.
 * Includes its prerequisites.
 *
 * @param int $course_id The ID of the course to fetch.
 * @param int $user_id   The ID of the user who owns the course.
 * @return array|null The course data with 'prerequisites' array, or null if not found/not owned by user.
 */
function getCourseById($course_id, $user_id) {
    $conn = getDbConnection();
    $course = null;

    // Fetch course details
    $stmt = $conn->prepare("SELECT id, course_code, course_name, credits, department FROM courses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $course_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $course = $result->fetch_assoc();
        // Fetch prerequisites for this course
        $prereqs_stmt = $conn->prepare("
            SELECT cd.prerequisite_course_id AS id, c.course_name, c.course_code
            FROM course_dependencies cd
            JOIN courses c ON cd.prerequisite_course_id = c.id
            WHERE cd.course_id = ? AND c.user_id = ? -- Ensure prerequisite also belongs to the same user
        ");
        $prereqs_stmt->bind_param("ii", $course_id, $user_id);
        $prereqs_stmt->execute();
        $prereqs_result = $prereqs_stmt->get_result();

        $course['prerequisites'] = [];
        while ($prereq_row = $prereqs_result->fetch_assoc()) {
            $course['prerequisites'][] = $prereq_row;
        }
        $prereqs_stmt->close();
    }

    $stmt->close();
    $conn->close();
    return $course;
}

/**
 * Fetches all courses for a specific user, including their prerequisites.
 *
 * @param int $user_id The ID of the user.
 * @return array An array of course data, each with a 'prerequisites' sub-array.
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
        $courses[$row['id']] = $row; // Use ID as key for easy lookup
        $courses[$row['id']]['prerequisites'] = []; // Initialize prerequisites array
    }
    $stmt->close();

    // Fetch all dependencies for this user's courses
    if (!empty($courses)) {
        // Using IN clause for efficiency. Max 999 items in IN clause for MySQL,
        // but for small to medium sets of courses, this is fine.
        $course_ids_str = implode(',', array_map('intval', array_keys($courses)));

        if (!empty($course_ids_str)) {
            $prereqs_sql = "
                SELECT cd.course_id, cd.prerequisite_course_id AS id, c.course_name, c.course_code
                FROM course_dependencies cd
                JOIN courses c ON cd.prerequisite_course_id = c.id
                WHERE cd.course_id IN ({$course_ids_str}) AND c.user_id = ?
            ";
            // Note: Directly injecting $course_ids_str is okay here since it's
            // generated from validated integer keys from $courses array.
            $prereqs_stmt = $conn->prepare($prereqs_sql);
            if (!$prereqs_stmt) {
                error_log("Prepare statement for prerequisites failed: " . $conn->error);
                // Continue without prerequisites or throw an exception based on desired error handling
            } else {
                $prereqs_stmt->bind_param("i", $user_id);
                $prereqs_stmt->execute();
                $prereqs_result = $prereqs_stmt->get_result();

                while ($prereq_row = $prereqs_result->fetch_assoc()) {
                    // Ensure the course_id exists in $courses before adding prerequisite
                    if (isset($courses[$prereq_row['course_id']])) {
                        $courses[$prereq_row['course_id']]['prerequisites'][] = [
                            'id' => $prereq_row['id'],
                            'course_name' => $prereq_row['course_name'],
                            'course_code' => $prereq_row['course_code']
                        ];
                    }
                }
                $prereqs_stmt->close();
            }
        }
    }

    $conn->close();
    return array_values($courses); // Return as a simple indexed array
}

/**
 * Fetches all available courses for selection as prerequisites for a given user.
 * Excludes the current course being edited to prevent self-dependency.
 *
 * @param int $user_id The ID of the user.
 * @param int|null $exclude_course_id Optional. The ID of the course currently being edited, to exclude from the list.
 * @return array An array of courses suitable for prerequisite selection.
 */
function getAllAvailableCoursesForPrerequisites($user_id, $exclude_course_id = null) {
    $conn = getDbConnection();
    $available_courses = [];

    $sql = "SELECT id, course_code, course_name FROM courses WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";

    if ($exclude_course_id !== null) {
        $sql .= " AND id != ?";
        $params[] = $exclude_course_id;
        $types .= "i";
    }
    $sql .= " ORDER BY course_code ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $available_courses[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $available_courses;
}

?>
