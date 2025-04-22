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

// Fetch events from the database
$events = [];
try {
    // Join with users table to get creator's username
    $stmt = $pdo->query("SELECT e.*, u.username as creator_username
                         FROM events e
                         JOIN users u ON e.created_by_user_id = u.id
                         ORDER BY e.event_date ASC, e.event_time ASC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // error_log("Dashboard Event Fetch Error: " . $e->getMessage()); // Log error
    $dashboard_error = "Could not load events. Please try again later."; // User-friendly message
}

// Include header AFTER session check and DB include
include 'includes/header.php';

// Display potential dashboard errors
if (isset($dashboard_error)): ?>
    <div class="notice-message notice-error" role="alert">
        <?php echo htmlspecialchars($dashboard_error); ?>
    </div>
<?php endif; ?>

<!-- Background Video -->
<div class="relative h-screen overflow-hidden">
    <video autoplay muted loop class="absolute w-full h-full object-cover z-0">
        <source src="https://cdn-az.allevents.in/events/banners/bg-video.mp4" type="video/mp4" />
        Your browser does not support the video tag.
    </video>
    <div class="absolute inset-0 bg-black opacity-50 z-10"></div>
    <div class="relative z-20 flex items-center justify-center h-full text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold leading-tight">Making The World<br><span class="text-blue-400">#Happening</span></h1>
    </div>
</div>

<!-- Additional Content -->
<section class="max-w-4xl mx-auto px-6 py-12 space-y-6">
    <h2 class="text-2xl font-semibold">We reimagine how you discover events</h2>

    <div class="space-y-4 text-gray-700">
        <p>Events are our pulse, the rhythm that drives us.</p>
        <p>
            AllEvents is now one of the <strong>world’s largest Event Discovery Platforms</strong>. Our mission is to turn ordinary days into extraordinary memories.
        </p>
        <p>
            Whether you want to be thrilled, inspired, or connected, we bring local events to your fingertips. We make your moments #Happening.
        </p>
        <p>
            As the ticketing partner for millions of events, we empower organizers with seamless tools to promote and manage events globally.
        </p>
    </div>
</section>

<section class="w-full overflow-hidden bg-blue-50 py-12">
    <h2 class="text-4xl font-bold text-center mb-10">Values that drive us!</h2>

    <!-- Carousel Slides (static layout for now) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto px-4">
        <!-- Slide -->
        <div class="flex flex-col items-center text-center">
            <img src="https://cdn2.allevents.in/transup/f2/77d0a5736b43cc8169398b23d49c2d/StayHappening.png" alt="Stay Happening" class="mb-4 max-h-48">
            <h3 class="text-xl font-semibold mb-2">We promise to #StayHappening</h3>
            <p class="text-gray-600 mb-1">We don't just preach it, we embody it!</p>
            <p class="text-gray-600 mb-1">Our spirit of staying happening helps make every day special for event explorers and organizers worldwide.</p>
        </div>

        <div class="flex flex-col items-center text-center">
            <img src="https://cdn2.allevents.in/transup/77/1ba32c365644d49c16d6cc2bfd15c2/Inclusivity.webp" alt="Inclusivity" class="mb-4 max-h-48">
            <h3 class="text-xl font-semibold mb-2">Inclusivity is a common practice</h3>
            <p class="text-gray-600 mb-1">We strive to ensure all types of events are represented.</p>
            <p class="text-gray-600 mb-1">Inclusivity is key — without room for hate.</p>
        </div>

        <div class="flex flex-col items-center text-center">
            <img src="https://cdn2.allevents.in/transup/ed/2ddb85cad040fab378297a743f4776/Innovation.webp" alt="Innovation" class="mb-4 max-h-48">
            <h3 class="text-xl font-semibold mb-2">Innovation is our constant companion</h3>
            <p class="text-gray-600 mb-1">We push boundaries, explore ideas, and redefine possibilities.</p>
            <p class="text-gray-600 mb-1">Innovation drives our journey forward.</p>
        </div>

        <div class="flex flex-col items-center text-center">
            <img src="https://cdn2.allevents.in/transup/7f/2872f022ba42f4b4a037b7ff544537/Community.png" alt="Community" class="mb-4 max-h-48">
            <h3 class="text-xl font-semibold mb-2">Fostering a sense of community</h3>
            <p class="text-gray-600 mb-1">Our explorers and organizers make up our success.</p>
            <p class="text-gray-600 mb-1">We ensure their journey with AllEvents is seamless and supportive.</p>
        </div>
    </div>
</section>

<!-- Join Us Section -->
<section class="bg-gray-100 py-12 text-center">
    <h2 class="text-2xl font-bold mb-2">Join the Revolution</h2>
    <p class="text-gray-700 mb-4">We’re always looking for creative, dynamic, and #happening people!</p>
    <a href="dashboard.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Join Us</a>
</section>


<!-- Meet the Team -->
<section class="bg-gray-50 py-12">
    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold">Meet our team</h2>
        <p class="text-gray-600">Dedicated professionals driven to bring ideas to life every day.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 max-w-6xl mx-auto px-6">
        <!-- Team Member -->
        <div class="text-center">
            <img src="https://cdn2.allevents.in/transup/be/3f5c8e10e841379542bcfc997e0f5c/Prateek_Aboutus.jpg" alt="Prateek Birla" class="w-full h-64 object-cover rounded-lg mb-4" />
            <h3 class="text-lg font-semibold">Prateek Birla</h3>
            <p class="text-sm text-gray-500">Frontend Devloper</p>
            <a href="https://www.linkedin.com/in/birlaprateek/" target="_blank" class="text-blue-500 hover:underline text-sm">LinkedIn</a>
        </div>

        <div class="text-center">
            <img src="https://cdn2.allevents.in/transup/4e/dee0fa44234312905af064d2cd5314/Vijay_About_us.jpg" alt="Vijay Chauhan" class="w-full h-64 object-cover rounded-lg mb-4" />
            <h3 class="text-lg font-semibold">Vijay Chauhan</h3>
            <p class="text-sm text-gray-500">Frontend Devloper</p>
            <a href="https://www.linkedin.com/in/vijaychauhanseo/" target="_blank" class="text-blue-500 hover:underline text-sm">LinkedIn</a>
        </div>

        <div class="text-center">
            <img src="https://cdn2.allevents.in/transup/ae/806d0c8d3742c993b8e44a9e3f18b9/Paras_About_us.jpg" alt="Paras Makhija" class="w-full h-64 object-cover rounded-lg mb-4" />
            <h3 class="text-lg font-semibold">Backend Devloper</h3>
            <p class="text-sm text-gray-500">Backend Devloper</p>
            <a href="https://www.linkedin.com/in/parasmakhija/" target="_blank" class="text-blue-500 hover:underline text-sm">LinkedIn</a>
        </div>

        <div class="text-center">
            <img src="https://cdn2.allevents.in/transup/68/046544dad64d56b4c09bf5c4b69f35/Dev-Patel_Aboutus.jpg" alt="Dev Patel" class="w-full h-64 object-cover rounded-lg mb-4" />
            <h3 class="text-lg font-semibold">Dev Patel</h3>
            <p class="text-sm text-gray-500">Backend Devloper</p>
            <a href="https://www.linkedin.com/in/ddevvedd/" target="_blank" class="text-blue-500 hover:underline text-sm">LinkedIn</a>
        </div>
    </div>
</section>

</body>
</html>
