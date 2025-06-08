<?php
// app/course_manager.php

require_once __DIR__ . '/config.php'; // For getDbConnection() and DEPARTMENTS, SYSTEM_USERNAME

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
        // Basic validation for department against DEPARTMENTS enum
        if (!in_array($department, DEPARTMENTS)) {
            throw new Exception("Invalid department selected.");
        }

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
 * Global courses can only be updated if the user_id matches the system user ID.
 *
 * @param int    $course_id     The ID of the course to update.
 * @param int    $user_id       The ID of the user who owns the course (or current logged-in user).
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
        // Basic validation for department against DEPARTMENTS enum
        if (!in_array($department, DEPARTMENTS)) {
            throw new Exception("Invalid department selected.");
        }

        // 1. Update course details
        // Ensure user can only update their own courses or global courses if they are the system user.
        // For simplicity, we'll allow current user to update any course they 'own' via the ID in the WHERE clause.
        // If a regular user tries to update a system course, this query will fail because user_id won't match.
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
 * Only the owner (user_id) can delete their course.
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
            WHERE cd.course_id = ? AND (c.user_id = ? OR c.user_id = ?) -- Allow global prereqs
        ");
        $system_user_id = getUserIdByUsername(SYSTEM_USERNAME); // Fetch system ID
        $prereqs_stmt->bind_param("iii", $course_id, $user_id, $system_user_id);
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
 * Fetches all courses for a specific user, with optional search and department filters.
 * Includes global courses (owned by the 'system' user).
 * Includes their prerequisites.
 *
 * @param int    $user_id         The ID of the current logged-in user.
 * @param int    $system_user_id  The ID of the special 'system' user.
 * @param string $search_query    Optional search term for course name/code.
 * @param string $department_filter Optional department to filter by.
 * @return array An array of course data, each with a 'prerequisites' sub-array.
 */
