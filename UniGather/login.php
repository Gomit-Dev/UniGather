<?php
session_start(); // Start session FIRST

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/db_connect.php'; // Include DB connection AFTER potential redirect
$error = '';
$identifier = ''; // Initialize for sticky form field

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // Can be username or email
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = "Username/Email and Password are required.";
    } else {
        try {
            // Prepare statement to fetch user by username OR email
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1"); // Added LIMIT 1
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Password matches - Login successful
                session_regenerate_id(true); // Regenerate session ID for security

                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect to the dashboard
                header('Location: dashboard.php');
                exit; // Crucial!
            } else {
                // Invalid credentials (user not found or password mismatch)
                $error = "Invalid username/email or password.";
            }
        } catch (PDOException $e) {
            // error_log("Login DB Error: " . $e->getMessage()); // Log actual error
            $error = "An error occurred during login. Please try again."; // User-friendly
        }
    }
}

// Include header AFTER processing form and potential redirect attempts

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
<main class="container mx-auto h-[100vh] flex items-center justify-center ">
        <div class="max-w-md mx-auto bg-gradient-to-t from-lime-600 to-teal-700 p-8 rounded-tl-full rounded-br-full shadow-md item-center justify-center container my-auto">
            <h1 class="text-2xl font-bold mb-6 text-center text-white">Login</h1>

            <?php if ($error): ?>
                <div class="notice-message notice-error" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" novalidate>
                <div class="mb-4">
                    <label for="identifier" class="block text-gray-700 text-white text-sm font-bold mb-2">Username or Email</label>
                    <input type="text" id="identifier" name="identifier" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($identifier); ?>">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm text-white font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Sign In
                    </button>
                    <a class="inline-block align-baseline font-bold text-sm text-blue-500 text-white hover:text-cyan-300" href="signup.php">
                        Don't have an account?
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
<script src="js/main.js"></script>
</body>
</html>