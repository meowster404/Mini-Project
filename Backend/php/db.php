<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without database
$conn = mysqli_connect($servername, $username, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS farm_fresh_market";
if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
$dbname = "farm_fresh_market";
mysqli_select_db($conn, $dbname);

mysqli_set_charset($conn, "utf8");
?>