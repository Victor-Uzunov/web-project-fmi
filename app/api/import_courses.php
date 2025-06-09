<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../course_manager.php';


if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'message' => 'Please log in to import courses.']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csvFile'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request', 'message' => 'No CSV file provided.']);
    exit();
}


$source = isset($_POST['source']) ? $_POST['source'] : 'user';
$user_id = $_SESSION['user_id'];

$file = $_FILES['csvFile'];


if ($file['type'] !== 'text/csv' && $file['type'] !== 'application/vnd.ms-excel') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid File', 'message' => 'Please upload a valid CSV file.']);
    exit();
}


$handle = fopen($file['tmp_name'], 'r');
if (!$handle) {
    http_response_code(500);
    echo json_encode(['error' => 'Server Error', 'message' => 'Failed to read the CSV file.']);
    exit();
}


$header = fgetcsv($handle);
if (!$header || count($header) !== 5) {
    fclose($handle);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Format', 'message' => 'CSV must have exactly 5 columns: course_code,course_name,credits,department,dependencies']);
    exit();
}


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


while (($row = fgetcsv($handle)) !== false) {
    if (count($row) !== 5) {
        $results['errors'][] = "Invalid row format: " . implode(',', $row);
        continue;
    }

    list($course_code, $course_name, $credits, $department, $dependencies) = $row;


    if (empty($course_code) || empty($course_name) || empty($credits) || empty($department)) {
        $results['errors'][] = "Missing required fields for course: $course_code";
        continue;
    }


    if (!is_numeric($credits) || $credits < 1) {
        $results['errors'][] = "Invalid credits value for course: $course_code";
        continue;
    }


    if (!in_array($department, DEPARTMENTS)) {
        $results['errors'][] = "Invalid department '$department' for course: $course_code";
        continue;
    }


    $prerequisite_codes = [];
    if (!empty($dependencies)) {
        $prerequisite_codes = array_map('trim', explode(',', $dependencies));
        $prerequisite_codes = array_filter($prerequisite_codes);
    }


    $response = addCourse($user_id, $course_code, $course_name, (int)$credits, $department, $prerequisite_codes, 'imported');

    if ($response['success']) {
        $results['success'][] = $course_code;
    } else {
        $results['errors'][] = "Failed to add course $course_code: " . $response['message'];
    }
}

fclose($handle);


echo json_encode([
    'success' => true,
    'message' => sprintf(
        'Import completed. Successfully imported %d courses. %d errors.',
        count($results['success']),
        count($results['errors'])
    ),
    'details' => $results,
    'source' => $source
]);
