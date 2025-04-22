<?php
session_start(); // Start session FIRST

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit; // IMPORTANT: Stop script execution
}

require_once 'includes/db_connect.php'; // Include DB AFTER session check

// Get user details from session (safe now)
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$username = $_SESSION['username'] ?? 'User';

// --- ADDED: Check for Category Filter ---
$category_filter = trim($_GET['category'] ?? ''); // Get category from URL
$category_display = htmlspecialchars($category_filter); // Sanitize for display

// --- Fetch events from the database ---
$events = [];
$dashboard_error = ''; // Initialize error variable
$params = []; // Parameters for prepared statement

try {
    // Base SQL query - Selecting necessary columns including rsvp_enabled, image_path
    $sql = "SELECT e.*, u.username as creator_username
            FROM events e
            JOIN users u ON e.created_by_user_id = u.id";

    // ADDED: WHERE clause if filtering by category
    if (!empty($category_filter)) {
        $sql .= " WHERE e.category = ?"; // Filter by category column
        $params[] = $category_filter;      // Add category to parameters
    }

    // Add ordering
    $sql .= " ORDER BY e.event_date ASC, e.event_time ASC";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Execute with or without category parameter
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // error_log("Dashboard Event Fetch Error: " . $e->getMessage()); // Log error
    $dashboard_error = "Could not load events. Please try again later."; // User-friendly message
}


// Placeholder data for categories (Ensure names match DB categories)
$categories = [
    ['name' => 'Business', 'img' => 'img/buisness.png'],
    ['name' => 'Music', 'img' => 'img/music.png'],
    ['name' => 'Performances', 'img' => 'img/performances.png'],
    ['name' => 'Festivals', 'img' => 'img/festivals.png'],
    ['name' => 'Workshops', 'img' => 'img/workshop.png'],
    ['name' => 'Others', 'img' => 'img/others.png'],
];


