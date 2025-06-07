<?php

require_once __DIR__ . '/config.php';

function loginUser($username, $password) {
    $conn = getDbConnection();

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
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
        }
    }

    $stmt->close();
    $conn->close();
    return false; 
}

function logoutUser() {
    $_SESSION = array(); 
    session_destroy();
    header("Location: login.php");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function registerUser($username, $password) {
    $conn = getDbConnection();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password_hash);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return true;
    } else {
        error_log("Error registering user: " . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }
}

?>