function getAllCoursesForUser($user_id, $system_user_id, $search_query = '', $department_filter = '') {
    $conn = getDbConnection();
    $courses = [];

    // Build the base SQL query to include courses for both current user AND system user
    $sql = "SELECT id, course_code, course_name, credits, department, user_id FROM courses WHERE (user_id = ? OR user_id = ?)";
    $params = [$user_id, $system_user_id];
    $types = "ii";

    // Add search filter for course name or code
    if (!empty($search_query)) {
        $sql .= " AND (course_name LIKE ? OR course_code LIKE ?)";
        $search_term = '%' . $search_query . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }

    // Add department filter
    if (!empty($department_filter) && in_array($department_filter, DEPARTMENTS)) {
        $sql .= " AND department = ?";
        $params[] = $department_filter;
        $types .= "s";
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare statement for getAllCoursesForUser failed: " . $conn->error);
        $conn->close();
        return [];
    }

    // Use call_user_func_array for dynamic bind_param
    $bind_params = array_merge([$types], $params);
    call_user_func_array([$stmt, 'bind_param'], refValues($bind_params));
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row; // Use ID as key for easy lookup
        $courses[$row['id']]['prerequisites'] = []; // Initialize prerequisites array
    }
    $stmt->close();

    // Fetch all dependencies for this user's courses AND global courses
    if (!empty($courses)) {
        $course_ids = array_keys($courses);
        if (!empty($course_ids)) {
            $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
            $prereqs_sql = "
                SELECT cd.course_id, cd.prerequisite_course_id AS id, c.course_name, c.course_code
                FROM course_dependencies cd
                JOIN courses c ON cd.prerequisite_course_id = c.id
                WHERE cd.course_id IN ({$placeholders}) AND (c.user_id = ? OR c.user_id = ?)
            ";

            $prereqs_types = str_repeat('i', count($course_ids)) . 'ii'; // e.g., 'iiii' + 'ii'
            $prereqs_params = array_merge($course_ids, [$user_id, $system_user_id]);

            $prereqs_stmt = $conn->prepare($prereqs_sql);
            if (!$prereqs_stmt) {
                error_log("Prepare statement for prerequisites failed: " . $conn->error);
            } else {
                call_user_func_array([$prereqs_stmt, 'bind_param'], refValues(array_merge([$prereqs_types], $prereqs_params)));
                $prereqs_stmt->execute();
                $prereqs_result = $prereqs_stmt->get_result();

                while ($prereq_row = $prereqs_result->fetch_assoc()) {
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
 * Helper function for bind_param with dynamic arguments.
 */
function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}

/**
 * Fetches all available courses for selection as prerequisites for a given user.
 * Includes global courses (owned by the 'system' user).
 * Excludes the current course being edited to prevent self-dependency.
 * Can also filter by a search term for course code or name.
 *
 * @param int $user_id The ID of the user.
 * @param int $system_user_id The ID of the special 'system' user.
 * @param int|null $exclude_course_id Optional. The ID of the course currently being edited, to exclude from the list.
 * @param string $search_term Optional search term for course code/name.
 * @return array An array of courses suitable for prerequisite selection.
 */
function getAllAvailableCoursesForPrerequisites($user_id, $system_user_id, $exclude_course_id = null, $search_term = '') {
    $conn = getDbConnection();
    $available_courses = [];

    $sql = "SELECT id, course_code, course_name FROM courses WHERE (user_id = ? OR user_id = ?)";
    $params = [$user_id, $system_user_id];
    $types = "ii";

    if ($exclude_course_id !== null) {
        $sql .= " AND id != ?";
        $params[] = $exclude_course_id;
        $types .= "i";
    }

    if (!empty($search_term)) {
        $sql .= " AND (course_code LIKE ? OR course_name LIKE ?)";
        $search_term_like = '%' . $search_term . '%'; // Use a different variable name for the LIKE string
        $params[] = $search_term_like;
        $params[] = $search_term_like;
        $types .= "ss";
    }

    $sql .= " ORDER BY course_code ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare statement for getAllAvailableCoursesForPrerequisites failed: " . $conn->error);
        $conn->close();
        return [];
    }

    // Use call_user_func_array for dynamic bind_param
    $bind_params = array_merge([$types], $params);
    call_user_func_array([$stmt, 'bind_param'], refValues($bind_params));
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $available_courses[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $available_courses;
}

/**
 * Fetches only manually added courses for a specific user (excluding system courses).
 * Includes their prerequisites.
 *
 * @param int    $user_id         The ID of the current logged-in user.
 * @param string $search_query    Optional search term for course name/code.
 * @param string $department_filter Optional department to filter by.
 * @return array An array of course data, each with a 'prerequisites' sub-array.
 */
function getManuallyAddedCourses($user_id, $search_query = '', $department_filter = '') {
    $conn = getDbConnection();
    $courses = [];

    // Build the base SQL query to include only user's manually added courses
    $sql = "SELECT id, course_code, course_name, credits, department, user_id FROM courses WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";

    // Add search filter for course name or code
    if (!empty($search_query)) {
        $sql .= " AND (course_name LIKE ? OR course_code LIKE ?)";
        $search_term = '%' . $search_query . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }

    // Add department filter
    if (!empty($department_filter) && in_array($department_filter, DEPARTMENTS)) {
        $sql .= " AND department = ?";
        $params[] = $department_filter;
        $types .= "s";
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare statement for getManuallyAddedCourses failed: " . $conn->error);
        $conn->close();
        return [];
    }

    // Use call_user_func_array for dynamic bind_param
    $bind_params = array_merge([$types], $params);
    call_user_func_array([$stmt, 'bind_param'], refValues($bind_params));
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $courses[$row['id']] = $row; // Use ID as key for easy lookup
        $courses[$row['id']]['prerequisites'] = []; // Initialize prerequisites array
    }
    $stmt->close();

    // Fetch all dependencies for this user's courses
    if (!empty($courses)) {
        $course_ids = array_keys($courses);
        if (!empty($course_ids)) {
            $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
            $prereqs_sql = "
                SELECT cd.course_id, cd.prerequisite_course_id AS id, c.course_name, c.course_code
                FROM course_dependencies cd
                JOIN courses c ON cd.prerequisite_course_id = c.id
                WHERE cd.course_id IN ({$placeholders})
            ";

            $prereqs_types = str_repeat('i', count($course_ids));
            $prereqs_params = $course_ids;

            $prereqs_stmt = $conn->prepare($prereqs_sql);
            if (!$prereqs_stmt) {
                error_log("Prepare statement for prerequisites failed: " . $conn->error);
            } else {
                call_user_func_array([$prereqs_stmt, 'bind_param'], refValues(array_merge([$prereqs_types], $prereqs_params)));
                $prereqs_stmt->execute();
                $prereqs_result = $prereqs_stmt->get_result();

                while ($prereq_row = $prereqs_result->fetch_assoc()) {
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

?>
