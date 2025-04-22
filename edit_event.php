<?php
session_start(); // Start session FIRST

require_once 'includes/db_connect.php'; // Include DB connection

// --- 1. Get Event ID and Validate ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location: dashboard.php?status=invalid_event_id');
    exit;
}
$event_id = (int)$_GET['id'];

// --- 2. Fetch Current Event Data ---
$event = null;
$fetch_error = '';
try {
    $sql = "SELECT * FROM events WHERE id = ? LIMIT 1"; // Select all columns
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header('Location: dashboard.php?status=event_not_found');
        exit;
    }
} catch (PDOException $e) {
    // error_log("Edit Event Fetch Error: " . $e->getMessage());
    die("Error fetching event details. Please try again later.");
}

// --- 3. UPDATED Authorization Check: ADMIN ONLY ---
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['user_role'] ?? null;

// ONLY check if the user is logged in AND their role is 'admin'
if (!$current_user_id || $current_user_role !== 'admin') {
    // Not logged in OR not an admin
    header('Location: view_event.php?id=' . $event_id . '&status=unauthorized'); // Redirect non-admins
    exit;
}
// --- END UPDATED Auth Check ---


// --- ADDED: Categories Definition ---
$categories = [
    ['name' => 'Business'], ['name' => 'Music'], ['name' => 'Performances'],
    ['name' => 'Festivals'], ['name' => 'Workshops'], ['name' => 'Others'],
];
// --- END Categories ---

// --- 4. Initialize Variables for Form (from fetched data) ---
$errors = []; // Holds validation errors
$title = $event['title'];
$description = $event['description'] ?? '';
$event_date = $event['event_date'];
$event_time = $event['event_time'] ?? '';
$location = $event['location'] ?? '';
$category_selected = $event['category'] ?? ''; // ADDED: Get current category for sticky form
$current_image_path = $event['image_path'];
$rsvp_checked = !empty($event['rsvp_enabled']);

// --- 5. Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify submitted event ID matches (optional security)
    if (!isset($_POST['event_id']) || (int)$_POST['event_id'] !== $event_id) {
        $errors[] = "Form submission error. Event ID mismatch.";
        goto display_form; // Jump to HTML part
    }

    // Get submitted data
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $category_selected = trim($_POST['category'] ?? ''); // ADDED: Get submitted category
    $rsvp_enabled = isset($_POST['enable_rsvp']) ? 1 : 0;
    $rsvp_checked = isset($_POST['enable_rsvp']);

    // Basic Validation
    if (empty($title)) $errors[] = "Event title is required.";
    if (empty($event_date)) $errors[] = "Event date is required.";
    elseif (strtotime($event_date) === false) $errors[] = "Invalid event date format.";
    if (!empty($event_time) && strtotime($event_time) === false) $errors[] = "Invalid event time format.";
    if (empty($category_selected)) $errors[] = "Category is required."; // ADDED: Validate category

    // Handle Image Upload (Optional Update)
    $image_path_to_save = $current_image_path;
    $new_image_uploaded = false;
    // ... (Your existing image upload logic - NO CHANGE NEEDED HERE) ...
    // It should set $image_path_to_save if new image valid & moved
    // It should set $new_image_uploaded = true if successful

    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $file_info = $_FILES['event_image'];
        $upload_dir = 'uploads/event_images/';
        $max_file_size = 2 * 1024 * 1024; // 2 MB
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if ($file_info['size'] > $max_file_size) $errors[] = "New image file is too large (Max 2MB).";
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime_type = finfo_file($finfo, $file_info['tmp_name']); finfo_close($finfo);
            if (!in_array($mime_type, $allowed_mime_types)) $errors[] = "Invalid new image file type (Allowed: JPG, PNG, WEBP, GIF).";
        }

        if (empty($errors)) {
            $extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
            $unique_filename = uniqid('event_', true) . '.' . $extension;
            $target_path = $upload_dir . $unique_filename;
            if (move_uploaded_file($file_info['tmp_name'], $target_path)) {
                $image_path_to_save = $target_path; $new_image_uploaded = true;
            } else { $errors[] = "Server error: Failed to move newly uploaded image."; }
        }
    } elseif (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Error uploading new image. Code: " . $_FILES['event_image']['error'];
    }


    // --- Database Update (if NO validation errors) ---
    if (empty($errors)) {
        try {
            // UPDATED: Added 'category = ?'
            $sql = "UPDATE events SET
                        title = ?, description = ?, event_date = ?, event_time = ?,
                        location = ?, category = ?, image_path = ?, rsvp_enabled = ?
                    WHERE id = ?"; // 9 placeholders total
            $stmt = $pdo->prepare($sql);

            $time_param = !empty($event_time) ? $event_time : null;

            // UPDATED: Added $category_selected to execute array
            if ($stmt->execute([$title, $description, $event_date, $time_param, $location, $category_selected, $image_path_to_save, $rsvp_enabled, $event_id])) {

                // Delete old image ONLY if a new one was successfully uploaded
                if ($new_image_uploaded && !empty($current_image_path) && $current_image_path !== $image_path_to_save) {
                    if (file_exists($current_image_path)) { @unlink($current_image_path); }
                }

                header('Location: view_event.php?id=' . $event_id . '&status=updated');
                exit;
            } else { $errors[] = "Database error: Failed to execute update."; }
        } catch (PDOException $e) { $errors[] = "Database error saving updates."; /* Log $e */ }
    }
    // If errors occurred, the script continues below to display the form

} // --- End POST Request Handling ---

