<?php
// Add this at the VERY TOP of auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Authentication functions
function authenticateUser($conn, $username, $password) {
    // error_log("Attempting to authenticate user: $username"); // Debug
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        // error_log("Prepare failed: " . $conn->error); // Debug
        return false;
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        // error_log("Execute failed: " . $stmt->error); // Debug
        return false;
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // error_log("Found user: " . print_r($user, return: true)); // Debug
        
        if (password_verify($password, $user['password'])) {
            // error_log("Password verification succeeded"); // Debug
            return $user;
        } else {
            // error_log("Password verification failed"); // Debug
            // error_log("Input password: $password"); // Debug
            // error_log("Stored hash: " . $user['password']); // Debug
        }
    } else {
        // error_log("User not found: $username"); // Debug
    }
    return false;
}
function isLoggedIn() {
    if (!isset($_SESSION)) {
        error_log("CRITICAL: Session not initialized in isLoggedIn()");
        return false;
    }
    return isset($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true || $_SESSION['logged_in'] === 1);
}

function isAdmin() {
    if (!isset($_SESSION)) {
        error_log("CRITICAL: Session not initialized in isAdmin()");
        return false;
    }
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isLoggedIn()) {
        error_log("Access denied - Session data: " . (isset($_SESSION) ? print_r($_SESSION, true) : 'NO SESSION'));
        header("Location: " . BASE_PATH . "/login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        error_log("Access denied - Not admin. Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'none'));
        header("Location: " . BASE_PATH . "/index.php");
        exit();
    }
}
?>