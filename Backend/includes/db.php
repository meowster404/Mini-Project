<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_fresh_market";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set character set
    if (!$conn->set_charset("utf8")) {
        throw new Exception("Error loading character set utf8: " . $conn->error);
    }
} catch (Exception $e) {
    // Log error and display user-friendly message
    error_log($e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}