// Label for goto jump
display_form:

// --- Include Header ---
include 'includes/header.php';
?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg border border-gray-200">
            <h1 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-3">Edit Event</h1>

            <?php if (!empty($errors)): ?>
                <div class="notice-message notice-error mb-6" role="alert">
                    <strong class="font-bold block mb-1">Please fix the following errors:</strong>
                    <ul class="list-disc list-inside mt-1 text-sm">
                        <?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="edit_event.php?id=<?php echo $event_id; ?>" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Event Title *</label>
                    <input type="text" id="title" name="title" required class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent <?php echo (!empty($errors) && empty($title)) ? 'border-red-500' : 'border-gray-300'; ?>" value="<?php echo htmlspecialchars($title); ?>">
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea id="description" name="description" rows="5" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Current Image</label>
                    <?php if (!empty($current_image_path) && file_exists($current_image_path)): ?>
                        <img src="<?php echo htmlspecialchars($current_image_path); ?>" alt="Current Event Image" class="max-w-xs h-auto rounded mb-2 border">
                    <?php else: ?>
                        <p class="text-sm text-gray-500 italic mb-2">No current image.</p>
                    <?php endif; ?>
                    <label for="event_image" class="block text-gray-700 text-sm font-bold mb-2">Upload New Image (Optional - Replaces Current)</label>
                    <input type="file" id="event_image" name="event_image" accept="image/jpeg, image/png, image/webp, image/gif" class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">Leave empty to keep the current image. Max 2MB. Allowed: JPG, PNG, WEBP, GIF.</p>
                </div>

                <div class="mb-4">
                    <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category *</label>
                    <select name="category" id="category" required class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent <?php echo (!empty($errors) && empty($category_selected)) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <option value="">-- Select a Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo ($category_selected === $cat['name']) ? 'selected' : ''; // Pre-select current category ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="event_date" class="block text-gray-700 text-sm font-bold mb-2">Date *</label>
                        <input type="date" id="event_date" name="event_date" required class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent <?php echo (!empty($errors) && empty($event_date)) ? 'border-red-500' : 'border-gray-300'; ?>" value="<?php echo htmlspecialchars($event_date); ?>">
                    </div>
                    <div>
                        <label for="event_time" class="block text-gray-700 text-sm font-bold mb-2">Time (Optional)</label>
                        <input type="time" id="event_time" name="event_time" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" value="<?php echo htmlspecialchars($event_time); ?>">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Location</label>
                    <input type="text" id="location" name="location" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" value="<?php echo htmlspecialchars($location); ?>">
                </div>

                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="enable_rsvp" name="enable_rsvp" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-offset-0 focus:ring-blue-200 focus:ring-opacity-50" <?php echo $rsvp_checked ? 'checked' : ''; ?> > <span class="ml-2 text-sm text-gray-600">Enable RSVP for this event</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-4 border-t pt-6 mt-6">
                    <a href="view_event.php?id=<?php echo $event_id; ?>" class="text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium py-2 px-4 rounded border border-gray-300 transition duration-150 ease-in-out">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition duration-150 ease-in-out">
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>