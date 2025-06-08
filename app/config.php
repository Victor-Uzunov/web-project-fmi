<?php
// app/config.php

// Database configuration
define('DB_SERVER', 'db');
define('DB_USERNAME', 'php_user');
define('DB_PASSWORD', 'php_password'); // IMPORTANT: Change this to your actual password from docker-compose.yml
define('DB_NAME', 'university_courses');

// Define valid department ENUM values for consistent use across application
define('DEPARTMENTS', [
    'Mathematics',
    'Software Technologies',
    'Informatics',
    'Database',
    'English',
    'Soft Skills',
    'Other'
]);

// Define the username for the system/global courses
define('SYSTEM_USERNAME', 'system');


// Start session only once
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to establish database connection
function getDbConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error); // Log error
        die("Connection failed: Please try again later."); // User-friendly message
    }
    return $conn;
}

/**
 * Fetches a user's ID by their username.
 *
 * @param string $username The username to search for.
 * @return int|null The user's ID if found, otherwise null.
 */
function getUserIdByUsername($username) {
    $conn = getDbConnection();
    $user_id = null;
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['id'];
    }
    $stmt->close();
    $conn->close();
    return $user_id;
}

?>
