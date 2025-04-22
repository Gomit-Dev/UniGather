<?php
session_start(); // Start session FIRST

// --- 1. Check Request Method ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Allow only POST requests for role changes
    header('Location: admin_panel.php?status=invalid_request');
    exit;
}

// --- 2. Authorization Check ---
// Ensure user is logged in AND is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php'); // Redirect non-admins
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

// Optional: Unset CSRF token after use
// unset($_SESSION['csrf_token']);

// --- 4. Input Validation ---
if (!isset($_POST['user_id']) || !isset($_POST['action'])) {
    header('Location: admin_panel.php?status=missing_data');
    exit;
}

$user_id_to_change = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
$action = trim($_POST['action']); // 'make_admin' or 'make_user'

if ($user_id_to_change === false) {
    header('Location: admin_panel.php?status=invalid_user_id');
    exit;
}
if ($action !== 'make_admin' && $action !== 'make_user') {
    header('Location: admin_panel.php?status=invalid_action');
    exit;
}

// --- 5. Self-Change Check ---
if ($user_id_to_change === $_SESSION['user_id']) {
    // Admin trying to change their own role via this form
    header('Location: admin_panel.php?status=cant_self');
    exit;
}

// --- 6. Determine New Role ---
$new_role = '';
if ($action === 'make_admin') {
    $new_role = 'admin';
} elseif ($action === 'make_user') {
    $new_role = 'user';
} else {
    // Should have been caught by validation, but as a fallback
    header('Location: admin_panel.php?status=invalid_action');
    exit;
}

// --- 7. Database Update ---
$update_status = 'role_failed'; // Default status
try {
    // Check if user exists before trying to update (optional but good)
    $check_sql = "SELECT id FROM users WHERE id = ? LIMIT 1";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$user_id_to_change]);

    if ($check_stmt->fetch()) {
        // User exists, proceed with update
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$new_role, $user_id_to_change])) {
            // Role potentially changed (rowCount might be 0 if role was already set)
            $update_status = 'role_changed'; // Consider success even if rowCount is 0
        } else {
            // Update execution failed
            $update_status = 'role_failed';
        }
    } else {
        // User to update was not found
        $update_status = 'user_not_found';
    }

} catch (PDOException $e) {
    // Log error in production
    // error_log("Change Role DB Error: " . $e->getMessage());
    $update_status = 'db_error'; // Or keep as 'role_failed'
}

// --- 8. Redirection ---
header('Location: admin_panel.php?status=' . $update_status);
exit;

?>