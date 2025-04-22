<?php
session_start();

// If user is logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit;
}
?>