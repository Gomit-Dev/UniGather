<?php
session_start(); // Start session FIRST

// --- 1. Check Request Method ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Allow only POST requests for deletion
    header('Location: admin_panel.php?status=invalid_request');
    exit;
}

// --- 2. Authorization Check ---
// Ensure user is logged in AND is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php'); // Redirect non-admins to dashboard
    exit;
}

// --- Includes ---
require_once 'includes/db_connect.php';

// --- 3. CSRF Token Validation ---
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // CSRF token mismatch or missing
    header('Location: admin_panel.php?status=csrf_error');
    exit;
}

// Optional: Unset CSRF token after use (for single-use tokens per action)
// unset($_SESSION['csrf_token']);

// --- 4. Input Validation ---
if (!isset($_POST['user_id']) || !filter_var($_POST['user_id'], FILTER_VALIDATE_INT)) {
    // Missing or invalid user ID
    header('Location: admin_panel.php?status=invalid_user_id');
    exit;
}
$user_id_to_delete = (int)$_POST['user_id'];

// --- 5. Self-Deletion Check ---
if ($user_id_to_delete === $_SESSION['user_id']) {
    // Admin trying to delete their own account
    header('Location: admin_panel.php?status=cant_self');
    exit;
}

// --- 6. Database Deletion ---
$delete_status = 'delete_failed'; // Default status
try {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$user_id_to_delete])) {
        // Check if any row was actually deleted
        if ($stmt->rowCount() > 0) {
            $delete_status = 'user_deleted'; // Success
        } else {
            // User ID might not exist (already deleted?)
            $delete_status = 'user_not_found';
        }
    } else {
        // Execution failed
        $delete_status = 'delete_failed';
    }

} catch (PDOException $e) {
    // Log error in production is better
    // error_log("Delete User DB Error: " . $e->getMessage());
    $delete_status = 'db_error'; // Or keep as 'delete_failed'
}

// --- 7. Redirection ---
header('Location: admin_panel.php?status=' . $delete_status);
exit;

?>