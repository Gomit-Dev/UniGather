<?php
session_start(); // Start session FIRST (if header/footer rely on session)
// Optional: Include DB if header/footer need it, otherwise not needed for static page
// require_once 'includes/db_connect.php';

// Include header
include 'includes/header.php';
?>

    <div class="container mx-auto px-4 py-8"> <div class="flex flex-col md:flex-row md:space-x-8">

            <aside class="w-full md:w-1/4 mb-8 md:mb-0">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">● Help</h3>
                <nav class="space-y-2">
                    <div><a href="terms.php" class="text-gray-600 hover:text-blue-600">Terms of Service</a></div> <div><a href="privacy.php" class="text-gray-600 hover:text-blue-600">Privacy Policy</a></div> <div><a href="contact.php" class="text-blue-600 font-semibold">Contact Us</a></div> </nav>
            </aside>

            <section class="w-full md:w-3/4">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">CONTACT US</h1>

                <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg mb-6 shadow-sm">
                    <p class="text-gray-700 mb-3">
                        Whether you're an event seeker or an event organizer, we are here to help you with all your queries.
                    </p>
                    <p class="text-gray-700 mb-4">
                        Please visit our Support Center to let us help you resolve your query.
                    </p>
                    <a href="#support-center" class="inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition-colors duration-200">
                        Visit Support Center
                    </a> </div>

                <p class="text-gray-600 mb-8 text-sm">
                    Don't find a resolution in the Support Center or just want to connect with us? Get in touch.
                </p>

                <div class="flex flex-col md:flex-row gap-6">

                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 flex-1">
                        <h4 class="font-semibold text-gray-700 mb-3">SNAIL MAIL</h4>
                        <address class="text-gray-600 text-sm leading-relaxed not-italic">
                            1402, Capestone, Chirag Motors Cross Roads,<br>
                            Near Parimal Garden,<br>
                            Ellisbridge,<br>
                            Ahmedabad, Gujarat 380006
                        </address>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 flex-1">
                        <h4 class="font-semibold text-gray-700 mb-3">REGISTERED OFFICE</h4>
                        <address class="text-gray-600 text-sm leading-relaxed not-italic">
                            1402, Capestone, Chirag Motors Cross Roads,<br>
                            Near Parimal Garden,<br>
                            Ellisbridge,<br>
                            Ahmedabad, Gujarat 380006
                        </address>
                    </div>

                </div>
            </section>

        </div>
    </div>

<?php
// Include footer
include 'includes/footer.php';
?>