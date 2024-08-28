document.addEventListener('DOMContentLoaded', function() {
    const meatballMenuBtn = document.querySelector('.meatball-menu-btn');
    const meatballMenuContainer = document.querySelector('.meatball-menu-container');

    meatballMenuBtn.addEventListener('click', function() {
        meatballMenuContainer.classList.toggle('show');
    });

    // Close the menu if clicked outside
    document.addEventListener('click', function(event) {
        if (!meatballMenuContainer.contains(event.target)) {
            meatballMenuContainer.classList.remove('show');
        }
    });
});