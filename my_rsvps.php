<?php
session_start(); // Start session FIRST

// --- Authorization Check ---
// Ensure user is logged in to access this page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my_rsvps.php'); // Redirect to login if not logged in
    exit;
}

// --- Includes and Get User Info ---
require_once 'includes/db_connect.php';
$user_id = $_SESSION['user_id']; // Get current user's ID
$user_role = $_SESSION['user_role'] ?? null; // Get role for event card logic

// --- Fetch Registered Events ---
$registered_events = [];
$fetch_error = '';
try {
    // Select events JOINED with RSVPs for the current user
    $sql = "SELECT e.*, u.username as creator_username
            FROM events e
            JOIN rsvps r ON e.id = r.event_id
            JOIN users u ON e.created_by_user_id = u.id
            WHERE r.user_id = ?
            ORDER BY e.event_date ASC, e.event_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $registered_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // error_log("My RSVPs Fetch Error: " . $e->getMessage()); // Log error
    $fetch_error = "Could not load your registered events.";
}


// --- Include Header ---
include 'includes/header.php';
?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-3">My Registered Events (RSVPs)</h1>

        <?php if ($fetch_error): ?>
            <div class="notice-message notice-error" role="alert">
                <?php echo htmlspecialchars($fetch_error); ?>
            </div>
        <?php elseif (empty($registered_events)): ?>
            <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
                You haven't RSVP'd to any upcoming events yet. <a href="dashboard.php" class="text-blue-600 hover:underline">Find events here</a>.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                // Loop through the events the user has RSVP'd to
                foreach ($registered_events as $event):
                    // Include the reusable event card template
                    // It will use the $event, $user_id, $user_role, $pdo variables from this scope
                    // The RSVP check inside the card *will* find an RSVP, so it will show the "Cancel" button
                    include('includes/template_parts/event_card.php');
                endforeach; // End event loop
                ?>
            </div> <?php endif; // End check for registered events ?>

    </div> <?php
// --- Include Footer ---
include 'includes/footer.php';
?>