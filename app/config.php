<?php
// app/config.php

// Database configuration
define('DB_SERVER', 'db');
define('DB_USERNAME', 'php_user');
define('DB_PASSWORD', 'php_password'); // IMPORTANT: Change this to your actual password from docker-compose.yml
define('DB_NAME', 'university_courses');

// Start session only once
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to establish database connection
function getDbConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        // In a real application, you'd log this error and show a user-friendly message.
        // For now, we'll die with the error.
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

?>
