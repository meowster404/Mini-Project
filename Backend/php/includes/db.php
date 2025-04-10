<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_fresh_market";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set character set
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error loading character set utf8mb4: " . $conn->error);
    }

    // Set error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
} catch (Exception $e) {
    // Log error and display user-friendly message
    error_log("Database Error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}
?>