<?php



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


    $username_for_db = strtolower($username);


    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    if (!$stmt) {
        error_log("Auth Error: prepare statement failed in loginUser: " . $conn->error);
        $conn->close();
        return false;
    }

    $stmt->bind_param("s", $username_for_db);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();


        if (password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $stmt->close();
            $conn->close();
            return true;
        } else {

            error_log("Auth Debug: password_verify() FAILED for user '" . $username_for_db . "'.");



        }
    } else {
        error_log("Auth Debug: User '" . $username_for_db . "' NOT found or multiple entries found. Rows found: " . $result->num_rows);
    }

    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Logs out the current user by destroying the session.
 */
function logoutUser() {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
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
 * Handles duplicate username errors gracefully.
 *
 * @param string $username The username for the new user.
 * @param string $password The plain-text password for the new user.
 * @return array An associative array with 'success' (bool) and 'message' (string).
 */
function registerUser($username, $password) {
    $conn = getDbConnection();


    $username_for_db = strtolower($username);


    $password_hash = password_hash($password, PASSWORD_DEFAULT);


    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    if (!$stmt) {
        error_log("Auth Error: prepare statement failed in registerUser: " . $conn->error);
        $conn->close();
        return ['success' => false, 'message' => "Database error during registration setup."];
    }

    $stmt->bind_param("ss", $username_for_db, $password_hash);

    try {
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => "Registration successful! You can now log in."];
        } else {

            error_log("Auth Error: General error registering user: " . $stmt->error);
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => "Registration failed. Please try again."];
        }
    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1062) {
            error_log("Auth Debug: Duplicate username registration attempt: " . $username_for_db);
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => "Registration failed. Username '{$username}' already exists."];
        } else {

            error_log("Auth Error: mysqli_sql_exception during registration: " . $e->getMessage() . " Code: " . $e->getCode());
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => "Registration failed due to a database error. Please try again."];
        }
    }
}

?>
