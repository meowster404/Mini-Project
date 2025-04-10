<?php
session_start();
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

// Password validation function
function validatePassword($password) {
    // At least 8 characters, one uppercase, one lowercase, one number, one special char
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $special   = preg_match('@[^\w]@', $password);
    
    if (!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
        return false;
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    // Accept both JSON and form data
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    } else if (strpos($contentType, 'application/x-www-form-urlencoded') !== false || 
               strpos($contentType, 'multipart/form-data') !== false) {
        $data = $_POST;
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid content type'
        ]);
        exit;
    }
    
    // Check if required fields are present
    if (!$data || !isset($data['fullName']) || !isset($data['email']) || 
        !isset($data['phone']) || !isset($data['password']) || !isset($data['userType'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }
    
    $fullname = filter_var($data['fullName'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($data['phone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $data['password'];
    $userType = filter_var($data['userType'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }
    
    // Validate user type
    if (!in_array($userType, ['farmer', 'consumer'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid user type'
        ]);
        exit;
    }
    
    // Validate password strength
    if (!validatePassword($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character'
        ]);
        exit;
    }
    
    if ($userType === 'farmer') {
        if (!isset($data['farmName']) || !isset($data['farmAddress'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Farm details are required for farmer accounts'
            ]);
            exit;
        }
        $farmName = filter_var($data['farmName'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $farmAddress = filter_var($data['farmAddress'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
    
    // Hash password securely
    $passwordHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid content type'
    ]);
    exit;
}
    
    // Check email existence in both tables
    $stmt = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists'
        ]);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT id FROM consumers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists'
        ]);
        exit;
    }
    
    // Begin transaction for data integrity
    $conn->begin_transaction();
    
    try {
        // Insert new user based on user type
        if ($userType === 'farmer') {
            $stmt = $conn->prepare("INSERT INTO farmers (name, email, phone, password, farm_name, farm_address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fullname, $email, $phone, $passwordHash, $farmName, $farmAddress);
        } else {
            $stmt = $conn->prepare("INSERT INTO consumers (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $email, $phone, $passwordHash);
        }
        
        if ($stmt->execute()) {
            $userId = $conn->insert_id;
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'userId' => $userId,
                'userType' => $userType
            ]);
        } else {
            throw new Exception("Error executing query");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error creating account. Please try again.'
        ]);
    }
?>