<?php
// Start session
session_start();

// Include database connection
require_once 'db_connect.php';

// Set header to return JSON response
header('Content-Type: application/json');

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $user_type = sanitize_input($_POST['user_type']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password']; // Don't sanitize password
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }
    
    // Determine which table to query based on user type
    $table = ($user_type === 'farmer') ? 'farmers' : 'consumers';
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user_type;
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user_type === 'farmer' ? $user['name'] : $user['full_name'];
            
            // Set remember me cookie if requested
            if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 days
                
                // Store token in database
                $stmt = $conn->prepare("UPDATE $table SET remember_token = ? WHERE id = ?");
                $stmt->bind_param("si", $token, $user['id']);
                $stmt->execute();
                
                // Set cookie
                setcookie('remember_token', $token, $expires, '/');
                setcookie('user_type', $user_type, $expires, '/');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful! Redirecting...',
                'redirect' => 'product.html'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email or password.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password.'
        ]);
    }
    
    $stmt->close();
}

// Handle signup request
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    $user_type = sanitize_input($_POST['user_type']);
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password']; // Don't sanitize password
    $confirm_password = $_POST['confirm_password']; // Don't sanitize password
    
    // Additional fields for farmers
    $farm_name = isset($_POST['farm_name']) ? sanitize_input($_POST['farm_name']) : '';
    $farm_address = isset($_POST['farm_address']) ? sanitize_input($_POST['farm_address']) : '';
    
    // Validate input
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ]);
        exit;
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match.'
        ]);
        exit;
    }
    
    // Check password strength
    if (strlen($password) < 8) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters long.'
        ]);
        exit;
    }
    
    // Determine which table to query based on user type
    $table = ($user_type === 'farmer') ? 'farmers' : 'consumers';
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists. Please use a different email or login.'
        ]);
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user data into database
    if ($user_type === 'farmer') {
        // Validate farmer-specific fields
        if (empty($farm_name) || empty($farm_address)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please fill in all required fields for farmer registration.'
            ]);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO farmers (name, email, phone, password, farm_name, farm_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $full_name, $email, $phone, $hashed_password, $farm_name, $farm_address);
    } else {
        $stmt = $conn->prepare("INSERT INTO consumers (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);
    }
    
    if ($stmt->execute()) {
        // Get the ID of the newly created user
        $user_id = $conn->insert_id;
        
        // Create session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $full_name;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Redirecting...',
            'redirect' => 'product.html'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed. Please try again later.'
        ]);
    }
    
    $stmt->close();
}

// Handle other requests
else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
}

// Close database connection
$conn->close();
?>
