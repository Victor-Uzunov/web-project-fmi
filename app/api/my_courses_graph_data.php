<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../course_manager.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'message' => 'Please log in to view the graph.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch only manually added courses for the current user
$courses_data = getManuallyAddedCourses($user_id);

$nodes = [];
$edges = [];

foreach ($courses_data as $course) {
    // Add course as a node
    $nodes[] = [
        'id' => $course['course_code'],
        'label' => htmlspecialchars($course['course_name']), // Display course name as main label
        'title' => htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ') - ' . $course['department'] . ' - ' . $course['credits'] . ' Credits'), // More details on hover
        'group' => htmlspecialchars($course['department']), // Group by department for potential coloring
        // Explicit data for click details in JavaScript
        'courseCode' => htmlspecialchars($course['course_code']),
        'courseName' => htmlspecialchars($course['course_name']),
        'department' => htmlspecialchars($course['department']),
        'credits' => htmlspecialchars($course['credits']),
    ];

    // Add edges for prerequisites
    if (!empty($course['prerequisites'])) {
        foreach ($course['prerequisites'] as $prereq) {
            $edges[] = [
                'from' => $prereq['course_code'],
                'to' => $course['course_code'],
                'arrows' => 'to',
                'label' => 'Prerequisite',
                'font' => ['align' => 'middle'],
                'color' => ['color' => '#888', 'highlight' => '#333'],
                'dashes' => true
            ];
        }
    }
}

// Return the graph data as JSON
header('Content-Type: application/json');
echo json_encode([
    'nodes' => $nodes,
    'edges' => $edges
]);
?> 