document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".shop-button").addEventListener("click", function (event) {
        event.preventDefault(); // Prevent default link behavior

        // Simulating session check (Replace this with actual session logic)
        let userSession = sessionStorage.getItem("userLoggedIn");

        if (userSession === "true") {
            window.location.href = "product-page.php"; // Redirect to product page
        } else {
            window.location.href = "login-signup.php"; // Redirect to login/signup page
        }
    });
});
