<?php
session_start(); // Start session FIRST

// --- Authorization Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// --- Includes and CSRF Token Generation ---
require_once 'includes/db_connect.php';

// Generate CSRF token if one doesn't exist (for user actions)
if (empty($_SESSION['csrf_token'])) {
    try { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); } catch (Exception $e) { die("Failed to generate security token."); }
}
$csrf_token = $_SESSION['csrf_token'];


// --- Fetch Users ---
$users = [];
$user_fetch_error = ''; // Renamed error variable
try {
    $stmt_users = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC");
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // error_log("Admin User Fetch Error: " . $e->getMessage());
    $user_fetch_error = "Could not retrieve user list.";
}

// --- Fetch Events with RSVP Count ---
$events_with_rsvp = [];
$event_fetch_error = ''; // Renamed error variable
try {
    $sql_events = "SELECT
                       e.id, e.title, e.event_date, e.location, e.rsvp_enabled,
                       COUNT(r.id) as rsvp_count
                   FROM events e
                   LEFT JOIN rsvps r ON e.id = r.event_id
                   GROUP BY e.id, e.title, e.event_date, e.location, e.rsvp_enabled
                   ORDER BY e.event_date ASC, e.id ASC";
    $stmt_events = $pdo->query($sql_events);
    $events_with_rsvp = $stmt_events->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // error_log("Admin Event Fetch Error: " . $e->getMessage());
    $event_fetch_error = "Could not retrieve event list with RSVP counts.";
}


// --- Include Header ---
include 'includes/header.php';

$current_admin_id = $_SESSION['user_id'];

?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Admin Panel</h1>

        <?php
        if (isset($_GET['status'])) {
            // ... (Existing status message display logic - no change) ...
            $status = $_GET['status']; $message = ''; $type = 'notice-error';
            if ($status === 'role_changed') { $message = 'User role updated.'; $type = 'notice-success'; }
            elseif ($status === 'user_deleted') { $message = 'User deleted.'; $type = 'notice-success'; }
            elseif ($status === 'delete_failed' || $status === 'role_failed') { $message = 'Action failed.'; }
            elseif ($status === 'csrf_error') { $message = 'Invalid security token.'; }
            elseif ($status === 'cant_self') { $message = 'Action cannot be performed on your own account.'; }
            elseif ($status === 'user_not_found') { $message = 'User not found.'; }
            if ($message) { echo "<div class='notice-message {$type} mb-6'>" . htmlspecialchars($message) . "</div>"; }
        }
        ?>

        <section class="mb-12">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4 border-b pb-2">User Management</h2>
            <?php if ($user_fetch_error): ?>
                <div class="notice-message notice-error" role="alert"><?php echo htmlspecialchars($user_fetch_error); ?></div>
            <?php else: ?>
                <div class="bg-white shadow-md rounded-lg overflow-x-auto">
                    <table class="w-full text-left table-auto">
                        <thead class="bg-gray-100 border-b-2 border-gray-200">
                        <tr>
                            <th class="p-3 text-sm font-semibold tracking-wide">ID</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Username</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Email</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Role</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Registered</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <?php if (empty($users)): ?>
                            <tr><td colspan="6" class="p-3 text-center text-gray-500">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-700"><?php echo $user['id']; ?></td>
                                    <td class="p-3 text-sm text-gray-700 font-medium"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?php if ($user['role'] === 'admin'): ?><span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Admin</span><?php else: ?><span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">User</span><?php endif; ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?></td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <div class="flex items-center space-x-1"> <?php /* Reduced space */ ?>
                                            <?php if ($user['id'] !== $current_admin_id): ?>
                                                <form action="change_role.php" method="POST" class="inline-block m-0 p-0"><input type="hidden" name="user_id" value="<?php echo $user['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>"><?php if ($user['role'] === 'user'): ?><input type="hidden" name="action" value="make_admin"><button type="submit" class="text-xs bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded focus:outline-none focus:ring-1 focus:ring-green-400" title="Promote">Make Admin</button><?php elseif ($user['role'] === 'admin'): ?><input type="hidden" name="action" value="make_user"><button type="submit" class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded focus:outline-none focus:ring-1 focus:ring-yellow-400" title="Demote">Make User</button><?php endif; ?></form>
                                                <form action="delete_user.php" method="POST" class="inline-block m-0 p-0"><input type="hidden" name="user_id" value="<?php echo $user['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>"><button type="submit" class="text-xs bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded focus:outline-none focus:ring-1 focus:ring-red-400" title="Delete User" onclick="return confirm('DELETE user \'<?php echo htmlspecialchars(addslashes($user['username'])); ?>\'? This also deletes their events/RSVPs!');">Delete</button></form>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 italic">(You)</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>


        <section>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4 border-b pb-2">Event Overview</h2>
            <?php if ($event_fetch_error): ?>
                <div class="notice-message notice-error" role="alert"><?php echo htmlspecialchars($event_fetch_error); ?></div>
            <?php else: ?>
                <div class="bg-white shadow-md rounded-lg overflow-x-auto">
                    <table class="w-full text-left table-auto">
                        <thead class="bg-gray-100 border-b-2 border-gray-200">
                        <tr>
                            <th class="p-3 text-sm font-semibold tracking-wide">ID</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Title</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Date</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Location</th>
                            <th class="p-3 text-sm font-semibold tracking-wide text-center">RSVP Enabled</th>
                            <th class="p-3 text-sm font-semibold tracking-wide text-center">RSVPs</th>
                            <th class="p-3 text-sm font-semibold tracking-wide">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <?php if (empty($events_with_rsvp)): ?>
                            <tr><td colspan="7" class="p-3 text-center text-gray-500">No events found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($events_with_rsvp as $event): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-700"><?php echo $event['id']; ?></td>
                                    <td class="p-3 text-sm text-gray-700 font-medium"><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars(date('Y-m-d', strtotime($event['event_date']))); ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></td>
                                    <td class="p-3 text-sm text-gray-700 text-center">
                                        <?php if(!empty($event['rsvp_enabled'])): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-sm text-gray-700 text-center font-medium">
                                        <?php echo $event['rsvp_count']; ?>
                                        <?php if(!empty($event['rsvp_enabled'])): ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-sm text-gray-700">
                                        <div class="flex items-center space-x-1">
                                            <a href="view_event.php?id=<?php echo $event['id']; ?>" class="text-xs bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded focus:outline-none focus:ring-1 focus:ring-blue-400" title="View Event">View</a>
                                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-2 rounded focus:outline-none focus:ring-1 focus:ring-yellow-400" title="Edit Event">Edit</a>
                                            <form action="delete_event.php?id=<?php echo $event['id']; ?>" method="GET" class="inline-block m-0 p-0" onsubmit="return confirm('Are you sure you want to delete event \'<?php echo htmlspecialchars(addslashes($event['title'])); ?>\'?');">
                                                <button type="submit" class="text-xs bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded focus:outline-none focus:ring-1 focus:ring-red-400" title="Delete Event">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php
// Include footer
include 'includes/footer.php';
?>