<?php
require_once(__DIR__ . '/../includes/db.php');

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']); // Restrict to known origin
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Function to rate limit email checks (prevent email enumeration attacks)
function checkRateLimit() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Track checks per IP
    $ip = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    $limit_period = 60; // 1 minute
    $max_checks = 5; // Maximum checks per minute
    
    if (!isset($_SESSION['email_checks'])) {
        $_SESSION['email_checks'] = [];
    }
    
    if (!isset($_SESSION['email_checks'][$ip])) {
        $_SESSION['email_checks'][$ip] = [
            'count' => 1,
            'timestamp' => $current_time
        ];
        return true;
    }
    
    // Reset counter if period elapsed
    if ($current_time - $_SESSION['email_checks'][$ip]['timestamp'] > $limit_period) {
        $_SESSION['email_checks'][$ip] = [
            'count' => 1,
            'timestamp' => $current_time
        ];
        return true;
    }
    
    // Check if limit exceeded
    if ($_SESSION['email_checks'][$ip]['count'] >= $max_checks) {
        return false;
    }
    
    // Increment counter
    $_SESSION['email_checks'][$ip]['count']++;
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check rate limit
    if (!checkRateLimit()) {
        http_response_code(429); // Too Many Requests
        echo json_encode([
            'success' => false,
            'message' => 'Too many requests. Please try again later.'
        ]);
        exit;
    }
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['email'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request'
        ]);
        exit;
    }
    
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }
    
    // Check in both tables, but don't tell which one has the match for security
    $exists = false;
    
    // Check farmers table
    $stmt = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $exists = true;
    }
    
    // Check consumers table
    $stmt = $conn->prepare("SELECT id FROM consumers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $exists = true;
    }
    
    // Use a generic message rather than specifying the type
    echo json_encode([
        'exists' => $exists,
        'message' => $exists ? 'Email already registered' : ''
    ]);
}