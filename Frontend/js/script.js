document.addEventListener("DOMContentLoaded", function () {
    // Scroll to the top (Home) on refresh
    if (window.location.hash) {
        history.replaceState(null, null, " ");
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
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.getElementById('nav-links');
    
    window.toggleMenu = function() {
        hamburger.classList.toggle('active');
        navLinks.classList.toggle('active');
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.hamburger-menu') && 
            !event.target.closest('.nav-links') && 
            navLinks.classList.contains('active')) {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
        }
    });

    // Close menu when clicking a link
    document.querySelectorAll(".nav-links a").forEach(link => {
        link.addEventListener("click", () => {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
        });
    });
});
