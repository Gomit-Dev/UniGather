<?php
session_start(); // Start session FIRST (if header/footer rely on session)
// Optional: DB connection if needed for header/footer
// require_once 'includes/db_connect.php';

// Include header
include 'includes/header.php';
?>

    <div class="container mx-auto px-4 py-8"> <div class="flex flex-col md:flex-row md:space-x-8">

            <aside class="w-full md:w-1/4 mb-8 md:mb-0 flex-shrink-0"> <h3 class="text-lg font-semibold text-gray-700 mb-4">● Help</h3>
                <nav class="space-y-2">
                    <div><a href="terms.php" class="text-blue-600 font-semibold">Terms of Service</a></div> <div><a href="privacy.php" class="text-gray-600 hover:text-blue-600">Privacy Policy</a></div> <div><a href="contact.php" class="text-gray-600 hover:text-blue-600">Contact Us</a></div> </nav>
            </aside>

            <section class="w-full md:w-3/4 bg-white p-8 shadow-md rounded-lg">
                <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-4">Terms and Conditions</h1>

                <p class="mb-4 text-sm text-gray-500">Last updated: <?php echo date('F j, Y'); // Or your actual last updated date ?></p>

                <div class="prose max-w-none text-gray-600 leading-relaxed"> <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">1. Acceptance of Terms</h2>
                    <p>
                        By accessing and using [Your Website Name] (the "Site"), you accept and agree... [Placeholder Text]
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">2. Description of Service</h2>
                    <p>
                        [Your Website Name] provides a platform for users to discover... [Placeholder Text]
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">3. User Registration Obligations</h2>
                    <p>
                        To use certain features of the Site, you may be required to register... [Placeholder Text]
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">4. User Conduct</h2>
                    <p>
                        You understand that all information... are the sole responsibility... You agree to not use the Service to:
                    </p>
                    <ul>
                        <li>Upload, post, email... unlawful, harmful...</li>
                        <li>Harm minors...</li>
                    </ul>

                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">5. Event Content and Responsibility</h2>
                    <p>
                        [Placeholder Text - Explain responsibilities re: events]
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">6. Disclaimers and Limitation of Liability</h2>
                    <p>
                        [Placeholder Text - Include standard disclaimers]
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">7. Modifications to Terms</h2>
                    <p>
                        We reserve the right, at our sole discretion, to modify... [Placeholder Text]
                    </p>

                    <h2 class="text-xl font-semibold text-gray-700 !mt-6 !mb-3">8. Governing Law</h2>
                    <p>
                        These Terms shall be governed and construed in accordance with the laws of India... [Placeholder Text]
                    </p>

                </div> </section>

        </div>
    </div>

<?php
// Include footer
include 'includes/footer.php';
?>