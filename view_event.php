<?php
session_start(); // Start session FIRST

require_once 'includes/db_connect.php'; // Include DB connection

// --- Get Event ID and Validate ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // No ID or invalid ID provided
    header('Location: dashboard.php?status=invalid_event_id');
    exit;
}
$event_id = (int)$_GET['id'];

// --- Initialize Variables ---
$event = null;             // Holds the fetched event data
$fetch_error = '';       // Holds any error message during fetch
$user_has_rsvpd = false;   // Tracks if the current user has RSVP'd
$user_id = $_SESSION['user_id'] ?? null; // Current user's ID (or null if not logged in)
$user_role = $_SESSION['user_role'] ?? null; // Current user's role (or null)

// --- Fetch Event Data ---
try {
    $sql = "SELECT e.*, u.username as creator_username
            FROM events e
            JOIN users u ON e.created_by_user_id = u.id
            WHERE e.id = ?
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        // Event with the given ID was not found
        $fetch_error = "Event not found.";
        // Optional: Redirect immediately if preferred
        // header('Location: dashboard.php?status=event_not_found');
        // exit;
    } else {
        // --- Check RSVP Status (if event found, user logged in, and RSVP enabled) ---
        if ($user_id && !empty($event['rsvp_enabled'])) {
            $rsvp_check_sql = "SELECT id FROM rsvps WHERE user_id = ? AND event_id = ? LIMIT 1";
            $rsvp_stmt = $pdo->prepare($rsvp_check_sql);
            $rsvp_stmt->execute([$user_id, $event_id]);
            if ($rsvp_stmt->fetch()) {
                $user_has_rsvpd = true; // User has RSVP'd
            }
        }
    }

} catch (PDOException $e) {
    // error_log("View Event Fetch Error: " . $e->getMessage()); // Log error
    $fetch_error = "An error occurred while retrieving event details.";
    $event = null; // Ensure event is null on error
}

// --- Include Header ---
include 'includes/header.php';
?>

    <div class="container mx-auto px-4 py-8">

        <?php if ($fetch_error): // Display fetch error if any ?>
            <div class="notice-message notice-error" role="alert">
                <?php echo htmlspecialchars($fetch_error); ?> <a href="dashboard.php" class="font-semibold underline hover:text-red-800">Go back to dashboard</a>.
            </div>
        <?php elseif ($event): // Display event details if fetched successfully ?>
            <article class="bg-white p-6 md:p-8 rounded-lg shadow-lg border border-gray-200">

                <?php if (!empty($event['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="w-full h-64 md:h-96 object-cover rounded-md mb-6">
                <?php else: ?>
                    <div class="w-full h-64 md:h-96 bg-gradient-to-br from-gray-200 to-gray-300 mb-6 rounded-md flex items-center justify-center text-gray-500 italic">No Event Image Available</div>
                <?php endif; ?>

                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-5">
                    <?php echo htmlspecialchars($event['title']); ?>
                </h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-gray-600 mb-6 border-t border-b py-4">
                    <p class="flex items-center"><i class="far fa-calendar-alt fa-fw mr-2 text-blue-500"></i> <strong>Date:</strong><span class="ml-2"><?php echo htmlspecialchars(date('l, F j, Y', strtotime($event['event_date']))); ?></span></p>
                    <?php if (!empty($event['event_time'])): ?>
                        <p class="flex items-center"><i class="far fa-clock fa-fw mr-2 text-blue-500"></i> <strong>Time:</strong><span class="ml-2"><?php echo htmlspecialchars(date('g:i A', strtotime($event['event_time']))); ?></span></p>
                    <?php endif; ?>
                    <?php if (!empty($event['location'])): ?>
                        <p class="flex items-center sm:col-span-2"><i class="fas fa-map-marker-alt fa-fw mr-2 text-blue-500"></i> <strong>Location:</strong><span class="ml-2"><?php echo htmlspecialchars($event['location']); ?></span></p>
                    <?php endif; ?>
                    <p class="flex items-center"><i class="fas fa-user fa-fw mr-2 text-blue-500"></i> <strong>Organizer:</strong><span class="ml-2"><?php echo htmlspecialchars($event['creator_username']); ?></span></p>
                    <p class="flex items-center text-xs sm:text-sm"><i class="far fa-calendar-plus fa-fw mr-2 text-blue-500"></i> <strong>Posted:</strong><span class="ml-2"><?php echo htmlspecialchars(date('M j, Y', strtotime($event['created_at']))); ?></span></p>
                </div>

                <div class="prose max-w-none text-gray-700 leading-relaxed mb-8">
                    <h3 class="text-xl font-semibold text-gray-700 mb-3">About this Event</h3>
                    <?php echo !empty($event['description']) ? nl2br(htmlspecialchars($event['description'])) : '<p><em>No description provided.</em></p>'; ?>
                </div>

                <div class="border-t pt-6 flex flex-wrap items-center justify-between gap-4">
                    <div> <?php if (!empty($event['rsvp_enabled'])): ?>
                            <?php if ($user_id): // User is logged in ?>
                                <?php if ($user_has_rsvpd): ?>
                                    <form action="handle_rsvp.php" method="POST" class="inline-block">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <input type="hidden" name="action" value="unrsvp">
                                        <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded transition-colors duration-150 text-sm" title="Cancel RSVP">
                                            <i class="fas fa-check mr-1"></i> You're Going! (Cancel)
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="handle_rsvp.php" method="POST" class="inline-block">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <input type="hidden" name="action" value="rsvp">
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded transition-colors duration-150 text-sm">
                                            RSVP Now
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: // User not logged in ?>
                                <a href="login.php?redirect=view_event.php?id=<?php echo $event['id']; ?>" class="inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors duration-200 text-sm">
                                    Login to RSVP
                                </a>
                            <?php endif; ?>
                        <?php else: // RSVP not enabled for this event ?>
                            <span class="text-sm text-gray-500 italic">RSVP not required for this event.</span>
                        <?php endif; ?>
                    </div>

                    <div> <?php $can_edit_delete = ($user_role === 'admin' || (isset($event['created_by_user_id']) && $event['created_by_user_id'] == $user_id)); ?>
                        <?php if ($can_edit_delete): ?>
                            <div class="flex items-center space-x-2">
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="text-sm bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1 px-3 rounded transition-colors duration-150">Edit</a>
                                <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="text-sm bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded transition-colors duration-150" onclick="return confirm('Are you sure you want to delete this event permanently?');">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div> </article>

        <?php else: // Fallback if $event is null but no specific error was set ?>
            <div class="notice-message notice-error" role="alert">
                Could not load event details. It may no longer exist. <a href="dashboard.php" class="font-semibold underline hover:text-red-800">Go back to dashboard</a>.
            </div>
        <?php endif; ?>

    </div> <?php
// Include footer
include 'includes/footer.php';
?>