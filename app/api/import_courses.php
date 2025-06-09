<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../course_manager.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'message' => 'Please log in to import courses.']);
    exit();
}

// Check if it's a POST request with a file
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csvFile'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request', 'message' => 'No CSV file provided.']);
    exit();
}

// Get the source of the import request
$source = isset($_POST['source']) ? $_POST['source'] : 'user';
$user_id = $_SESSION['user_id'];

// If importing from all_courses.php, use system user ID for global visibility
if ($source === 'global') {
    $system_user_id = getUserIdByUsername(SYSTEM_USERNAME);
    if ($system_user_id === null) {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error', 'message' => 'System user not found.']);
        exit();
    }
    $user_id = $system_user_id;
}

$file = $_FILES['csvFile'];

// Validate file type
if ($file['type'] !== 'text/csv' && $file['type'] !== 'application/vnd.ms-excel') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid File', 'message' => 'Please upload a valid CSV file.']);
    exit();
}

// Read and parse the CSV file
$handle = fopen($file['tmp_name'], 'r');
if (!$handle) {
    http_response_code(500);
    echo json_encode(['error' => 'Server Error', 'message' => 'Failed to read the CSV file.']);
    exit();
}

// Read header row
$header = fgetcsv($handle);
if (!$header || count($header) !== 5) {
    fclose($handle);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Format', 'message' => 'CSV must have exactly 5 columns: course_code,course_name,credits,department,dependencies']);
    exit();
}

// Validate header columns
$expected_columns = ['course_code', 'course_name', 'credits', 'department', 'dependencies'];
if ($header !== $expected_columns) {
    fclose($handle);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Format', 'message' => 'CSV must have columns: course_code,course_name,credits,department,dependencies']);
    exit();
}

$results = [
    'success' => [],
    'errors' => []
];

// Read and process each row
while (($row = fgetcsv($handle)) !== false) {
    if (count($row) !== 5) {
        $results['errors'][] = "Invalid row format: " . implode(',', $row);
        continue;
    }

    list($course_code, $course_name, $credits, $department, $dependencies) = $row;

    // Basic validation
    if (empty($course_code) || empty($course_name) || empty($credits) || empty($department)) {
        $results['errors'][] = "Missing required fields for course: $course_code";
        continue;
    }

    // Validate credits is a number
    if (!is_numeric($credits) || $credits < 1) {
        $results['errors'][] = "Invalid credits value for course: $course_code";
        continue;
    }

    // Validate department
    if (!in_array($department, DEPARTMENTS)) {
        $results['errors'][] = "Invalid department '$department' for course: $course_code";
        continue;
    }

    // Process dependencies
    $prerequisite_codes = [];
    if (!empty($dependencies)) {
        $prerequisite_codes = array_map('trim', explode(',', $dependencies));
        $prerequisite_codes = array_filter($prerequisite_codes); // Remove empty values
    }

    // Add the course
    $response = addCourse($user_id, $course_code, $course_name, (int)$credits, $department, $prerequisite_codes);
    
    if ($response['success']) {
        $results['success'][] = $course_code;
    } else {
        $results['errors'][] = "Failed to add course $course_code: " . $response['message'];
    }
}

fclose($handle);

// Return results
echo json_encode([
    'success' => true,
    'message' => sprintf(
        'Import completed. Successfully imported %d courses. %d errors.',
        count($results['success']),
        count($results['errors'])
    ),
    'details' => $results,
    'source' => $source // Include the source in the response
]); 