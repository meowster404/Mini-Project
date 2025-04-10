<?php
session_start();
require_once('../includes/db.php');

$userType = $_POST['userType'];
$email = $_POST['email'];
$password = $_POST['password'];

// Select from appropriate table based on user type
$table = ($userType === 'farmer') ? 'farmers' : 'consumers';
$nameField = ($userType === 'farmer') ? 'name' : 'full_name';

$sql = "SELECT * FROM $table WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $userType;
        $_SESSION['user_name'] = $user[$nameField];
        
        echo "success";
    } else {
        echo "Invalid password";
    }
} else {
    echo "User not found";
}

$stmt->close();
$conn->close();
?>