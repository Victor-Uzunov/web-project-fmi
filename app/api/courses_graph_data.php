<?php
// app/api/courses_graph_data.php

// Ensure no output is sent before JSON, especially errors
ini_set('display_errors', 'Off'); // Suppress display of errors in browser for API endpoint
error_reporting(E_ALL); // Log all errors

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../course_manager.php';

// Debugging for constants/includes
if (!defined('DEPARTMENTS')) {
    error_log("API DEBUG: DEPARTMENTS constant NOT defined in courses_graph_data.php");
    // Attempt to manually include config.php again, though require_once should prevent this
    // This is mostly for diagnostic logging if a previous require_once failed silently
    if (file_exists(__DIR__ . '/../config.php')) {
        // This should not be needed if require_once worked, but helps debug
        // what might have gone wrong with initial parsing/loading
        include_once __DIR__ . '/../config.php';
        if (!defined('DEPARTMENTS')) {
            error_log("API DEBUG: DEPARTMENTS still NOT defined after second include_once.");
        }
    } else {
        error_log("API DEBUG: config.php not found at expected path: " . __DIR__ . '/../config.php');
    }
} else {
    error_log("API DEBUG: DEPARTMENTS constant IS defined in courses_graph_data.php.");
}


header('Content-Type: application/json'); // Set header to indicate JSON response

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized', 'message' => 'User not logged in.']);
    http_response_code(401); // Unauthorized
    exit();
}

$user_id = $_SESSION['user_id'];
$system_user_id = getUserIdByUsername(SYSTEM_USERNAME); // Get the ID of the global system user

if ($system_user_id === null) {
    error_log("CRITICAL ERROR: 'system' user not found when generating graph data.");
    echo json_encode(['error' => 'Server Error', 'message' => 'System user not found. Please contact support.']);
    http_response_code(500);
    exit();
}


// Fetch all courses for the current user, including their prerequisites
// Pass both current user ID and system user ID to fetch all relevant courses for the graph
$courses_data = getAllCoursesForUser($user_id, $system_user_id);

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

    // Add dependencies as edges
    if (!empty($course['prerequisites'])) {
        foreach ($course['prerequisites'] as $prereq_code) {
            $edges[] = [
                'from' => $prereq_code,
                'to' => $course['course_code'],
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
