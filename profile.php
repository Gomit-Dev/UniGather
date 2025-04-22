<?php
session_start(); // Start session FIRST

// --- Authorization Check ---
// Ensure user is logged in to access this page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php'); // Redirect to login if not logged in
    exit;
}

// --- Includes and Initializations ---
require_once 'includes/db_connect.php';
$user_id = $_SESSION['user_id']; // Get current user's ID

$user_data = null;
$fetch_error = '';
$password_error = ''; // Holds errors from password change attempt
$password_success = ''; // Holds success message for password change

// --- Fetch User Data for Display ---
try {
    $sql = "SELECT username, email, role, created_at FROM users WHERE id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        // Should not happen if user is logged in, but handle just in case
        $fetch_error = "Could not retrieve your profile information.";
    }
} catch (PDOException $e) {
    // error_log("Profile Fetch Error: " . $e->getMessage());
    $fetch_error = "An error occurred while loading profile data.";
}


// --- Handle Password Change Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $errors = []; // Use local errors array for password change

    // Validation
    if (empty($current_password)) $errors[] = "Current Password is required.";
    if (empty($new_password)) $errors[] = "New Password is required.";
    elseif (strlen($new_password) < 6) $errors[] = "New Password must be at least 6 characters long.";
    if ($new_password !== $confirm_password) $errors[] = "New passwords do not match.";

    // If basic validation passes, verify current password
    if (empty($errors)) {
        try {
            // Fetch current password hash
            $sql_pass = "SELECT password_hash FROM users WHERE id = ? LIMIT 1";
            $stmt_pass = $pdo->prepare($sql_pass);
            $stmt_pass->execute([$user_id]);
            $result = $stmt_pass->fetch(PDO::FETCH_ASSOC);

            if ($result && password_verify($current_password, $result['password_hash'])) {
                // Current password is correct, proceed to update

                // Hash the new password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                if ($new_password_hash === false) {
                    $errors[] = "Failed to hash the new password.";
                } else {
                    // Update the password in the database
                    $sql_update = "UPDATE users SET password_hash = ? WHERE id = ?";
                    $stmt_update = $pdo->prepare($sql_update);
                    if ($stmt_update->execute([$new_password_hash, $user_id])) {
                        $password_success = "Password updated successfully!";
                        // Optionally: Force logout or update session if needed,
                        // but usually just showing success is fine.
                    } else {
                        $errors[] = "Database error: Could not update password.";
                    }
                }

            } else {
                // Current password verification failed
                $errors[] = "Incorrect Current Password.";
            }

        } catch (PDOException $e) {
            // error_log("Password Change Error: " . $e->getMessage());
            $errors[] = "A database error occurred during password change.";
        }
    }
    // If errors occurred, store them for display
    if(!empty($errors)) {
        $password_error = implode('<br>', $errors);
    }

} // End POST handling

// --- Include Header ---
include 'includes/header.php';
?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">

            <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-3">Your Profile</h1>

            <?php if ($fetch_error): ?>
                <div class="notice-message notice-error" role="alert">
                    <?php echo htmlspecialchars($fetch_error); ?>
                </div>
            <?php elseif ($user_data): ?>
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Account Details</h2>
                    <div class="space-y-3 text-gray-600">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user_data['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                        <p><strong>Role:</strong>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $user_data['role'] === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo ucfirst(htmlspecialchars($user_data['role'])); ?>
                        </span>
                        </p>
                        <p><strong>Member Since:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($user_data['created_at']))); ?></p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Change Password</h2>

                    <?php if ($password_success): ?>
                        <div class="notice-message notice-success mb-4" role="alert">
                            <?php echo htmlspecialchars($password_success); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($password_error): ?>
                        <div class="notice-message notice-error mb-4" role="alert">
                            <?php echo $password_error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST" novalidate>
                        <div class="mb-4">
                            <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required
                                   class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div class="mb-4">
                            <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password (min 6 chars)</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6"
                                   class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div class="mb-6">
                            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" name="change_password"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 transition duration-150 ease-in-out">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

            <?php endif; // End if $user_data ?>

        </div> </div> <?php
// Include footer
include 'includes/footer.php';
?>