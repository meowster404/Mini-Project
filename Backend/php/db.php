<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecokart"; // Standardized database name

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db($dbname);
    $conn->set_charset("utf8mb4"); // Better character encoding support

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>