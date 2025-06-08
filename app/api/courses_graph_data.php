<?php
// app/api/courses_graph_data.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../course_manager.php';

header('Content-Type: application/json'); // Set header to indicate JSON response

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized', 'message' => 'User not logged in.']);
    http_response_code(401); // Unauthorized
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all courses for the current user, including their prerequisites
$courses_data = getAllCoursesForUser($user_id);

$nodes = [];
$edges = [];

foreach ($courses_data as $course) {
    // Add course as a node
    $nodes[] = [
        'id' => $course['id'],
        'label' => htmlspecialchars($course['course_name']), // CHANGED: Display course name as main label
        'title' => htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ') - ' . $course['department'] . ' - ' . $course['credits'] . ' Credits'), // More details on hover
        'group' => htmlspecialchars($course['department']), // Group by department for potential coloring
        // ADDED: Explicit data for click details in JavaScript
        'courseCode' => htmlspecialchars($course['course_code']),
        'courseName' => htmlspecialchars($course['course_name']),
        'department' => htmlspecialchars($course['department']),
        'credits' => htmlspecialchars($course['credits']),
    ];

    // Add dependencies as edges
    if (!empty($course['prerequisites'])) {
        foreach ($course['prerequisites'] as $prereq) {
            $edges[] = [
                'from' => $prereq['id'],
                'to' => $course['id'],
                'arrows' => 'to', // Arrow points from prerequisite to the course
                'label' => 'Prerequisite',
                'font' => ['align' => 'middle'],
                'color' => ['color' => '#888', 'highlight' => '#333'],
                'dashes' => true // Optional: show dependencies as dashed lines
            ];
        }
    }
}

// Prepare the data structure for vis.js
$graph_data = [
    'nodes' => $nodes,
    'edges' => $edges
];

echo json_encode($graph_data);

?>
