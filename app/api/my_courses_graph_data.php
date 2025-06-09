<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../course_manager.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'message' => 'Please log in to view the graph.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$courses_data = getManuallyAddedCourses($user_id);

$nodes = [];
$edges = [];

foreach ($courses_data as $course) {
    // Add course as a node
    $nodes[] = [
        'id' => $course['id'],
        'label' => htmlspecialchars($course['course_name']),
        'title' => htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ') - ' . $course['department'] . ' - ' . $course['credits'] . ' Credits'),
        'group' => htmlspecialchars($course['department']),
        'courseCode' => htmlspecialchars($course['course_code']),
        'courseName' => htmlspecialchars($course['course_name']),
        'department' => htmlspecialchars($course['department']),
        'credits' => htmlspecialchars($course['credits']),
    ];

    // Add edges for prerequisites
    if (!empty($course['prerequisites'])) {
        foreach ($course['prerequisites'] as $prereq) {
            $edge_exists = false;
            foreach ($edges as $existing_edge) {
                if ($existing_edge['from'] === $prereq['course_code'] && $existing_edge['to'] === $course['course_code']) {
                    $edge_exists = true;
                    break;
                }
            }
            
            if (!$edge_exists) {
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
}

header('Content-Type: application/json');
echo json_encode([
    'nodes' => $nodes,
    'edges' => $edges
]);
?> 