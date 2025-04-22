<?php
// Session MUST be started in the main calling file (e.g., dashboard.php) BEFORE including this header.
// This file should NOT contain session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniGather Event Calendar</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Add minor custom styles if needed */
        body { font-family: sans-serif; }
        .notice-message { padding: 1rem; margin-bottom: 1rem; border-radius: 0.25rem; }
        .notice-success { background-color: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
        .notice-error { background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
        /* Basic scrollbar styling for category row if needed */
        .category-scroll::-webkit-scrollbar { height: 8px; }
        .category-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px;}
        .category-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px;}
        .category-scroll::-webkit-scrollbar-thumb:hover { background: #aaa; }
    </style>
</head>
<body class="bg-gray-100">
<nav class="bg-gradient-to-t from-blue-800 to-cyan-500 text-white p-4 shadow-md sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">

        <div class="flex-shrink-0">
            <a href="dashboard.php" class="text-2xl font-bold">UniGather</a>
        </div>

        <div class="flex space-x-3 md:space-x-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="hover:bg-white/20 px-3 py-2 rounded transition-colors duration-150">Dashboard</a>
                <a href="my_rsvps.php" class="hover:bg-white/20 px-3 py-2 rounded transition-colors duration-150">My RSVPs</a> <a href="profile.php" class="hover:bg-white/20 px-3 py-2 rounded transition-colors duration-150">Profile</a>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin_panel.php" class="hover:bg-white/20 px-3 py-2 rounded transition-colors duration-150">Admin Panel</a>
                    <a href="add_event.php" class="hover:bg-white/20 px-3 py-2 rounded transition-colors duration-150">Add Event</a>
                <?php endif; // End admin check ?>

            <?php else: ?>
                <a href="login.php" class="hover:bg-white/20 px-3 py-2 rounded transition-colors duration-150">Login</a>
                <a href="signup.php" class="hover:bg-white/20 px-3 py-2 rounded transition-colors duration-150">Sign Up</a>
            <?php endif; // End logged-in check ?>
        </div>

        <div class="flex items-center space-x-4 flex-shrink-0">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="hidden md:inline-block">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded transition-colors duration-150 text-sm font-medium">Logout</a>
            <?php else: ?>
                <span class="w-10 h-8"></span> <?php endif; // End logged-in check for right section ?>
        </div>

    </div>
</nav>
<main>