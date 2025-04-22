<?php
session_start(); // Start session FIRST

// --- Authorization Check ---
// Ensure user is logged in AND is an admin to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php?status=unauthorized'); // Redirect non-admins/logged-out users
    exit;
}

// --- Includes and Initializations ---
require_once 'includes/db_connect.php'; // DB AFTER check

// --- ADDED: Categories Definition ---
// Ideally, fetch from DB or config file later
$categories = [
    ['name' => 'Business'], ['name' => 'Music'], ['name' => 'Performances'],
    ['name' => 'Festivals'], ['name' => 'Workshops'], ['name' => 'Others'],
];
// --- END Categories ---

// Initialize variables for the form
$errors = []; // Holds validation errors
$title = '';
$description = '';
$event_date = '';
$event_time = '';
$location = '';
$category_selected = ''; // ADDED: For sticky category selection
$image_db_path = null;
$rsvp_checked = false;

// --- Form Submission Handling ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Get Data from POST
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $category_selected = trim($_POST['category'] ?? ''); // ADDED: Get selected category
    $admin_user_id = $_SESSION['user_id'];
    $rsvp_enabled = isset($_POST['enable_rsvp']) ? 1 : 0;
    $rsvp_checked = isset($_POST['enable_rsvp']);

    // 2. Basic Validation
    if (empty($title)) $errors[] = "Event title is required.";
    if (empty($event_date)) $errors[] = "Event date is required.";
    elseif (strtotime($event_date) === false) $errors[] = "Invalid event date format.";
    if (!empty($event_time) && strtotime($event_time) === false) $errors[] = "Invalid event time format.";
    if (empty($category_selected)) $errors[] = "Category is required."; // ADDED: Validate category

    // 3. Image Upload Handling
    // ... (Your existing image upload logic - NO CHANGE NEEDED HERE) ...
    // It should set $image_db_path if successful

    // 4. Database Insertion (Proceed only if ALL validation passed)
    if (empty($errors)) {
        try {
            // UPDATED: Added 'category' column and placeholder
            $sql = "INSERT INTO events
                        (title, description, event_date, event_time, location, category, created_by_user_id, image_path, rsvp_enabled)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 9 placeholders now
            $stmt = $pdo->prepare($sql);

            $time_param = !empty($event_time) ? $event_time : null;

            // UPDATED: Added $category_selected to the execute array
            if ($stmt->execute([$title, $description, $event_date, $time_param, $location, $category_selected, $admin_user_id, $image_db_path, $rsvp_enabled])) {
                header('Location: dashboard.php?status=event_added');
                exit;
            } else {
                $errors[] = "Database error: Failed to execute statement.";
            }
        } catch (PDOException $e) {
            // error_log("Add Event DB Error: " . $e->getMessage());
            $errors[] = "Database error saving the event. Please try again.";
        }
    }

} // --- End POST Request Handling ---

// Label for goto jump (if used in file upload)
display_form:

// --- HTML Form Section ---
include 'includes/header.php'; // Include header AFTER processing
?>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg border border-gray-200">
            <h1 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-3">Add New Event</h1>

            <?php if (!empty($errors)): ?>
                <div class="notice-message notice-error mb-6" role="alert">
                    <strong class="font-bold block mb-1">Please fix the following errors:</strong>
                    <ul class="list-disc list-inside mt-1 text-sm">
                        <?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="add_event.php" method="POST" enctype="multipart/form-data" novalidate>

                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Event Title *</label>
                    <input type="text" id="title" name="title" required
                           class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent <?php echo (!empty($errors) && empty($title)) ? 'border-red-500' : 'border-gray-300'; ?>"
                           value="<?php echo htmlspecialchars($title); ?>">
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea id="description" name="description" rows="5"
                              class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="event_image" class="block text-gray-700 text-sm font-bold mb-2">Event Image (Optional, Max 2MB)</label>
                    <input type="file" id="event_image" name="event_image" accept="image/jpeg, image/png, image/webp, image/gif"
                           class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">Allowed types: JPG, PNG, WEBP, GIF.</p>
                </div>

                <div class="mb-4">
                    <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category *</label>
                    <select name="category" id="category" required
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent <?php echo (!empty($errors) && empty($category_selected)) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <option value="">-- Select a Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo ($category_selected === $cat['name']) ? 'selected' : ''; /* Sticky select */ ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="event_date" class="block text-gray-700 text-sm font-bold mb-2">Date *</label>
                        <input type="date" id="event_date" name="event_date" required
                               class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent <?php echo (!empty($errors) && empty($event_date)) ? 'border-red-500' : 'border-gray-300'; ?>"
                               value="<?php echo htmlspecialchars($event_date); ?>">
                    </div>
                    <div>
                        <label for="event_time" class="block text-gray-700 text-sm font-bold mb-2">Time (Optional)</label>
                        <input type="time" id="event_time" name="event_time"
                               class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                               value="<?php echo htmlspecialchars($event_time); ?>">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Location</label>
                    <input type="text" id="location" name="location"
                           class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                           value="<?php echo htmlspecialchars($location); ?>">
                </div>

                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="enable_rsvp" name="enable_rsvp" value="1"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-offset-0 focus:ring-blue-200 focus:ring-opacity-50"
                            <?php echo $rsvp_checked ? 'checked' : ''; ?> >
                        <span class="ml-2 text-sm text-gray-600">Enable RSVP for this event</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-4 border-t pt-6 mt-6">
                    <a href="dashboard.php" class="text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium py-2 px-4 rounded border border-gray-300 transition duration-150 ease-in-out">Cancel</a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 transition duration-150 ease-in-out">
                        Add Event
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>