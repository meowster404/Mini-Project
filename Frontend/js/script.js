document.addEventListener("DOMContentLoaded", function () {
    // Scroll to the top (Home) on refresh
    if (window.location.hash) {
        history.replaceState(null, null, " "); // Remove hash from URL
    }
    window.scrollTo(0, 0);

    // Navigation scroll effect
    const header = document.getElementById("header");
    window.addEventListener("scroll", function () {
        if (window.scrollY > 50) {
            header.classList.add("scrolled");
        } else {
            header.classList.remove("scrolled");
        }
    });

    // Mobile menu toggle
    const menuToggle = document.getElementById("menu-toggle");
    const navLinks = document.getElementById("mobile-menu");

    menuToggle.addEventListener("change", function () {
        if (this.checked) {
            navLinks.classList.add("active");
        } else {
            navLinks.classList.remove("active");
        }
    });

    // Close menu when clicking a link (for mobile)
    document.querySelectorAll(".nav-links a").forEach(link => {
        link.addEventListener("click", () => {
            if (menuToggle.checked) {
                menuToggle.checked = false;
                navLinks.classList.remove("active");
            }
        });
    });
});
