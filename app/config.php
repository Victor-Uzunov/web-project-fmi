<?php

define('DB_SERVER', 'db');
define('DB_USERNAME', 'php_user');
define('DB_PASSWORD', 'php_password');
define('DB_NAME', 'university_courses');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function getDbConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

?>
