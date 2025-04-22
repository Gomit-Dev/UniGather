<?php
/**
 * Template Part for Displaying a Single Event Card.
 *
 * Expects the following variables to be available in the scope where it's included:
 * - $event: (array) An associative array containing the event details (id, title, image_path, event_date, etc.)
 * including 'creator_username', 'rsvp_enabled', and 'created_by_user_id'.
 * - $user_id: (int|null) The ID of the currently logged-in user, or null if not logged in.
 * - $user_role: (string|null) The role ('admin' or 'user') of the currently logged-in user, or null.
 * - $pdo: (PDO|null) The PDO database connection object (needed for RSVP check), or null if DB connection failed.
 *
 * Usage: Include within a loop: include('includes/template_parts/event_card.php');
 */

// Basic check if $event data is available
if (!isset($event) || !is_array($event)) {
    echo '<div class="bg-white p-6 rounded-lg shadow-md border border-red-300 text-red-700">Error: Event data is missing for this card.</div>';
    return; // Stop rendering this specific card
}

// Ensure required keys exist in $event array to avoid warnings (provide defaults)
$event_id = $event['id'] ?? 0; // Use 0 or handle error if ID missing
$event_title = $event['title'] ?? 'Untitled Event';
$image_path = $event['image_path'] ?? null;
$event_date = $event['event_date'] ?? null;
$event_time = $event['event_time'] ?? null;
$location = $event['location'] ?? null;
$description = $event['description'] ?? null;
$creator_username = $event['creator_username'] ?? 'Unknown';
$rsvp_enabled = !empty($event['rsvp_enabled']); // Treat as boolean
$creator_id = $event['created_by_user_id'] ?? null;

?>
<div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 flex flex-col justify-between h-full border border-gray-100">
    <div> <?php if (!empty($image_path) && file_exists($image_path)): ?>
            <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($event_title); ?>" class="w-full h-40 object-cover mb-4 rounded">
        <?php else: ?>
            <div class="w-full h-40 bg-gray-200 mb-4 rounded flex items-center justify-center text-gray-400 italic text-sm">No Image</div>
        <?php endif; ?>

        <h2 class="text-xl font-semibold mb-2 text-gray-800 break-words"><?php echo htmlspecialchars($event_title); ?></h2>

        <?php if ($event_date): ?>
            <p class="text-gray-600 mb-1 text-sm flex items-center">
                <i class="far fa-calendar-alt mr-2 opacity-75 w-4 text-center"></i>
                <span class="font-medium mr-1">Date:</span> <?php echo htmlspecialchars(date('D, M j, Y', strtotime($event_date))); ?>
            </p>
        <?php endif; ?>
        <?php if (!empty($event_time)): ?>
            <p class="text-gray-600 mb-1 text-sm flex items-center">
                <i class="far fa-clock mr-2 opacity-75 w-4 text-center"></i>
                <span class="font-medium mr-1">Time:</span> <?php echo htmlspecialchars(date('g:i A', strtotime($event_time))); ?>
            </p>
        <?php endif; ?>
        <?php if (!empty($location)): ?>
            <p class="text-gray-600 mb-3 text-sm flex items-center">
                <i class="fas fa-map-marker-alt mr-2 opacity-75 w-4 text-center"></i>
                <span class="font-medium mr-1">Location:</span> <?php echo htmlspecialchars($location); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($description)): ?>
            <p class="text-gray-700 my-3 text-sm prose prose-sm max-w-none">
                <?php
                $desc_text = htmlspecialchars($description);
                echo nl2br(strlen($desc_text) > 100 ? substr($desc_text, 0, 100) . '...' : $desc_text);
                ?>
            </p>
        <?php endif; ?>

        <p class="text-xs text-gray-500 mb-4 mt-2">
            Posted by: <?php echo htmlspecialchars($creator_username); ?>
        </p>
    </div> <div class="border-t pt-3 mt-auto flex flex-wrap items-center justify-end gap-2">

        <?php
        // --- RSVP Button Logic ---
        $user_has_rsvpd = false; // Default assumption
        if ($rsvp_enabled && $user_id && isset($pdo) && $pdo instanceof PDO) { // Check user, enabled status, and PDO object
            try {
                $rsvp_check_sql = "SELECT id FROM rsvps WHERE user_id = ? AND event_id = ? LIMIT 1";
                $rsvp_stmt = $pdo->prepare($rsvp_check_sql);
                $rsvp_stmt->execute([$user_id, $event_id]);
                if ($rsvp_stmt->fetch()) {
                    $user_has_rsvpd = true;
                }
            } catch (PDOException $e) { /* Ignore error for card display */ }
        }
        // --- End RSVP Check ---
        ?>

        <?php // --- Display RSVP/Cancel Button ---
        if ($rsvp_enabled):
            if ($user_id): // Check if user is logged in
                if ($user_has_rsvpd): ?>
                    <form action="handle_rsvp.php" method="POST" class="inline-block m-0 p-0"><input type="hidden" name="event_id" value="<?php echo $event_id; ?>"><input type="hidden" name="action" value="unrsvp"><button type="submit" class="text-xs bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150" title="Cancel RSVP"><i class="fas fa-check mr-1"></i> Going</button></form>
                <?php else: ?>
                    <form action="handle_rsvp.php" method="POST" class="inline-block m-0 p-0"><input type="hidden" name="event_id" value="<?php echo $event_id; ?>"><input type="hidden" name="action" value="rsvp"><button type="submit" class="text-xs bg-green-500 hover:bg-green-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">RSVP Now</button></form>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php?redirect=view_event.php?id=<?php echo $event_id; ?>" class="text-xs text-blue-600 italic hover:underline" title="Login to RSVP">Login to RSVP</a>
            <?php endif; ?>
        <?php endif; ?>
        <?php // --- END RSVP Button --- ?>


        <?php // --- View/Edit/Delete Buttons ---
        // Only Admins can Edit/Delete
        $can_edit_delete = (isset($user_role) && $user_role === 'admin');
        ?>
        <a href="view_event.php?id=<?php echo $event_id; ?>" class="text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">View</a>
        <?php if ($can_edit_delete): ?>
            <a href="edit_event.php?id=<?php echo $event_id; ?>" class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">Edit</a>
            <a href="delete_event.php?id=<?php echo $event_id; ?>" class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
        <?php endif; ?>
        <?php // --- End View/Edit/Delete Buttons --- ?>

    </div> </div>