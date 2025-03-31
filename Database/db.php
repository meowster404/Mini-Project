<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_fresh_market";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");
?>