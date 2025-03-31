<?php
session_start();

// Check if the user selected "Remember Me"
if (isset($_SESSION['remember_me']) && $_SESSION['remember_me'] === true) {
    // Check if the session timestamp exists
    if (isset($_SESSION['login_time'])) {
        $three_months = 90 * 24 * 60 * 60; // 3 months in seconds
        
        if (time() - $_SESSION['login_time'] > $three_months) {
            // Session expired, destroy it and logout
            session_destroy();
            header("location:/index.php"); // Redirect to index.php after logout
            exit();
        }
    } else {
        // If login_time is not set, log out as a precaution
        session_destroy();
        header("location:/index.php");
        exit();
    }
}
