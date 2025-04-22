<?php
session_start(); // Start session FIRST

require_once 'includes/db_connect.php'; // Include DB connection

// Get User ID and Role (needed for RSVP/Edit/Delete buttons)
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

// --- Get and Sanitize Search Queries ---
$query = trim($_GET['q'] ?? '');
$location_query = trim($_GET['loc'] ?? '');

$display_query = htmlspecialchars($query);
$display_location = htmlspecialchars($location_query);

// --- Initialize Variables ---
$results = [];
$search_error = '';
$sql_conditions = []; // Holds WHERE conditions
$params = [];         // Holds parameters for PDO binding

// --- Build Search Query ---
if (empty($query) && empty($location_query)) {
    $search_error = "Please enter a search term or location.";
} else {
    // Base SQL
    $sql = "SELECT e.*, u.username as creator_username
            FROM events e
            JOIN users u ON e.created_by_user_id = u.id
            WHERE 1=1"; // Start with a true condition

    // Add general query condition (searches title and description)
    if (!empty($query)) {
        $sql_conditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
        $search_term = "%" . $query . "%";
        $params[] = $search_term;
        $params[] = $search_term;
    }

    // Add location condition (searches location field)
    if (!empty($location_query)) {
        $sql_conditions[] = "e.location LIKE ?";
        $location_term = "%" . $location_query . "%";
        $params[] = $location_term;
    }

    // Append conditions to SQL if any exist
    if (!empty($sql_conditions)) {
        $sql .= " AND " . implode(' AND ', $sql_conditions);
    }

    // Add ordering
    $sql .= " ORDER BY e.event_date ASC, e.event_time ASC";

    // --- Execute Search ---
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params); // Execute with the built parameters
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // error_log("Search Error: " . $e->getMessage()); // Log error
        $search_error = "An error occurred during the search. Please try again.";
        $results = []; // Ensure results is empty on error
    }
} // End if query or location provided


// --- Include Header ---
include 'includes/header.php';
?>

    <div class="container mx-auto px-4 py-8">

        <h1 class="text-3xl font-bold text-gray-800 mb-6">
            Search Results
            <?php
            $criteria = [];
            if (!empty($display_query)) { $criteria[] = 'for "'. $display_query . '"'; }
            if (!empty($display_location)) { $criteria[] = 'in "'. $display_location . '"'; }
            echo !empty($criteria) ? ' ' . implode(' ', $criteria) : '';
            ?>
        </h1>

        <?php if ($search_error): // Display any errors ?>
            <div class="notice-message notice-error" role="alert">
                <?php echo htmlspecialchars($search_error); ?>
            </div>
        <?php elseif (empty($results) && (!empty($query) || !empty($location_query))): // Display no results message only if a search was attempted ?>
            <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
                No events found matching your search criteria.
            </div>
        <?php elseif (!empty($results)): // Display Results Grid ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <?php
                // --- Loop Through Results ---
                // Uses the SAME event card structure as dashboard.php
                // !! Consider extracting event card to an include file !!
                foreach ($results as $event):
                    ?>
                    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 flex flex-col justify-between">
                        <div> <?php if (!empty($event['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="w-full h-40 object-cover mb-4 rounded">
                            <?php else: ?>
                                <div class="w-full h-40 bg-gray-200 mb-4 rounded flex items-center justify-center text-gray-400 italic text-sm">No Image</div>
                            <?php endif; ?>
                            <h2 class="text-xl font-semibold mb-2 text-gray-800"><?php echo htmlspecialchars($event['title']); ?></h2>
                            <p class="text-gray-600 mb-1 text-sm flex items-center"><i class="far fa-calendar-alt mr-2 opacity-75 w-4 text-center"></i> <span class="font-medium mr-1">Date:</span> <?php echo htmlspecialchars(date('D, M j, Y', strtotime($event['event_date']))); ?></p>
                            <?php if (!empty($event['event_time'])): ?><p class="text-gray-600 mb-1 text-sm flex items-center"><i class="far fa-clock mr-2 opacity-75 w-4 text-center"></i> <span class="font-medium mr-1">Time:</span> <?php echo htmlspecialchars(date('g:i A', strtotime($event['event_time']))); ?></p><?php endif; ?>
                            <?php if (!empty($event['location'])): ?><p class="text-gray-600 mb-3 text-sm flex items-center"><i class="fas fa-map-marker-alt mr-2 opacity-75 w-4 text-center"></i> <span class="font-medium mr-1">Location:</span> <?php echo htmlspecialchars($event['location']); ?></p><?php endif; ?>
                            <?php if (!empty($event['description'])): ?><p class="text-gray-700 my-3 text-sm prose prose-sm max-w-none"><?php $desc = htmlspecialchars($event['description']); echo nl2br(strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc);?></p><?php endif; ?>
                            <p class="text-xs text-gray-500 mb-4 mt-2">Posted by: <?php echo htmlspecialchars($event['creator_username']); ?></p>
                        </div>

                        <div class="border-t pt-3 mt-auto flex flex-wrap items-center justify-end gap-2">
                            <?php
                            $user_has_rsvpd = false;
                            if (!empty($event['rsvp_enabled']) && $user_id) {
                                try {
                                    $rsvp_check_sql = "SELECT id FROM rsvps WHERE user_id = ? AND event_id = ? LIMIT 1";
                                    $rsvp_stmt = $pdo->prepare($rsvp_check_sql); $rsvp_stmt->execute([$user_id, $event['id']]);
                                    if ($rsvp_stmt->fetch()) $user_has_rsvpd = true;
                                } catch (PDOException $e) { /* Ignore */ }
                            }
                            ?>
                            <?php if (!empty($event['rsvp_enabled'])): if ($user_id): if ($user_has_rsvpd): ?><form action="handle_rsvp.php" method="POST" class="inline-block"><input type="hidden" name="event_id" value="<?php echo $event['id']; ?>"><input type="hidden" name="action" value="unrsvp"><button type="submit" class="text-xs bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150" title="Cancel RSVP"><i class="fas fa-check mr-1"></i> Going</button></form><?php else: ?><form action="handle_rsvp.php" method="POST" class="inline-block"><input type="hidden" name="event_id" value="<?php echo $event['id']; ?>"><input type="hidden" name="action" value="rsvp"><button type="submit" class="text-xs bg-green-500 hover:bg-green-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">RSVP Now</button></form><?php endif; else: ?><span class="text-xs text-gray-400 italic" title="Login to RSVP">RSVP Enabled</span><?php endif; endif; ?>
                            <?php $can_edit_delete = ($user_role === 'admin' || (isset($event['created_by_user_id']) && $event['created_by_user_id'] == $user_id)); ?>
                            <a href="view_event.php?id=<?php echo $event['id']; ?>" class="text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">View</a>
                            <?php if ($can_edit_delete): ?>
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">Edit</a>
                                <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150" onclick="return confirm('Are you sure?');">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; // End results loop ?>

            </div> <?php endif; // End else results exist ?>

    </div> <?php
// --- Include Footer ---
include 'includes/footer.php';
?>