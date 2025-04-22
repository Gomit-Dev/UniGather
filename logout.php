<?php
session_start(); // Need to start session to destroy it

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie (best practice)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to login page
header("Location: login.php?status=logged_out"); // Add a status message if desired
exit;
?>