document.addEventListener('DOMContentLoaded', async function() {
    const menuAuthLinks = document.querySelector('.menu-auth-links');

    // Check authentication status and update menu
    function updateMenuVisibility() {
        const auth = checkAuth();
        if (!auth.isAuthenticated) {
            if (menuAuthLinks) menuAuthLinks.style.display = 'flex';
        } else {
            if (menuAuthLinks) menuAuthLinks.style.display = 'none';
        }
    }

    // Initial check
    updateMenuVisibility();

    // Periodically check authentication status
    setInterval(updateMenuVisibility, 30000);
});
