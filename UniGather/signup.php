<?php
session_start(); // Start session FIRST

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/db_connect.php'; // Include DB connection AFTER potential redirect
$errors = [];
$success_message = '';
$username = ''; // Initialize for sticky form
$email = ''; // Initialize for sticky form

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Basic Validation ---
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email)) $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($password)) $errors[] = "Password is required.";
    elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    // --- Check if username or email already exists ---
    if (empty($errors)) {
        try {
            // Check username
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = "Username already taken.";
            }

            // Check email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email already registered.";
            }

        } catch (PDOException $e) {
            // error_log("Signup Check DB Error: " . $e->getMessage()); // Log actual error
            $errors[] = "An error occurred during signup checks. Please try again."; // User-friendly message
        }
    }


    // --- Process Signup ---
    if (empty($errors)) {
        // Hash the password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if ($password_hash === false) {
            $errors[] = "Failed to hash password."; // Should not happen usually
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'user')"); // Default role 'user'
                if ($stmt->execute([$username, $email, $password_hash])) {
                    $success_message = "Signup successful! You can now <a href='login.php' class='text-blue-600 hover:underline font-bold'>login</a>.";
                    // Clear form fields on success
                    $username = '';
                    $email = '';
                } else {
                    $errors[] = "Signup failed during insertion. Please try again.";
                }
            } catch (PDOException $e) {
                // error_log("Signup Insert DB Error: " . $e->getMessage()); // Log actual error
                $errors[] = "An error occurred saving your details. Please try again later."; // User-friendly
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Event Calendar</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <style>
        /* Add minor custom styles if needed */
        body { font-family: sans-serif; }
        /* Basic styling for notice/error messages if needed */
        .notice-message { padding: 1rem; margin-bottom: 1rem; border-radius: 0.25rem; }
        .notice-success { background-color: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
        .notice-error { background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
    </style>
</head>
<body class="bg-gray-100">

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <a href="add_event.php" class="hover:bg-blue-700 px-3 py-2 rounded">Add Event</a>
<?php endif; // End admin check for Add Event link ?>
<div class="bg-[url('img/bg-img.jpg')] bg-no-repeat">
<main class="container mx-auto h-[100vh] flex items-center justify-center  ">
    <div class="max-w-md mx-auto bg-gradient-to-t from-lime-600 to-teal-700 p-8 rounded-tl-full rounded-br-full shadow-md item-center justify-center container my-auto">
        <h1 class="text-2xl font-bold mb-6 text-center text-white">Sign Up</h1>

        <?php if (!empty($errors)): ?>
            <div class="notice-message notice-error" role="alert">
                <strong class="font-bold">Errors:</strong>
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="notice-message notice-success" role="alert">
                <?php echo $success_message; // Contains HTML link, so no htmlspecialchars here ?>
            </div>
        <?php else: // Only show form if no success message ?>
            <form action="signup.php" method="POST" novalidate>
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2 text-white">Username</label>
                    <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($username); ?>">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2 text-white">Email</label>
                    <input type="email" id="email" name="email" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2 text-white">Password (min 6 chars)</label>
                    <input type="password" id="password" name="password" required minlength="6" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2 text-white">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Sign Up
                    </button>
                    <a class="inline-block align-baseline font-bold text-sm text-white  hover:text-cyan-300" href="login.php">
                        Already have an account?
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>
</div>

<script src="js/main.js"></script>
</body>
</html>