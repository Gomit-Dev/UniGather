<?php
session_start(); // Start session FIRST
include 'includes/header.php';
?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:space-x-8">

            <aside class="w-full md:w-1/4 mb-8 md:mb-0 flex-shrink-0">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">● Help</h3>
                <nav class="space-y-2">
                    <div><a href="terms.php" class="text-gray-600 hover:text-blue-600">Terms of Service</a></div>
                    <div><a href="privacy.php" class="text-blue-600 font-semibold">Privacy Policy</a></div> <div><a href="contact.php" class="text-gray-600 hover:text-blue-600">Contact Us</a></div>
                    <div><a href="support.php" class="text-gray-600 hover:text-blue-600">Support Center</a></div>
                </nav>
            </aside>

            <section class="w-full md:w-3/4 bg-white p-8 shadow-md rounded-lg">
                <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-4">Privacy Policy</h1>
                <p class="mb-4 text-sm text-gray-500">Last updated: <?php echo date('F j, Y'); ?></p>

                <div class="prose max-w-none text-gray-600 leading-relaxed">
                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">1. Introduction</h2>
                    <p>[PLACEHOLDER CONTENT - REPLACE WITH YOUR ACTUAL POLICY]</p>
                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">2. Information We Collect</h2>
                    <p>[PLACEHOLDER CONTENT]</p>
                    <ul><li>[Detail types of data]</li></ul>
                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">3. How We Use Your Information</h2>
                    <p>[PLACEHOLDER CONTENT]</p>
                    <ul><li>[Detail uses]</li></ul>
                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">7. Contact Us</h2>
                    <p>Contact us via the <a href="contact.php" class="text-blue-600 hover:underline">Contact Us page</a>.</p>
                </div>

            </section>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>