// Include header AFTER session check and DB include
include 'includes/header.php';
?>

    <div class="relative w-full h-[60vh] text-white text-center mb-6 bg-[url('img/ic_bg.png')] bg-cover bg-center bg-no-repeat">
        <div class="absolute inset-0 bg-black/50"></div> <div class="absolute inset-0 z-10 flex flex-col items-center justify-center p-4 pt-20"> <h1 class="text-[50px] font-bold mb-2 flex flex-wrap items-baseline justify-center">
                <p class="text-cyan-400 mr-2">Live.</p> Don't Just Exist.</h1>
            <h2 class="text-[26px] mb-4">Discover the Most happening events around you</h2>
            <form action="search_results.php" method="GET" class="w-full max-w-xl mx-auto">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                    <input type="search" name="q" placeholder="Search keyword..." class="p-2 h-[50px] rounded-lg sm:rounded-l-lg sm:rounded-r-none border border-gray-300 text-gray-800 placeholder-gray-500 w-full sm:flex-grow focus:outline-none focus:ring-2 focus:ring-blue-400" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    <input type="text" name="loc" placeholder="Enter City or Area..." class="p-2 h-[50px] rounded-lg sm:rounded-none border border-gray-300 text-gray-800 placeholder-gray-500 w-full sm:w-auto sm:flex-grow-[0.7] focus:outline-none focus:ring-2 focus:ring-blue-400" value="<?php echo htmlspecialchars($_GET['loc'] ?? ''); ?>">
                    <button class="rounded-lg sm:rounded-r-lg sm:rounded-l-none h-[50px] px-5 bg-gradient-to-t from-blue-800 to-cyan-500 text-white font-semibold hover:opacity-90 w-full sm:w-auto" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>


    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Explore Categories</h2>
            <div class="flex space-x-8 justify-center overflow-x-auto py-4 category-scroll">
                <a href="dashboard.php" class="flex flex-col items-center text-center w-32 flex-shrink-0 group" title="Show All Events">
                    <div class="w-32 h-32 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center mb-2 shadow-sm group-hover:shadow-lg transition-shadow duration-200 <?php echo (empty($category_filter)) ? 'ring-4 ring-blue-400 ring-offset-2' : ''; // Highlight if no filter ?>">
                        <i class="fas fa-asterisk text-4xl text-gray-500"></i> </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 <?php echo (empty($category_filter)) ? 'text-blue-600 font-bold' : ''; // Highlight if no filter ?>">All Events</span>
                </a>
                <?php foreach ($categories as $category):
                    // Generate the link for this category filter
                    $category_link = 'dashboard.php?category=' . urlencode($category['name']);
                    $is_active = ($category_filter === $category['name']); // Check if this category is active
                    ?>
                    <a href="<?php echo htmlspecialchars($category_link); ?>" class="flex flex-col items-center text-center w-32 flex-shrink-0 group">
                        <img
                                src="<?php echo htmlspecialchars($category['img']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>"
                                class="w-32 h-32 rounded-full object-cover mb-2 border border-gray-200 shadow-sm group-hover:shadow-lg transition-shadow duration-200 <?php echo $is_active ? 'ring-4 ring-blue-400 ring-offset-2' : ''; // Highlight active category ?>"
                                loading="lazy"
                        />
                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 <?php echo $is_active ? 'text-blue-600 font-bold' : ''; // Highlight active category label ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="container mx-auto px-4 py-8">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                Upcoming Events <?php echo !empty($category_display) ? 'in "' . $category_display . '"' : ''; ?>
            </h1>
            <?php if ($user_role === 'admin'): ?>
                <a href="add_event.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow flex-shrink-0">
                    + Add New Event
                </a>
            <?php endif; ?>
        </div>


        <?php if (isset($dashboard_error) && $dashboard_error): ?>
            <div class="notice-message notice-error" role="alert">
                <?php echo htmlspecialchars($dashboard_error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($events) && empty($dashboard_error)): ?>
            <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
                No events found<?php echo !empty($category_display) ? ' for the category "' . $category_display . '"' : ' scheduled yet'; ?>.
                <?php if ($user_role === 'admin'): ?>
                <?php endif; ?>
            </div>
        <?php elseif (!empty($events)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($events as $event): ?>
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
                            <?php /* RSVP Logic and Buttons (copied, no change needed here) */
                            $user_has_rsvpd = false;
                            if (!empty($event['rsvp_enabled']) && isset($_SESSION['user_id'])) { try { $rsvp_check_sql = "SELECT id FROM rsvps WHERE user_id = ? AND event_id = ? LIMIT 1"; $rsvp_stmt = $pdo->prepare($rsvp_check_sql); $rsvp_stmt->execute([$_SESSION['user_id'], $event['id']]); if ($rsvp_stmt->fetch()) { $user_has_rsvpd = true; } } catch (PDOException $e) { /* Ignore */ } }
                            if (!empty($event['rsvp_enabled'])): if (isset($_SESSION['user_id'])): if ($user_has_rsvpd): ?><form action="handle_rsvp.php" method="POST" class="inline-block"><input type="hidden" name="event_id" value="<?php echo $event['id']; ?>"><input type="hidden" name="action" value="unrsvp"><button type="submit" class="text-xs bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150" title="Cancel RSVP"><i class="fas fa-check mr-1"></i> Going</button></form><?php else: ?><form action="handle_rsvp.php" method="POST" class="inline-block"><input type="hidden" name="event_id" value="<?php echo $event['id']; ?>"><input type="hidden" name="action" value="rsvp"><button type="submit" class="text-xs bg-green-500 hover:bg-green-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">RSVP Now</button></form><?php endif; else: ?><span class="text-xs text-gray-400 italic" title="Login to RSVP">RSVP Enabled</span><?php endif; endif;
                            /* View/Edit/Delete Buttons (copied, no change needed here) */
                            $can_edit_delete = ($user_role === 'admin' || (isset($event['created_by_user_id']) && $event['created_by_user_id'] == $user_id));
                            ?>
                            <a href="view_event.php?id=<?php echo $event['id']; ?>" class="text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">View</a>
                            <?php if ($can_edit_delete): ?>
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150">Edit</a>
                                <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-2 rounded transition-colors duration-150" onclick="return confirm('Are you sure?');">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; // End event loop ?>
            </div>
        <?php endif; // End check for events ?>
    </section>

<?php include 'includes/footer.php'; ?>