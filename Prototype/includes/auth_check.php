<?php
require_once 'session_init.php';

// Check if user is logged in and has the required role
function check_auth($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit;
    }

    if ($required_role && $_SESSION['role'] !== $required_role) {
        header("Location: ../index.php");
        exit;
    }
}

// Get current user data
function get_logged_in_user_data() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Add audit log
function add_audit_log($action, $details = '') {
    // Disabled at user request
}
?>
