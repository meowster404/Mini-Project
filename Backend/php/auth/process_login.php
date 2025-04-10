<?php
session_start();
require_once(__DIR__ . '/../includes/db.php');

// Set session lifetime to 2 weeks
ini_set('session.gc_maxlifetime', 14 * 24 * 60 * 60); // 2 weeks in seconds
session_set_cookie_params(14 * 24 * 60 * 60);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!isset($data['email']) || !isset($data['password']) || !isset($data['userType'])) {
                throw new Exception('Missing required fields');
            }
            
            $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            $password = $data['password'];
            $userType = filter_var($data['userType'], FILTER_SANITIZE_STRING);
        }
        
    // First check if email exists in the opposite table
    $oppositeTable = $userType === 'farmer' ? 'consumers' : 'farmers';
    $stmt = $conn->prepare("SELECT 1 FROM $oppositeTable WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => "This email is registered as a " . ($userType === 'farmer' ? 'consumer' : 'farmer')
        ]);
        exit;
    }
    
    // Check user type and query appropriate table
    if ($userType === 'farmer') {
        $stmt = $conn->prepare("SELECT id, password, name as full_name FROM farmers WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, password, full_name FROM consumers WHERE email = ?");
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_type'] = $userType;
            $_SESSION['last_activity'] = time();
            $_SESSION['expires'] = time() + (14 * 24 * 60 * 60); // 2 weeks from now
            
            if ($userType === 'farmer') {
                $_SESSION['farmer_id'] = $user['id'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['full_name'],
                    'type' => $userType
                ],
                'token' => bin2hex(random_bytes(16)),
                'expires' => $_SESSION['expires']
            ]);
            exit;
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
    
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}