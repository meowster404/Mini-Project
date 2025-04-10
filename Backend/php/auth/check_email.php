<?php
require_once(__DIR__ . '/../includes/db.php');

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $userType = filter_var($data['userType'], FILTER_SANITIZE_STRING);
    
    // Check in both tables
    $exists = false;
    $message = '';
    
    // Check farmers table
    $stmt = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $exists = true;
        $message = 'Email already registered as a farmer';
    }
    
    // Check consumers table
    $stmt = $conn->prepare("SELECT id FROM consumers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $exists = true;
        $message = 'Email already registered as a consumer';
    }
    
    echo json_encode([
        'exists' => $exists,
        'message' => $message
    ]);
}