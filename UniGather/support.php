<?php
session_start(); // Start session FIRST
include 'includes/header.php';

// Placeholder data for categories (In reality, you'd fetch this)
$collections = [
    [
        'id' => 1, 'title' => 'Event Organizer', 'href' => '#collection-organizer',
        'categories' => [
            ['id' => 185, 'title' => 'About AllEvents', 'desc' => '', 'count' => 1, 'href' => '#cat-185'],
            ['id' => 97, 'title' => 'FAQs', 'desc' => 'Frequently asked common questions...', 'count' => 11, 'href' => '#cat-97'],
            ['id' => 155, 'title' => 'Dashboard', 'desc' => '"Centralized tools..."', 'count' => 4, 'href' => '#cat-155'],
            ['id' => 10, 'title' => 'Event', 'desc' => '"From creating your first event..."', 'count' => 21, 'href' => '#cat-10'],
        ]
    ],
    [
        'id' => 4, 'title' => 'Event Attendee', 'href' => '#collection-attendee',
        'categories' => [
            ['id' => 86, 'title' => 'FAQs', 'desc' => 'All the frequently asked questions...', 'count' => 2, 'href' => '#cat-86'],
            ['id' => 12, 'title' => 'Event', 'desc' => 'Everything from exploring nearby events...', 'count' => 5, 'href' => '#cat-12'],
            ['id' => 80, 'title' => 'Ticket', 'desc' => 'Everything related to ticket...', 'count' => 3, 'href' => '#cat-80'],
        ]
    ]
];
?>

    <section class="bg-gradient-to-r from-blue-500 to-blue-600 py-12 text-white mb-10">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Support Center</h1>
            <form action="search_results.php" method="GET" class="max-w-xl mx-auto" autocomplete="off">
                <div class="flex items-center border-2 border-white rounded-full bg-white/20 p-1">
                    <input type="text" name="query" class="search-query appearance-none bg-transparent border-none w-full text-white placeholder-white/70 px-4 py-2 leading-tight focus:outline-none" placeholder="Search help articles…" aria-label="Search help articles" value="">
                    <button type="submit" aria-label="Search" class="bg-white text-blue-600 rounded-full p-2 m-1 hover:bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <div class="container mx-auto px-4 py-8">

        <?php if (empty($collections)): ?>
            <p class="text-center text-gray-500">No support collections found.</p>
        <?php else: ?>
            <?php foreach ($collections as $collection): ?>
                <section class="mb-12" id="collection-<?php echo $collection['id']; ?>">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-2">
                        <a href="<?php echo htmlspecialchars($collection['href']); ?>" class="hover:text-blue-600">
                            <?php echo htmlspecialchars($collection['title']); ?>
                        </a>
                    </h2>
                    <?php if (empty($collection['categories'])): ?>
                        <p class="text-gray-500">No categories in this collection yet.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($collection['categories'] as $category): ?>
                                <a href="<?php echo htmlspecialchars($category['href']); ?>" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 group" id="category-<?php echo $category['id']; ?>">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2 group-hover:text-blue-600"><?php echo htmlspecialchars($category['title']); ?></h3>
                                    <?php if (!empty($category['desc'])): ?><p class="text-sm text-gray-500 mb-3"><?php echo htmlspecialchars($category['desc']); ?></p><?php endif; ?>
                                    <p class="text-sm text-blue-500 group-hover:text-blue-700 font-medium"><?php echo $category['count']; ?> article<?php echo ($category['count'] !== 1 ? 's' : ''); ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

<?php include 'includes/footer.php'; ?>