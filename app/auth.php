<?php
// app/auth.php

// Include configuration for database connection
require_once __DIR__ . '/config.php';

/**
 * Attempts to log in a user.
 *
 * @param string $username The username provided by the user.
 * @param string $password The plain-text password provided by the user.
 * @return bool True if login is successful, false otherwise.
 */
function loginUser($username, $password) {
    $conn = getDbConnection();

    // --- DEBUGGING START ---
    error_log("DEBUG: Attempting login for username: '" . $username . "'");
    // WARNING: Do NOT log plain passwords in production. This is for temporary debugging only.
    error_log("DEBUG: Password entered (plaintext): '" . $password . "'");
    // --- DEBUGGING END ---

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // --- DEBUGGING START ---
        error_log("DEBUG: User found in DB. Stored username: '" . $user['username'] . "'");
        error_log("DEBUG: Stored password hash: '" . $user['password_hash'] . "'");
        // --- DEBUGGING END ---

        // Verify the provided password against the stored hash
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $stmt->close();
            $conn->close();
            error_log("DEBUG: Password VERIFIED. Login successful.");
            return true;
        } else {
            error_log("DEBUG: password_verify() FAILED for user '" . $username . "'.");
        }
    } else {
        error_log("DEBUG: User '" . $username . "' NOT found in database (or multiple users with same name). Rows found: " . $result->num_rows);
    }

    $stmt->close();
    $conn->close();
    return false; // Login failed
}

/**
 * Logs out the current user by destroying the session.
 */
function logoutUser() {
    $_SESSION = array(); // Clear all session variables
    session_destroy();    // Destroy the session
    header("Location: login.php"); // Redirect to login page
    exit();
}

/**
 * Checks if a user is currently logged in.
 *
 * @return bool True if a user is logged in, false otherwise.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Registers a new user.
 *
 * @param string $username The username for the new user.
 * @param string $password The plain-text password for the new user.
 * @return bool True if registration is successful, false otherwise.
 */
function registerUser($username, $password) {
    $conn = getDbConnection();

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Prepare statement to insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password_hash);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return true;
    } else {
        // Handle potential errors like duplicate username
        error_log("Error registering user: " . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }
}

?>
