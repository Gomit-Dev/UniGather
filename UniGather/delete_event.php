<?php
session_start(); // Start session FIRST

require_once 'includes/db_connect.php'; // DB required for checks

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// Check if ID is provided and is a valid integer
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location: dashboard.php?status=invalid_id');
    exit;
}

$event_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

try {
    // First, fetch the event creator's ID to check ownership
    $stmt_check = $pdo->prepare("SELECT created_by_user_id FROM events WHERE id = ? LIMIT 1");
    $stmt_check->execute([$event_id]);
    $event = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        // Event not found
        header('Location: dashboard.php?status=not_found');
        exit;
    }

    // Authorization check: Admin or Owner
    if ($user_role === 'admin' || $event['created_by_user_id'] == $user_id) {
        // Authorized: Proceed with deletion
        $stmt_delete = $pdo->prepare("DELETE FROM events WHERE id = ?");
        if ($stmt_delete->execute([$event_id])) {
            header('Location: dashboard.php?status=event_deleted');
            exit;
        } else {
            header('Location: dashboard.php?status=delete_failed');
            exit;
        }
    } else {
        // Not authorized
        header('Location: dashboard.php?status=unauthorized');
        exit;
    }

} catch (PDOException $e) {
    // error_log("Error deleting event ID $event_id by user ID $user_id: " . $e->getMessage()); // Log error
    header('Location: dashboard.php?status=db_error');
    exit;
}
