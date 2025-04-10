document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".shop-button").addEventListener("click", function (event) {
        event.preventDefault();

        // Check if user is logged in
        let userSession = localStorage.getItem("user");
        let userType = localStorage.getItem("userType");

        if (userSession) {
            // If user is logged in, redirect to products page
            window.location.href = "products.html";
        } else {
            // If user is not logged in, redirect to auth page
            window.location.href = "auth.html";
        }
    });
});
