// For Meatball Menu
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript loaded');
    const meatballMenuBtns = document.querySelectorAll('.meatball-menu-btn');
    meatballMenuBtns.forEach(function(meatballMenuBtn) {
        console.log('Attaching event to:', meatballMenuBtn); // Check if listeners are being attached
        meatballMenuBtn.addEventListener('click', function(event) {
            console.log('Meatball menu button clicked:', meatballMenuBtn); // Log click event
            // Close any open menus first
            document.querySelectorAll('.meatball-menu-container').forEach(function(container) {
                if (container !== meatballMenuBtn.parentElement) {
                    container.classList.remove('show');
                }
            });

            // Toggle the clicked menu
            const meatballMenuContainer = meatballMenuBtn.parentElement;
            meatballMenuContainer.classList.toggle('show');

            // Stop the event from bubbling up to the document
            event.stopPropagation();
        });
    });

    // Close the menu if clicked outside
    document.addEventListener('click', function(event) {
        document.querySelectorAll('.meatball-menu-container').forEach(function(container) {
            if (!container.contains(event.target)) {
                container.classList.remove('show');
            }
        });
    });
});