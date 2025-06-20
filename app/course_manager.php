<?php


require_once __DIR__ . '/config.php';

/**
 * Adds a new course to the database for a specific user, including its prerequisites.
 *
 * @param int    $user_id       The ID of the user creating the course.
 * @param string $course_code   The unique code for the course.
 * @param string $course_name   The name of the course.
 * @param int    $credits       The number of credits for the course.
 * @param string $department    The department the course belongs to.
 * @param array  $prerequisite_codes An array of course codes that are prerequisites.
 * @param string $source_type   The type of source ('system', 'imported', or 'added').
 * @return array An associative array with 'success' (bool) and 'message' (string).
 */
function addCourse($user_id, $course_code, $course_name, $credits, $department, $prerequisite_codes = [], $source_type = 'added') {
    $conn = getDbConnection();
    $conn->begin_transaction();

    try {

        if (!in_array($department, DEPARTMENTS)) {
            throw new Exception("Invalid department selected.");
        }


        $stmt = $conn->prepare("INSERT INTO courses (user_id, course_code, course_name, credits, department, source_type) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ississ", $user_id, $course_code, $course_name, $credits, $department, $source_type);
        if (!$stmt->execute()) {

            if ($conn->errno == 1062) {
                throw new Exception("A course with code '{$course_code}' already exists in your account.");
            }
            throw new Exception("Error adding course: " . $stmt->error);
        }
        $stmt->close();


        if (!empty($prerequisite_codes)) {

            $prerequisite_codes = array_filter($prerequisite_codes, function($code) use ($course_code) {
                return $code != $course_code;
            });

            if (!empty($prerequisite_codes)) {

                $system_user_id = getUserIdByUsername(SYSTEM_USERNAME);


                $check_prereq_stmt = $conn->prepare("
                    SELECT course_code, user_id
                    FROM courses
                    WHERE course_code = ? AND (user_id = ? OR user_id = ?)
                ");

                $insert_prereq_sql = "INSERT INTO course_dependencies (course_user_id, course_code, prereq_user_id, prerequisite_course_code) VALUES (?, ?, ?, ?)";
                $stmt_prereq = $conn->prepare($insert_prereq_sql);
                if (!$stmt_prereq) {
                    throw new Exception("Prepare prerequisite failed: " . $conn->error);
                }

                foreach ($prerequisite_codes as $prereq_code) {

                    $check_prereq_stmt->bind_param("sii", $prereq_code, $user_id, $system_user_id);
                    $check_prereq_stmt->execute();
                    $result = $check_prereq_stmt->get_result();

                    if ($result->num_rows === 0) {
                        throw new Exception("Prerequisite course '{$prereq_code}' does not exist in your courses or system courses.");
                    }


                    $prereq_row = $result->fetch_assoc();
                    $prereq_user_id = $prereq_row['user_id'];


                    $stmt_prereq->bind_param("isis", $user_id, $course_code, $prereq_user_id, $prereq_code);
                    if (!$stmt_prereq->execute()) {
                        error_log("Failed to add prerequisite {$prereq_code} for course {$course_code}: " . $stmt_prereq->error);
                    }
                }
                $stmt_prereq->close();
                $check_prereq_stmt->close();
            }
        }

        $conn->commit();
        $conn->close();
        return ['success' => true, 'message' => "New course '{$course_name}' added successfully!"];

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Updates an existing course for a specific user, including its prerequisites.
 * Global courses can only be updated if the user_id matches the system user ID.
 *
 * @param string $course_code   The code of the course to update.
 * @param int    $user_id       The ID of the user who owns the course (or current logged-in user).
 * @param string $new_course_code The updated course code.
 * @param string $course_name   The updated course name.
 * @param int    $credits       The updated number of credits.
 * @param string $department    The updated department.
 * @param array  $prerequisite_codes An array of new prerequisite course codes.
 * @return array An associative array with 'success' (bool) and 'message' (string).
 */
function updateCourse($course_code, $user_id, $new_course_code, $course_name, $credits, $department, $prerequisite_codes = []) {
    $conn = getDbConnection();
    $conn->begin_transaction();

    try {

        if (!in_array($department, DEPARTMENTS)) {
            throw new Exception("Invalid department selected.");
        }



        $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, credits = ?, department = ? WHERE course_code = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssisss", $new_course_code, $course_name, $credits, $department, $course_code, $user_id);
        if (!$stmt->execute()) {
             if ($conn->errno == 1062) {
                throw new Exception("A course with code '{$new_course_code}' already exists in your account.");
            }
            throw new Exception("Error updating course details: " . $stmt->error);
        }
        $stmt->close();



        $stmt_delete_prereqs = $conn->prepare("DELETE FROM course_dependencies WHERE course_code = ? AND course_user_id = ?");
        if (!$stmt_delete_prereqs) {
            throw new Exception("Prepare delete prerequisites failed: " . $conn->error);
        }
        $stmt_delete_prereqs->bind_param("si", $course_code, $user_id);
        if (!$stmt_delete_prereqs->execute()) {
            throw new Exception("Error clearing existing prerequisites: " . $stmt_delete_prereqs->error);
        }
        $stmt_delete_prereqs->close();


        if (!empty($prerequisite_codes)) {

            $prerequisite_codes = array_filter($prerequisite_codes, function($code) use ($new_course_code) {
                return $code != $new_course_code;
            });

            if (!empty($prerequisite_codes)) {

                $system_user_id = getUserIdByUsername(SYSTEM_USERNAME);


                $check_prereq_stmt = $conn->prepare("
                    SELECT course_code, user_id
                    FROM courses
                    WHERE course_code = ? AND (user_id = ? OR user_id = ?)
                ");

                $insert_prereq_sql = "INSERT INTO course_dependencies (course_user_id, course_code, prereq_user_id, prerequisite_course_code) VALUES (?, ?, ?, ?)";
                $stmt_add_prereq = $conn->prepare($insert_prereq_sql);
                if (!$stmt_add_prereq) {
                    throw new Exception("Prepare add prerequisites failed: " . $conn->error);
                }

                foreach ($prerequisite_codes as $prereq_code) {

                    $check_prereq_stmt->bind_param("sii", $prereq_code, $user_id, $system_user_id);
                    $check_prereq_stmt->execute();
                    $result = $check_prereq_stmt->get_result();

                    if ($result->num_rows === 0) {
                        throw new Exception("Prerequisite course '{$prereq_code}' does not exist in your courses or system courses.");
                    }


                    $prereq_row = $result->fetch_assoc();
                    $prereq_user_id = $prereq_row['user_id'];


                    $stmt_add_prereq->bind_param("isis", $user_id, $new_course_code, $prereq_user_id, $prereq_code);
                    if (!$stmt_add_prereq->execute()) {
                        error_log("Failed to add prerequisite {$prereq_code} for course {$new_course_code}: " . $stmt_add_prereq->error);
                    }
                }
                $stmt_add_prereq->close();
                $check_prereq_stmt->close();
            }
        }

        $conn->commit();
        $conn->close();
        return ['success' => true, 'message' => "Course '{$course_name}' updated successfully!"];

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Deletes a course from the database for a specific user.
 * Due to ON DELETE CASCADE on foreign keys, associated dependencies will also be removed.
 * Only the owner (user_id) can delete their course.
 *
 * @param string $course_code The code of the course to delete.
 * @param int $user_id   The ID of the user who owns the course.
 * @return array An associative array with 'success' (bool) and 'message' (string).
 */
function deleteCourse($course_code, $user_id) {
    $conn = getDbConnection();
    $conn->begin_transaction();

    try {

        $check_stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_code = ? AND user_id = ?");
        if (!$check_stmt) {
            throw new Exception("Prepare check failed: " . $conn->error);
        }
        $check_stmt->bind_param("si", $course_code, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows === 0) {
            $check_stmt->close();
            $conn->close();
            return ['success' => false, 'message' => "Course not found or you don't have permission to delete it."];
        }

        $course_name = $result->fetch_assoc()['course_name'];
        $check_stmt->close();


        $delete_stmt = $conn->prepare("DELETE FROM courses WHERE course_code = ? AND user_id = ?");
        if (!$delete_stmt) {
            throw new Exception("Prepare delete failed: " . $conn->error);
        }
        $delete_stmt->bind_param("si", $course_code, $user_id);

        if (!$delete_stmt->execute()) {
            throw new Exception("Error deleting course: " . $delete_stmt->error);
        }

        $delete_stmt->close();
        $conn->commit();
        $conn->close();

        return ['success' => true, 'message' => "Course '{$course_name}' deleted successfully!"];
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Fetches a single course by its code for a specific user.
 * Includes its prerequisites.
 *
 * @param string $course_code The code of the course to fetch.
 * @param int $user_id   The ID of the user who owns the course.
 * @return array|null The course data with 'prerequisites' array, or null if not found/not owned by user.
 */
function getCourseByCode($course_code, $user_id) {
    $conn = getDbConnection();
    $course = null;


    $stmt = $conn->prepare("SELECT course_code, course_name, credits, department FROM courses WHERE course_code = ? AND user_id = ?");
    $stmt->bind_param("si", $course_code, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $course = $result->fetch_assoc();

        $prereqs_stmt = $conn->prepare("
            SELECT cd.prerequisite_course_code AS course_code, c.course_name
            FROM course_dependencies cd
            JOIN courses c ON cd.prerequisite_course_code = c.course_code AND cd.user_id = c.user_id
            WHERE cd.course_code = ? AND cd.user_id = ?
        ");
        $prereqs_stmt->bind_param("si", $course_code, $user_id);
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
function getAllCoursesForUser($user_id, $system_user_id, $search = '', $department = '') {
    $conn = getDbConnection();


    $query = "
        SELECT c.*,
               GROUP_CONCAT(DISTINCT cd.prerequisite_course_code) as prerequisites
        FROM courses c
        LEFT JOIN course_dependencies cd ON c.user_id = cd.course_user_id AND c.course_code = cd.course_code
        WHERE (c.user_id = ? OR c.user_id = ?)
    ";

    $params = [$user_id, $system_user_id];
    $types = "ii";


    if (!empty($search)) {
        $query .= " AND (c.course_code LIKE ? OR c.course_name LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }


    if (!empty($department)) {
        $query .= " AND c.department = ?";
        $params[] = $department;
        $types .= "s";
    }


    $query .= " GROUP BY c.user_id, c.course_code ORDER BY c.created_at DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {

        $row['prerequisites'] = $row['prerequisites'] ? explode(',', $row['prerequisites']) : [];
        $courses[] = $row;
    }

    $stmt->close();
    $conn->close();

    return $courses;
}

/**
 * Helper function for bind_param with dynamic arguments.
 */
function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0)
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
 * @param string|null $exclude_course_code Optional. The code of the course currently being edited, to exclude from the list.
 * @param string $search_term Optional search term for course code/name.
 * @return array An array of courses suitable for prerequisite selection.
 */
function getAllAvailableCoursesForPrerequisites($user_id, $system_user_id, $exclude_course_code = null, $search_term = '') {
    $conn = getDbConnection();
    $available_courses = [];

    $sql = "SELECT course_code, course_name FROM courses WHERE (user_id = ? OR user_id = ?)";
    $params = [$user_id, $system_user_id];
    $types = "ii";

    if ($exclude_course_code !== null) {
        $sql .= " AND course_code != ?";
        $params[] = $exclude_course_code;
        $types .= "s";
    }

    if (!empty($search_term)) {
        $sql .= " AND (course_code LIKE ? OR course_name LIKE ?)";
        $search_term_like = '%' . $search_term . '%';
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



    $sql = "SELECT course_code, course_name, credits, department, user_id, source_type FROM courses WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";


    if (!empty($search_query)) {
        $sql .= " AND (course_name LIKE ? OR course_code LIKE ?)";
        $search_term = '%' . $search_query . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }


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


    $bind_params = array_merge([$types], $params);
    call_user_func_array([$stmt, 'bind_param'], refValues($bind_params));
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $courses[$row['course_code']] = $row;
        $courses[$row['course_code']]['prerequisites'] = [];
    }
    $stmt->close();


    if (!empty($courses)) {
        $course_codes = array_keys($courses);
        if (!empty($course_codes)) {
            $placeholders = implode(',', array_fill(0, count($course_codes), '?'));
            $prereqs_sql = "
                SELECT cd.course_code, cd.prerequisite_course_code, c.course_name
                FROM course_dependencies cd
                JOIN courses c ON cd.prerequisite_course_code = c.course_code
                WHERE cd.course_code IN ({$placeholders})
                AND (c.user_id = ? OR c.user_id = (SELECT id FROM users WHERE username = ?))
            ";

            $prereqs_types = str_repeat('s', count($course_codes)) . 'is';
            $prereqs_params = array_merge($course_codes, [$user_id, SYSTEM_USERNAME]);

            $prereqs_stmt = $conn->prepare($prereqs_sql);
            if (!$prereqs_stmt) {
                error_log("Prepare statement for prerequisites failed: " . $conn->error);
            } else {
                call_user_func_array([$prereqs_stmt, 'bind_param'], refValues(array_merge([$prereqs_types], $prereqs_params)));
                $prereqs_stmt->execute();
                $prereqs_result = $prereqs_stmt->get_result();

                while ($prereq_row = $prereqs_result->fetch_assoc()) {
                    if (isset($courses[$prereq_row['course_code']])) {
                        $courses[$prereq_row['course_code']]['prerequisites'][] = [
                            'course_code' => $prereq_row['prerequisite_course_code'],
                            'course_name' => $prereq_row['course_name']
                        ];
                    }
                }
                $prereqs_stmt->close();
            }
        }
    }

    $conn->close();
    return array_values($courses);
}

?>
