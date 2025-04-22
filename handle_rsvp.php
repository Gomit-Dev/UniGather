<?php
session_start(); // Start session FIRST

// --- Authorization Check ---
if (!isset($_SESSION['user_id'])) {
    // Redirect non-logged-in users
    header('Location: login.php?status=login_required');
    exit;
}

// --- Includes ---
require_once 'includes/db_connect.php';

// --- Input Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Only allow POST requests
    header('Location: dashboard.php?status=invalid_request');
    exit;
}

// Check if required fields are present
if (!isset($_POST['event_id']) || !isset($_POST['action'])) {
    header('Location: dashboard.php?status=missing_data');
    exit;
}

// Sanitize/Validate inputs
$event_id = filter_var($_POST['event_id'], FILTER_VALIDATE_INT);
$action = trim($_POST['action']); // 'rsvp' or 'unrsvp'
$user_id = $_SESSION['user_id'];

if ($event_id === false || ($action !== 'rsvp' && $action !== 'unrsvp')) {
    header('Location: dashboard.php?status=invalid_data');
    exit;
}

// --- Logic ---
try {
    // Check if the event exists and if RSVP is enabled for it
    $event_check_sql = "SELECT id, rsvp_enabled FROM events WHERE id = ? LIMIT 1";
    $event_stmt = $pdo->prepare($event_check_sql);
    $event_stmt->execute([$event_id]);
    $event = $event_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event || !$event['rsvp_enabled']) {
        // Event doesn't exist or RSVP is not enabled for it
        header('Location: dashboard.php?status=rsvp_not_allowed');
        exit;
    }

    // --- Perform Action ---
    if ($action === 'rsvp') {
        // Try to insert RSVP - IGNORE if duplicate exists (due to unique key)
        $insert_sql = "INSERT IGNORE INTO rsvps (user_id, event_id) VALUES (?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([$user_id, $event_id]);
        // Optionally check affected rows if needed: $insert_stmt->rowCount()
        header('Location: dashboard.php?status=rsvpd');
        exit;

    } elseif ($action === 'unrsvp') {
        // Delete the RSVP record
        $delete_sql = "DELETE FROM rsvps WHERE user_id = ? AND event_id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$user_id, $event_id]);
        // Optionally check affected rows: $delete_stmt->rowCount()
        header('Location: dashboard.php?status=unrsvpd');
        exit;
    }

} catch (PDOException $e) {
    // error_log("RSVP Handling Error: " . $e->getMessage()); // Log error
    header('Location: dashboard.php?status=db_error');
    exit;
}

// Fallback redirect if something unexpected happens
header('Location: dashboard.php');
exit;
?>