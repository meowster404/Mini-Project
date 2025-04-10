<?php
session_start();
require_once(__DIR__ . '/../includes/db.php');

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $fullname = filter_var($data['fullName'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $phone = filter_var($data['phone'], FILTER_SANITIZE_STRING);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $userType = filter_var($data['userType'], FILTER_SANITIZE_STRING);
        
        if ($userType === 'farmer') {
            $farmName = filter_var($data['farmName'], FILTER_SANITIZE_STRING);
            $farmAddress = filter_var($data['farmAddress'], FILTER_SANITIZE_STRING);
        }
    }
    
    // Check email existence in both tables
    if ($userType === 'farmer') {
        $stmt = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT id FROM consumers WHERE email = ?");
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists'
        ]);
        exit;
    }
    
    // Insert new user based on user type
    if ($userType === 'farmer') {
        $stmt = $conn->prepare("INSERT INTO farmers (name, email, phone, password, farm_name, farm_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $fullname, $email, $phone, $password, $farmName, $farmAddress);
    } else {
        $stmt = $conn->prepare("INSERT INTO consumers (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $email, $phone, $password);
    }
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'userId' => $userId,
            'userType' => $userType
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating account: ' . $conn->error
        ]);
    }
}
