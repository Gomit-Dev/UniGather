// Simple confirmation for delete links
// You might want more sophisticated JS, maybe using event delegation

document.addEventListener('DOMContentLoaded', () => {
    const deleteLinks = document.querySelectorAll('a[href^="delete_event.php"]');

    deleteLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            // Use the confirmation message from the link's onclick attribute
            // or add a default one here if the onclick is removed from HTML
            // if (!confirm('Are you sure you want to delete this event?')) {
            //     event.preventDefault();
            // }
            // Note: The current PHP code includes onclick directly in the HTML,
            // so this JS example is slightly redundant unless you remove the inline onclick.
        });
    });

    // You could add more JS here for:
    // - Frontend form validation
    // - AJAX calls to add/edit/delete events without full page reloads
    // - Integrating a JS calendar library (like FullCalendar)
});