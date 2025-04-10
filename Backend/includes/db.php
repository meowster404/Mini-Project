<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'farm_fresh_market';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . mysqli_connect_error()
    ]));
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");
?>