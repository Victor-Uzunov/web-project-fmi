<?php
// app/config.php

// Database configuration
define('DB_SERVER', 'db');
define('DB_USERNAME', 'php_user');
define('DB_PASSWORD', 'php_password');
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

?>
