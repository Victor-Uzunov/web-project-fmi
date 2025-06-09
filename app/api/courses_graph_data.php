<?php
// app/api/courses_graph_data.php

ini_set('display_errors', 'Off');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../course_manager.php';

if (!defined('DEPARTMENTS')) {
    error_log("API DEBUG: DEPARTMENTS constant NOT defined in courses_graph_data.php");
    if (file_exists(__DIR__ . '/../config.php')) {
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


header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized', 'message' => 'User not logged in.']);
    http_response_code(401);
    exit();
}

$user_id = $_SESSION['user_id'];
$system_user_id = getUserIdByUsername(SYSTEM_USERNAME);

if ($system_user_id === null) {
    error_log("CRITICAL ERROR: 'system' user not found when generating graph data.");
    echo json_encode(['error' => 'Server Error', 'message' => 'System user not found. Please contact support.']);
    http_response_code(500);
    exit();
}


$courses_data = getAllCoursesForUser($user_id, $system_user_id);

$nodes = [];
$edges = [];

foreach ($courses_data as $course) {
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

    if (!empty($course['prerequisites'])) {
        foreach ($course['prerequisites'] as $prereq_code) {
            $edges[] = [
                'from' => $prereq['id'],
                'to' => $course['id'],
                'arrows' => 'to',
                'label' => 'Prerequisite',
                'font' => ['align' => 'middle'],
                'color' => ['color' => '#888', 'highlight' => '#333'],
                'dashes' => true
            ];
        }
    }
}

$graph_data = [
    'nodes' => $nodes,
    'edges' => $edges
];

echo json_encode($graph_data);

?>
